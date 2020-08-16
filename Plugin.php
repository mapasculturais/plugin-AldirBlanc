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
        $config += [
            'inciso1_enabled' => true,
            'inciso2_enabled' => true,
            'inciso1_opportunity_id' => null,
            'inciso2_opportunity_ids' => [
            ],
        ];
       
        parent::__construct($config);
    }

    public function _init()
    {
        $app = App::i();

        // enqueue scripts and styles
        $app->view->enqueueStyle('aldirblanc', 'app', 'aldirblanc/app.css');
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
