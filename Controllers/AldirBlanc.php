<?php

namespace AldirBlanc\Controllers;

use Exception;
use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\i;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\RegistrationSpaceRelation as RegistrationSpaceRelationEntity;
use MapasCulturais\Entities\User;
use MapasCulturais\Exceptions\PermissionDenied;

/**
 * Registration Controller
 *
 * By default this controller is registered with the id 'registration'.
 *
 * @property-read \MapasCulturais\Entities\Registration $requestedEntity The Requested Entity
 * @property-read mixed $config configuração do plugin
 */
// class AldirBlanc extends \MapasCulturais\Controllers\EntityController {
class AldirBlanc extends \MapasCulturais\Controllers\Registration
{
    const CATEGORY_ESPACO_FORMALIZADO = 0;
    const CATEGORY_ESPACO_NAO_FORMALIZADO = 1;
    const CATEGORY_COLETIVO_FORMALIZADO = 2;
    const CATEGORY_COLETIVO_NAO_FORMALIZADO = 3;
    

    /**
     * Instância do plugin
     *
     * @var \AldirBlanc\Plugin
     */
    protected $plugin;

    function __construct()
    {
        parent::__construct();

        $app = App::i();

        $this->plugin = $app->plugins['AldirBlanc'];

        $opportunitiesArrayInciso2 = $this->config['inciso2_opportunity_ids'];
        $opportunityInciso1 = $this->config['inciso1_opportunity_id'];
        if (array_unique($opportunitiesArrayInciso2) != $opportunitiesArrayInciso2 || in_array ($opportunityInciso1, array_values($opportunitiesArrayInciso2) )){
            throw new \Exception('A mesma oportunidade não pode ser utiilizada para duas cidades ou dois incisos');
        }
       
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
     * Retorna a Categoria do inciso II
     *
     * @return string;
     */
    function getCategoryName(string $slug)
    {
        if (isset($this->config['inciso2_categories'][$slug])) {
            $categoryName = $this->config['inciso2_categories'][$slug];
            return $categoryName;
        } else {
            throw new \Exception('Categoria não existe');
        }
    }

    function getConfig() {
        return $this->plugin->config;
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
        if (!isset($opportunity_id) || $opportunity_id == "") {
            // @todo tratar esse erro
            throw new \Exception();
        }
        $opportunity = $app->repo('Opportunity')->find($opportunity_id);

        if(!$opportunity){
            // @todo tratar esse erro
            throw new \Exception();
        }

        return $opportunity;
    }
    
    /**
     * Retorna a oportunidade do inciso II
     *
     * @return \MapasCulturais\Entities\Opportunity;
     */
    function getOpportunityInciso2(string $opportunity_id)
    {
        if (!in_array($opportunity_id, $this->config['inciso2_opportunity_ids']) || $opportunity_id == "" ){
            // @todo tratar esse erro
            throw new \Exception();
        }
        $app = App::i();
        
        $opportunity = $app->repo('Opportunity')->find($opportunity_id);


        if(!$opportunity){
            // @todo tratar esse erro
            throw new \Exception();
        }
        return $opportunity;
    }
    /**
     * Retorna a oportunidade do inciso III
     *
     * @return array
     */
    function getOpportunitiesInciso3()
    {
        $app = App::i();
        $project = $app->repo('Project')->find($this->config['project_id']);
        $projectsIds = $project->getChildrenIds();
        $projectsIds[] = $project->id;
        $opportunitiesByProject = $app->repo('ProjectOpportunity')->findBy(['ownerEntity' => $projectsIds ] );
        $inciso1e2Ids = array_values(array_merge([$this->config['inciso1_opportunity_id']], $this->config['inciso2_opportunity_ids']));
        $opportunitiesInciso3 = [];

        foreach ($opportunitiesByProject as $opportunity){
            if ( !in_array($opportunity->id, $inciso1e2Ids) ) {
                $opportunitiesInciso3[] = $opportunity;
            }
        }        

        return $opportunitiesInciso3;
    }
    /**
     * Retorna o array associativo com os numeros e nomes de status
     *
     * @return array
     */
    function getStatusNames(){
        $summaryStatusName = [
            Registration::STATUS_DRAFT => i::__('Rascunho', 'aldirblanc'),
            Registration::STATUS_SENT => i::__('Em análise', 'aldirblanc'),
            Registration::STATUS_APPROVED => i::__('Aprovado', 'aldirblanc'),
            Registration::STATUS_NOTAPPROVED => i::__('Reprovado', 'aldirblanc'),
            Registration::STATUS_WAITLIST => i::__('Recursos Exauridos', 'aldirblanc'),
        ];
        return $summaryStatusName;
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

    protected function createMediado() {
        $app = App::i();

        $owner_id = $this->config['mediados_owner'];
        
        if (!$owner_id) {
            throw new Exception('Verifique a configuração `mediados_owner`.');
        }
        $owner = $app->repo('Agent')->find($owner_id);
        
        $app->disableAccessControl();
        
        $agent = new Agent($owner->user);
        $agent->type = 1;
        $agent->name = "";
        $agent->shortDescription = "";

        $agent->save(true);

        $agent->createAgentRelation($app->user->profile, 'mediaror', true, true);

        $app->disableAccessControl();

        return $agent;
    }

    /**
    * Redireciona o usuário para o formulário do inciso II
    *
    * rota: /aldirblanc/coletivo/[?opportunity={opportunity}&category=category]
    *
    * @return void
    */
   function GET_coletivo()
   {

        $this->requireAuthentication();
        $app = App::i();

        if ($app->user->is('mediador')) {
            $agent = $this->createMediado();
            $this->data = array_merge($this->data, ['agent' => $agent->id, 'inciso' => 2]);
            $app->redirect($this->createUrl('nova_inscricao', $this->data ));
            
        } else if (isset($this->data['agent']) && $this->data['agent'] != "" ) {
            $agent = $app->repo('Agent')->find($this->data['agent']);
        } else {
            $agent = $app->user->profile;
        }
        // se ainda não tem inscrição
        if (!isset($agent->aldirblanc_inciso2_registration)) {
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
                $this->data['tipo']=1;
                $this->data['inciso']=2;
                $app->redirect($this->createUrl('selecionar_agente',$this->data));
            } else {

                // redireciona para a rota de criação de nova inscrição
                $data = array_merge( $this->data,['inciso' => 2,'agent' => $app->user->profile->id] );
                $app->redirect($this->createUrl('nova_inscricao', $data));
            }
        }
        $app->redirect($this->createUrl('formulario', [$agent->aldirblanc_inciso2_registration]));
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

        if ($app->user->is('mediador')) {
            $agent = $this->createMediado();

            $app->redirect($this->createUrl('nova_inscricao', ['agent' => $agent->id, 'inciso' => 1]));
            
        } else if (isset($this->data['agent']) && $this->data['agent'] != "" ) {
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
                $app->redirect($this->createUrl('selecionar_agente',['tipo'=>1, 'inciso' => 1]));
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
     * Redireciona o usuário para as oportunidades do inciso 3
     * 
     * rota: /aldirblanc/inciso3/[?agent={agent_id}]
     * 
     * @return void
     */
    function GET_fomentos()
    {               
        $app = App::i();
        $niceName = $app->user->profile->name;
        $opportunities = $this->getOpportunitiesInciso3();
        $this->requireAuthentication();
        $this->render('fomentos', ['opportunities' => $opportunities, 'cidades' => $cidades = [], 'niceName' => $niceName]);
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
        //verifica se existe e se o agente owner é individual
        if(!$agent || $agent->type->id != 1){
            // @todo tratar esse erro
            throw new \Exception();
        }
        $agent->checkPermission('@control');
        
        $registration = new \MapasCulturais\Entities\Registration;
        $registration->owner = $agent;

        if ($this->data['inciso'] == 1) {         
            $registration->opportunity = $this->getOpportunityInciso1();

        } else if($this->data['inciso'] == 2) {
            // inciso II
            if (!isset($this->data['opportunity']) || !isset($this->data['category'])) {
                // @todo tratar esse erro
                throw new \Exception();
            }

            $opportunity = $this->getOpportunityInciso2($this->data['opportunity']);


            $registration->opportunity = $opportunity;
            //pega o nome da category pela slug
            $category = $this->getCategoryName($this->data['category']);
            $registration->category = $category;

            //Espaço
            if (strpos($this->data['category'], 'espaco') !== false ) {
                //quantos espaços tem?
                $space_controller = $app->controller('space');
                $spaces_ids = $space_controller->apiQuery([
                    '@select' => 'id',
                    '@permissions' => '@control',
                ]);
                
                if(count($spaces_ids) == 1){
                    if (!isset($spaces_ids[0]['id']) || $spaces_ids[0]['id'] == "" ) {
                        // @todo tratar esse erro
                        throw new \Exception();
                    }
                    $space = $app->repo('space')->find($spaces_ids[0]['id']);
                }
                else if (count($spaces_ids) == 0) {
                    $space = new \MapasCulturais\Entities\Space;
                    //@TODO: confirmar tipo do Espaço
                    $space->owner = $agent;
                    $space->setType(199); //199 = outros espaços
                    $space->name = ' ';
                    $space->save(true);   
                }                  
                else if (count($spaces_ids) > 1 && (!isset($this->data['space']) || $this->data['space'] =='' )) {
                    // redireciona para a página de escolha de espaço
                    $app->redirect($this->createUrl('selecionar_espaco', ['agent' => $agent->id, 'inciso' =>2, 'category' => $this->data['category'],'opportunity' => $this->data['opportunity']]) );
                } 
                // Pega dados da página de seleção de espaço e cria o objeto do espaço
                if (isset($this->data['space']) && $this->data['space'] != "" ){
                    $space = $app->repo('space')->find($this->data['space']);  

                }
            }
            //É coletivo:
            else if (strpos($this->data['category'], 'coletivo') !== false ){
                $agent_controller = $app->controller('agent');
                $agentsQuery = $agent_controller->apiQuery([
                    '@select' => 'id,name,type,terms',
                    '@permissions' => '@control',
                    '@files' => '(avatar.avatarMedium):url',
                    'type'=>'EQ(2)',

                ]);
                
                if(count($agentsQuery) == 1 && !$app->user->is('mediador')){
                    if (!isset($agentsQuery[0]['id']) || $agentsQuery[0]['id'] == "" ) {
                        // @todo tratar esse erro
                        throw new \Exception();
                    }
                    $agentRelated = $app->repo('agent')->find($agentsQuery[0]['id']);
                }
                else if (count($agentsQuery) == 0 || $app->user->is('mediador') ) {
                    $app->disableAccessControl();
                    $agentRelated = new \MapasCulturais\Entities\Agent($agent->user);
                    //@TODO: confirmar nome e tipo do Agente coletivo
                    $agentRelated->name = ' ';
                    $agentRelated->type = 2;
                    $agentRelated->parent = $agent;
                    $agentRelated->save(true);
                    $app->enableAccessControl();
                }                  
                else if (count($agentsQuery) > 1 && (!isset($this->data['agentRelated']) || $this->data['agentRelated'] == '' )) {

                    // redireciona para a página de escolha de agente

                    $app->redirect($this->createUrl('selecionar_agente',
                    [
                        'tipo'        => 2,
                        'agentOwner'  => $agent->id,
                        'inciso'      => 2,
                        'category'    => $this->data['category'],
                        'opportunity' => $this->data['opportunity'],
                        ]
                    ));
                } 
                if (isset($this->data['agentRelated']) && ($this->data['agentRelated'] != '')){
                    $agentRelated = $app->repo('agent')->find($this->data['agentRelated']);  

                }
            }
        }

        $registration->inciso = $this->data['inciso'];

        $registration->save(true);
        if (isset($space)){
            $space->checkPermission('@control');
            $relation = new RegistrationSpaceRelationEntity();
            $relation->space = $space;
            $relation->owner = $registration;
            $relation->save(true);
        }
        if(isset($agentRelated)){
            $agentRelated->checkPermission('@control');
            $registration->createAgentRelation($agentRelated, 'coletivo');
        }
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
        $summaryStatusName = $this->getStatusNames();
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
        if($registration->status != Registration::STATUS_DRAFT){
            $app->redirect($this->createUrl('status', [$registration->id]));
        }
        $registration->checkPermission('modify');
        
        if (!$registration->termos_aceitos) {
            $app->redirect($this->createUrl('termos_e_condicoes', [$registration->id]));
        }
        //@todo verificar se funciona isso 
        //se existe espaco relacionado, ele tem nome em branco e tipo 199
        $registration->getSpaceRelation();
        if (($relation = $registration->getSpaceRelation()) && ($relation->space->type->id ==199 && $relation->space->name = '')){
            $registration->getSpaceRelation()->space->type = '';
        }

        $this->registerRegistrationMetadata($registration->opportunity);
        $app->view->includeEditableEntityAssets();
        $this->render('registration-edit', ['entity' => $registration]);
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

        if ($app->user->is('mediador')) {
            $app->redirect($this->createUrl('cadastro'));
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

    function GET_fixregistrationinciso1() {
        ini_set('max_execution_time', 0);
        App::i()->disableAccessControl();
        $op = App::i()->repo('Opportunity')->find($this->config['inciso1_opportunity_id']);
        $registrations = App::i()->repo('Registration')->findBy(['opportunity' => $op]);

        foreach ($registrations as $registration) {
            if($registration->inciso == null) {
                $registration->inciso = 1;
                $registration->save();
            } 
        }
        App::i()->em->flush();
        App::i()->enableAccessControl();
    }

    /**
     * Tela onde o usuário escolhe o inciso I ou II
     *
     * @return void
     */
    function GET_cadastro()
    {
        $this->requireAuthentication();
        
        $app = App::i();

        $controller = $app->controller('registration');

        $registrationsInciso1 = [];
        $registrationsInciso2 = [];

        $summaryStatusName = $this->getStatusNames();

        $owner_id = $app->user->profile->id;
        $owner_name = $app->user->profile->name;

        $repo = $app->repo('Registration');
        
        if ($this->config['inciso1_enabled']) {
            $inciso1 = $this->getOpportunityInciso1();
            $registrations = $controller->apiQuery([
                '@select' => 'id', 
                'opportunity' => "EQ({$inciso1->id})", 
                'status' => 'GTE(0)'
            ]);
            $registrations_ids = array_map(function($r) { return $r['id']; }, $registrations);
            $registrationsInciso1 = $repo->findBy(['id' => $registrations_ids ]);
        }

        $opportunitiesInciso2 = [];
        $registrationsInciso2 = [];
        
        if ($this->config['inciso2_enabled']) {
            $inciso2_ids = implode(',', $this->config['inciso2_opportunity_ids']);
            $registrations = $controller->apiQuery([
                '@select' => 'id', 
                'opportunity' => "IN({$inciso2_ids})", 
                'status' => 'GTE(0)'
            ]);
            $registrations_ids = array_map(function($r) { return $r['id']; }, $registrations);
            $registrationsInciso2 = $repo->findBy(['id' => $registrations_ids]);
            $opportunitiesIdsInciso2 = array_values($this->config['inciso2_opportunity_ids']);
            $opportunitiesInciso2 = $app->repo('Opportunity')->findRegistrationDateByIds($opportunitiesIdsInciso2); 
        }
        $opportunitiesInciso3 = [];
        if ($this->config['inciso2_enabled']) {
            $opportunitiesInciso3 = $this->getOpportunitiesInciso3();
        }
        $this->render('cadastro', [
                'cidades' => $this->getCidades(), 
                'registrationsInciso1' => $registrationsInciso1, 
                'registrationsInciso2' => $registrationsInciso2, 
                'summaryStatusName'=>$summaryStatusName, 
                'niceName' => $owner_name,
                'opportunitiesInciso2' => $opportunitiesInciso2,
                'opportunitiesInciso3' => $opportunitiesInciso3
            ]);
    }

    function GET_termos_e_condicoes()
    {
        $app = App::i();
        if (!isset($this->data['id']) || $this->data['id'] == "" ) {
            // @todo tratar esse erro
            throw new \Exception();
        }
        $this->requireAuthentication();
        $registration = $app->repo('Registration')->find($this->data['id']);
        
        $this->render('termos-e-condicoes-inciso'.$registration->inciso, ['registration_id' => $this->data['id']]);
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

        $tipo = $this->data['tipo'];
        if($tipo != 1 && $tipo != 2){
            //@TODO tratar esse erro
            throw new \Exception();
        }
        $this->requireAuthentication();
        $app = App::i();
        $tipo = $this->data['tipo'];
        $agent_controller = $app->controller('agent');
        $agentsQuery = $agent_controller->apiQuery([
            '@select' => 'id,name,type,terms',
            '@permissions' => '@control',
            '@files' => '(avatar.avatarMedium):url',
            'type'=>'EQ(' . $tipo . ')',
        ]);
        $agents= [];
        foreach($agentsQuery as $agent){
            $agentItem         = new \stdClass();
            $agentItem->id     = $agent['id'];
            $agentItem->name   = $agent['name'];
            $agentItem->avatar = isset($agent['@files:avatar.avatarMedium']) ? $agent['@files:avatar.avatarMedium']['url']: '';
            $agentItem->type   = $agent['type']->name;
            $agentItem->areas  = $agent['terms']['area'];
            array_push($agents, $agentItem);
        }
        //Ordena o array de agents pelo name
        usort($agents, function($a, $b) {return strcmp($a->name, $b->name);});
        $this->data['agents'] = $agents;
        $this->render('selecionar-agente', $this->data);
    }

    function GET_selecionar_espaco()
    {
        $this->requireAuthentication();

        $app = App::i();
        $space_controller = $app->controller('space');
        $spacesQuery = $space_controller->apiQuery([
            '@select' => 'id,name,terms,agent_id',
            '@permissions' => '@control',
        ]);
        $spaces= [];
        foreach($spacesQuery as $space){
            $spaceItem         = new \stdClass();
            $spaceItem->id     = $space['id'];
            $spaceItem->name   = $space['name'];
            $spaceItem->areas  = $space['terms']['area'];
            array_push($spaces, $spaceItem);
        }
        //Ordena o array de agents pelo name
        usort($spaces, function($a, $b) {return strcmp($a->name, $b->name);});
        $this->data['spaces'] = $spaces;
        $this->render('selecionar-espaco', $this->data);

    }
    /**
     * Confirmação de dados antes do envio do formulário
     * 
     * rota: /aldirblanc/confirmacao/{id_inscricao}
     * 
     * @return void
     */
    function GET_confirmacao()
    {
        $app = App::i();
        $this->requireAuthentication();
        //verificar se registration status
        $registration = $this->getRequestedEntity();
        if($registration->status != Registration::STATUS_DRAFT){
            $app->redirect($this->createUrl('status', [$registration->id]));
        }
        if (!$registration->termos_aceitos) {
            $app->redirect($this->createUrl('termos_e_condicoes', [$registration->id]));
        }
        $registration->checkPermission('control');
        $this->data['entity'] = $registration;
        $this->render('registration-confirmacao', $this->data);
    }


    function GET_generateOpportunities() {
        $this->requireAuthentication();

        $app = App::i();

        if(!$app->user->is('admin')) {
            $this->errorJson('Permissao negada', 403);
        }
        
        set_time_limit(0);
        
        $this->plugin->createOpportunityInciso1();
        $this->plugin->createOpportunityInciso2();

        $this->json("Sucesso");
    }

    /* REPORTE */
    function GET_reporte() {

        $data = [
            'inciso1' => null, 
            'inciso2' => null
        ];

        if ($this->config['inciso1_enabled']) {
            $data['inciso1'] = $this->getInciso1ReportData();
        }
        
        if ($this->config['inciso2_enabled']) {
            $data['inciso2'] = $this->getInciso2ReportData();
        }
        
        $this->render('reporte', $data);
    }

    function getInciso1ReportData() {
        if (!$this->config['inciso1_enabled']) return null;

        $app = App::i();

        $dql = "
            SELECT 
                COUNT(e.id) 
            FROM 
                MapasCulturais\Entities\Registration e 
            WHERE 
                e.status > 0 AND 
                e.opportunity = :opportunityId";

        $query = $app->em->createQuery($dql);

        $query->setParameters([
            'opportunityId' => $this->config['inciso1_opportunity_id']
        ]);

        return (object) [
            'total' => $query->getSingleScalarResult()
        ];
    }

    function getInciso2ReportData() {
        if (!$this->config['inciso2_enabled']) return null;

        $app = App::i();

        $dql = "
            SELECT 
                COUNT(e.id) 
            FROM 
                MapasCulturais\Entities\Registration e 
            WHERE 
                e.status > 0 AND 
                e.opportunity IN(:opportunities)";

        $query = $app->em->createQuery($dql);

        $query->setParameters([
            'opportunities' => array_values($this->config['inciso2_opportunity_ids'])
        ]);

        return (object) [
            'total' => $query->getSingleScalarResult()
        ];
    }
}
