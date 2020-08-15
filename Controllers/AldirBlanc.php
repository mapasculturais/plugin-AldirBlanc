<?php

namespace AldirBlanc\Controllers;

use \MapasCulturais\Entities\Registration;
use \MapasCulturais\Controllers;
use \MapasCulturais\App;
use MapasCulturais\Controllers\Auth;
use \MapasCulturais\Traits;
use \MapasCulturais\Definitions;
use \MapasCulturais\Entities;

use \MapasCulturais\Themes\BaseV1\Theme;

/**
 * Registration Controller
 *
 * By default this controller is registered with the id 'registration'.
 *
 *  @property-read \MapasCulturais\Entities\Registration $requestedEntity The Requested Entity
 */
// class AldirBlanc extends \MapasCulturais\Controllers\EntityController {
class AldirBlanc extends \MapasCulturais\Controllers\Registration {
    use Traits\ControllerUploads,
        Traits\ControllerAgentRelation,
    	Traits\ControllerSealRelation;

    function __construct() {
        parent::__construct();
        $app = App::i();

        $app->hook('view.render(<<aldirblanc/individual>>):before', function() use($app) {
            $app->view->includeEditableEntityAssets();
        });

        
        $app->hook('<<GET|POST|PUT|PATCH|DELETE>>(aldirblanc.<<*>>):before', function() {
            $registration = $this->getRequestedEntity();
            
            
            if(!$registration || !$registration->id){
                return;
            }

            $opportunity = $registration->opportunity;
            
            $this->registerRegistrationMetadata($opportunity);
            
        });

        // parent::__construct();
        // $this->entityClassName = preg_replace("#Controllers\\\([^\\\]+)$#", 'Entities\\\$1', get_class($this));
        $this->entityClassName = '\MapasCulturais\Entities\Registration';
        $this->layout = 'aldirblanc';
    }

    // @override
    /**
     * Sets the response content type to application/json and prints the $data encoded to json.
     *
     * @param mixed $data
     */
    public function json($data, $status = 200){
        $app = App::i();
        $app->persistPCachePendingQueue();
        $app->contentType('application/json');
        $app->halt($status, json_encode($data));
    }

    // @override
    function finish($data, $status = 200, $isAjax = false){
        // $data['test'] = "testValue";
        $app = App::i();

        if($app->request->isAjax() || $isAjax || $app->request->headers('MapasSDK-REQUEST')){

            // $data = (array)$data;
            
            $json = json_decode(json_encode($data));
            $json->redirect = "false";
            $json = json_encode($json);

            $app->persistPCachePendingQueue();
            $app->contentType('application/json');
            // $app->halt($status, json_encode($data));
            $app->halt($status, $json);

        }elseif(isset($this->getData['redirectTo'])){
            $app->redirect($this->getData['redirectTo'], $status);
        }else{
            $app->redirect($app->request()->getReferer(), $status);
        }
    }

    /**
     * Formulário do inciso I
     *
     * @return void
     */
    function GET_individual()
    {
        $urlId = $this->urlData['id'];
        $app = App::i();

        $app->view->includeEditableEntityAssets();

        if($urlId == null || empty($urlId)) {
            
            $opportunity = App::i()->repo('Opportunity')->find(1647);

            $registration = App::i()->repo('Registration')->findByOpportunityAndUser($opportunity,$app->user);

            $app->redirect($app->getBaseUrl().'aldirblanc/individual/'.$registration[0]->id);
            
        } else {
            
            $entity = App::i()->repo('Registration')->find($urlId);

            $this->registerRegistrationMetadata($entity->opportunity);

            $app->render('aldirblanc/edit', ['entity' => $entity]);
            
        }
        
    }

    public function createUrl($actionName, array $data = array()) {
        if($actionName == 'single' || $actionName == 'edit'){
            $actionName = 'view';
        }
        return parent::createUrl($actionName, $data);
    }

    function registerRegistrationMetadata(\MapasCulturais\Entities\Opportunity $opportunity){
        
        $app = App::i();
        
        if($opportunity->projectName){
            $cfg = [ 'label' => \MapasCulturais\i::__('Nome do Projeto') ];
            
            $metadata = new Definitions\Metadata('projectName', $cfg);
            $app->registerMetadata($metadata, 'MapasCulturais\Entities\Registration');
        }

        foreach($opportunity->registrationFieldConfigurations as $field){

            $cfg = [
                'label' => $field->title,
                'type' => $field->fieldType === 'checkboxes' ? 'checklist' : $field->fieldType ,
                'private' => true,
            ];

            $def = $field->getFieldTypeDefinition();

            if($def->requireValuesConfiguration){
                $cfg['options'] = $field->fieldOptions;
            }

            if(is_callable($def->serialize)){
                $cfg['serialize'] = $def->serialize;
            }

            if(is_callable($def->unserialize)){
                $cfg['unserialize'] = $def->unserialize;
            }

            $metadata = new Definitions\Metadata($field->fieldName, $cfg);

            $app->registerMetadata($metadata, 'MapasCulturais\Entities\Registration');
        }
    }
    
