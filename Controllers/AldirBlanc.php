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
class AldirBlanc extends \MapasCulturais\Controllers\EntityController {
    use Traits\ControllerUploads,
        Traits\ControllerAgentRelation,
    	Traits\ControllerSealRelation;

    function __construct() {
        $app = App::i();

        $app->hook('view.render(<<aldirblanc/individual>>):before', function() use($app) {
            $app->view->includeEditableEntityAssets();
        });

        $app->hook('POST(registration.upload):before', function() use($app) {
            $mime_types = [
                'application/pdf',
                'audio/.+',
                'video/.+',
                'image/(gif|jpeg|pjpeg|png)',

                // ms office
                'application/msword',
                'application/vnd\.openxmlformats-officedocument\.wordprocessingml\.document',
                'application/vnd\.openxmlformats-officedocument\.wordprocessingml\.template',
                'application/vnd\.ms-word\.document\.macroEnabled\.12',
                'application/vnd\.ms-word\.template\.macroEnabled\.12',
                'application/vnd\.ms-excel',
                'application/vnd\.openxmlformats-officedocument\.spreadsheetml\.sheet',
                'application/vnd\.openxmlformats-officedocument\.spreadsheetml\.template',
                'application/vnd\.ms-excel\.sheet\.macroEnabled\.12',
                'application/vnd\.ms-excel\.template\.macroEnabled\.12',
                'application/vnd\.ms-excel\.addin\.macroEnabled\.12',
                'application/vnd\.ms-excel\.sheet\.binary\.macroEnabled\.12',
                'application/vnd\.ms-powerpoint',
                'application/vnd\.openxmlformats-officedocument\.presentationml\.presentation',
                'application/vnd\.openxmlformats-officedocument\.presentationml\.template',
                'application/vnd\.openxmlformats-officedocument\.presentationml\.slideshow',
                'application/vnd\.ms-powerpoint\.addin\.macroEnabled\.12',
                'application/vnd\.ms-powerpoint\.presentation\.macroEnabled\.12',
                'application/vnd\.ms-powerpoint\.template\.macroEnabled\.12',
                'application/vnd\.ms-powerpoint\.slideshow\.macroEnabled\.12',

                // libreoffice / openoffice
                'application/vnd\.oasis\.opendocument\.chart',
                'application/vnd\.oasis\.opendocument\.chart-template',
                'application/vnd\.oasis\.opendocument\.formula',
                'application/vnd\.oasis\.opendocument\.formula-template',
                'application/vnd\.oasis\.opendocument\.graphics',
                'application/vnd\.oasis\.opendocument\.graphics-template',
                'application/vnd\.oasis\.opendocument\.image',
                'application/vnd\.oasis\.opendocument\.image-template',
                'application/vnd\.oasis\.opendocument\.presentation',
                'application/vnd\.oasis\.opendocument\.presentation-template',
                'application/vnd\.oasis\.opendocument\.spreadsheet',
                'application/vnd\.oasis\.opendocument\.spreadsheet-template',
                'application/vnd\.oasis\.opendocument\.text',
                'application/vnd\.oasis\.opendocument\.text-master',
                'application/vnd\.oasis\.opendocument\.text-template',
                'application/vnd\.oasis\.opendocument\.text-web',

                // compacted files
                'application/x-rar',
                'application/x-rar-compressed',
                'application/octet-stream',
                'application/x-zip-compressed',
                'application/x-zip',
                'application/zip'

            ];
            $registration = $this->requestedEntity;
            foreach($registration->opportunity->registrationFileConfigurations as $rfc){

                $fileGroup = new Definitions\FileGroup($rfc->fileGroupName, $mime_types, \MapasCulturais\i::__('O arquivo enviado não é um documento válido.'), true, null, true);
                $app->registerFileGroup('registration', $fileGroup);
            }
        });

        $app->hook('entity(Registration).file(rfc_<<*>>).insert:before', function() use ($app){
            // find registration file configuration
            $rfc = null;
            foreach($this->owner->opportunity->registrationFileConfigurations as $r){
                if($r->fileGroupName === $this->group){
                    $rfc = $r;
                }
            }
            $finfo = pathinfo($this->name);
            $hash = uniqid();

            $this->name = $this->owner->number . ' - ' . $hash . ' - ' . preg_replace ('/[^\. \-\_\p{L}\p{N}]/u', '', $rfc->title) . '.' . $finfo['extension'];
            $tmpFile = $this->tmpFile;
            $tmpFile['name'] = $this->name;
            $this->tmpFile = $tmpFile;
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

        if($urlId == null || empty($urlId)) {
            
            //se nao tem URL verifica se o cara já tem ALGUMA incrição em andamento, se tiver, redireciona para EU MESMO(aldirblanc/individual/ID_AQUI)
            //se nao tiver nenhuma inscrição em andamento, leva o cara de volta para cadastro

            // $app->user->profile->id
            // 37419 << OWNER_ID

            $opportunity = App::i()->repo('Opportunity')->find(1647);

            $entity = App::i()->repo('Registration')->findByOpportunityAndUser($opportunity,$app->user);

            $app->redirect('http://localhost:8080/aldirblanc/individual/'.$entity[0]->id);
            
        } else {
            $app = App::i();

            $app->view->includeEditableEntityAssets();

            $entity = App::i()->repo('Registration')->find($urlId);

            $this->registerRegistrationMetadata($entity->opportunity);

            // $app->render('registration/edit', ['entity' => $entity]);//SE DER RUIM VOLTA PRA ESSE
            $app->render('aldirblanc/edit', ['entity' => $entity]);
            
            // $app->render('aldirblanc/individual', ['entity' => $entity]);
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

    /**
     *
     * @return \MapasCulturais\Entities\Opportunity
     */
    function getRequestedOpportunity(){
        $app = App::i();
        if(!isset($this->urlData['opportunityId']) || !intval($this->urlData['opportunityId'])){
            $app->pass();
        }

        $opportunity = $app->repo('Opportunity')->find(intval($this->urlData['opportunityId']));

        if(!$opportunity){
            $this->pass();
        }

        return $opportunity;
    }

    function GET_preview(){
        $this->requireAuthentication();

        $app = App::i();

        // $opportunity = $this->getRequestedOpportunity();

        $registration = App::i()->repo('Registration')->find(1148961597);

        // $this->_requestedEntity = $registration;

        // $this->registerRegistrationMetadata($registration->opportunity);

        $app->render('aldirblanc/edit', ['entity' => $registration]);

        // $this->render('edit', ['entity' => $registration]);
    }

    function GET_create(){
        $this->requireAuthentication();

        $opportunity = $this->getRequestedOpportunity();

        $opportunity->checkPermission('register');

        $registration = new $this->entityClassName;

        $registration->opportunity = $opportunity;

        $this->render('create', ['entity' => $registration]);
    }

    function GET_view(){
        $this->requireAuthentication();
        
        $entity = $this->requestedEntity;
        if(!$entity){
            App::i()->pass();
        }

        $entity->checkPermission('view');

        if($entity->status === Entities\Registration::STATUS_DRAFT && $entity->canUser('modify')){
            parent::GET_edit();
        } else {
            parent::GET_single();
        }
    }

    function GET_single(){
        App::i()->pass();
    }

    function GET_edit(){
        App::i()->pass();
    }

    function POST_setStatusTo(){
        $this->requireAuthentication();
        $app = App::i();

        $registration = $this->requestedEntity;

        if(!$registration){
            $app->pass();
        }

        $status = isset($this->postData['status']) ? $this->postData['status'] : null;

        $method_name = 'setStatusTo' . ucfirst($status);

        if(!method_exists($registration, $method_name)){
            if($app->request->isAjax()){
                $this->errorJson('Invalid status name');
            }else{
                $app->halt(200, 'Invalid status name');
            }
        }

        $registration->$method_name();

        if($app->request->isAjax()){
            $this->json($registration);
        }else{
            $app->redirect($app->request->getReferer());
        }
    }

    function POST_setMultipleStatus() {
        $this->requireAuthentication();

        $_registrations = $this->data;

        if(!is_null($_registrations) && is_array($_registrations) && (count($_registrations) > 0)) {
            $final_statuses = $this->getSmallerStatuses($_registrations['evaluations']);
            foreach ($final_statuses as $reg => $status) {
                $ref = App::i()->em->getReference($this->entityClassName, $reg);
                $ref->_setStatusTo($status);
            }

            return $this->json($final_statuses);
        }
    }

    private function getSmallerStatuses($registrations) {
        if (is_array($registrations)) {
            $filtered = [];
            foreach($registrations as $reg) {
                $_id = intval($reg["reg_id"]);
                $_result = intval($reg["result"]);

                if (key_exists($_id, $filtered)) {
                    if ($filtered[$_id] > $_result)
                        $filtered[$_id] = $_result;
                } else {
                    $filtered[$_id] = $_result;
                }
            }
            return $filtered;
        }

        return array();
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
    
    function POST_saveEvaluation(){
        $registration = $this->getRequestedEntity();
        if(isset($this->postData['uid'])){
            $user = App::i()->repo('User')->find($this->postData['uid']);
        } else {
            $user = null;
        }
        
        if(isset($this->urlData['status']) && $this->urlData['status'] === 'evaluated'){
            if($errors = $registration->getEvaluationMethod()->getValidationErrors($registration->getEvaluationMethodConfiguration(), $this->postData['data'])){
                $this->errorJson($errors, 400);
                return;
            } else {
                $status = Entities\RegistrationEvaluation::STATUS_EVALUATED;
                $evaluation = $registration->saveUserEvaluation($this->postData['data'], $user, $status);
            }
        } else {
            $evaluation = $registration->saveUserEvaluation($this->postData['data'], $user);
        }

        $this->setRegistrationStatus($registration);

        $this->json($evaluation);
    }

    function setRegistrationStatus(Entities\Registration $registration) {
        $evaluation_type = $registration->getEvaluationMethodDefinition()->slug;
        if ("technical" === $evaluation_type) {
            $app = App::i();
            $reg_evaluations = $app->repo('RegistrationEvaluation')->findBy(['registration' => $registration->id]);
            if (is_array($reg_evaluations) && count($reg_evaluations) > 0) {
                $valids = $invalids = 0;
                $_status = "pendent";
                foreach ($reg_evaluations as $_evaluation) {
                    if (property_exists($_evaluation->evaluationData, "viability")) {
                        if ("invalid" === $_evaluation->evaluationData->viability) {
                            $invalids++;
                        } else if ("valid" === $_evaluation->evaluationData->viability) {
                            $valids++;
                        }
                    }
                }

                if ($invalids > $valids)
                    $_status = "invalid";

                $registration->forceSetStatus($registration, $_status);
            }
        }
    }

    function POST_saveEvaluationAndChangeStatus(){
        $registration = $this->getRequestedEntity();

        if(isset($this->postData['uid'])){
            $user = App::i()->repo('User')->find($this->postData['uid']);
        } else {
            $user = null;
        }

        if(isset($this->urlData['status']) && $this->urlData['status'] === 'evaluated'){
            if($errors = $registration->getEvaluationMethod()->getValidationErrors($registration->getEvaluationMethodConfiguration(), $this->postData['data'])){
                $this->errorJson($errors, 400);
                return;
            } else {
                $status = Entities\RegistrationEvaluation::STATUS_EVALUATED;
                $evaluation = $registration->saveUserEvaluation($this->postData['data'], $user, $status);
            }
        } else {
            $evaluation = $registration->saveUserEvaluation($this->postData['data'], $user);
        }

        $status = $evaluation->result === '-1' ?  'invalid' : 'approved';
        if ($registration->evaluationUserChangeStatus($user, $registration, $status)) {
            $this->json($evaluation);
        }
    }

    function PATCH_valuersExceptionsList(){
        $registration = $this->getRequestedEntity();

        $exclude = (array) @$this->data['valuersExcludeList'];
        $include = (array) @$this->data['valuersIncludeList'];

        $registration->checkPermission('modifyValuers');
        
        $registration->setValuersExcludeList($exclude);
        $registration->setValuersIncludeList($include);
        $app = App::i();
        $app->disableAccessControl();
        $this->_finishRequest($registration);
        $app->enableAccessControl();
    
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
     * Formulário do inciso II
     *
     * @return void
     */
    function GET_coletivo()
    {
        $this->checkUserType('solicitante');


        // @todo definir registration
        $registration = null;
        
        $this->render('coletivo', ['registration' => $registration]);
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

        return $app->user->aldirblanc_tipo_usuario === $expected;
    }

}
