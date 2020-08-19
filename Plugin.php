<?php

namespace AldirBlanc;

use Exception;
use MapasCulturais\App;
use MapasCulturais\Controllers\Opportunity;
use MapasCulturais\Entities\Project;
use MapasCulturais\i;

// @todo refatorar autoloader de plugins para resolver classes em pastas
require_once 'Controllers/AldirBlanc.php';

class Plugin extends \MapasCulturais\Plugin
{
    function __construct(array $config = [])
    {
        $config += [
            'inciso1_enabled' => true,
            'inciso2_enabled' => true,
            'inciso1_opportunity_id' => null,
            'inciso2_opportunity_ids' => [
            ], 
            'inciso1_limite' => 1,
            'inciso2_limite' => 1,
            'inciso2_categories' => [
                'Espaço formalizado',
                'Espaço não formalizados',
                'Coletivo formalizado',
                'Coletivo não formalizado'
            ]
        ];
       
        parent::__construct($config);
    }

    public function _init()
    {
        $app = App::i();
        $plugin = $this;

        // enqueue scripts and styles
        $app->view->enqueueStyle('aldirblanc', 'app', 'aldirblanc/app.css');
        $app->view->enqueueScript('app', 'entity.module.opportunity.aldirblanc', 'aldirblanc/ng.entity.module.opportunity.aldirblanc.js', array('ng-mapasculturais'));
        $app->view->assetManager->publishFolder('aldirblanc/img', 'aldirblanc/img');

        // add hooks
        $app->hook('mapasculturais.styles', function () use ($app) {
            $app->view->printStyles('aldirblanc');
        });

        $app->hook('template(subsite.<<create|edit>>.tabs):end', function () {
            $this->part('aldirblanc/subsite-tab');
        });

        $app->hook('template(subsite.<<create|edit>>.tabs-content):end', function () {
            $this->part('aldirblanc/subsite-tab-content');
        });

        $app->hook('template(site.index.home-search):end', function () {
            $this->part('aldirblanc/home-search');
        });

        /**
         * modifica o template do autenticador quando o redirect url for para o plugin aldir blanc
         */
        $app->hook('controller(auth).render(multiple-local)', function() use ($app) {
            $redirect_url = @$_SESSION['mapasculturais.auth.redirect_path'] ?: '';
            
            if(strpos($redirect_url, '/aldirblanc') === 0){
                $req = $app->request;
                $this->layout = 'aldirblanc';
            }
        });

        $app->hook('mapasculturais.run:after', function() use ($plugin) {
            /**
             * Criação automatica da opportunidade do inciso1
             */
            
            $plugin->createOpportunityInciso1();
        });


        

        /**
         * Na criação da inscrição, define os metadados inciso2_opportunity_id ou 
         * inciso1_opportunity_id do agente responsável pela inscrição
         */
        $app->hook('entity(Registration).save:after', function() use ($plugin) {
            
            if(in_array($this->opportunity->id, $plugin->config['inciso2_opportunity_ids'])){
                $agent = $this->owner;
                $agent->aldirblanc_inciso2_registration = $this->id;
                $agent->save(true);

            } else if ($this->opportunity->id == $plugin->config['inciso1_opportunity_id']) {
                $agent = $this->owner;
                $agent->aldirblanc_inciso1_registration = $this->id;
                $agent->save(true);
            }
        });

        
        
    }

    public function getOpportunityByInciso(Project $project,int $inciso) {

        $result = [];

        $opportunities = $project->getOpportunities();

        foreach($opportunities as $opportunity) {
            if((int)$opportunity->getMetadata('aldirblanc_inciso') == $inciso) {
                $result[] = $opportunity;
            }
        }

        return $result;
    }

    public function createOpportunityInciso1() {
        $app = App::i();

        if($app->user->is('guest')) {
            return;
        }

        $idProjectFromConfig = $this->config['project_id'] ? $this->config['project_id'] : null; 

        if(!$idProjectFromConfig) {
            throw new Exception('Defina a configuração "project_id" no config.php["AldirBlanc"] ');
        }

        $project = $app->repo('Project')->find($idProjectFromConfig);

        if(!$project) {
            throw new Exception('Id do projeto está invalido');
        }

        $opportunities =  $this->getOpportunityByInciso($project, 1);

        if(empty($opportunities)) {

            $app->disableAccessControl();

            $opportunity = new \MapasCulturais\Entities\ProjectOpportunity();
            $opportunity->name = "Inciso1";
            $opportunity->shortDescription = "Uma descricao pequena aqui";
            $opportunity->registrationFrom = new \DateTime();
            $opportunity->registrationTo = new \DateTime( '2025-01-31' );
            $opportunity->owner = $project->owner;
            $opportunity->ownerEntity = $project;

            $opportunityMeta = new \MapasCulturais\Entities\OpportunityMeta();
            $opportunityMeta->owner = $opportunity;
            $opportunityMeta->key = 'aldirblanc_inciso';
            $opportunityMeta->value = 1;
            
            $project->_relatedOpportunities = [$opportunity];
    
            $opportunity->save();

            $opportunityMeta->save();

            $project->save();

            $app->enableAccessControl();
            $app->em->flush();

        } 

    }

    // @todo FAZER INCISO 2
    public function createOpportunityInciso2() {
        $app = App::i();

        $idProjectFromConfig = $this->config['project_id'] ? $this->config['project_id'] : null; 

        if(!$idProjectFromConfig) {
            throw new Exception('Defina a configuração "project_id" no config.php["AldirBlanc"] ');
        }

        $project = $app->repo('Project')->find($idProjectFromConfig);

        $opportunities =  $this->getOpportunityByInciso($project, 1);

        if(empty($opportunities)) {
            //cria opprtunidade
        } 

    }

    /**
     * Registra os controladores e metadados das entidades
     *
     * @return void
     */
    public function register()
    {
        $app = App::i();

        $app->registerController('aldirblanc', 'AldirBlanc\Controllers\AldirBlanc');

        /* registrinado metadados do usuário */

        /**
         * Tipo de usuário na aldir 
         * @var string
         */
        $this->registerUserMetadata('aldirblanc_tipo_usuario', [
            'label' => i::__('Tipo de Usuário'),
            'type' => 'select',
            'private' => true,
            'options' => [
                'assistente-social' => i::__('Assistência Social'),
                'solicitante' => i::__('Solicitante')
            ]
        ]);

        $this->registerMetadata('MapasCulturais\Entities\Registration', 'termos_aceitos', [
            'label' => i::__('Aceite dos termos e condições'),
            'type' => 'boolean',
            'private' => true,
        ]);

        if($this->config['inciso1_enabled']){
            /**
             * Id da inscrição no insico I
             * @var string
             */
            $this->registerAgentMetadata('aldirblanc_inciso1_registration', [
                'label' => i::__('Id da inscrição no Insiso I'),
                'type' => 'string',
                'private' => true,
                // @todo: validação que impede a alteração do valor desse metadado
            ]);
        }

        if($this->config['inciso2_enabled']){
            /**
             * Id da inscrição no insico II
             * @var string
             */
            $this->registerAgentMetadata('aldirblanc_inciso2_registration', [
                'label' => i::__('Id da inscrição no inciso II'),
                'type' => 'string',
                'private' => true,
                // @todo: validação que impede a alteração do valor desse metadado
            ]);
        }
    }
}
