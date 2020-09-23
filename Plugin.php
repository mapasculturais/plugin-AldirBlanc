<?php

namespace AldirBlanc;

use Exception;
use MapasCulturais\App;
use MapasCulturais\i;

// @todo refatorar autoloader de plugins para resolver classes em pastas
require_once 'Controllers/AldirBlanc.php';

class Plugin extends \MapasCulturais\Plugin
{
    function __construct(array $config = [])
    {
        $app = App::i();
        // se for multisite pega do subsite_meta
        if ($app->view->subsite){
            $config = $app->view->subsite->aldir_blanc_config;
        }

        $config += [
            'logotipo_central' => env('AB_LOGOTIPO_CENTRAL',''),
            'logotipo_instituicao' => env('AB_LOGOTIPO_INSTITUICAO',''),
            'inciso1_enabled' => env('AB_INCISO1_ENABLE',true),
            'inciso2_enabled' => env('AB_INCISO2_ENABLE',true),
            'project_id' => env('AB_INCISO2_PROJECT_ID',null),
            'inciso1_opportunity_id' => env('AB_INCISO1_OPPORTUNITY_ID', null),
            'inciso2_opportunity_ids' => (array) json_decode(env('AB_INCISO2_OPPORTUNITY_IDS', '[]')),
            'inciso1' => (array) json_decode(env('AB_INCISO1', '[]')),
            'inciso2' => (array) json_decode(env('AB_INCISO2_CITIES', '[]')),
            'inciso2_default' => (array) json_decode(env('AB_INCISO2_DEFAULT', '[]')),
            'inciso1_limite' => env('AB_INCISO1_LIMITE',1),
            'inciso2_limite' => env('AB_INCISO2_LIMITE',1),
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
        ];

        $skipConfig = false;
        
        $app->applyHookBoundTo($this, 'aldirblanc.config',[&$config,&$skipConfig]);

        
        if (!$skipConfig) {
            $cache_id = __METHOD__ . ':' . 'config';

            if ($cached = $app->cache->fetch($cache_id)) {
                $config = $cached;
            } else {
                $config = $this->configOpportunitiesIds($config);
                if(!empty($config['inciso2_opportunity_ids'])){
                    $app->cache->save($cache_id, $config, 3600);
                }
                
            }
        }
        parent::__construct($config);
    }

