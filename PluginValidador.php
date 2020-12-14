<?php

namespace AldirBlanc;

use MapasCulturais\App;
use MapasCulturais\Entities\User;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\EvaluationMethodConfigurationAgentRelation;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;

/**
 * @property-read \MapasCulturais\Entities\User $user
 * @property-read string $slug
 * @property-read string $name
 * 
 * @package AldirBlanc
 */
abstract class PluginValidador extends \MapasCulturais\Plugin
{

    /**
     * Usuário validador
     *
     * @var MapasCulturais\Entities\User;
     */
    protected $_user = null;

    function __construct(array $config = [])
    {
        $slug = $this->getSlug();
        $config += [
            // se true, só considera a validação deste validador na consolidação
            'forcar_resultado' => false,

            // se true, só consolida se houver ao menos uma homologação
            'consolidacao_requer_homologacao' => true,
            
            // lista de validadores requeridos na consolidação
            'consolidacao_requer_validacoes' => (array) json_decode(env(strtoupper($slug) . '_CONSOLIDACAO_REQ_VALIDACOES', '[]')),
        ];
        parent::__construct($config);
    }

    /**
     * Inicializa o plugin
     * Cria o usuário avaliador se este ainda não existir
     *
     * @return void
     */
    function _init()
    {
        $app = App::i();

        $app->hook('slim.before', function () use ($app) {
            $this->createUserIfNotExists();
        });

        $plugin = $this;
        $user = $this->getUser();

        $app->hook('opportunity.registrations.reportCSV', function(\MapasCulturais\Entities\Opportunity $opportunity, $registrations, &$header, &$body) use($app, $user, $plugin) {
            $em = $opportunity->getEvaluationMethod();
            $_evaluations = $app->repo('RegistrationEvaluation')->findBy(['user' => $user, 'registration' => $registrations]);

            $evaluations_status = [];
            $evaluations_obs = [];
            foreach($_evaluations as $eval) {
                $evaluations_status[$eval->registration->number] = $em->valueToString($eval->result);
                $evaluations_obs[$eval->registration->number] = $eval->evaluationData->obs ?? json_encode($eval->evaluationData) ;
            }


            $header[] = $plugin->getName() . ' - status';
            $header[] = $plugin->getName() . ' - obs';
            
            foreach($body as $i => $line){
                $body[$i][] = $evaluations_status[$line[0]] ?? null;
                $body[$i][] = $evaluations_obs[$line[0]] ?? null;
            }
        });
        

        /**
         * @TODO: implementar para metodo de avaliação documental
         */
        $app->hook('entity(Registration).consolidateResult', function(&$result, $caller) use($plugin, $app) {
            // só aplica quando a consolidação partir da avaliação do usuário validador 
            if (!$caller->user->equals($plugin->getUser())) {
                return;
            }
            
            $can_consolidate = true;

            $evaluations = $app->repo('RegistrationEvaluation')->findBy(['registration' => $this, 'status' => 1]);

            /**
             * Se a consolidação requer homologação, verifica se existe alguma
             * avaliação de um usuário que não é um validador 
             * (validadores são usuários criados por plugins baseados nessa classe)
             */
            if($plugin->config['consolidacao_requer_homologacao']){
                $can = false;
                foreach ($evaluations as $eval) {
                    if(!$eval->user->aldirblanc_validador) {
                        $can = true;
                    }
                }

                if(!$can) {
                    $can_consolidate = false;
                }
            }

            /**
             * Se a consolidação requer outras validações, verifica se existe alguma
             * a avaliação dos usuários validadores
             */
            if($validacoes = $plugin->config['consolidacao_requer_validacoes']){
                foreach($validacoes as $slug) {
                    $can = false;
                    foreach ($evaluations as $eval) {
                        if($eval->user->aldirblanc_validador == $slug) {
                            $can = true;
                        }
                    }

                    if(!$can) {
                        $can_consolidate = false;
                    }
                }
            }

            if ($can_consolidate) {
                if ($plugin->config['forcar_resultado']) {
                    $result = $caller->result;
                }
            // se não pode consolidar, coloca string 'validado por {nome}' ou 'invalidado por {nome}'
            } else {
                $nome = $plugin->getName();
                $string = "";
                if($caller->result == '10'){
                    $string = "validado por {$nome}";
                } else if($caller->result == '2') {
                    $string = "invalidado por {$nome}";
                } else if($caller->result == '3') {
                    $string = "não selecionado por {$nome}";
                } else if($caller->result == '8') {
                    $string = "suplente por {$nome}";
                }
                // se não tem valor ainda ou se está atualizando:
                if (!$this->consolidatedResult || count($evaluations) <= 1) {
                    $result = $string;
                } else if (strpos($this->consolidatedResult, $nome) === false) {
                    $current_result = $this->consolidatedResult;

                    if($current_result == '10'){
                        $current_result = "selecionada";
                    } else if($current_result == '2') {
                        $current_result = "inválida";
                    } else if($current_result == '3') {
                        $current_result = "não selecionada";
                    } else if($current_result == '8') {
                        $current_result = "suplente";
                    }
                    
                    $result = "{$current_result}, {$string}";
                } else {
                    $result = $this->consolidatedResult;
                }            
            }
        });

        $app->hook('GET(opportunity.single):before', function () use ($app, $plugin) {
            $ids = [];
            $aldirblanc = $app->plugins['AldirBlanc'];
            $opportunity = $this->requestedEntity;

            $ids =  $aldirblanc->config['inciso2_opportunity_ids'];
            
            if ($aldirblanc->config['inciso3_enabled']) {
                $inciso3_ids = $aldirblanc->getOpportunitiesInciso3Ids();
                $ids = array_merge($ids, $inciso3_ids);
            }

            if ($aldirblanc->config['inciso1_enabled'] || $aldirblanc->config['inciso1_opportunity_id']) {
                $ids[] = $aldirblanc->config['inciso1_opportunity_id'];
            }
            
            if (in_array($opportunity->id, $ids)) {
                
                $user = $plugin->getUser();
                if (!in_array($opportunity->id, $user->aldirblanc_avaliador)) {
                    $plugin->makeUserEvaluatorIn($opportunity);
                }
            }
        });
    }

