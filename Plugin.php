<?php

namespace AldirBlanc;

use MapasCulturais\App;
use MapasCulturais\i;

// @todo refatorar autoloader de plugins para resolver classes em pastas
require_once 'Controllers/AldirBlanc.php';

class Plugin extends \MapasCulturais\Plugin
{
    function __construct(array $config = [])
    {
        // se for multisite pega do subsite_meta
        if (App::i()->view->subsite){
            $config = App::i()->view->subsite->aldir_blanc_config;
        }

        $config += [
            'inciso1_enabled' => true,
            'inciso2_enabled' => true,
            'inciso1_opportunity_id' => null,
            'inciso2_opportunity_ids' => [],
            'inciso1_limite' => 1,
            'inciso2_limite' => 1,
            'inciso2_categories' => [
                'espaco-formalizado' => 'Espaço formalizado',
                'espaco-nao-formalizado' => 'Espaço não formalizado',
                'coletivo-formalizado' => 'Coletivo formalizado',
                'coletivo-nao-formalizado' => 'Coletivo não formalizado'
            ],
            'msg_inciso1_disabled' => 'Em breve!',
            'msg_inciso2_disabled' => 'A solicitação deste benefício será lançada em breve. Acompanhe a divulgação pelas instituições responsáveis pela gestão da cultura em seu município!',
            'link_suporte' => 'https://bit.ly/3hOQfBz',
            'privacidade_termos_condicoes' =>'https://mapacultural.pa.gov.br/files/subsite/2/termos-e-politica.pdf',
        ];
       
        parent::__construct($config);
    }

    public function registerAssets(){
        $app = App::i();

        // enqueue scripts and styles
        $app->view->enqueueStyle('aldirblanc', 'app', 'aldirblanc/app.css');
        $app->view->enqueueStyle('aldirblanc', 'fontawesome', 'https://use.fontawesome.com/releases/v5.8.2/css/all.css');
        $app->view->assetManager->publishFolder('aldirblanc/img', 'aldirblanc/img');
    }

    public function _init()
    {
        $app = App::i();
        
        $plugin = $this;

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

        $app->hook("GET(aldirblanc.<<*>>):before", function () use($plugin) {
            $plugin->registerAssets();
        });

        /**
         * modifica o template do autenticador quando o redirect url for para o plugin aldir blanc
         */
        $app->hook('controller(auth).render(multiple-local)', function() use ($app, $plugin) {
            $redirect_url = @$_SESSION['mapasculturais.auth.redirect_path'] ?: '';
            
            if(strpos($redirect_url, '/aldirblanc') === 0){
                $plugin->registerAssets();

                $req = $app->request;
                $this->layout = 'aldirblanc';
            }
        });

        $plugin = $this;

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
        $this->registerMetadata('MapasCulturais\Entities\Registration', 'inciso', [
            'label' => i::__('Inciso'),
            'type' => 'number',
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