    function getPreviewEntity(){
        $registration = new Registration();
        
        $registration->id = -1;

        $registration->preview = true;
        
        return $registration;
    }
    /**
     * Tela onde o usuário escolhe o inciso I ou II
     *
     * @return void
     */
    function GET_status()
    {
        $this->requireAuthentication();

        $this->render('status');
    }

    /**
     * @return \MapasCulturais\Entities\Registration
     */
    function getRequestedEntity() {
        $preview_entity = $this->getPreviewEntity();

        if(isset($this->urlData['id']) && $this->urlData['id'] == $preview_entity->id){
            if(!App::i()->request->isGet()){
                $this->errorJson(['message' => [\MapasCulturais\i::__('Este formulário é um pré-visualização da da ficha de inscrição.')]]);
            } else {
                return $preview_entity;
            }
        }
        return parent::getRequestedEntity();
    }

    function POST_send(){
        $this->requireAuthentication();
        $app = App::i();

        $registration = $this->requestedEntity;
        
        if(!$registration){
            $app->pass();
        }

        if($errors = $registration->getSendValidationErrors()){
            $this->errorJson($errors);
        }else{
            $registration->cleanMaskedRegistrationFields();
            $registration->send();

            if($app->request->isAjax()){
                $this->json($registration);
            }else{
                $app->redirect($app->request->getReferer());
            }
        }
    }
    
    /**
     * Encaminha o usuário para a rota correta, de acordo com o tipo do usuário
     *
     * @return void
     */
    function GET_index()
    {
        // $this->requireAuthentication();

        $app = App::i();
        $config = $app->config['auth.config'];

        $app->view->enqueueScript('app', 'multipleLocal', 'js/multipleLocal.js');
        $app->view->enqueueStyle('app', 'multipleLocal', 'css/multipleLocal.css');

        $app->render('auth/multiple-local', [
            'redirectUrl' => '/aldirblanc/cadastro',
            'config' => $config,
            'register_form_action' => $app->auth->register_form_action,
            'register_form_method' => $app->auth->register_form_method,
            'login_form_action' => $app->createUrl('auth', 'login'),
            'recover_form_action' => $app->createUrl('auth', 'recover'),
            'feedback_success'        => $app->auth->feedback_success,
            'feedback_msg'    => $app->auth->feedback_msg,   
            'triedEmail' => $app->auth->triedEmail,
            'triedName' => $app->auth->triedName,
        ]);

        // deixei o codigo do rafael comentario aqui pra sevir de referencia se precisar
        // if($app->user->aldirblanc_tipo_usuario == 'assistente-social'){
        //     $app->redirect($this->createUrl('assistenteSocial'));

        // } else if($app->user->aldirblanc_tipo_usuario == 'solicitante') {
        //     $app->redirect($this->createUrl('cadastro'));

        // } else {
        //     $app->user->aldirblanc_tipo_usuario = 'solicitante';
        //     $app->disableAccessControl();
        //     $app->user->save(true);
        //     $app->enableAccessControl();
        //     $app->redirect($this->createUrl('cadastro'));
        // }
    }

    /**
     * Tela onde o usuário escolhe o inciso I ou II
     *
     * @return void
     */
    function GET_cadastro()
    {
        // $this->requireAuthentication();

        // $params = array(
        //     "category" => null,
        //     "agentOpportunityId" => PEGUE_OPORTUNIDADE,
        //     "spaceOpportunityId" => PEGUE_OPORTUNIDADE,
        //     "agentOwnerId" => PEGUE_USUARIOLOGADO_PROFILE_ID,
        //     "spaceOwnerId" => PEGUE_USUARIOLOGADO_PROFILE_ID,
        // )
        //DADOS FAKES, delete depois
        $params = array(
            "category" => null,
            "agentOpportunityId" => 1647,
            "spaceOpportunityId" => 1647,
            "agentOwnerId" => 37419,
            "spaceOwnerId" => 37419
        );

        $this->render('cadastro', $params);
    }

    function GET_termosecondicoes()
    {
        $this->render('termos-e-condicoes');
    }

    function GET_selecionaragente()
    {
        $opportunity = App::i()->repo('Opportunity')->find(1647);

        $this->render('selecionar-agente',['entity' => $opportunity]);
    }

}
