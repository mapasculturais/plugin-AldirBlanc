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
        $opportunitiesByProject = $app->repo('ProjectOpportunity')->findBy(['ownerEntity' => $projectsIds, 'status' => 1 ] );
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
            Registration::STATUS_INVALID => i::__('Inválida', 'aldirblanc'),
        ];
        return $summaryStatusName;
    }

    /**
     * Endpoint para enviar emails das oportunidades
     */
    function ALL_sendEmails(){
        ini_set('max_execution_time', 0);
        
        $this->requireAuthentication();

        if (empty($this->data['opportunity'])) {
            $this->errorJson('O parâmetro opportunity é obrigatório');
        }

        $app = App::i();

        $opportunity = $app->repo('Opportunity')->find($this->data['opportunity']);

        if (!$opportunity) {
            $this->errorJson('Oportunidade não encontrada');
        }

        $opportunity->checkPermission('@control');

        $inciso1Ids = [$this->config['inciso1_opportunity_id']];
        $inciso2Ids = array_values($this->config['inciso2_opportunity_ids']);
        $inciso3Ids = is_array($this->config['inciso3_opportunity_ids']) ? $this->config['inciso3_opportunity_ids'] : [];

        $lab_opportunities = array_merge($inciso1Ids, $inciso2Ids, $inciso3Ids);

        if (!in_array($opportunity->id, $lab_opportunities)) {
            $this->errorJson("Oportunidade não é da Lei Aldir Blanc");
        }

        if (empty($this->data['status'])) {
            $status = '2,3,8,10';
        } else {
            $status = intval($this->data['status']);
            if (!in_array($status, [2,3,8,10])) {
                $this->errorJson('Os status válidos são 2, 3, 8 ou 10');
                die;
            }
        }

        $registrations = $app->em->getConnection()->fetchAll("
            SELECT 
                r.id,
                r.status,
                les.value AS last_email_status

            FROM registration r
                LEFT JOIN
                    registration_meta les ON 
                        les.object_id = r.id AND 
                        les.key = 'lab_last_email_status'
                    
            WHERE 
                r.opportunity_id = {$opportunity->id} AND 
                r.status IN ({$status}) AND 
                (les.value IS NULL OR les.value <> r.status::VARCHAR)

            ORDER BY r.sent_timestamp ASC");

        foreach ($registrations as &$reg) {
            $reg = (object) $reg;
            $registration = $app->repo('Registration')->find($reg->id);
            $this->sendEmail($registration);
        }
    }

    /**
     * Envia email com status da inscrição
     *
     */
    function sendEmail(Registration $registration){
        $app = App::i();
        $registrationStatusInfo = $this->getRegistrationStatusInfo($registration);

        $mustache = new \Mustache_Engine();
        $site_name = $app->view->dict('site: name', false);
        $baseUrl = $app->getBaseUrl();
        $justificativaAvaliacao = "";
        foreach ($registrationStatusInfo['justificativaAvaliacao'] as $message) {
            if (is_array($message) && !empty($this->config['exibir_resultado_padrao'] ) ) {
               $justificativaAvaliacao .= $message['message'] . "<hr>";
            }else{
                $justificativaAvaliacao .= $message .'<hr>';
            }
        }
        $filename = $app->view->resolveFilename("views/aldirblanc", "email-status.html");
        $template = file_get_contents($filename);

        $params = [
            "siteName" => $site_name,
            "urlImageToUseInEmails" => $this->config['logotipo_central'],
            "user" => $registration->owner->name,
            "inscricaoId" => $registration->id, 
            "inscricao" => $registration->number, 
            "statusNum" => $registration->status,
            "statusTitle" => $registrationStatusInfo['registrationStatusMessage']['title'],
            "justificativaAvaliacao" => $justificativaAvaliacao,
            "msgRecurso" => $this->config['msg_recurso'],
            "emailRecurso" => $this->config['email_recurso'],
            "baseUrl" => $baseUrl
        ];
        $content = $mustache->render($template,$params);
        $email_params = [
            'from' => $app->config['mailer.from'],
            'to' => $registration->owner->user->email,
            'subject' => $site_name . " - Status de inscrição",
            'body' => $content
        ];

        $app->log->debug("ENVIANDO EMAIL DE STATUS DA {$registration->number} ({$registrationStatusInfo['registrationStatusMessage']['title']})");
        $app->createAndSendMailMessage($email_params);

        
        $sent_emails = $registration->lab_sent_emails ;
        $sent_emails[] = [
            'timestamp' => date('Y-m-d H:i:s'),
            'loggedin_user' => [
                'id' => $app->user->id,
                'email' => $app->user->email,
                'name' => $app->user->profile->name 
            ],
            'email' => $email_params
        ];

        $app->disableAccessControl();
        $registration->lab_sent_emails = $sent_emails;

        $registration->lab_last_email_status = $registration->status;

        $registration->save(true);
        $app->enableAccessControl();
    }
    /**
     * Retorna Array com informações sobre o status de uma inscrição
     *
     * @return array
     */
    function getRegistrationStatusInfo(Registration $registration){
        $app = App::i();
        // retorna a mensagem de acordo com o status
        $getStatusMessages = $this->getStatusMessages();
        $registrationStatusInfo=[];
        $registrationStatusInfo['registrationStatusMessage'] = $getStatusMessages[$registration->status];
        // retorna as avaliações da inscrição
        $evaluations = $app->repo('RegistrationEvaluation')->findByRegistrationAndUsersAndStatus($registration);
        
        // monta array de mensagens
        $justificativaAvaliacao = [];

        if (in_array($registration->status, $this->config['exibir_resultado_padrao'])) {
            $justificativaAvaliacao[] = $getStatusMessages[$registration->status];
        }
        
        foreach ($evaluations as $evaluation) {

            if ($evaluation->getResult() == $registration->status) {
                
                if (in_array($evaluation->user->id, $this->config['avaliadores_dataprev_user_id']) && in_array($registration->status, $this->config['exibir_resultado_dataprev'])) {
                    // resultados do dataprev
                    $justificativaAvaliacao[] = $evaluation->getEvaluationData()->obs ?? '';
                } elseif (in_array($evaluation->user->id, $this->config['avaliadores_genericos_user_id']) && in_array($registration->status, $this->config['exibir_resultado_generico'])) {
                    // resultados dos avaliadores genericos
                    $justificativaAvaliacao[] = $evaluation->getEvaluationData()->obs ?? '';
                } 
                
                if (in_array($registration->status, $this->config['exibir_resultado_avaliadores']) && !in_array($evaluation->user->id, $this->config['avaliadores_dataprev_user_id']) && !in_array($evaluation->user->id, $this->config['avaliadores_genericos_user_id'])) {
                    // resultados dos demais avaliadores
                    $justificativaAvaliacao[] = $evaluation->getEvaluationData()->obs ?? '';
                }

            }
            
        }
        $registrationStatusInfo['justificativaAvaliacao'] = $justificativaAvaliacao;
        return $registrationStatusInfo;
    }
    /**
     * Retorna array associativo com mensagens para cada status da inscrição
     *
     * @return array
     */
    function getStatusMessages(){
        $summaryStatusMessages = [
            //STATUS_SENT = 1 - Em análise
            '1' => [
                'title'   => 'Sua solicitação segue em análise.',
                'message'  => $this->config['msg_status_sent']
            ],
            //STATUS_INVALID = 2 - Inválida
            '2' => [
                'title'    => 'Sua solicitação não foi aprovada.',
                'message'  => $this->config['msg_status_invalid']
            ],
            //STATUS_NOTAPPROVED = 3 - Reprovado
            '3' => [
                'title'    => 'Sua solicitação não foi aprovada.',
                'message'  => $this->config['msg_status_notapproved']
            ],
            //STATUS_APPROVED = 10 - Aprovado
            '10' => [
                'title'   => 'Sua solicitação foi aprovada.',
                'message' => $this->config['msg_status_approved']
            ],
            //STATUS_WAITLIST = 8 - Recursos Exauridos
            '8' => [
                'title'   => 'Sua solicitação foi validada.',
                'message' => $this->config['msg_status_waitlist']
            ]
        ];
        return $summaryStatusMessages;
    }
    
    function getCidades($ids = [])
    {
        $cidadesConfig = $this->config['inciso2_opportunity_ids'];
        if (count($ids)){
            $cidades = [];
            foreach ($cidadesConfig as $cidade => $id) {
                if (in_array($id, $ids)){
                    $cidades[$cidade] = $id;
                }
            }
            return $cidades;
        }
        return $cidadesConfig;
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

        $agent->createAgentRelation($app->user->profile, 'mediador', true, true);

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
          //se é coletivo cria um agente individual
        if ($agent->type->id == 2){
            unset($agent);
            $app->disableAccessControl();
            $agent = new \MapasCulturais\Entities\Agent($agent->user);
            //@TODO: confirmar nome e tipo do Agente coletivo
            $agent->name = ' ';
            $agent->type = 1;
            $agent->save(true);
            $app->enableAccessControl();
        }
       
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
        $app = App::i();
        if (isset($_SESSION['mediado_data']) && $app->user->is('guest') ){
            $data = $_SESSION['mediado_data'];
            $registration = $this->requestedEntity;
            $agentCpf = $this->cleanCpf($registration->owner->getMetadata('documento'));
            $sessionCpf = $this->cleanCpf($data['cpf']);
            if( $agentCpf == $sessionCpf && time() - $data['last_activity'] < 600 ){
                $_SESSION['mediado_data']['last_activity'] = time();
            }
            else{
                unset( $_SESSION['mediado_data'] );
            }
        }
        else{
            $this->requireAuthentication();
            $registration = $this->requestedEntity;

        }
        if(!$registration) {
            $app->pass();
        }
        if($registration->status == 0) {
            $app->redirect($this->createUrl('cadastro'));
        }
        $registration->checkPermission('view');
        $registrationStatusInfo = $this->getRegistrationStatusInfo($registration);

        // retorna a mensagem de acordo com o status
        $getStatusMessages = $this->getStatusMessages();
        $registrationStatusMessage = $getStatusMessages[$registration->status];

        // retorna as avaliações da inscrição
        $evaluations = $app->repo('RegistrationEvaluation')->findByRegistrationAndUsersAndStatus($registration);
        
        // monta array de mensagens
        $justificativaAvaliacao = [];

        if (in_array($registration->status, $this->config['exibir_resultado_padrao'])) {
            $justificativaAvaliacao[] = $getStatusMessages[$registration->status];
        }
        
        $recursos = [];

        foreach ($evaluations as $evaluation) {
            $validacao = $evaluation->user->aldirblanc_validador ?? null;
            if ($validacao == 'recurso') {
                $recursos[] = $evaluation;
            }

            if ($evaluation->getResult() == $registration->status) {
                
                if (in_array($evaluation->user->id, $this->config['avaliadores_dataprev_user_id']) && in_array($registration->status, $this->config['exibir_resultado_dataprev'])) {
                    // resultados do dataprev
                    $avaliacao = $evaluation->getEvaluationData()->obs ?? '';
                    if (!empty($avaliacao)) {
                        if (($registration->status == 3 || $registration->status == 2) && substr_count($evaluation->getEvaluationData()->obs, 'Reprocessado')) {

                            if ($this->config['msg_reprocessamento_dataprev']) {
                                $justificativaAvaliacao[] = $this->config['msg_reprocessamento_dataprev'];
                            } else {
                                $justificativaAvaliacao[] = $avaliacao;
                            }
                            
                        } else {
                            $justificativaAvaliacao[] = $avaliacao;
                        }
                    }
                } elseif (in_array($evaluation->user->id, $this->config['avaliadores_genericos_user_id']) && in_array($registration->status, $this->config['exibir_resultado_generico'])) {
                    // resultados dos avaliadores genericos
                    $justificativaAvaliacao[] = $evaluation->getEvaluationData()->obs ?? '';
                } 
                
                if (in_array($registration->status, $this->config['exibir_resultado_avaliadores']) && !in_array($evaluation->user->id, $this->config['avaliadores_dataprev_user_id']) && !in_array($evaluation->user->id, $this->config['avaliadores_genericos_user_id'])) {
                    // resultados dos demais avaliadores
                    $justificativaAvaliacao[] = $evaluation->getEvaluationData()->obs ?? '';
                }

            }
            
        }

        $this->render('status', [
            'registration' => $registration, 
            'registrationStatusMessage' => $registrationStatusMessage, 
            'justificativaAvaliacao' => array_filter($justificativaAvaliacao),
            'recursos' => $recursos
        ]);
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
        $ignoreDates = $this->config['mediadores_prolongar_tempo'] && $app->user->is('mediador');
        $now = new \DateTime('now');
        $notInTime = ($registration->opportunity->registrationFrom > $now || $registration->opportunity->registrationTo < $now );
        $showDraft = !($notInTime && !$ignoreDates);
        if (!$showDraft){
            $app->redirect($this->createUrl('cadastro'));
        }
        if (!$registration->termos_aceitos) {
            if ($app->user->is('mediador')) {
                $this->GET_aceitar_termos();
            }
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
        $ignoreDates = $this->config['mediadores_prolongar_tempo'] && $app->user->is('mediador');
        
        // pega inscrições do inciso 1
        $inciso1 = $this->getOpportunityInciso1();
        $registrations = $controller->apiQuery([
            '@select' => 'id', 
            'opportunity' => "EQ({$inciso1->id})", 
            'status' => 'GTE(0)'
        ]);
        $registrations_ids = array_map(function($r) { return $r['id']; }, $registrations);
        $registrationsInciso1 = $repo->findBy(['id' => $registrations_ids ]);
        // 

        $inciso1_enabled = $this->config['inciso1_enabled'];
        if ($this->config['inciso1_enabled'] || ( $ignoreDates )) {
            if ($app->user->is('mediador')){
                $allowed = $this->config['lista_mediadores'][$app->user->email] ?? '';
                
                if( !empty($allowed) && !in_array($inciso1->id, $allowed )){
                    $inciso1 = "";
                }
            }
            if($inciso1){
                $inciso1_enabled = true;
            }
        }

        $opportunitiesInciso2 = [];
        $registrationsInciso2 = [];
        $inciso2_enabled = $this->config['inciso2_enabled'] ;
        $inciso2_ids = $this->config['inciso2_opportunity_ids'];

        // busca inscrições
        $inciso2_ids_strings = implode(',', $inciso2_ids);
        $registrations = $controller->apiQuery([
            '@select' => 'id', 
            'opportunity' => "IN({$inciso2_ids_strings})", 
            'status' => 'GTE(0)'
        ]);
        $registrations_ids = array_map(function($r) { return $r['id']; }, $registrations);
        $registrationsInciso2 = $repo->findBy(['id' => $registrations_ids]);
        // 

        if ($inciso2_enabled || $ignoreDates) {
            if ($app->user->is('mediador')){
                $allowed = $this->config['lista_mediadores'][$app->user->email] ?? "";
                if (!$allowed){
                    $allowed = $inciso2_ids;
                }
                $inciso2_ids = array_filter($inciso2_ids, function($id) use($allowed){ 
                    if( in_array($id, $allowed )){
                        return $id;
                    }
                });
                $inciso2_ids = array_values($inciso2_ids);
            }

            if($inciso2_ids){
                $inciso2_enabled = true;
                $opportunitiesInciso2 = $app->repo('Opportunity')->findOpportunitiesWithDateByIds(array_values($inciso2_ids)); 
            }
        }
        $opportunitiesInciso3 = [];
        if ($this->config['inciso3_enabled']) {
            $opportunitiesInciso3 = $this->getOpportunitiesInciso3();
        }
         // redireciona admins para painel
         $opportunities_ids = array_values($this->config['inciso2_opportunity_ids']);
         $opportunities_ids[] = $this->config['inciso1_opportunity_id'];

         $opportunities = $app->repo('Opportunity')->findBy(['id' => $opportunities_ids]);

         $evaluation_method_configurations = [];

         foreach($opportunities as $opportunity) {
             $evaluation_method_configurations[] = $opportunity->evaluationMethodConfiguration;

             if($opportunity->canUser('@control') || $opportunity->canUser('viewEvaluations') || $opportunity->canUser('evaluateRegistrations')) {
                 $app->redirect($app->createUrl('painel'));

             }
         }
        $this->render('cadastro', [
                'inciso1Limite' => $this->config['inciso1_limite'],
                'inciso2Limite' => $this->config['inciso2_limite'],
                'inciso2_enabled' => isset($inciso2_ids) && $inciso2_ids ? $inciso2_enabled:false,
                'inciso1_enabled' => isset($inciso1) &&  $inciso1 ? $inciso1_enabled : false,
                'inciso3_enabled' => $app->user->is('mediador') ? false : $this->config['inciso3_enabled'],
                'cidades' => isset($inciso2_ids) && $inciso2_ids ? $this->getCidades($inciso2_ids) : [], 
                'registrationsInciso1' => isset($inciso1) &&  $inciso1 ? $registrationsInciso1 : [], 
                'registrationsInciso2' => isset($inciso2_ids) && $inciso2_ids ? $registrationsInciso2 : [], 
                'summaryStatusName'=>$summaryStatusName, 
                'niceName' => $owner_name,
                'opportunitiesInciso2' => isset($inciso2_ids) && $inciso2_ids ? $opportunitiesInciso2 : [],
                'opportunitiesInciso3' => $app->user->is('mediador') ? [] : $opportunitiesInciso3,
                'ignoreDates' => $ignoreDates
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
        $opportunityId = $registration->opportunity->id;

        if (!isset($registration->inciso) || $registration->inciso == ''){
            if ($opportunityId == $this->config['inciso1_opportunity_id'] ){
                $registration->inciso = 1;
            } else if (in_array($opportunityId, $this->config['inciso2_opportunity_ids']) ){
                $registration->inciso = 2;
            }
            $registration->save(true);
        }

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
            if ($app->user->is('mediador')) {
                $this->GET_aceitar_termos();
            }
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

    //Atualiza roles dos mediadores a partir da lista da configuração 
    function GET_atualizarmediadores() {
        $this->requireAuthentication();

        $app = App::i();

        if(!$app->user->is('admin')) {
            $this->errorJson('Permissao negada', 403);
        }
        
        set_time_limit(0);
        $mediadores = $this->config['lista_mediadores'];
        $emails = array_keys($mediadores);
        $users = $app->repo('User')->findBy(['email' => $emails]);
        foreach ($users as $u){
            $u->addRole('mediador');
        }
        $this->json($users);
    }


    /**
     * Tela para login dos mediados
     * 
     * rota: /aldirblanc/mediados
     * 
     * @return void
     */
    function ALL_mediados()
    {
        $app = APP::i();
        if (!count ($this->data) > 0){
            $this->render('mediados-login', ['errors'=>[], 'data' => $this->data]);
            return;
        }
        
        $cpf = ($this->data['cpf'] ?? '');
        $pass = ($this->data['password'] ?? '');
        $errors = [];
        if (!$cpf){
            $errors['user'] = "CPF não informado.";
        }
        if (!$pass){
            $errors['pass'] = "Senha não informada.";
        }
        if ($cpf){
            $cpf = $this->mask($cpf,'###.###.###-##');
            $agentMeta = $app->repo("AgentMeta")->findBy(array('key' => 'documento', 'value' => $cpf));
            $cpfClean = $this->cleanCpf($cpf);
            
            $agentMetaCpfClean = $app->repo("AgentMeta")->findBy(array('key' => 'documento', 'value' => $cpfClean));
            $agentMetas = array_merge($agentMeta, $agentMetaCpfClean);

            if (!$agentMetas){
                $errors['inexistente'] = "CPF não cadastrado";
            }
            
        }
        if(count($errors) > 0 ){
            $this->render('mediados-login', ['errors'=>$errors, 'data' => $this->data]);
           return;
        }
        $registrations = [];
        foreach ($agentMetas as $agentMeta) {
            $agent = $agentMeta->owner;
            $agentRegistrations = $app->repo('registration')->findBy(['owner' => $agent]);
            $registrations = array_merge($registrations, $agentRegistrations);
        }
        if(count($registrations) < 1){
            $errors['inexistente'] = "CPF incorreto.";
            $this->render('mediados-login', ['errors'=>$errors, 'data' => $this->data]);            
            return;
        }
        $app->disableAccessControl();
        $registrationsFiltered = array_filter($registrations, function($r) use($pass) { 
            if ($r->mediacao_senha && $r->mediacao_senha == md5($pass)){
                return $r;
            }
        });

        $registrationsFiltered = array_values($registrationsFiltered);
        $app->enableAccessControl();
        if(count($registrationsFiltered) < 1){
            $errors['inexistente'] = "Senha incorreta.";
            $this->render('mediados-login', ['errors'=>$errors, 'data' => $this->data]);
            return;
        }
        $summaryStatusName = $this->getStatusNames();
        $_SESSION['mediado_data'] = [
            'cpf' => $cpf,
            'last_activity' => time()
        ];
        // Caso só tenha um registro no cpf
        if (count($registrationsFiltered) == 1){
            $registrationStatusName = "";
            foreach($summaryStatusName as $key => $value) {
                if($key == $registrationsFiltered[0]->status) {
                    $registrationStatusName = $value;
                    break;
                }
            }
            
            $app->redirect($this->createUrl('status', [$registrationsFiltered[0]->id]));
            return;
        }
        else{
            foreach ($registrationsFiltered as $registration) {
                $registrationStatusName = "";
                foreach($summaryStatusName as $key => $value) {
                    if($key == $registration->status) {
                        $registration->statusName = $value;
                        break;
                    }
                }        
            }
            $this->render('lista-mediado', ['registrations' => $registrationsFiltered, 'registrationStatusName'=> $registrationStatusName]);
        } 
    }

    function ALL_reportMediacoes()
    {
        $this->requireAuthentication();
        $app = App::i();

        $requestedOpportunity = $this->controller->requestedEntity; //Tive que chamar o controller para poder requisitar a entity
        if (($requestedOpportunity->canUser('@control'))) {

            $registrations = $app->repo('Registration')->findBy(array('opportunity' => $requestedOpportunity->id));

            $registrationsByMediator = [];
            foreach ($registrations as $registration) {

                if (array_key_exists('mediador', $registration->getOwner()->getAgentRelationsGrouped())) {
                    $registrationsByMediator[] = $registration;
                }
            }
        }

        $filename = sprintf(\MapasCulturais\i::__("inscricoes-%s--mediacoes"), $entity->id);

        //$this->reportOutput('mediacao-csv', ['entity' => $entity, 'registrationsByMediator' => $registrationsByMediator], $filename);

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

        $conn = $app->em->getConnection();


        $enviadas = $conn->fetchAll("
            SELECT  
                o.id, 
                o.name, 
                count(r.*) as num_inscricoes 

            FROM 
                registration r, 
                opportunity o 

            WHERE 
                o.id = r.opportunity_id AND 
                o.id IN (
                        SELECT object_id 
                        FROM opportunity_meta 
                        WHERE key = 'aldirblanc_inciso' AND value = '2'
                ) AND 
                r.status > 0 AND 
                o.status > 0 

            GROUP BY 
                o.name, 
                o.id 

            ORDER BY 
                num_inscricoes desc,
                o.name ASC");



        $soh_rascunhos = $conn->fetchAll("
            SELECT  
                o.id, 
                o.name, 
                count(r.*) as num_inscricoes 

            FROM 
                registration r, 
                opportunity o 

            WHERE 
                o.id = r.opportunity_id AND 
                o.id IN (
                        SELECT object_id 
                        FROM opportunity_meta 
                        WHERE key = 'aldirblanc_inciso' AND value = '2'
                ) AND 
                o.id NOT IN (
                        SELECT opportunity_id 
                        FROM registration
                        WHERE status > 0
                ) AND
                r.status = 0 AND 
                o.status > 0 

            GROUP BY 
                o.name, 
                o.id 

            ORDER BY 
                num_inscricoes desc,
                o.name ASC");
            

        $sem_inscricao = $conn->fetchAll("
            SELECT 
                id, 
                name

            FROM 
                opportunity 

            WHERE 
                id NOT IN (
                        SELECT opportunity_id 
                        FROM registration
                ) AND 
                id IN (
                        SELECT object_id 
                        FROM opportunity_meta 
                        WHERE key = 'aldirblanc_inciso' AND 
                        value = '2'
                )

            ORDER BY name ASC
        ");

        return (object) [
            'total' => $query->getSingleScalarResult(),
            'enviadas' => $enviadas,
            'soh_rascunhos' => $soh_rascunhos,
            'sem_inscricao' => $sem_inscricao
        ];
    }
    function mask($val, $mask) {
        if (strlen($val) == strlen($mask)) return $val;
        $maskared = '';
        $k = 0;
        for($i = 0; $i<=strlen($mask)-1; $i++) {
            if($mask[$i] == '#') {
                if(isset($val[$k]))
                    $maskared .= $val[$k++];
            } else {
                if(isset($mask[$i]))
                    $maskared .= $mask[$i];
            }
        }
        return $maskared;
    }
    function cleanCpf($cpf){
        $cpfClean = str_replace("-","",$cpf);
        $cpfClean = str_replace(".","",$cpfClean);
        return $cpfClean;
    }
    
}
