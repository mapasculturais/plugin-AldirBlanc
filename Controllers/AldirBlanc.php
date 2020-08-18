<?php

namespace AldirBlanc\Controllers;

use MapasCulturais\App;
use MapasCulturais\i;
use MapasCulturais\Entities\Registration;

/**
 * Registration Controller
 *
 * By default this controller is registered with the id 'registration'.
 *
 *  @property-read \MapasCulturais\Entities\Registration $requestedEntity The Requested Entity
 */
// class AldirBlanc extends \MapasCulturais\Controllers\EntityController {
class AldirBlanc extends \MapasCulturais\Controllers\Registration
{
    protected $config = [];

    function __construct()
    {
        parent::__construct();

        $app = App::i();

        $this->config = $app->plugins['AldirBlanc']->config;

        $app->hook('view.render(<<aldirblanc/individual>>):before', function () use ($app) {
            $app->view->includeEditableEntityAssets();
        });

        $app->hook('<<GET|POST|PUT|PATCH|DELETE>>(aldirblanc.<<*>>):before', function () {
            $registration = $this->getRequestedEntity();

            if (!$registration || !$registration->id) {
                return;
            }

            $opportunity = $registration->opportunity;

            $this->registerRegistrationMetadata($opportunity);
        });

        $this->entityClassName = '\MapasCulturais\Entities\Registration';
        $this->layout = 'aldirblanc';
    }

    /**
     * Retorna a oportunidade do inciso I
     *
     * @return \MapasCulturais\Entities\Opportunity;
     */
    function getOpportunityInciso1()
    {
        $opportunity_id = $this->config['inciso1_opportunity_id'];

        $app = App::i();

        $opportunity = $app->repo('Opportunity')->find($opportunity_id);

        return $opportunity;
    }

    /**
     * Retorna a oportunidade do inciso II
     *
     * @return \MapasCulturais\Entities\Opportunity;
     */
    function getOpportunityInciso2(string $cidade)
    {
        if (isset($this->config['inciso2_opportunity_ids']['cidade'])) {
            $app = App::i();

            $opportunity_id = $this->config['inciso2_opportunity_ids'][$cidade];
            $opportunity = $app->repo('Opportunity')->find($opportunity_id);

            return $opportunity;
        } else {
            throw \Exception('Cidade não disponível para cadastro');
        }
    }

    function getCidades()
    {
        return $this->config['inciso2_opportunity_ids'];
    }

    function finish($data, $status = 200, $isAjax = false)
    {
        if (is_array($data)) {
            $data['redirect'] = 'false';
        } else if (is_object($data)) {
            $data->redirect = 'false';
        }
        parent::finish($data, $status, $isAjax);
    }

    /**
     * Redireciona o usuário para o formulário do inciso I
     * 
     * rota: /aldirblanc/individual/[?agent={agent_id}]
     * 
     * @return void
     */
    function GET_individual()
    {
        $this->requireAuthentication();

        $app = App::i();

        if (isset($this->data['agent'])) {
            $agent = $app->repo('Agent')->find($this->data['agent']);
        } else {
            $agent = $app->user->profile;
        }

        // se ainda não tem inscrição
        if (!isset($agent->aldirblanc_inciso1_registration)) {
            /** 
             * verificar se o usuário tem mais de um agente, 
             * se tiver redireciona para a página de escolha de agente
             */
            $agent_controller = $app->controller('agent');

            $num_agents = $agent_controller->apiQuery([
                '@select' => 'id',
                '@permissions' => '@control',
                'type'=>'EQ(1)',
                '@count' => 1
            ]);                    
            if ($num_agents > 1) {
                // redireciona para a página de escolha de agente
                $app->redirect($this->createUrl('selecionar_agente'));
            } else {

                // redireciona para a rota de criação de nova inscrição
                $app->redirect($this->createUrl('nova_inscricao', [
                    'inciso' => 1,
                    'agent' => $app->user->profile->id
                ]));
            }
        }

        $app->redirect($this->createUrl('formulario', [$agent->aldirblanc_inciso1_registration]));
    }