    /**
     * Registro
     *
     * @return void
     */
    function register()
    {
        $app = App::i();

        // registra o controlador
        $app->registerController($this->getSlug(), $this->getControllerClassname());

        $this->registerUserMetadata('aldirblanc_avaliador', [
            'label' => 'Oportunidades da Aldir Blanc onde o usuário é avaliador',
            'type' => 'json',
            'private' => false,
            'default_value' => '[]'
        ]);

        $this->registerUserMetadata('aldirblanc_validador', [
            'label' => 'É o usuário um validador da Aldir Blanc?',
            'type' => 'string',
            'private' => false,
            'default_value' => false
        ]);
    }

    /**
     * Retorna o authUid do usuário do plugin validador
     *
     * @return string
     */
    protected function getAuthUid(): string
    {
        return $this->getSlug() . '@validador';
    }

    /**
     * Verifica se o usuário do plugin validador já existe no banco
     *
     * @return bool
     */
    protected function userExists(): bool
    {
        return (bool) $this->getUser();
    }

    /**
     * Cria o usuário do plugin validador se este ainda não existir
     *
     * @return bool se criou ou não criou o usuário
     */
    protected function createUserIfNotExists()
    {
        $app = App::i();

        if (!$this->userExists()) {
            $app->disableAccessControl();
            $user = new User;

            $user->authProvider = __CLASS__;
            $user->authUid = $this->getAuthUid();
            $user->email = $this->getAuthUid();
            $user->aldirblanc_validador = $this->getSlug();

            $app->em->persist($user);
            $app->em->flush();

            $agent = new Agent($user);
            $agent->name = $this->getName();
            $agent->type = 2;
            $agent->status = 1;

            $agent->save();

            $app->em->flush();

            $user->profile = $agent;
            $user->save(true);

            $app->enableAccessControl();

            return true;
        } else {
            return false;
        }
    }

    function getUser()
    {
        $app = App::i();

        return $app->repo('User')->findOneBy(['authUid' => $this->getAuthUid()]);
    }

    /**
     * Definine o usuário avaliador como avaliador na oportunidade
     *
     * @param Opportunity $opportunity
     * @return void
     */
    protected function makeUserEvaluatorIn(Opportunity $opportunity)
    {
        $app = App::i();
        $user = $this->getUser();
        
        $app->disableAccessControl();
        
        $relation = new EvaluationMethodConfigurationAgentRelation;
        $relation->owner = $opportunity->evaluationMethodConfiguration;
        $relation->agent = $user->profile;
        $relation->group = 'group-admin';
        $relation->hasControl = true;

        $relation->save(true);
        
        $ids = $user->aldirblanc_avaliador;
        $ids[] = $opportunity->id;

        $user->aldirblanc_avaliador = $ids;

        $user->save(true);

        $app->disableAccessControl();
    }

    /**
     * Retorna o nome da instituição avaliadora
     *
     * @return string
     */
    abstract function getName(): string;

    /**
     * Retorna o slug da instituição avaliadora
     *
     * @return string
     */
    abstract function getSlug(): string;

    /**
     * Retorna o nome da classe do controlador.
     * 
     * Será registrado no sistema com o slug do plugin validador
     *
     * @return string
     */
    abstract function getControllerClassName(): string;


    /**
     * Verifica se a inscrição está apta a ser validada
     *
     * @param \MapasCulturais\Entities\Registration $registration
     * 
     * @return boolean
     */
    abstract function isRegistrationEligible(Registration $registration): bool;
}
