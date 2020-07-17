<?php

namespace AldirBlanc;

use MapasCulturais\App;
use MapasCulturais\i;

// @todo refatorar autoloader de plugins para resolver classes em pastas
require_once 'Controllers/AldirBlanc.php';

class Plugin extends \MapasCulturais\Plugin
{
    public function _init()
    {
        $app = \MapasCulturais\App::i();
        // enqueue scripts and styles

        // add hooks

        $app->hook('template(subsite.<<create|edit>>.tabs):end', function () {
            $this->part('aldirblanc/subsite-tab');
        });

        $app->hook('template(subsite.<<create|edit>>.tabs-content):end', function () {
            $this->part('aldirblanc/subsite-tab-content');
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
            'options' => [
                '' => i::__('Assistência Social'),
                'proponente' => i::__('Proponente')
            ]
        ]);

        $this->registerUserMetadata('aldirblanc_tipo_cadastro', [
            'label' => i::__('Inciso'),
            'type' => 'select',
            'options' => [
                'individual' => i::__('Inciso I - Trabalhador da Cultura'),
                'coletivo' => i::__('Inciso II - Espaços e Coletivos')
            ]
        ]);

        $this->registerUserMetadata('tipo_cadastro', [
            'label' => i::__('Inciso'),
            'type' => 'select',
            'options' => [
                'individual' => i::__('Inciso I - Trabalhador da Cultura'),
                'coletivo' => i::__('Inciso II - Espaços e Coletivos')
            ]
        ]);
    }
}