    /**
     * Cria nova inscrição para o agente no inciso informado e redireciona para o formulário
     * 
     */
    function GET_nova_inscricao()
    {
        $this->requireAuthentication();

        if (!isset($this->data['agent']) || !in_array(intval(@$this->data['inciso']), [1, 2])) {
            // @todo tratar esse erro
            throw new \Exception();
        }

        $app = App::i();

        $agent = $app->repo('Agent')->find($this->data['agent']);
        $agent->checkPermission('@control');

        $registration = new \MapasCulturais\Entities\Registration;
        $registration->owner = $agent;

        if ($this->data['inciso'] == 1) {
            $registration->opportunity = $this->getOpportunityInciso1();
        } else {
            if (!isset($this->data['cidade']) || !isset($this->data['category'])) {
                // @todo tratar esse erro
                throw new \Exception();
            }
            $registration->opportunity = $this->getOpportunityInciso2($this->data['cidade']);

            $registration->category = $this->data['category'];
        }

        $registration->save(true);

        $app->redirect($this->createUrl('formulario', [$registration->id]));
    }
    /**
     * Tela onde o usuário escolhe o inciso I ou II
     *
     * @return void
     */
    function GET_status()
    {
        $this->requireAuthentication();
        $app = App::i();

        $registration = $this->requestedEntity;

        if(!$registration) {
            $app->pass();
        }

        $registration->checkPermission('view');
        $summaryStatusName = [
            Registration::STATUS_DRAFT => i::__('Rascunho', 'aldirblanc'),
            Registration::STATUS_SENT => i::__('Em análise', 'aldirblanc'),
            Registration::STATUS_APPROVED => i::__('Aprovado', 'aldirblanc'),
            Registration::STATUS_NOTAPPROVED => i::__('Reprovado', 'aldirblanc'),
            Registration::STATUS_WAITLIST => i::__('Recursos Exauridos', 'aldirblanc'),
        ];

        $registrationStatusName = "";
        foreach($summaryStatusName as $key => $value) {
            if($key == $registration->status) {
                $registrationStatusName = $value;
                break;
            }
        }

        $this->render('status', ['registration' => $registration, 'registrationStatusName'=> $registrationStatusName]);
    }

    /**
     * Renderiza o formulário da solicitação
     * 
     * Pode ser dos incisos I e II
     * 
     * rota: /aldirblanc/formulario/[{registration_id}]
     * 
     * @return void
     */
    function GET_formulario()
    {
        $app = App::i();
        $this->requireAuthentication();

        $registration = $this->getRequestedEntity();

        $registration->checkPermission('modify');

        if (!$registration->termos_aceitos) {
            $app->redirect($this->createUrl('termos_e_condicoes', [$registration->id]));
        }

        $this->registerRegistrationMetadata($registration->opportunity);
        $app->view->includeEditableEntityAssets();
        $this->render('edit', ['entity' => $registration]);
    }

    /**
     * Encaminha o usuário para a rota correta, de acordo com o tipo do usuário
     *
     * @return void
     */
    function GET_index()
    {
        $this->requireAuthentication();

        $app = App::i();

        if ($app->user->aldirblanc_tipo_usuario == 'assistente-social') {
            $app->redirect($this->createUrl('assistenteSocial'));
        } else if ($app->user->aldirblanc_tipo_usuario == 'solicitante') {
            $app->redirect($this->createUrl('cadastro'));
        } else {
            $app->user->aldirblanc_tipo_usuario = 'solicitante';
            $app->disableAccessControl();
            $app->user->save(true);
            $app->enableAccessControl();
            $app->redirect($this->createUrl('cadastro'));
        }
    }

    /**
     * Tela onde o usuário escolhe o inciso I ou II
     *
     * @return void
     */
    function GET_cadastro()
    {
        $this->requireAuthentication();

        $this->render('cadastro', ['cidades' => $this->getCidades()]);
    }

    function GET_termos_e_condicoes()
    {
        $this->requireAuthentication();
        if (!isset($this->data['id'])) {
            // @todo tratar esse erro
            throw new \Exception();
        }
        $this->render('termos-e-condicoes', ['registration_id' => $this->data['id']]);
    }

    /**
     * Aceitar os termos e condiçoes
     * 
     * rota: /aldirblanc/aceitar_termos/{id_inscricao}
     * 
     * @return void
     */
    function GET_aceitar_termos()
    {

        $this->requireAuthentication();
        $registration = $this->requestedEntity;
        $registration->checkPermission('modify');
        $registration->termos_aceitos = true;
        $registration->save(true);
        $app = App::i();
        $app->redirect($this->createUrl('formulario', [$registration->id]));
    }

    function GET_selecionar_agente()
    {
        $user = $this->_getUser();

        $this->render('selecionar-agente', ['user' => $user]);
    }

    protected function _getUser(){
        $app = App::i();
        $user = null;
        if($app->user->is('admin') && key_exists('userId', $this->data)){
            $user = $app->repo('User')->find($this->data['userId']);


        }elseif($app->user->is('admin') && key_exists('agentId', $this->data)){
            $agent = $app->repo('Agent')->find($this->data['agentId']);
            $user = $agent->user;
        }
        if(!$user)
            $user = $app->user;

        return $user;
    }
}
