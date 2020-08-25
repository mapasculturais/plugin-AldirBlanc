<?php

namespace AldirBlanc;

use Exception;
use MapasCulturais\App;
use MapasCulturais\Controllers\Opportunity;
use MapasCulturais\Entities\Project;
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
            'inciso1_limite' => 1,
            'inciso2_limite' => 1,
            'inciso2_categories' => [
                'Espaço formalizado',
                'Espaço não formalizados',
                'Coletivo formalizado',
                'Coletivo não formalizado'
            ]
        ];
       
        parent::__construct($config);
    }

    public function _init()
    {
        $app = App::i();
        $plugin = $this;

        // enqueue scripts and styles
        $app->view->enqueueStyle('aldirblanc', 'app', 'aldirblanc/app.css');
        $app->view->enqueueScript('app', 'entity.module.opportunity.aldirblanc', 'aldirblanc/ng.entity.module.opportunity.aldirblanc.js', array('ng-mapasculturais'));
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

        $app->hook('mapasculturais.run:before', function() use ($plugin) {
            /**
             * Criação automatica da opportunidade do inciso1
             */
            
            set_time_limit(0);
            ini_set('memory_limit', '2048M');

            $plugin->createOpportunityInciso1();
            $plugin->createOpportunityInciso2();
        });


        

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
     * @return bool
     */
    public function checkIfIsValidDateString(string $dateString) {
        if (\DateTime::createFromFormat('Y-m-d', $dateString) !== FALSE) {
            return true;
        } 

        return false;
    }

    function setAvatarToOpportunity($avatarName, $opportunity) {
        $app = App::i();

        $configOrginalFilename = $avatarName; // exemplo: olamundo.png

        $filePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'importFiles/'.$configOrginalFilename;

        // cria um arquivo auxiliar para ser removido da pasta e deixar o "original" intacto
        // ex: ola.png gera outro como bakola.png
        $auxFileName = 'bak'.$configOrginalFilename;
        $bakFileName = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'importFiles/'.$auxFileName;
        copy($filePath, $bakFileName);

        $opportunityFile = new \MapasCulturais\Entities\OpportunityFile([
            "name"=> $auxFileName,
            "type"=> mime_content_type($bakFileName),
            "tmp_name"=> $bakFileName,
            "error"=> 0,
            "size"=> filesize($bakFileName)
        ]); 

        $opportunityFile->description = "AldirBlanc";
        $opportunityFile->group = "avatar";
        $opportunityFile->owner = $opportunity;
        $opportunityFile->save();   
        $app->em->flush();
    }


    // @override
    // Função copiada de Class EntitySealRelation->createSealRelation()
    function setSealToOpportunity($sealId, $opportunity) {
        $app = App::i();

        if(!$sealId) {
            throw new Exception('É necessario passar o seloId para a função setSealToOpportunity');
        }

        $seal = $app->repo('Seal')->find($sealId);

        if(!$seal) {
            throw new Exception('Selo ID: '.$sealId .' Invalido');
        }

        $relation = new \MapasCulturais\Entities\OpportunitySealRelation();
        $relation->seal = $seal;
        $relation->owner = $opportunity;
        $relation->agent = $opportunity->owner;

        $relation->save(true);
    }

    //importa de um .txt dos campos de cadastro que cada opportunidade deve ter
    function importFields($opportunityId) {
        $app = App::i();
        $app->disableAccessControl();

        $opportunity_id = $opportunityId;

        $filepath = dirname(__FILE__) . DIRECTORY_SEPARATOR . './importFiles/inciso2.txt';

        $importFile = fopen( $filepath , "r");
        $importSource = fread($importFile,filesize($filepath));
        $importSource = json_decode($importSource);

        $opportunity =  $app->repo("Opportunity")->find($opportunity_id);


        if (!is_null($importSource)) {

            // Fields
            foreach($importSource->fields as $field) {

                $newField = new \MapasCulturais\Entities\RegistrationFieldConfiguration;
                $newField->owner = $opportunity;
                $newField->title = $field->title;
                $newField->description = $field->description;
                $newField->maxSize = $field->maxSize;
                $newField->fieldType = $field->fieldType;
                $newField->required = $field->required;
                $newField->categories = $field->categories;
                $newField->fieldOptions = $field->fieldOptions;
                $newField->displayOrder = $field->displayOrder;

                $app->em->persist($newField);

                $newField->save();

            }

            //Files (attachments)
            foreach($importSource->files as $file) {

                $newFile = new \MapasCulturais\Entities\RegistrationFileConfiguration;

                $newFile->owner = $opportunity;
                $newFile->title = $file->title;
                $newFile->description = $file->description;
                $newFile->required = $file->required;
                $newFile->categories = $file->categories;
                $newFile->displayOrder = $file->displayOrder;

                $app->em->persist($newFile);

                $newFile->save();

                if (is_object($file->template)) {

                    $originFile = $app->repo("RegistrationFileConfigurationFile")->find($file->template->id);

                    if (is_object($originFile)) { // se nao achamos o arquivo, talvez este campo tenha sido apagado

                        $tmp_file = sys_get_temp_dir() . '/' . $file->template->name;

                        if (file_exists($originFile->path)) {
                            copy($originFile->path, $tmp_file);

                            $newTemplateFile = array(
                                'name' => $file->template->name,
                                'type' => $file->template->mimeType,
                                'tmp_name' => $tmp_file,
                                'error' => 0,
                                'size' => filesize($tmp_file)
                            );

                            $newTemplate = new \MapasCulturais\Entities\RegistrationFileConfigurationFile($newTemplateFile);

                            $newTemplate->owner = $newFile;
                            $newTemplate->description = $file->template->description;
                            $newTemplate->group = $file->template->group;

                            $app->em->persist($newTemplate);

                            $newTemplate->save();
                        }

                    }

                }
            }

            // Metadata
            foreach($importSource->meta as $key => $value) {
                $opportunity->$key = $value;
            }

            $opportunity->save(true);

            $app->em->flush();

        }

        $app->enableAccessControl();

    }

    public function createOpportunity($params, $inciso, $project) {
        $app = App::i();

        $app->disableAccessControl();

        $opportunity = new \MapasCulturais\Entities\ProjectOpportunity();
        $opportunity->name = $params['name'];
        $opportunity->shortDescription = $params['shortDescription'];
        $opportunity->registrationFrom = new \Datetime($params['registrationFrom']);
        $opportunity->registrationTo = new \DateTime( $params['registrationTo'] );
        $opportunity->owner = $params['owner'];
        $opportunity->ownerEntity = $project;
        $opportunity->type = 9;
        
        $evaluationMethodConfiguration = new \MapasCulturais\Entities\EvaluationMethodConfiguration();
        $evaluationMethodConfiguration->type = "simple";
        $evaluationMethodConfiguration->opportunity = $opportunity;

        $opportunityMeta = new \MapasCulturais\Entities\OpportunityMeta();
        $opportunityMeta->owner = $opportunity;
        $opportunityMeta->key = 'aldirblanc_inciso';
        $opportunityMeta->value = $inciso;

        $project->_relatedOpportunities = [$opportunity];

        $opportunity->save();

        $evaluationMethodConfiguration->save();

        $opportunityMeta->save();

        if($inciso == 2) {
            $opportunityMetaCity = new \MapasCulturais\Entities\OpportunityMeta();
            $opportunityMetaCity->owner = $opportunity;
            $opportunityMetaCity->key = 'aldirblanc_city';
            $opportunityMetaCity->value = $params['city'];
            $opportunityMetaCity->save();
        }

        $project->save();

        $app->enableAccessControl();
        $app->em->flush();

        $this->importFields($opportunity->id);

        if($params['seal']) {
            $this->setSealToOpportunity( $params['seal'] , $opportunity);
        }
        

        if($params['avatar']) {
            $this->setAvatarToOpportunity($params['avatar'] , $opportunity);
        }   
    }

    public function createOpportunityInciso1() {
        $app = App::i();

        if($app->user->is('guest')) {
            return;
        }

        $idProjectFromConfig = $this->config['project_id'] ? $this->config['project_id'] : null; 

        if(!$idProjectFromConfig) {
            throw new Exception('Defina a configuração "project_id" no config.php["AldirBlanc"] ');
        }

        //VALIDAÇÕES PARA VER SE AS CONFIG TÃO SETADAS
        $aldirblancSettings = $this->config['inciso1'] ? $this->config['inciso1'] : [];

        if(empty($aldirblancSettings)) {
            throw new Exception(
                'Defina as configurações "registrationFrom","registrationTo","shortDescription","name","owner","avatar","seal" no config.php["AldirBlanc"]["inciso1"]'
            );
        }

        $project = $app->repo('Project')->find($idProjectFromConfig);

        if(!$project) {
            throw new Exception('Id do projeto está invalido');
        }


        if(!isset($aldirblancSettings['registrationFrom'])) {
            throw new Exception('É necessario preencher "registrationFrom" nas config.php[Aldirblanc]');
        }

        if(!isset($aldirblancSettings['registrationTo'])) {
            throw new Exception('É necessario preencher "registrationTo" nas config.php[Aldirblanc]');
        }


        $aldirblancSettings['registrationFrom'] = $this->checkIfIsValidDateString($aldirblancSettings['registrationFrom']) ? $aldirblancSettings['registrationFrom'] : '2020-08-20';
        $aldirblancSettings['registrationTo'] = $this->checkIfIsValidDateString($aldirblancSettings['registrationTo']) ? $aldirblancSettings['registrationTo'] : '2050-01-01';
        $aldirblancSettings['shortDescription'] = isset($aldirblancSettings['shortDescription']) ? $aldirblancSettings['shortDescription'] : 'DESCRIÇÃO PADRÃO';
        $aldirblancSettings['name'] = isset($aldirblancSettings['name']) ? $aldirblancSettings['name'] : 'NOME PADRÃO';
        $aldirblancSettings['owner'] = is_int($aldirblancSettings['owner']) ? $aldirblancSettings['owner'] : $project->owner;
        $aldirblancSettings['avatar'] = isset($aldirblancSettings['avatar']) ? $aldirblancSettings['avatar'] : null;
        $aldirblancSettings['seal'] = isset($aldirblancSettings['seal']) ? $aldirblancSettings['seal'] : null;

        $owner = $app->repo("Agent")->find($aldirblancSettings['owner']);

        if(!$owner) {
            throw new Exception('Owner invalido');
        }

        $opportunityMeta = $app->repo("OpportunityMeta")->findOneBy(array('key' => 'aldirblanc_inciso', 'value' => 1));

        if(!$opportunityMeta) {

            $params = [
                'registrationFrom' => $aldirblancSettings['registrationFrom'],
                'registrationTo' => $aldirblancSettings['registrationTo'],
                'shortDescription' => $aldirblancSettings['shortDescription'],
                'name' => $aldirblancSettings['name'],
                'owner' => $owner,
                'avatar' => $aldirblancSettings['avatar'],
                'seal' => $aldirblancSettings['seal'],
            ];

            $this->createOpportunity($params,1,$project);

        } 

    }

    public function createOpportunityInciso2() {
         $app = App::i();

        if($app->user->is('guest')) {
            return;
        }

        $idProjectFromConfig = $this->config['project_id'] ? $this->config['project_id'] : null; 

        if(!$idProjectFromConfig) {
            throw new Exception('Defina a configuração "project_id" no config.php["AldirBlanc"] ');
        }

        $inciso2Cities = $this->config['inciso2'] ? $this->config['inciso2'] : [];

        if(empty($inciso2Cities)) {
            throw new Exception('Defina a configuração "inciso2" no config.php["AldirBlanc"] ');
        }

        $project = $app->repo('Project')->find($idProjectFromConfig);

        if(!$project) {
            throw new Exception('Id do projeto está invalido');
        }

        //Faz um loop em todas as cidades
        foreach ($inciso2Cities as $city) {

            $city['registrationFrom'] = $this->checkIfIsValidDateString($city['registrationFrom']) ? $city['registrationFrom'] : '2020-08-20';
            $city['registrationTo'] = $this->checkIfIsValidDateString($city['registrationTo']) ? $city['registrationTo'] : '2050-01-01';
            $city['shortDescription'] = $city['shortDescription'] ? $city['shortDescription'] : 'DESCRIÇÃO PADRÃO';
            $city['owner'] = is_int($city['owner']) ? $city['owner'] : $project->owner;
            $city['city'] = $city['city'] ? $city['city'] : 'NOME PADRÃO';
            $city['avatar'] = $city['avatar'] ? $city['avatar'] : null;
            $city['seal'] = $city['seal'] ? $city['seal'] : null;


            $owner = $app->repo("Agent")->find($city['owner']);

            if(!$owner) {
                throw new Exception('Owner invalido');
            }

            $opportunityMeta = $app->repo("OpportunityMeta")->findOneBy(array('key' => 'aldirblanc_city', 'value' => $city['city']));

            //cria opportunidade SOMENTE se ainda NÃO tiver sido criada para a cidade "[i]"
            if(!$opportunityMeta) {

                $params = [
                    'registrationFrom' => $city['registrationFrom'],
                    'registrationTo' => $city['registrationTo'],
                    'shortDescription' => $city['shortDescription'],
                    'name' => $city['name'],
                    'owner' => $owner,
                    'avatar' => $city['avatar'],
                    'seal' => $city['seal'],
                    'city' => $city['city'],
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
