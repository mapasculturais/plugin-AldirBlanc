<?php

namespace AldirBlanc\Controllers;

use DateInterval;
use DateTime;
use Exception;
use League\Csv\Writer;
use MapasCulturais\App;
use MapasCulturais\Entities\Registration;
use MapasCulturais\i;

/**
 * Registration Controller
 *
 * By default this controller is registered with the id 'registration'.
 *
 *  @property-read \MapasCulturais\Entities\Registration $requestedEntity The Requested Entity
 */
// class AldirBlanc extends \MapasCulturais\Controllers\EntityController {
class DataPrev extends \MapasCulturais\Controllers\Registration
{
    protected $config = [];

    public function __construct()
    {
        parent::__construct();

        $app = App::i();

        $this->config = $app->plugins['AldirBlanc']->config;
        $this->entityClassName = '\MapasCulturais\Entities\Registration';
        $this->layout = 'aldirblanc';
    }

    public function GET_export()
    {   
        //Seta o timeout
        ini_set('max_execution_time', 0);
        ini_set('memory_limit','768M');

        $this->requireAuthentication();
        $app = App::i();
        if (!$app->user->is("admin")) {
            throw new Exception("Não autorizado");
        }

        //Oportunidade que a query deve filtrar
        $opportunity_id = $this->config['inciso1_opportunity_id'];

         //Data ínicial que a query deve filtrar
         $startDate = new DateTime();
         $startDate = $startDate->sub(new DateInterval('P7D'))->format('Y-m-d 00:00'); //Retorna o startDate a 7 dias atraz

         //Data final que a query deve filtrar
         $finishDate = new DateTime();
         $finishDate = $finishDate->format('Y-m-d 23:59');

         //Satatus que a query deve filtrar
         $status = 1;

         //Inciso que a query deve filtrar
         $inciso = 1;

        /**
         * Recebe e verifica os dados contidos no endpoint
         * https://localhost:8080/from:2020-09-01/to:2020-09-30/
         * @var string $startDate
         * @var string $finishDate
         * @var \DateTime $date
         */
        if (!empty($this->data)) {

            if (isset($this->data['from']) && isset($this->data['to'])) {

                if (!preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/", $this->data['from']) ||
                    !preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/", $this->data['to'])) {

                    throw new \Exception("O formato da data é inválido.");

                } else {
                    //Data ínicial
                    $startDate = new DateTime($this->data['to']);
                    $startDate = $startDate->format('Y-m-d 00:00');

                    //Data final
                    $finishDate = new DateTime($this->data['from']);
                    $finishDate = $finishDate->format('Y-m-d 23:59');
                }

            }

            //Pega o status do endpoint
            $status = isset($this->data['status']) && is_numeric($this->data['status']) ? $this->data['status'] : 1;

            //Pega o inciso do endpoint
            $inciso = isset($this->data['inciso']) && is_numeric($this->data['inciso']) ? $this->data['inciso'] : 1;

        }

        
        $opportunity = $app->repo('Opportunity')->find($opportunity_id);
        $this->registerRegistrationMetadata($opportunity);

        /**
         * Busca os registros no banco de dados         *
         * @var string $startDate
         * @var string $finishDate
         * @var string $dql
         * @var int $opportunity_id
         * @var array $key_registrations
         */
        $dql = "
        SELECT
            e
        FROM
            MapasCulturais\Entities\Registration e
        WHERE
            e.sentTimestamp >=:startDate AND
            e.sentTimestamp <= :finishDate AND
            e.status = :status AND
            e.opportunity = :opportunity_Id";

        $query = $app->em->createQuery($dql);
        $query->setParameters([
            'opportunity_Id' => $opportunity_id,
            'startDate' => $startDate,
            'finishDate' => $finishDate,
            'status' => $status,
        ]);
        $registrations = $query->getResult();

        if (empty($registrations)) {
            echo "Não existe registros para o intervalo selecionado " . $startDate . " - " . $finishDate;
            die();
        }

        /**
         * Array com header do documento CSV
         * @var array $headers
         */
        $headers = [
            "CPF",
            "SEXO",
            "FLAG_CAD_ESTADUAL",
            "SISTEMA_CAD_ESTADUAL",
            "IDENTIFICADOR_CAD_ESTADUAL",
            "FLAG_CAD_MUNICIPAL",
            "SISTEMA_CAD_MUNICIPAL",
            "IDENTIFICADOR_CAD_MUNICIPAL",
            "FLAG_CAD_DISTRITAL",
            "SISTEMA_CAD_DISTRITAL",
            "IDENTIFICADOR_CAD_DISTRITAL",
            "FLAG_CAD_SNIIC",
            "SISTEMA_CAD_SNIIC",
            "IDENTIFICADOR_CAD_SNIIC",
            "FLAG_CAD_SALIC",
            "FLAG_CAD_SICAB",
            "FLAG_CAD_OUTROS",
            "SISTEMA_CAD_OUTROS",
            "IDENTIFICADOR_CAD_OUTROS",
            "FLAG_ATUACAO_ARTES_CENICAS",
            "FLAG_ATUACAO_AUDIOVISUAL",
            "FLAG_ATUACAO_MUSICA",
            "FLAG_ATUACAO_ARTES_VISUAIS",
            "FLAG_ATUACAO_PATRIMONIO_CULTURAL",
            "FLAG_ATUACAO_MUSEUS_MEMORIA",
            "FLAG_ATUACAO_HUMANIDADES",
            "FAMILIARCPF",
            "GRAUPARENTESCO",
        ];

        /**
         * Mapeamento de campos do documento CSV
         * @var array $fields
         */
        $csv_conf = $this->config['csv_inciso1'];
        $fields = [
            "CPF" => function ($registrations) use ($csv_conf) {
                $field_id = $csv_conf["CPF"];
                return str_replace(['.', '-'], '', $registrations->$field_id);

            },
            'SEXO' => function ($registrations) use ($csv_conf) {
                $field_id = $csv_conf["SEXO"];

                if ($registrations->$field_id == 'Masculino') {
                    return 1;

                } else if ($registrations->$field_id == 'Feminino') {
                    return 2;

                } else {
                    return 0;
                }

            },
            "FLAG_CAD_ESTADUAL" => function ($registrations) use ($csv_conf) {
                $field_id = $csv_conf["FLAG_CAD_ESTADUAL"];
                return $field_id;

            },
            "SISTEMA_CAD_ESTADUAL" => function ($registrations) use ($csv_conf, $app) {
                return $csv_conf['FLAG_CAD_ESTADUAL'] ? $app->view->dict('site: name', false) : '';
                
            },
            "IDENTIFICADOR_CAD_ESTADUAL" => function ($registrations) use ($csv_conf) {
                return $csv_conf['FLAG_CAD_ESTADUAL'] ? $registrations->number : '';

            },
            "FLAG_CAD_MUNICIPAL" => function ($registrations) use ($csv_conf) {
                return $csv_conf["FLAG_CAD_MUNICIPAL"];

            },
            "SISTEMA_CAD_MUNICIPAL" => function ($registrations) use ($csv_conf, $app) {
                return $csv_conf['FLAG_CAD_MUNICIPAL'] ? $app->view->dict('site: name', false) : '';

            },
            "IDENTIFICADOR_CAD_MUNICIPAL" => function ($registrations) use ($csv_conf) {
                return $csv_conf['FLAG_CAD_MUNICIPAL'] ? $registrations->number : '';

            },
            "FLAG_CAD_DISTRITAL" => function ($registrations) use ($csv_conf) {
                return $csv_conf["FLAG_CAD_DISTRITAL"];

            },
            "SISTEMA_CAD_DISTRITAL" => function ($registrations) use ($csv_conf, $app) {
                return $csv_conf['FLAG_CAD_DISTRITAL'] ? $app->view->dict('site: name', false) : '';


            },
            "IDENTIFICADOR_CAD_DISTRITAL" => function ($registrations) use ($csv_conf) {
                return $csv_conf['FLAG_CAD_DISTRITAL'] ? $registrations->number : '';

            },
            "FLAG_CAD_SNIIC" => function ($registrations) use ($csv_conf) {
                return $csv_conf["FLAG_CAD_SNIIC"];

            },
            "SISTEMA_CAD_SNIIC" => function ($registrations) use ($csv_conf, $app) {
                return $csv_conf['FLAG_CAD_SNIIC'] ? $app->view->dict('site: name', false) : '';

            },
            "IDENTIFICADOR_CAD_SNIIC" => function ($registrations) use ($csv_conf) {
                return $csv_conf['FLAG_CAD_SNIIC'] ? $registrations->number : '';
            },
            "FLAG_CAD_SALIC" => function ($registrations) use ($csv_conf) {
                return $csv_conf["FLAG_CAD_SALIC"];

            },
            "FLAG_CAD_SICAB" => function ($registrations) use ($csv_conf) {
                return $csv_conf["FLAG_CAD_SICAB"];

            },
            "FLAG_CAD_OUTROS" => function ($registrations) use ($csv_conf) {
                return $csv_conf["FLAG_CAD_OUTROS"];

            },
            "SISTEMA_CAD_OUTROS" => function ($registrations) use ($csv_conf) {
                return $csv_conf["SISTEMA_CAD_OUTROS"];

            },
            "IDENTIFICADOR_CAD_OUTROS" => function ($registrations) use ($csv_conf) {
                return $csv_conf["IDENTIFICADOR_CAD_OUTROS"];

            },
            "FLAG_ATUACAO_ARTES_CENICAS" => function ($registrations) use ($csv_conf) {
                $field_id = $csv_conf["FLAG_ATUACAO_ARTES_CENICAS"];
                $options = [
                    'Artes Circenses',
                    'Dança',
                    'Teatro',
                    'Artes Visuais',
                    'Artesanato',
                    'Ópera',
                ];
                foreach ($options as $key => $value) {
                    if (in_array($value, $registrations->$field_id)) {
                        return 1;
                    } else {
                        return 0;
                    }
                }
            },
            "FLAG_ATUACAO_AUDIOVISUAL" => function ($registrations) use ($csv_conf) {
                $field_id = $csv_conf["FLAG_ATUACAO_AUDIOVISUAL"];
                $options = [
                    'Audiovisual',
                ];
                foreach ($options as $key => $value) {
                    if (in_array($value, $registrations->$field_id)) {
                        return 1;
                    } else {
                        return 0;
                    }
                }
            },
            "FLAG_ATUACAO_MUSICA" => function ($registrations) use ($csv_conf) {
                $field_id = $csv_conf["FLAG_ATUACAO_MUSICA"];
                $options = [
                    'Música',
                ];
                foreach ($options as $key => $value) {
                    if (in_array($value, $registrations->$field_id)) {
                        return 1;
                    } else {
                        return 0;
                    }
                }
            },
            "FLAG_ATUACAO_ARTES_VISUAIS" => function ($registrations) use ($csv_conf) {
                $field_id = $csv_conf["FLAG_ATUACAO_MUSICA"];
                $options = [
                    'Design',
                    'Moda',
                    'Fotografia',
                ];
                foreach ($options as $key => $value) {
                    if (in_array($value, $registrations->$field_id)) {
                        return 1;
                    } else {
                        return 0;
                    }
                }
            },
            "FLAG_ATUACAO_PATRIMONIO_CULTURAL" => function ($registrations) use ($csv_conf) {
                $field_id = $csv_conf["FLAG_ATUACAO_PATRIMONIO_CULTURAL"];
                $options = [
                    'Cultura Popular',
                    'Gastronomia',
                    'Outros',
                ];
                foreach ($options as $key => $value) {
                    if (in_array($value, $registrations->$field_id)) {
                        return 1;
                    } else {
                        return 0;
                    }
                }
            },
            "FLAG_ATUACAO_MUSEUS_MEMORIA" => function ($registrations) use ($csv_conf) {
                $field_id = $csv_conf["FLAG_ATUACAO_MUSEUS_MEMORIA"];
                $options = [
                    'Museu',
                ];
                foreach ($options as $key => $value) {
                    if (in_array($value, $registrations->$field_id)) {
                        return 1;
                    } else {
                        return 0;
                    }
                }
            },
            "FLAG_ATUACAO_HUMANIDADES" => function ($registrations) use ($csv_conf) {
                $field_id = $csv_conf["FLAG_ATUACAO_MUSEUS_MEMORIA"];
                $options = [
                    'Literatura',
                ];
                foreach ($options as $key => $value) {
                    if (in_array($value, $registrations->$field_id)) {
                        return 1;
                    } else {
                        return 0;
                    }
                }
            },
            "FAMILIARCPF" => function ($registrations) use ($csv_conf) {
                return $csv_conf["FAMILIARCPF"];

            },
            "GRAUPARENTESCO" => function ($registrations) use ($csv_conf) {
                return $csv_conf["GRAUPARENTESCO"];

            },
        ];

        /**
         * Itera sobre os registros mapeados
         * @var array $data_candidate
         * @var array $data_familyGroup
         * @var int $cpf
         */
        $data_candidate = [];
        $data_familyGroup = [];
        foreach ($registrations as $key_registration => $registration) {
            $cpf_candidate = '';
            foreach ($fields as $key_fields => $field) {
                if ($key_fields != "FAMILIARCPF" && $key_fields != "GRAUPARENTESCO") {
                    if (is_callable($field)) {
                        $data_candidate[$key_registration][$key_fields] = $field($registration);

                        if ($key_fields == "CPF") {
                            $cpf_candidate = $field($registration);
                        }

                    } else if (is_string($field) && strlen($field) > 0) {
                        $data_candidate[$key_registration][$key_fields] = $registration->$field;

                    } else {
                        $data_candidate[$key_registration][$key_fields] = $field;

                    }
                } else {
                    $data_candidate[$key_registration][$key_fields] = null;
                    $_field = $field($registrations);
                    
                    if(is_array($registration->$_field)) {
                        foreach ($registration->$_field as $key_familyGroup => $familyGroup) {
                            if(!isset($familyGroup->cpf) || !$familyGroup->relationship){
                                continue;
                            }

                            foreach ($headers as $key => $header) {
                                if ($header == "CPF") {
                                    $data_familyGroup[$key_registration][$key_familyGroup][$header] = $cpf_candidate;
    
                                } elseif ($header == "FAMILIARCPF") {
                                    $data_familyGroup[$key_registration][$key_familyGroup][$header] = str_replace(['.', '-'], '', $familyGroup->cpf);
    
                                } elseif ($header == "GRAUPARENTESCO") {
                                    $data_familyGroup[$key_registration][$key_familyGroup][$header] = $familyGroup->relationship;
    
                                } else {
                                    $data_familyGroup[$key_registration][$key_familyGroup][$header] = null;
    
                                }
                            }
    
                        }
                    }
                }
            }
        }

        /**
         * Prepara as linhas do CSV
         * @var array $data_candidate
         * @var array $data_familyGroup
         * @var array $headers
         * @var array $data
         */
        foreach ($data_candidate as $key_candidate => $candidate) {
            $data[] = $candidate;

            if (isset($data_familyGroup[$key_candidate])) {
                foreach ($data_familyGroup[$key_candidate] as $key_familyGroup => $familyGroup) {

                    foreach ($headers as $key_header => $header) {

                        if ($header == "FAMILIARCPF") {
                            $data[] = $familyGroup;
                        }
                    }
                }
            }
        }
       
        /**
         * Cria o CSV
         */
        $csv = Writer::createFromString("");

        $csv->insertOne($headers);

        foreach ($data as $key_csv => $csv_line) {
            $csv->insertOne($csv_line);

        }

        $csv->output('inciso1-' . md5(json_encode($data)) . '.csv');
    }

}
