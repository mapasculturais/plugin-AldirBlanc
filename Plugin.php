<?php

namespace AldirBlanc;

use MapasCulturais\App;
use MapasCulturais\Definitions\Role;
use MapasCulturais\Entities\Registration;
use MapasCulturais\i;

// @todo refatorar autoloader de plugins para resolver classes em pastas
require_once 'Controllers/AldirBlanc.php';
require_once 'Controllers/Remessas.php';
require_once 'vendor/autoload.php';

class Plugin extends \MapasCulturais\Plugin
{
    function __construct(array $config = [])
    {
        $app = App::i();
        // se for multisite pega do subsite_meta
        if ($app->view->subsite) {
            $config = $app->view->subsite->aldir_blanc_config;
        }

        $config += [
            'texto_home'=> env('AB_TEXTO_HOME','A Lei Aldir Blanc é fruto de forte mobilização social do campo artístico e cultural brasileiro, resultado de construção coletiva, a partir de webconferências nacionais e estaduais como plataformas políticas na formulação, articulação, tramitação e sanção presidencial.<br/><br/> Ela prevê o uso de 3 bilhões de reais para o auxílio de agentes da cultura atingidos pela pandemia da COVID-19. Investimentos para assegurar a preservação de toda a estrutura profissional e dinâmica de produção, criação, participação, preservação, formação e circulação dos bens e serviços culturais.<br/><br/> Clique no link abaixo para solicitar a renda emergencial como trabalhadora e trabalhador da cultura ou o subsídio para a manutenção de espaços artísticos e organizações culturais que tiveram as suas atividades interrompidas por força das medidas de isolamento social.'),
            'botao_home'=> env('AB_BOTAO_HOME','Solicite seu auxilio'),
            'titulo_home'=> env('AB_TITULO_HOME','Lei Aldir Blanc'),
            'logotipo_central' => env('AB_LOGOTIPO_CENTRAL',''),
            'logotipo_instituicao' => env('AB_LOGOTIPO_INSTITUICAO',''),
            'inciso1_enabled' => env('AB_INCISO1_ENABLE',true),
            'inciso2_enabled' => env('AB_INCISO2_ENABLE',true),
            'inciso3_enabled' => env('AB_INCISO3_ENABLE',false),
            'project_id' => env('AB_INCISO2_PROJECT_ID',null),
            'inciso1_opportunity_id' => env('AB_INCISO1_OPPORTUNITY_ID', null),
            'inciso2_opportunity_ids' => (array) json_decode(env('AB_INCISO2_OPPORTUNITY_IDS', '[]')),
            'inciso3_opportunity_ids' => (array) json_decode(env('AB_INCISO3_OPPORTUNITY_IDS', '[]')),
            'inciso1' => (array) json_decode(env('AB_INCISO1', '[]')),
            'inciso2' => (array) json_decode(env('AB_INCISO2_CITIES', '[]')),
            'inciso2_default' => (array) json_decode(env('AB_INCISO2_DEFAULT', '[]')),
            'inciso1_limite' => env('AB_INCISO1_LIMITE', 1),
            'inciso2_limite' => env('AB_INCISO2_LIMITE', 1),
            'inciso2_categories' => [
                'espaco-formalizado' => 'BENEFICIÁRIO COM CNPJ E ESPAÇO FÍSICO',
                'espaco-nao-formalizado' => 'BENEFICIÁRIO COM CPF E ESPAÇO FÍSICO',
                'coletivo-formalizado' => 'BENEFICIÁRIO COM CNPJ E SEM ESPAÇO FÍSICO',
                'coletivo-nao-formalizado' => 'BENEFICIÁRIO COM CPF E SEM ESPAÇO FÍSICO',
            ],
            'msg_inciso1_disabled' => env('AB_INCISO1_DISABLE_MESSAGE','Em breve!'),
            'msg_inciso2_disabled' => env('AB_INCISO2_DISABLE_MESSAGE','A solicitação deste benefício será lançada em breve. Acompanhe a divulgação pelas instituições responsáveis pela gestão da cultura em seu município!'),
            'link_suporte' => env('AB_LINK_SUPORTE',null),
            'privacidade_termos_condicoes' => env('AB_PRIVACIDADE_TERMOS',null),
            'mediados_owner' => env('AB_MEDIADOS_OWNER',''),
            'texto_categoria_espaco-formalizado' => env('AB_TXT_CAT_ESPACO_FORMALIZADO', '<strong>Entidade, empresa ou cooperativa do setor cultural com inscrição em CNPJ</strong> para espaço do tipo <strong>Espaço físico próprio, alugado, itinerante, público cedido em comodato, emprestado ou de uso compartilhado</strong>.' ),
            'texto_categoria_espaco-nao-formalizado' => env('AB_TXT_CAT_ESPACO_NAO_FORMALIZADO', '<strong>Espaço artístico e cultural mantido por coletivo ou grupo cultural (sem CNPJ) ou por pessoa física (CPF)</strong> para espaço do tipo <strong>Espaço físico próprio, alugado, itinerante, público cedido em comodato, emprestado ou de uso compartilhado</strong>.' ),
            'texto_categoria_coletivo-formalizado' => env('AB_TXT_CAT_COLETIVO_FORMALIZADO', '<strong>Entidade, empresa ou cooperativa do setor cultural com inscrição em CNPJ</strong> para espaço do tipo <strong>Espaço público (praça, rua, escola, quadra ou prédio custeado pelo poder público) ou espaço virtual de cultura digital</strong>.' ),
            'texto_categoria_coletivo-nao-formalizado' => env('AB_TXT_CAT_COLETIVO_NAO_FORMALIZADO', '<strong>Espaço artístico e cultural mantido por coletivo ou grupo cultural (sem CNPJ) ou por pessoa física (CPF)</strong> para espaço do tipo <strong>Espaço público (praça, rua, escola, quadra ou prédio custeado pelo poder público) ou espaço virtual de cultura digital</strong>.' ),
            'texto_cadastro_espaco'  => env('AB_TXT_CADASTRO_ESPACO', 'Espaço físico próprio, alugado, itinerante, público cedido em comodato, emprestado ou de uso compartilhado.'),
            'texto_cadastro_coletivo'  => env('AB_TXT_CADASTRO_COLETIVO', 'Espaço público (praça, rua, escola, quadra ou prédio custeado pelo poder público) ou espaço virtual de cultura digital.'),
            'texto_cadastro_cpf'  => env('AB_TXT_CADASTRO_CPF', 'Coletivo ou grupo cultural (sem CNPJ). Pessoa física (CPF) que mantêm espaço artístico'),
            'lista_mediadores' => (array) json_decode(env('AB_OPORTUNIDADES_MEDIADORES', '[]')),
            'mediadores_prolongar_tempo' => env('AB_MEDIADORES_PROLONGAR_TEMPO', false),
            'texto_cadastro_cnpj'  => env('AB_TXT_CADASTRO_CNPJ', 'Entidade, empresa ou cooperativa do setor cultural com inscrição em CNPJ.'),            
            'csv_generic_inciso2' => require_once env('AB_CSV_GENERIC_INCISO2', __DIR__ . '/config-csv-generic-inciso2.php'),
            'csv_generic_inciso3' => require_once env('AB_CSV_GENERIC_INCISO3', __DIR__ . '/config-csv-generic-inciso3.php'),
            'config-cnab240-inciso1' => require_once env('AB_TXT_CANAB240_INCISO1', __DIR__ . '/config-cnab240-inciso1.php'),
            'config-cnab240-inciso2' => require_once env('AB_TXT_CANAB240_INCISO2', __DIR__ . '/config-cnab240-inciso2.php'),

            'prefix_project' =>  env('AB_GERADOR_PROJECT_PREFIX', 'Lei Aldir Blanc - Inciso II | '),
            'config-mci460' => require_once env('AB_CONFIG_MCI460', __DIR__ . '/config-mci460.php'),
            'config-ppg10x' => require_once env('AB_CONFIG_PPG10x', __DIR__ . '/config-ppg10x.php'),

            // define os ids para dataprev e avaliadores genericos
            'avaliadores_dataprev_user_id' => (array) json_decode(env('AB_AVALIADORES_DATAPREV_USER_ID', '[]')),
            'avaliadores_genericos_user_id' => (array) json_decode(env('AB_AVALIADORES_GENERICOS_USER_ID', '[]')),
            
            // define a exibição do resultado das avaliações para cada status (1, 2, 3, 8, 10)
            'exibir_resultado_padrao' => (array) json_decode(env('AB_EXIBIR_RESULTADO_PADRAO', '["1", "2", "3", "8", "10"]')),
            'exibir_resultado_dataprev' => (array) json_decode(env('AB_EXIBIR_RESULTADO_DATAPREV', '[]')),
            'exibir_resultado_generico' => (array) json_decode(env('AB_EXIBIR_RESULTADO_GENERICO', '[]')),
            'exibir_resultado_avaliadores' => (array) json_decode(env('AB_EXIBIR_RESULTADO_AVALIADORES', '["10"]')),

            // mensagens de status padrao
            'msg_status_sent' => env('AB_STATUS_SENT_MESSAGE', 'Consulte novamente em outro momento. Você também receberá o resultado da sua solicitação por e-mail.'), // STATUS_SENT = 1
            'msg_status_invalid' => env('AB_STATUS_INVALID_MESSAGE', 'Não atendeu aos requisitos necessários ou os recursos disponíveis foram esgotados, conforme Incisos/Artº da Lei/Regulamentações: No preenchimento do Formulário de Inscrição, o requerente não atendeu ao Inciso V do Art. 6º da Lei nº 14.017/2020, e ao Inciso V do Art. 4º do Decreto 10.464/2020.'), // STATUS_INVALID = 2
            'msg_status_approved' => env('AB_STATUS_APPROVED_MESSAGE', '<p>Caso tenha optado por transação bancária, brevemente seu benefício será disponibilizado na conta informada.</p>
        <p>Caso tenha optado por ordem de pagamento, quando disponibilizado o recurso, você poderá realizar o saque diretamente em qualquer agência do Banco do Brasil pessoalmente - apresentando RG e CPF, sem nenhum custo.</p>
        <p>Em virtude da pandemia da covid-19, algumas agências do Banco do Brasil podem estar operando com restrições e horários diferenciados de funcionamento, conforme determinação do poder público.</p>'), // STATUS_APPROVED = 10
            'msg_status_notapproved' => env('AB_STATUS_NOTAPPROVED_MESSAGE', 'Não atendeu aos requisitos necessários. Caso não concorde com o resultado, você poderá enviar um novo formulário de solicitação ao benefício - fique atento ao preenchimento dos campos.'), // STATUS_NOTAPPROVED = 3
            'msg_status_waitlist' => env('AB_STATUS_WAITLIST_MESSAGE', 'Os recursos disponibilizados já foram destinados. Para sua solicitação ser aprovada será necessário aguardar possível liberação de recursos. Em caso de aprovação, você também será notificado por e-mail. Consulte novamente em outro momento.'), //STATUS_WAITLIST = 8

            // mensagem padrão para recurso das inscrições com status 2 e 3
            'msg_recurso' => env('AB_MENSAGEM_RECURSO', ''),

            // mensagem para reprocessamento do Dataprev, para ignorar a mensagem retornada pelo Dataprev e exibir a mensagem abaixo
            'msg_reprocessamento_dataprev' => env('AB_MENSAGEM_REPROCESSAMENTO_DATAPREV', ''),
                        
            // só libera para os homologadores as inscrićões que já tenham sido validadas pelos validadores configurados
            'homologacao_requer_validacao' => (array) json_decode(env('HOMOLOG_REQ_VALIDACOES', '[]')),

            // só consolida a a homologaćão se todos as validaćões já tiverem sido feitas
            'consolidacao_requer_validacao' => (array) json_decode(env('HOMOLOG_REQ_VALIDACOES', '["dataprev", "financeiro"]')),
            
            //zammad
            'zammad_enable' => env('AB_ZAMMAD_ENABLE', false),
            'zammad_src_form' => env('AB_ZAMMAD_SRC_FORM', ''),
            'zammad_src_chat' => env('AB_ZAMMAD_SRC_CHAT', ''),
            'zammad_background_color' => env('AB_ZAMMAD_BACKGROUND_COLOR', '#000000'),
             
            //pre inscrições
             'oportunidades_desabilitar_envio' => (array) json_decode(env('AB_OPORTUNIDADES_DESABILITAR_ENVIO', '[]')),
             'mensagens_envio_desabilitado' => (array) json_decode(env('AB_MENSAGENS_ENVIO_DESABILITADO', '[]')),
            
        ];

        $skipConfig = false;

        $app->applyHookBoundTo($this, 'aldirblanc.config', [&$config, &$skipConfig]);

        if (isset($_GET['ab_skip_cache'])) {
            $this->deleteConfigCache();
        }

        if (!$skipConfig) {
            if($cache = $this->getConfigCache()){
                $config = $cache;
            } else {
                $config = $this->configOpportunitiesIds($config);
                $this->setConfigCache($config);
            }
        }
        parent::__construct($config);
    }

    public function deleteConfigCache() {
        unlink(PRIVATE_FILES_PATH . 'plugin.AldirBlanc.config.cache.serialized');
    }

    public function getConfigCache()
    {
        $config_cache_filename = PRIVATE_FILES_PATH . 'plugin.AldirBlanc.config.cache.serialized';
        if (file_exists($config_cache_filename)) {
            if ($config = unserialize(file_get_contents($config_cache_filename))) {
                return $config;
            }
        }

        return null;
    }

    public function setConfigCache($config) {
        $config_cache_filename = PRIVATE_FILES_PATH . 'plugin.AldirBlanc.config.cache.serialized';
        if ($serialized = serialize($config)) {
            file_put_contents($config_cache_filename, $serialized);
            return true;
        } else {
            return false;
        }
    }

    public function configOpportunitiesIds($config)
    {

        if (empty($config['project_id'])) {
            return $config;
        }

        $app = App::i();

        $project = $app->repo('Project')->find($config['project_id']);

        if (!$project) {
            return $config;
        }

        $opportunityInciso1 = $app->repo('Opportunity')->findByProjectAndOpportunityMeta($project, 'aldirblanc_inciso', 1);

        if (!empty($opportunityInciso1)) {
            $config['inciso1_opportunity_id'] = $opportunityInciso1[0]->id;
        }

        $opportunitiesIds = [];
        foreach ($config['inciso2'] as $value) {
            $value = (array) $value;

            $opportunity = $app->repo('Opportunity')->findByProjectAndOpportunityMeta($project, 'aldirblanc_city', $value['city']);
            if (!empty($opportunity)) {
                $city = $value['city'];
                $opportunitiesIds[$city] = $opportunity[0]->id;
            }
        }

        if (!empty($opportunitiesIds)) {
            $config['inciso2_opportunity_ids'] = array_merge($config['inciso2_opportunity_ids'], $opportunitiesIds);
        }

        return $config;
    }

    public function registerAssets()
    {
        $app = App::i();

        // enqueue scripts and styles
        $app->view->enqueueScript('app', 'aldirblanc', 'aldirblanc/app.js');
        $app->view->enqueueStyle('aldirblanc', 'app-customization', 'aldirblanc/customization.css');
        $app->view->enqueueStyle('aldirblanc', 'app', 'aldirblanc/app.css');
        $app->view->enqueueStyle('aldirblanc', 'fontawesome', 'https://use.fontawesome.com/releases/v5.8.2/css/all.css');
        $app->view->assetManager->publishFolder('aldirblanc/img', 'aldirblanc/img');
    }

    public function _init()
    {
        $app = App::i();

        $plugin = $this;
        if($plugin->config['zammad_enable']) {
            // $app->view->enqueueStyle('app','chat','chat.css');
        }

        // adiciona informações do status das validações ao formulário de avaliação
        $app->hook('template(registration.view.evaluationForm.simple):before', function(Registration $registration, $opportunity) use($plugin, $app) {
            $inciso1Ids = [$plugin->config['inciso1_opportunity_id']];
            $inciso2Ids = array_values($plugin->config['inciso2_opportunity_ids']);
            $opportunities_ids = array_merge($inciso1Ids, $inciso2Ids);
            if (in_array($opportunity->id, $opportunities_ids) && $registration->consolidatedResult) {
                $em = $registration->getEvaluationMethod();
                $result = $em->valueToString($registration->consolidatedResult);
                echo "<div class='alert warning'> Status das avaliações: <strong>{$result}</strong></div>";
            }
        });

        // reordena avaliações antes da reconsolidação, colocando as que tem id = registration_id no começo, 
        // pois indica que foram importadas
        $app->hook('controller(opportunity).reconsolidateResult', function($opportunity, &$evaluations) {

            usort($evaluations, function($a,$b) {
                if(preg_replace('#[^\d]+#', '', $a['number']) == $a['id']) {
                    return -1;
                } else if(preg_replace('#[^\d]+#', '', $b['number']) == $b['id']) {
                    return 1;
                } else {
                    $_a = (int) $a['id'];
                    $_b = (int) $b['id'];
                    return $_a <=> $_b;
                }
            });

        });

         //Botão exportador CNAB240 BB
         $app->hook('template(opportunity.single.header-inscritos):end', function () use($plugin, $app){
            $inciso1Ids = [$plugin->config['inciso1_opportunity_id']];
            $inciso2Ids = array_values($plugin->config['inciso2_opportunity_ids']);
            $inciso3Ids = [];//$plugin->config['inciso3_opportunity_ids'];
            $opportunities_ids = array_merge($inciso1Ids, $inciso2Ids, $inciso3Ids);
            $requestedOpportunity = $this->controller->requestedEntity; //Tive que chamar o controller para poder requisitar a entity
            $opportunity = $requestedOpportunity->id;

            //Configura em que incisos deve ser exibido o botão do CNAB240. deixar o array vazio para nao exibir
            $exibirBtnIncisos = [1];            
            
            $selectList = false;            
            if(($requestedOpportunity->canUser('@control')) && in_array($requestedOpportunity->id,$opportunities_ids) ) {
                $selectList = true;
                $app->view->enqueueScript('app', 'aldirblanc', 'aldirblanc/app.js');
                if (in_array($requestedOpportunity->id, $inciso1Ids)){
                    $inciso = 1;

                }
                else if (in_array($requestedOpportunity->id, $inciso2Ids)){
                    $inciso = 2;

                }else if(in_array($requestedOpportunity->id, $inciso3Ids)){
                    $inciso = 3;

                }
                if(in_array($inciso, $exibirBtnIncisos)){ //<= Configurar para exibir o botão do CNAB 240
                    $this->part('aldirblanc/cnab240-txt-button', ['inciso' => $inciso, 'opportunity' => $opportunity, 'selectList' => $selectList, 'exibirBtnIncisos' =>$exibirBtnIncisos]);
                }
            }
        });

        $app->hook('opportunity.registrations.reportCSV', function(\MapasCulturais\Entities\Opportunity $opportunity, $registrations, &$header, &$body) use($app) {
            $em = $opportunity->getEvaluationMethod();

            $_evaluations = $app->repo('RegistrationEvaluation')->findBy(['registration' => $registrations]);

            $evaluations_avaliadores = [];
            $evaluations_status = [];
            $evaluations_obs = [];
            $registrations_mediadas = [];

            foreach ($_evaluations as $eval) {

                if (substr($eval->user->email,-10) == '@validador') {
                    continue;
                }

                if (isset($evaluations_status[$eval->registration->number])) {
                    if ($eval->result < $evaluations_status[$eval->registration->number]) {
                        $evaluations_status[$eval->registration->number] = $em->valueToString($eval->result);
                    }
                } else {
                    $evaluations_status[$eval->registration->number] = $em->valueToString($eval->result);
                }

                $obs = $eval->evaluationData->obs ?? json_encode($eval->evaluationData);

                if (isset($evaluations_obs[$eval->registration->number])) {
                    $evaluations_obs[$eval->registration->number] .= "\n-------------\n" . $obs;
                } else {
                    $evaluations_obs[$eval->registration->number] = $obs;
                }

                if (isset($evaluations_avaliadores[$eval->registration->number])) {
                    $evaluations_avaliadores[$eval->registration->number] .= "\n-------------\n" . $eval->user->profile->name;
                } else {
                    $evaluations_avaliadores[$eval->registration->number] = $eval->user->profile->name;
                }
            }

            foreach ($registrations as $r) {
                if ($r->mediacao_senha && $r->mediacao_contato) {
                    $registrations_mediadas[$r->number] = 'Sim';
                } else {
                    $registrations_mediadas[$r->number] = 'Não';
                }
            }

            $header[] = 'Homologação - avaliadores';
            $header[] = 'Homologação - status';
            $header[] = 'Homologação - obs';
            $header[] = 'Inscrição Mediada?';

            foreach($body as $i => $line){
                $body[$i][] = $evaluations_avaliadores[$line[0]] ?? null;
                $body[$i][] = $evaluations_status[$line[0]] ?? null;
                $body[$i][] = $evaluations_obs[$line[0]] ?? null;
                $body[$i][] = $registrations_mediadas[$line[0]] ?? null;
            }
        });
       
        // modulo de mediacao
        $app->hook('entity(Agent).canUser(<<viewPrivateData>>)', function($user,&$can) use($app){
            

            if (isset($_SESSION['mediado_data']) && $user->is('guest') ){
                $data = $_SESSION['mediado_data'];
                $data = $_SESSION['mediado_data'];
                $cpf = $this->getMetadata('documento');
                $cpfClean = str_replace("-","",$cpf);
                $cpfClean = str_replace(".","",$cpfClean);
                $cpfSession = $data['cpf'];
                $cpfSessionClean =str_replace("-","",$cpfSession);
                $cpfSessionClean = str_replace(".","",$cpfSessionClean);
                if( $cpfClean == $cpfSessionClean && time() - $data['last_activity'] < 600 ){
                    $can = true;
                    $_SESSION['mediado_data']['last_activity'] = time();
                }
                else{
                    unset( $_SESSION['mediado_data'] );
               }
            }
        });
        $app->hook('entity(Registration).canUser(<<@control|view|viewPrivateData|viewConsolidatedResult>>)', function($user,&$can) use($app){
            
            if (isset($_SESSION['mediado_data']) && $user->is('guest') ){
                $data = $_SESSION['mediado_data'];
                $cpf = $this->owner->getMetadata('documento');
                $cpfClean = str_replace("-","",$cpf);
                $cpfClean = str_replace(".","",$cpfClean);
                $cpfSession = $data['cpf'];
                $cpfSessionClean =str_replace("-","",$cpfSession);
                $cpfSessionClean = str_replace(".","",$cpfSessionClean);
                if( $cpfSessionClean == $cpfClean && time() - $data['last_activity'] < 600 ){
                    $can = true;
                    $_SESSION['mediado_data']['last_activity'] = time();

                }
                else{
                    unset( $_SESSION['mediado_data'] );
                }
            }
            

        });
        // Permite mediadores cadastrar fora do prazo
        $app->hook('entity(Registration).canUser(<<send>>)', function($user,&$can) use($plugin, $app){
            $oportunidades_desabilitar_envio = $plugin->config['oportunidades_desabilitar_envio'];
            $cant_send =  in_array($this->opportunity->id, $oportunidades_desabilitar_envio );
            if ($cant_send){
                $can = false;
                return;
            }
            
            if ( $app->user->is('mediador') ){
                $allowed_opportunities = $plugin->config['lista_mediadores'][$app->user->email];
                if ($allowed_opportunities == []){
                    $allowed = true;
                }
                else{
                    $allowed =  in_array($this->opportunity->id, $allowed_opportunities );
                }
                if ( $allowed && $plugin->config['mediadores_prolongar_tempo'] ){
                    $can = true;
                }
            }
        });

        // botão exportadores desbancarizados
        $app->hook('template(opportunity.single.header-inscritos):end', function () use($plugin, $app) {
            // condiciona exibição do botão a uma configuração
            if (!isset($plugin->config['exporta_desbancarizados']) ||
                !is_array($plugin->config['exporta_desbancarizados']) ||
                empty($plugin->config['exporta_desbancarizados'])) {
                return;
            }
            $requestedOpportunity = $this->controller->requestedEntity;
            $opportunity = $requestedOpportunity->id;
            // exclui qualquer oportunidade que não seja inciso 1 (sujeito a futuras alterações)
            if ($opportunity != $plugin->config['inciso1_opportunity_id']) {
                return;
            }
            if ($requestedOpportunity->canUser('@control')) {
                $app->view->enqueueScript('app', 'aldirblanc', 'aldirblanc/app.js');
                $this->part('aldirblanc/bankless-button', [
                    'inciso' => 1,
                    'opportunity' => $opportunity,
                    'exports' => $plugin->config['exporta_desbancarizados'],
                    'selectList' => true,
                ]);
            }
            return;
        });

        //Botão exportador genérico
        $app->hook('template(opportunity.single.header-inscritos):end', function () use($plugin, $app){
            $inciso1Ids = [$plugin->config['inciso1_opportunity_id']];
            $inciso2Ids = array_values($plugin->config['inciso2_opportunity_ids']);
            $inciso3Ids = array_values($plugin->config['inciso3_opportunity_ids']);
            $opportunities_ids = array_merge($inciso1Ids, $inciso2Ids, $inciso3Ids);
            $requestedOpportunity = $this->controller->requestedEntity; //Tive que chamar o controller para poder requisitar a entity
            $opportunity = $requestedOpportunity->id;
            $selectList = false;
            if(($requestedOpportunity->canUser('@control')) && in_array($requestedOpportunity->id,$opportunities_ids) ) {
                $selectList = true;
                $app->view->enqueueScript('app', 'aldirblanc', 'aldirblanc/app.js');
                if (in_array($requestedOpportunity->id, $inciso1Ids)){
                    $inciso = 1;
                }
                else if (in_array($requestedOpportunity->id, $inciso2Ids)){
                    $inciso = 2;
                }
                else if (in_array($requestedOpportunity->id, $inciso3Ids)){
                    $inciso = 3;
                }
                $this->part('aldirblanc/csv-generic-button', ['inciso' => $inciso, 'opportunity' => $opportunity, 'selectList'=> $selectList]);
            }
        });

        // uploads de desbancarizados
        $app->hook('template(opportunity.<<single|edit>>.sidebar-right):end', function () use ($plugin) {
            // condiciona exibição da área de uploads à configuração que controla o botão de exportação
            if (!isset($plugin->config['exporta_desbancarizados']) ||
                !is_array($plugin->config['exporta_desbancarizados']) ||
                empty($plugin->config['exporta_desbancarizados'])) {
                return;
            }
            $opportunity = $this->controller->requestedEntity;
            if ($opportunity->canUser('@control')) {
                $this->part('aldirblanc/bankless-uploads', ['entity' => $opportunity]);
            }
        });

        /**
         * só consolida as avaliações para "selecionado" se tiver acontecido as validações (dataprev, etc)
         * 
         * @TODO: implementar para método de avaliaçào documental
         */
        $app->hook('entity(Registration).consolidateResult', function(&$result, $caller) use($plugin, $app) {
            // só aplica o hook para as oportunidades do inciso I e II
            $ids = $plugin->config['inciso2_opportunity_ids'] ?: [];
            $ids[] = $plugin->config['inciso1_opportunity_id'];

            if (!in_array($this->opportunity->id, $ids)) {
                return;
            }

            // só aplica o hook para usuários homologadores
            if ($caller->user->aldirblanc_validador) {
                return;
            }

            $evaluations = $app->repo('RegistrationEvaluation')->findBy(['registration' => $this, 'status' => 1]);

            $result = $caller->result;
            
            foreach ($evaluations as $eval) {
                if ($eval->user->aldirblanc_avaliador) {
                    continue;
                }

                if(intval($eval->result) < intval($result)) {
                    $result = "$eval->result";
                }
            }

            
            // se a consolidação não é para selecionada (statu = 10) pode continuar
            if ($result != '10') {
                return;
            } 

            $can_consolidate = true;


            /**
             * Se a consolidação requer validações, verifica se existe alguma
             * avaliação dos usuários validadores
             */
            if ($validacoes = $plugin->config['consolidacao_requer_validacao']) {
                foreach($validacoes as $slug) {
                    $can = false;
                    foreach ($evaluations as $eval) {
                        if ($eval->user->aldirblanc_validador == $slug) {
                            $can = true;
                        }
                    }
                    
                    if (!$can) {
                        $can_consolidate = false;
                    }
                }
            }
            
            $tem_validacoes = false;
            foreach ($evaluations as $eval) {
                if ($eval->user->aldirblanc_validador) {
                    $tem_validacoes = true;
                }
            }

            // se não pode consolidar, coloca a string 'homologado'
            if (!$can_consolidate) {
                if (!$this->consolidatedResult || count($evaluations) <= 1 || !$tem_validacoes) {
                    $result = 'homologado';
                } else if (strpos($this->consolidatedResult, 'homologado') === false) {
                    $result = "homologado, {$this->consolidatedResult}";
                } else {
                    $result = $this->consolidatedResult;
                }
            }
        });


        if($this->_config['homologacao_requer_validacao']){
            /**
             * para o caso das instalaćões que homologarão depois do retorno do Dataprev,
             * só dá permissão para o usuário avaliar depois das validaćões configuradas
             */
            $app->hook('entity(Registration).canUser(<<evaluate|viewUserEvaluation>>)', function($user, &$can) use($plugin, $app) {
                $ids = [];
                if ($plugin->config['inciso2_enabled']) {
                    $ids = $plugin->config['inciso2_opportunity_ids'];
                }
                
                if ($plugin->config['inciso1_enabled']) {
                    $ids[] = $plugin->config['inciso1_opportunity_id'];
                }
    
                if (!in_array($this->opportunity->id, $ids)) {
                    return;
                }

                if($user->is('guest')) {
                    return;
                }
    
                if ($user->aldirblanc_validador) {
                    return;
                }

                if ($can) {
                    $evaluations = $app->repo('RegistrationEvaluation')->findBy(['registration' => $this, 'status' => 1]);
                    foreach ($plugin->config['homologacao_requer_validacao'] as $validador) {
                        $ok = false;
                        foreach ($evaluations as $evaluation) {
                            if($evaluation->user->aldirblanc_validador == $validador && $evaluation->result == '10'){
                                $ok = true;
                            }
                        }

                        if(!$ok) {
                            $can = false;
                        }
                    }
                }
                
            });
        }

        $app->hook('template(panel.opportunities.panel-header):end', function () use($app){
            if(!$app->user->is('admin')) {
                return;
            }
            $this->part('aldirblanc/generate-opportunities-button');
        });
        
        // add hooks
        $app->hook('mapasculturais.styles', function () use ($app) {
            $app->view->printStyles('aldirblanc');
        });

        //No cadastro da oportunidade (inciso2), adiciona os campos para bloqueio de edição/deleção
        $app->hook('opportunity.blockedFields', function ($entity) use ($app) {
            if (!$app->user->is('admin')) {
                $app->view->jsObject['blockedOpportunityFields'] = $entity->aldirBlancFields;
            }
        });

        //No cadastro da oportunidade (inciso2), muda a permissao de editar as categorias
        $app->hook('opportunity.blockedCategoryFields', function (&$entity, &$can_edit) use ($app) {
            if (!$app->user->is('admin')) {
                $fields = $entity->aldirBlancFields;
                if (!empty($fields)) {
                    $can_edit = false;
                }
            }
        });

        //No cadastro da oportunidade (inciso2), apresenta mensagem de bloqueio de edição das categorias
        $app->hook('template(opportunity.<<create|edit>>.categories-messages):begin', function ($entity) use ($app) {
            if (!$app->user->is('admin')) {
                $fields = $entity->aldirBlancFields;
                if (!empty($fields)) {
                    $this->part('aldirblanc/categories-messages');
                }
            }
        });


        $app->hook('template(subsite.<<create|edit>>.tabs):end', function () {
            $this->part('aldirblanc/subsite-tab');
        });

        $app->hook('template(subsite.<<create|edit>>.tabs-content):end', function () {
            $this->part('aldirblanc/subsite-tab-content');
        });

        // $app->hook('template(site.index.home-search):end', function () use ($plugin) {
        //     $texto = $plugin->config['texto_home'];
        //     $botao = $plugin->config['botao_home'];
        //     $titulo = $plugin->config['titulo_home'];
        //     $this->part('aldirblanc/home-search', ['texto' => $texto, 'botao' => $botao, 'titulo' => $titulo]);
        // });

        /**
         * modifica o template do autenticador quando o redirect url for para o plugin aldir blanc
         */
        $app->hook('controller(auth).render(<<*>>)', function () use ($app, $plugin) {
            $redirect_url = $_SESSION['mapasculturais.auth.redirect_path'] ?? '';

            if (strpos($redirect_url, '/aldirblanc') === 0) {
                $plugin->registerAssets();

                $req = $app->request;
                $this->layout = 'aldirblanc';
            }
        });

        $app->hook('auth.createUser:redirectUrl', function(&$redirectUrl) {
            if(isset($_SESSION['mapasculturais.auth.redirect_path']) && strpos($_SESSION['mapasculturais.auth.redirect_path'], '/aldirblanc') === 0) {
                $redirectUrl =  '/aldirblanc';
            } 
        });

        $plugin = $this;

        /**
         * Na criação da inscrição, define os metadados inciso2_opportunity_id ou 
         * inciso1_opportunity_id do agente responsável pela inscrição
         */
        $app->hook('entity(Registration).save:after', function () use ($plugin) {

            if (in_array($this->opportunity->id, $plugin->config['inciso2_opportunity_ids'])) {
                $agent = $this->owner;
                $agent->aldirblanc_inciso2_registration = $this->id;
                $agent->save(true);
            } else if ($this->opportunity->id == $plugin->config['inciso1_opportunity_id']) {
                $agent = $this->owner;
                $agent->aldirblanc_inciso1_registration = $this->id;
                $agent->save(true);
            }
        });

        $app->hook('GET(aldirblanc.<<*>>):before', function () use ($plugin, $app) {
            if ($app->user->is('mediador')) {
                $limit = 1000;

                $plugin->_config['inciso1_limite'] = $limit;
                $plugin->_config['inciso2_limite'] = $limit;
            }
        });

        // Adiciona permissão para mediador se o email do usuário estiver na lista de mediadores na config
        $app->hook('entity(User).save:after', function() use ($plugin, $app) {
            $emails = array_keys($plugin->config['lista_mediadores']);
            if (in_array($this->email, $emails)) {
                $this->addRole('mediador');
            }
        });
        // atualiza roles de mediadores conforme lista de emails
        $app->hook('template(panel.agents.panel-header):end', function () use($app){
            if(!$app->user->is('admin')) {
                return;
            }
            $this->part('aldirblanc/generate-mediadores-button');
        });
        $app->hook('auth.successful', function() use($plugin, $app) {
            $opportunities_ids = array_values($plugin->config['inciso2_opportunity_ids']);
            $opportunities_ids[] = $plugin->config['inciso1_opportunity_id'];

            $opportunities = $app->repo('Opportunity')->findBy(['id' => $opportunities_ids]);
            
            foreach($opportunities as $opportunity) { 
                if($opportunity->canUser('@control')) {
                    $_SESSION['mapasculturais.auth.redirect_path'] = $app->createUrl('panel', 'index');
                }
            }
        });

        // Redireciona usuário que acessar a oportunidade dos incisos I e II pelo mapas para o plugin
        $app->hook('GET(opportunity.single):before', function() use($plugin, $app) {
            $opportunities_ids = array_values($plugin->config['inciso2_opportunity_ids']);
            $opportunities_ids[] = $plugin->config['inciso1_opportunity_id'];
            $requestedOpportunity = $this->requestedEntity;
            if (!$requestedOpportunity) {
                return;
            }
            $can_view = $requestedOpportunity->canUser('@control') || 
                        $requestedOpportunity->canUser('viewEvaluations') || 
                        $requestedOpportunity->canUser('evaluateRegistrations');
            if(!$can_view && in_array($requestedOpportunity->id,$opportunities_ids) ) {
                $url = $app->createUrl('aldirblanc', 'cadastro');
                $app->redirect($url);
            }
        });
        $app->hook('GET(registration.view):before', function() use($plugin, $app) {
            $opportunities_ids = array_values($plugin->config['inciso2_opportunity_ids']);
            $opportunities_ids[] = $plugin->config['inciso1_opportunity_id'];
            $registration = $this->requestedEntity;
            $requestedOpportunity = $registration->opportunity;
            if (!$requestedOpportunity) {
                return;
            }
            $can_view = $requestedOpportunity->canUser('@control') || 
                        $requestedOpportunity->canUser('viewEvaluations') || 
                        $requestedOpportunity->canUser('evaluateRegistrations');

            if(!$can_view && in_array($requestedOpportunity->id,$opportunities_ids) ) {
                $url = $app->createUrl('aldirblanc', 'formulario',[$registration->id]);
                $app->redirect($url);
            }
        });

        /**
         * Carrega campo adicional "Mensagem de Recurso" nas oportunidades
         * @return void
         */
        $app->hook('view.partial(singles/opportunity-registrations--importexport):before', function () use ($plugin, $app) {
            $this->part('aldirblanc/status-recurso-fields', ['opportunity' => $this->controller->requestedEntity]);
        });
        
        $app->hook('view.partial(footer):before', function() use($plugin, $app) {
            if($plugin->config['zammad_enable']) {
                ?>

            
            <script src="<?= $plugin->config['zammad_src_chat']; ?>"></script>
            <script>
                $(function() {
                new ZammadChat({
                    background: ("<?= $plugin->config['zammad_background_color']?>"),
                    fontSize: '12px',
                    chatId: 1,
                    title: '<strong>Dúvidas?</strong> Fale conosco'
                });
                });
        </script>
         <style>.zammad-chat{
            z-index: 9999!important;
        }</style>
    
    <?php
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
        $app->registerController('remessas', 'AldirBlanc\Controllers\Remessas');

        // registra o role para mediadores
        $role_definition = new Role('mediador', 'Mediador', 'Mediadores', true, function ($user) {
            return $user->is('admin');
        });
        $app->registerRole($role_definition);

        $def_autorizacao = new \MapasCulturais\Definitions\FileGroup('mediacao-autorizacao', [
            '^application/.*$',
            '^image/(jpeg|png)$'
        ], ['O arquivo deve ser um documento ou uma imagem .jpg ou .png'], true, null, true);

        $def_documento = new \MapasCulturais\Definitions\FileGroup('mediacao-documento', [
            '^application/.*',
            '^image/(jpeg|png)$'
        ], ['O arquivo deve ser um documento ou uma imagem .jpg ou .png'], true, null, true);

        // registra campos para mediaçào
        $app->registerFileGroup('aldirblanc', $def_autorizacao);
        $app->registerFileGroup('aldirblanc', $def_documento);

        // registrinado metadados
        $this->registerMetadata('MapasCulturais\Entities\Registration', 'mediacao_contato_tipo', [
            'label' => i::__('Tipo de contato da mediação'),
            'type' => 'select',
            'private' => true,
            'options' => [
                'telefone-fixo' => i::__('Telefone Fixo'),
                'whatsapp' => i::__('Whatsapp'),
                'sms' => i::__('SMS'),
            ]
        ]);

        $this->registerMetadata('MapasCulturais\Entities\Registration', 'lab_sent_emails', [
            'label' => i::__('E-mails enviados'),
            'type' => 'json',
            'private' => true,
            'default' => '[]'
        ]);

        $this->registerMetadata('MapasCulturais\Entities\Registration', 'lab_last_email_status', [
            'label' => i::__('Status do último e-mail enviado'),
            'type' => 'integer',
            'private' => true
        ]);

        $this->registerMetadata('MapasCulturais\Entities\Registration', 'mediacao_contato', [
            'label' => i::__('Número telefônico do contato'),
            'type' => 'text',
            'private' => true
        ]);

        // mediação senha
        $this->registerMetadata('MapasCulturais\Entities\Registration', 'mediacao_senha', [
            'label'   => i::__('Senha'),
            'type'    => 'text',
            'private' => true,
            'serialize' => function ($val) {
                return md5($val);
            },
        ]);

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

        $this->registerMetadata('MapasCulturais\Entities\Opportunity', 'aldirBlancFields', [
            'label' => i::__('Lista de ID dos campos AldirBlanc'),
            'type' => 'array',
            'serialize' => function ($val) {
                return json_encode($val);
            },
            'unserialize' => function ($val) {
                return json_decode($val);
            },
            'private' => true,
        ]);

        $this->registerMetadata('MapasCulturais\Entities\Opportunity', 'aldirblanc_inciso', [
            'label' => i::__('Inciso do Aldirblanc'),
            'type' => 'string',
            'private' => true,
        ]);

        $this->registerMetadata('MapasCulturais\Entities\Opportunity', 'aldirblanc_city', [
            'label' => i::__('Cidades do Aldirblanc'),
            'type' => 'string',
            'private' => true,
        ]);

        $this->registerMetadata('MapasCulturais\Entities\Registration', 'inciso', [
            'label' => i::__('Inciso'),
            'type' => 'number',
            'private' => true,
        ]);

        if ($this->config['inciso1_enabled']) {
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

        if ($this->config['inciso2_enabled']) {
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

        /**
         * Registra campo adicional "Mensagem de Recurso" nas oportunidades
         * @return void
         */
        $this->registerMetadata('MapasCulturais\Entities\Opportunity', 'aldirblanc_status_recurso', [
            'label' => i::__('Mensagem para Recurso na tela de Status'),
            'type' => 'text'
        ]);
        // metadados do agente para processos de abertura de conta
        $this->registerMetadata('MapasCulturais\Entities\Agent',
                                'account_creation', [
            'label' => i::__('Dados para abertura de conta'),
            'type' => 'json',
            'private' => true,
        ]);
        // metadados da oportunidade para suporte a arquivos de desbancarizados
        $this->registerMetadata('MapasCulturais\Entities\Opportunity',
                                'bankless_processed_files', [
            'label' => 'Arquivos de Desbancarizados Processados',
            'type' => 'json',
            'private' => true,
            'default_value' => '{}',
        ]);
        // FileGroup para os arquivos de desbancarizados
        $defBankless = new \MapasCulturais\Definitions\FileGroup(
            "bankless",
            ["^text/plain$", "^application/octet-stream$"],
            "O arquivo enviado não é um retorno de desbancarizados.",
            false,
            null,
            true
        );
        $app->registerFileGroup("opportunity", $defBankless);
    }

    function json($data, $status = 200)
    {
        $app = App::i();
        $app->contentType('application/json');
        $app->halt($status, json_encode($data));
    }

    /**
     * Retorna os ids das oportunidades do inciso III
     *
     * @return array
     */
    function getOpportunitiesInciso3Ids()
    {
        $app = App::i();
        
        if ($app->cache->contains(__METHOD__)) {
            return $app->cache->fetch(__METHOD__);
        }
        $project = $app->repo('Project')->find($this->config['project_id']);
        $projectsIds = $project->getChildrenIds();
        $projectsIds[] = $project->id;
        $opportunitiesByProject = $app->repo('ProjectOpportunity')->findBy(['ownerEntity' => $projectsIds, 'status' => 1 ] );
        $inciso1e2Ids = array_values(array_merge([$this->config['inciso1_opportunity_id']], $this->config['inciso2_opportunity_ids']));
        $ids = [];

        foreach ($opportunitiesByProject as $opportunity){
            if ( !in_array($opportunity->id, $inciso1e2Ids) ) {
                $ids[] = $opportunity->id;
            }
        }        

        $app->cache->save(__METHOD__, $ids, 300);
        return $ids;
    }

    public function createOpportunityInciso1()
    {
        $app = App::i();

        if ($app->user->is('guest')) {
            throw new \Exception(
                "É necessario estar logado e ser um ADMIN para executar essa ação"
            );
        }

        //VALIDAÇÕES PARA VER SE AS CONFIG TÃO SETADAS
        $aldirblancSettings = $this->config['inciso1'] ? $this->config['inciso1'] : [];

        if (empty($aldirblancSettings)) {
            return;
        }

        $idProjectFromConfig = $this->config['project_id'] ? $this->config['project_id'] : null;

        if (!$idProjectFromConfig) {
            throw new \Exception('Defina a configuração "project_id" no config.php["AldirBlanc"] ');
        }

        $project = $app->repo('Project')->find($idProjectFromConfig);

        if (!$project) {
            throw new \Exception('Id do projeto está invalido');
        }


        if (!isset($aldirblancSettings['registrationFrom'])) {
            throw new \Exception('É necessario preencher "registrationFrom" nas config.php[Aldirblanc]');
        }

        if (!isset($aldirblancSettings['registrationTo'])) {
            throw new \Exception('É necessario preencher "registrationTo" nas config.php[Aldirblanc]');
        }


        $aldirblancSettings['registrationFrom'] = $this->checkIfIsValidDateString($aldirblancSettings['registrationFrom']) ? $aldirblancSettings['registrationFrom'] : '2020-08-20';
        $aldirblancSettings['registrationTo'] = $this->checkIfIsValidDateString($aldirblancSettings['registrationTo']) ? $aldirblancSettings['registrationTo'] : '2050-01-01';
        $aldirblancSettings['shortDescription'] = isset($aldirblancSettings['shortDescription']) ? $aldirblancSettings['shortDescription'] : 'DESCRIÇÃO PADRÃO';
        $aldirblancSettings['name'] = isset($aldirblancSettings['name']) ? $aldirblancSettings['name'] : 'NOME PADRÃO';
        $aldirblancSettings['owner'] = is_int($aldirblancSettings['owner']) ? $aldirblancSettings['owner'] : $project->owner;
        $aldirblancSettings['avatar'] = isset($aldirblancSettings['avatar']) ? $aldirblancSettings['avatar'] : null;
        $aldirblancSettings['seal'] = isset($aldirblancSettings['seal']) ? $aldirblancSettings['seal'] : null;
        $aldirblancSettings['status'] = isset($aldirblancSettings['status']) ? $aldirblancSettings['status'] : 1;

        $owner = $app->repo("Agent")->find($aldirblancSettings['owner']);

        if (!$owner) {
            throw new \Exception('Owner invalido');
        }

        // $opportunityMeta = $app->repo("OpportunityMeta")->findOneBy(array('key' => 'aldirblanc_inciso', 'value' => 1));

        $activeOpportunities = $app->repo('Opportunity')->findByProjectAndOpportunityMeta($project, 'aldirblanc_inciso', 1, 1);
        $draftOpportunities = $app->repo('Opportunity')->findByProjectAndOpportunityMeta($project, 'aldirblanc_inciso', 1, 0);
        $opportunity = array_merge($activeOpportunities, $draftOpportunities);

        if (count($opportunity) > 0) {

            $params = [
                'registrationFrom' => $aldirblancSettings['registrationFrom'],
                'registrationTo' => $aldirblancSettings['registrationTo'],
                'shortDescription' => $aldirblancSettings['shortDescription'],
                'opportunity_name' => $aldirblancSettings['name'],
                'project_name' => $aldirblancSettings['name'],
                'owner' => $owner,
                'avatar' => $aldirblancSettings['avatar'],
                'seal' => $aldirblancSettings['seal'],
                'status' => $aldirblancSettings['status'],
            ];

            $this->createOpportunity($params, 1, $project);
        }
    }

    public function createOpportunityInciso2()
    {
        $app = App::i();

        if ($app->user->is('guest')) {
            throw new \Exception(
                "É necessario estar logado e ser um ADMIN para executar essa ação"
            );
        }

        $idProjectFromConfig = $this->config['project_id'] ? $this->config['project_id'] : null;

        if (!$idProjectFromConfig) {
            throw new \Exception('Defina a configuração "project_id" no config.php["AldirBlanc"] ');
        }

        $inciso2Cities = $this->config['inciso2'];

        if (empty($inciso2Cities)) {
            throw new \Exception('Defina a configuração "inciso2" no config.php["AldirBlanc"] ');
        }

        $inciso2DefaultConfigs = $this->config['inciso2_default'];

        $project = $app->repo('Project')->find($idProjectFromConfig);

        if (!$project) {
            throw new \Exception('Id do projeto está invalido');
        }

        $cityDefault = [
            'registrationFrom' => date('Y-m-d'),
            'registrationTo' => '2020-12-01',
            'shortDescription' => 'DESCRIÇÃO PADRÃO',
            'owner' => $project->owner->id,
            'city' => 'CIDADE PADRÃO',
            'name' => 'NOME PADRÃO',
            'avatar' => 'avatar-aldirblanc.jpg',
            'seal' => null,
            'status' => 1
        ];


        //Faz um loop em todas as cidades
        foreach ($inciso2Cities as $city) {
            if (is_object($city)) {
                $city = (array) $city;
            }

            $default = array_merge($cityDefault, $inciso2DefaultConfigs);
            $city = array_merge($default, $city);

            $city['project_name'] = ($city['name'] === 'NOME PADRÃO') ? $this->config['prefix_project'] . "{$city['city']}" : $city['name'];
            $city['name'] = ($city['name'] === 'NOME PADRÃO') ? "Lei Aldir Blanc - Inciso II | {$city['city']}" : $city['name'];

            if (isset($city['registrationTo'])) {
                if (!$this->checkIfIsValidDateString($city['registrationTo'])) {
                    throw new \Exception('Campo registrationTo não é uma data valida');
                }
            }

            if (isset($city['registrationFrom'])) {
                if (!$this->checkIfIsValidDateString($city['registrationFrom'])) {
                    throw new \Exception('Campo registrationFrom não é uma data valida');
                }
            }

            $owner = $app->repo("Agent")->find($city['owner']);

            if (!$owner) {
                throw new \Exception('Owner invalido');
            }

            $activeOpportunities = $app->repo('Opportunity')->findByProjectAndOpportunityMeta($project, 'aldirblanc_city', $city['city'], 1);
            $draftOpportunities = $app->repo('Opportunity')->findByProjectAndOpportunityMeta($project, 'aldirblanc_city', $city['city'], 0);
            $opportunity = array_merge($activeOpportunities, $draftOpportunities);

            //cria opportunidade SOMENTE se ainda NÃO tiver sido criada para a cidade "[i]"
            if (count($opportunity) == 0) {

                $params = [
                    'registrationFrom' => $city['registrationFrom'],
                    'registrationTo' => $city['registrationTo'],
                    'shortDescription' => $city['shortDescription'],
                    'opportunity_name' => $city['name'],
                    'project_name' => $city['project_name'],
                    'owner' => $owner,
                    'avatar' => $city['avatar'],
                    'seal' => $city['seal'],
                    'city' => $city['city'],
                    'status' => $city['status'],
                ];

                $this->createOpportunity(
                    $params,
                    2,
                    $project
                );
            }
        }
    }

    /**
     * @return bool
     */
    public function checkIfIsValidDateString(string $dateString)
    {
        if (\DateTime::createFromFormat('Y-m-d', $dateString) !== FALSE) {
            return true;
        }

        return false;
    }

    public function createOpportunity($params, $inciso, $project)
    {
        $app = App::i();

        $filepath = dirname(__FILE__) . DIRECTORY_SEPARATOR . "./importFiles/inciso${inciso}.txt";
        if (!file_exists($filepath)) {
            throw new \Exception('Arquivo para importar campos de incriçao nao existe');
        }

        $app->log->debug("========================");

        $app->disableAccessControl();

        $opportunityProject = $project;

        if($inciso == 2) {
            $app->log->debug( "Criando projeto {$params['project_name']}");
            $opportunityProject = new \MapasCulturais\Entities\Project();
            $opportunityProject->parent = $project;
            $opportunityProject->shortDescription = $params['shortDescription'];
            $opportunityProject->area=30;
            $opportunityProject->name = $params['project_name'];
            $opportunityProject->status = 1;
            $opportunityProject->type = $project->type->id;
            $opportunityProject->save(true);

            if ($params['seal']) {
                $this->setSealToEntity($params['seal'], $opportunityProject);
            }

            if ($params['avatar']) {
                $this->setAvatarToEntity($params['avatar'], $opportunityProject);
            }
        }
        $app->log->debug( "Criando oportunidade {$params['opportunity_name']}");
        $opportunity = new \MapasCulturais\Entities\ProjectOpportunity();
        $opportunity->name = $params['opportunity_name'];
        $opportunity->status = $params['status'];
        $opportunity->shortDescription = $params['shortDescription'];
        $opportunity->registrationFrom = new \Datetime($params['registrationFrom']);
        $opportunity->registrationTo = new \DateTime($params['registrationTo']);
        $opportunity->owner = $params['owner'];
        $opportunity->ownerEntity = $opportunityProject;
        $opportunity->type = 9;
        $opportunity->aldirblanc_inciso = $inciso;
        if ($inciso == 2) {
            $opportunity->aldirblanc_city = $params['city'];
        }

        $opportunity->save();

        $evaluationMethodConfiguration = new \MapasCulturais\Entities\EvaluationMethodConfiguration();
        $evaluationMethodConfiguration->type = "simple";
        $evaluationMethodConfiguration->opportunity = $opportunity;

        $opportunityProject->_relatedOpportunities = [$opportunity];

        $evaluationMethodConfiguration->save();

        $opportunityProject->save();

        $app->em->flush();

        $app->log->debug( "Importando campos da oportunidade {$params['opportunity_name']}");
        $this->importFields($opportunity->id, $inciso);

        if ($inciso == 2) {
            $myConfigs = $this->config['inciso2_categories'];
            $categories = implode("\n", $myConfigs);
            $opportunity->setRegistrationCategories($categories);
        }

        $opportunity->save();

        if ($params['seal']) {
            $this->setSealToEntity($params['seal'], $opportunity);
        }


        if ($params['avatar']) {
            $this->setAvatarToEntity($params['avatar'], $opportunity);
        }

        $app->enableAccessControl();
        $app->em->flush();

        $app->log->debug( "finalizada oportunidade {$params['opportunity_name']}\n\n\n");
    }

    //importa de um .txt dos campos de cadastro que cada opportunidade deve ter
    function importFields($opportunityId, $inciso)
    {
        $app = App::i();

        $fieldIdList = [];

        $opportunity_id = $opportunityId;

        $filepath = dirname(__FILE__) . DIRECTORY_SEPARATOR . "./importFiles/inciso${inciso}.txt";

        $importFile = fopen($filepath, "r");
        $importSource = fread($importFile, filesize($filepath));
        $importSource = json_decode($importSource);

        $opportunity =  $app->repo("Opportunity")->find($opportunity_id);

        $opportunity->importFields($importSource);

        // pegar as fields e definir o metadado para bloquear a edição
        $opportunity->refresh();

        $field_ids = [];
        foreach ($opportunity->registrationFieldConfigurations as $field) {
            $field_ids[] = "field_{$field->id}";
        }

        foreach ($opportunity->registrationFileConfigurations as $file) {
            $field_ids[] = "file_{$file->id}";
        }

        $opportunity->aldirBlancFields = $field_ids;

        $opportunity->save();
    }

    function setAvatarToEntity($avatarName, \MapasCulturais\Entity $entity)
    {
        $app = App::i();

        $configOrginalFilename = $avatarName; // exemplo: olamundo.png

        $filePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'importFiles/' . $configOrginalFilename;

        // cria um arquivo auxiliar para ser removido da pasta e deixar o "original" intacto
        // ex: ola.png gera outro como bakola.png
        $auxFileName = 'bak' . $configOrginalFilename;
        $bakFileName = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'importFiles/' . $auxFileName;
        copy($filePath, $bakFileName);

        $file_class_name = $entity->getFileClassName();

        $entityFile = new $file_class_name([
            "name" => $auxFileName,
            "type" => mime_content_type($bakFileName),
            "tmp_name" => $bakFileName,
            "error" => 0,
            "size" => filesize($bakFileName)
        ]);

        $entityFile->description = "AldirBlanc";
        $entityFile->group = "avatar";
        $entityFile->owner = $entity;
        $entityFile->save();
        $app->em->flush();
    }


    // @override
    // Função copiada de Class EntitySealRelation->createSealRelation()
    function setSealToEntity($sealId, \MapasCulturais\Entity $entity)
    {
        $app = App::i();

        if (!$sealId) {
            throw new \Exception('É necessario passar o seloId para a função setSealToEntity');
        }

        $seal = $app->repo('Seal')->find($sealId);

        if (!$seal) {
            throw new \Exception('Selo ID: ' . $sealId . ' Invalido');
        }
        $seal_class_name = $entity->getSealRelationEntityClassName();
        $relation = new $seal_class_name;
        $relation->seal = $seal;
        $relation->owner = $entity;
        $relation->agent = $entity->owner;

        $app->disableAccessControl();
        $relation->save(true);
        $app->enableAccessControl();
    }
}