    public function configOpportunitiesIds($config) {
        
        if(empty($config['project_id'])) {
            return $config;
        }

        $app = App::i();

        $project = $app->repo('Project')->find($config['project_id']);

        if(!$project) {
            return $config;
        }

        $opportunityInciso1 = $app->repo('Opportunity')->findByProjectAndOpportunityMeta($project, 'aldirblanc_inciso', 1);

        if(!empty($opportunityInciso1)) {
            $config['inciso1_opportunity_id'] = $opportunityInciso1[0]->id;
        }

        $opportunitiesIds = [];
        foreach($config['inciso2'] as $value) {
            $value = (array) $value;
            
            $opportunity = $app->repo('Opportunity')->findByProjectAndOpportunityMeta($project, 'aldirblanc_city', $value['city']);
            if(!empty($opportunity)) {
                $city = $value['city'];
                $opportunitiesIds[$city] = $opportunity[0]->id;
            }
        }

        if(!empty($opportunitiesIds)) {
            $config['inciso2_opportunity_ids'] = array_merge( $config['inciso2_opportunity_ids'], $opportunitiesIds);
        }
        
        return $config;
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
            if(!$app->user->is('admin')) {
                $app->view->jsObject['blockedOpportunityFields'] = $entity->aldirBlancFields;
            }
        });

        //No cadastro da oportunidade (inciso2), muda a permissao de editar as categorias
        $app->hook('opportunity.blockedCategoryFields', function (&$entity,&$can_edit) use ($app) {
            if(!$app->user->is('admin')) {
                $fields = $entity->aldirBlancFields;
                if(!empty($fields)) {
                    $can_edit = false;
                }
            }            
        });
        
        //No cadastro da oportunidade (inciso2), apresenta mensagem de bloqueio de edição das categorias
        $app->hook('template(opportunity.<<create|edit>>.categories-messages):begin', function ($entity) use($app) {
            if(!$app->user->is('admin')) {
                $fields = $entity->aldirBlancFields;
                if(!empty($fields)) {
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

        $app->hook('template(site.index.home-search):end', function () {
            $this->part('aldirblanc/home-search');
        });

        /**
         * modifica o template do autenticador quando o redirect url for para o plugin aldir blanc
         */
        $app->hook('controller(auth).render(<<*>>)', function() use ($app, $plugin) {
            $redirect_url = $_SESSION['mapasculturais.auth.redirect_path'] ?? '';
            
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

        // registra o role para mediadores
        $role_definition = new Role('mediador', 'Mediador', 'Mediadores', true, function($user){ return $user->is('admin'); });
        $app->registerRole($role_definition);

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

        $this->registerMetadata('MapasCulturais\Entities\Opportunity', 'aldirBlancFields', [
            'label' => i::__('Lista de ID dos campos AldirBlanc'),
            'type' => 'array',
            'serialize' => function($val) {
                return json_encode($val);
            },
            'unserialize' => function($val) {
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

    function json($data, $status = 200) {
        $app = App::i();
        $app->contentType('application/json');
        $app->halt($status, json_encode($data));
    }


    public function createOpportunityInciso1() {
        $app = App::i();

        if($app->user->is('guest')) {
            throw new \Exception(
                "É necessario estar logado e ser um ADMIN para executar essa ação"
            );
        }

        //VALIDAÇÕES PARA VER SE AS CONFIG TÃO SETADAS
        $aldirblancSettings = $this->config['inciso1'] ? $this->config['inciso1'] : [];

        if(empty($aldirblancSettings)) {
            return ;
        }

        $idProjectFromConfig = $this->config['project_id'] ? $this->config['project_id'] : null; 

        if(!$idProjectFromConfig) {
            throw new \Exception('Defina a configuração "project_id" no config.php["AldirBlanc"] ');
        }

        $project = $app->repo('Project')->find($idProjectFromConfig);

        if(!$project) {
            throw new \Exception('Id do projeto está invalido');
        }


        if(!isset($aldirblancSettings['registrationFrom'])) {
            throw new \Exception('É necessario preencher "registrationFrom" nas config.php[Aldirblanc]');
        }

        if(!isset($aldirblancSettings['registrationTo'])) {
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

        if(!$owner) {
            throw new \Exception('Owner invalido');
        }

        // $opportunityMeta = $app->repo("OpportunityMeta")->findOneBy(array('key' => 'aldirblanc_inciso', 'value' => 1));

        $activeOpportunities = $app->repo('Opportunity')->findByProjectAndOpportunityMeta($project, 'aldirblanc_inciso', 1, 1);
        $draftOpportunities = $app->repo('Opportunity')->findByProjectAndOpportunityMeta($project, 'aldirblanc_inciso', 1, 0);
        $opportunity = array_merge($activeOpportunities, $draftOpportunities);

        if(count($opportunity) > 0) {

            $params = [
                'registrationFrom' => $aldirblancSettings['registrationFrom'],
                'registrationTo' => $aldirblancSettings['registrationTo'],
                'shortDescription' => $aldirblancSettings['shortDescription'],
                'name' => $aldirblancSettings['name'],
                'owner' => $owner,
                'avatar' => $aldirblancSettings['avatar'],
                'seal' => $aldirblancSettings['seal'],
                'status' => $aldirblancSettings['status'],
            ];

            $this->createOpportunity($params,1,$project);

        } 

    }

    public function createOpportunityInciso2() {
        $app = App::i();

        if($app->user->is('guest')) {
            throw new \Exception(
                "É necessario estar logado e ser um ADMIN para executar essa ação"
            );
        }

        $idProjectFromConfig = $this->config['project_id'] ? $this->config['project_id'] : null; 

        if(!$idProjectFromConfig) {
            throw new \Exception('Defina a configuração "project_id" no config.php["AldirBlanc"] ');
        }

        $inciso2Cities = $this->config['inciso2'];

        if(empty($inciso2Cities)) {
            throw new \Exception('Defina a configuração "inciso2" no config.php["AldirBlanc"] ');
        }

        $inciso2DefaultConfigs = $this->config['inciso2_default'];

        $project = $app->repo('Project')->find($idProjectFromConfig);

        if(!$project) {
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
            if(is_object($city)) {
                $city = (array) $city;
            }

            $default = array_merge($cityDefault, $inciso2DefaultConfigs);
            $city = array_merge($default, $city);

            $city['name'] = ($city['name'] === 'NOME PADRÃO') ? "Lei Aldir Blanc - Inciso II | {$city['city']}" : $city['name'];
            
            if(isset($city['registrationTo']) ) {
                if(! $this->checkIfIsValidDateString($city['registrationTo'])) {
                    throw new \Exception('Campo registrationTo não é uma data valida');
                }
            }

            if(isset($city['registrationFrom']) ) {
                if(! $this->checkIfIsValidDateString($city['registrationFrom'])) {
                    throw new \Exception('Campo registrationFrom não é uma data valida');
                }
            }

            $owner = $app->repo("Agent")->find($city['owner']);

            if(!$owner) {
                throw new \Exception('Owner invalido');
            }

            $activeOpportunities = $app->repo('Opportunity')->findByProjectAndOpportunityMeta($project, 'aldirblanc_city', $city['city'], 1);
            $draftOpportunities = $app->repo('Opportunity')->findByProjectAndOpportunityMeta($project, 'aldirblanc_city', $city['city'], 0);
            $opportunity = array_merge($activeOpportunities, $draftOpportunities);

            //cria opportunidade SOMENTE se ainda NÃO tiver sido criada para a cidade "[i]"
            if(count($opportunity) == 0) {

                $params = [
                    'registrationFrom' => $city['registrationFrom'],
                    'registrationTo' => $city['registrationTo'],
                    'shortDescription' => $city['shortDescription'],
                    'name' => $city['name'],
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
    public function checkIfIsValidDateString(string $dateString) {
        if (\DateTime::createFromFormat('Y-m-d', $dateString) !== FALSE) {
            return true;
        } 

        return false;
    }

    public function createOpportunity($params, $inciso, $project) {
        $app = App::i();

        $filepath = dirname(__FILE__) . DIRECTORY_SEPARATOR . "./importFiles/inciso${inciso}.txt";
        if(!file_exists($filepath)) {
            throw new \Exception('Arquivo para importar campos de incriçao nao existe');
        }

        $app->log->debug("========================");

        $app->disableAccessControl();

        $opportunityProject = $project;

        if($inciso == 2) {
            $app->log->debug( "Criando projeto {$params['name']}");
            $opportunityProject = new \MapasCulturais\Entities\Project();
            $opportunityProject->parent = $project;
            $opportunityProject->shortDescription = $params['shortDescription'];
            $opportunityProject->area=30;
            $opportunityProject->name = $params['name'];
            $opportunityProject->status = 1;
            $opportunityProject->type = $project->type->id;
            $opportunityProject->save(true);

            if($params['seal']) {
                $this->setSealToEntity( $params['seal'] , $opportunityProject);
            }
    
            if($params['avatar']) {
                $this->setAvatarToEntity($params['avatar'] , $opportunityProject);
            }
        }
        $app->log->debug( "Criando oportunidade {$params['name']}");
        $opportunity = new \MapasCulturais\Entities\ProjectOpportunity();
        $opportunity->name = $params['name'];
        $opportunity->status = $params['status'];
        $opportunity->shortDescription = $params['shortDescription'];
        $opportunity->registrationFrom = new \Datetime($params['registrationFrom']);
        $opportunity->registrationTo = new \DateTime( $params['registrationTo'] );
        $opportunity->owner = $params['owner'];
        $opportunity->ownerEntity = $opportunityProject;
        $opportunity->type = 9;
        $opportunity->aldirblanc_inciso = $inciso;
        if($inciso == 2) {
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

        $app->log->debug( "Importando campos da oportunidade {$params['name']}");
        $this->importFields($opportunity->id, $inciso);

        if($inciso == 2) {
            $myConfigs = $this->config['inciso2_categories'];
            $categories = implode("\n",$myConfigs);
            $opportunity->setRegistrationCategories($categories);
        }

        $opportunity->save();

        if($params['seal']) {
            $this->setSealToEntity( $params['seal'] , $opportunity);
        }
        

        if($params['avatar']) {
            $this->setAvatarToEntity($params['avatar'] , $opportunity);
        }   

        $app->enableAccessControl();
        $app->em->flush();

        $app->log->debug( "finalizada oportunidade {$params['name']}\n\n\n");
    }

    //importa de um .txt dos campos de cadastro que cada opportunidade deve ter
    function importFields($opportunityId, $inciso) {
        $app = App::i();

        $fieldIdList= [];

        $opportunity_id = $opportunityId;

        $filepath = dirname(__FILE__) . DIRECTORY_SEPARATOR . "./importFiles/inciso${inciso}.txt";

        $importFile = fopen( $filepath , "r");
        $importSource = fread($importFile,filesize($filepath));
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

    function setAvatarToEntity($avatarName, \MapasCulturais\Entity $entity) {
        $app = App::i();

        $configOrginalFilename = $avatarName; // exemplo: olamundo.png

        $filePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'importFiles/'.$configOrginalFilename;

        // cria um arquivo auxiliar para ser removido da pasta e deixar o "original" intacto
        // ex: ola.png gera outro como bakola.png
        $auxFileName = 'bak'.$configOrginalFilename;
        $bakFileName = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'importFiles/'.$auxFileName;
        copy($filePath, $bakFileName);

        $file_class_name = $entity->getFileClassName();
        
        $entityFile = new $file_class_name([
            "name"=> $auxFileName,
            "type"=> mime_content_type($bakFileName),
            "tmp_name"=> $bakFileName,
            "error"=> 0,
            "size"=> filesize($bakFileName)
        ]); 

        $entityFile->description = "AldirBlanc";
        $entityFile->group = "avatar";
        $entityFile->owner = $entity;
        $entityFile->save();   
        $app->em->flush();
    }


    // @override
    // Função copiada de Class EntitySealRelation->createSealRelation()
    function setSealToEntity($sealId, \MapasCulturais\Entity $entity) {
        $app = App::i();

        if(!$sealId) {
            throw new \Exception('É necessario passar o seloId para a função setSealToEntity');
        }

        $seal = $app->repo('Seal')->find($sealId);

        if(!$seal) {
            throw new \Exception('Selo ID: '.$sealId .' Invalido');
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


