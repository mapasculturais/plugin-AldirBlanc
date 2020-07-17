<?php

namespace AldirBlanc\Controllers;

use MapasCulturais\App;

class AldirBlanc extends \MapasCulturais\Controller
{
    function __construct()
    {
        $this->layout = 'aldirblanc';
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

        switch ($app->user->aldirblanc_tipo_cadastro) {

            case 'individual':
                $app->redirect($this->createUrl('individual'));
                break;

            case 'coletivo':
                $app->redirect($this->createUrl('coletivo'));
                break;

            case 'assistente-social':
                $app->redirect($this->createUrl('assistenteSocial'));
                break;

            default:
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

        $this->render('cadastro');
    }

    /**
     * Painel de controle do assistente social, onde o usuário pode gerenciar as inscições realizadas:
     * - adicionar/editar/exluir inscrição 
     * - ver status da inscrição
     *  
     * @return void
     */
    function GET_assistenteSocial()
    {
        $this->checkUserType('assistente-social');

        $this->render('assistente-social');
    }

    /**
     * Formulário do inciso I
     *
     * @return void
     */
    function GET_individual()
    {
        $this->checkUserType('individual');

        $this->render('individual');
    }

    /**
     * Formulário do inciso II
     *
     * @return void
     */
    function GET_coletivo()
    {
        $this->checkUserType('coletivo');

        $this->render('coletivo');
    }

    /**
     * Define o tipo de cadastro do usuário
     *
     * @return void
     */
    function GET_tipo()
    {
        $app = App::i();

        $this->requireAuthentication();

        $tipo_cadastro = isset($this->data[0]) ? $this->data[0] : null;

        if (in_array($tipo_cadastro, ['individual', 'coletivo'])) {
            $app->user->aldirblanc_tipo_cadastro = $tipo_cadastro;
            $app->user->save(true);

            $app->redirect($this->createUrl($tipo_cadastro));
        } else {
            $app->redirect($this->createUrl('index'));
        }
    }

    /**
     * Verifica se o usuário é do tipo informado
     *
     * @param string $expected "individual|coletivo"
     * @return bool
     */
    function checkUserType(string $expected)
    {
        $this->requireAuthentication();

        $app = App::i();

        return $app->user->aldirblanc_tipo_cadastro === $expected;
    }
}
