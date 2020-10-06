<?php

namespace AldirBlanc\Controllers;

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

    /**
     * Exportador para o inciso 1
     *
     * Implementa o sistema de exportação para a lei AldirBlanc no inciso 1
     * http://localhost:8080/dataprev/export_inciso1/status:1/from:2020-01-01/to:2020-01-30
     *
     * Parametros to e from não são obrigatórios, caso nao informado retorna os últimos 7 dias de registros
     *
     * Paramentro status não é obrigatorio, caso não informado retorna todos com status 1
     *
     */
    public function ALL_export_inciso1()
    {
        //Seta o timeout
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '768M');

        $this->requireAuthentication();
        $app = App::i();
        if (!$app->user->is("admin")) {
            throw new Exception("Não autorizado");
        }

        //Oportunidade que a query deve filtrar
        $opportunity_id = $this->config['inciso1_opportunity_id'];

        $parameter = $this->config['csv_inciso1']['parameters_csv_defalt'];

        //Satatus que a query deve filtrar
        $status = $parameter['status'];

        /**
         * Recebe e verifica os dados contidos no endpoint
         * http://localhost:8080/dataprev/export_inciso1/status:1/from:2020-01-01/to:2020-01-30
         * @var string $startDate
         * @var string $finishDate
         * @var \DateTime $date
         */
        $getdata = false;
        if (!empty($this->data)) {

            if (isset($this->data['from']) && isset($this->data['to'])) {

                if (!empty($this->data['from']) && !empty($this->data['to'])) {
                    if (!preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/", $this->data['from']) ||
                        !preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/", $this->data['to'])) {

                        throw new \Exception("O formato da data é inválido.");

                    } else {
                        //Data ínicial
                        $startDate = new DateTime($this->data['from']);
                        $startDate = $startDate->format('Y-m-d 00:00');

                        //Data final
                        $finishDate = new DateTime($this->data['to']);
                        $finishDate = $finishDate->format('Y-m-d 23:59');
                    }

                    $getdata = true;
                }

            }

            //Pega o status do endpoint
            $status = isset($this->data['status']) && is_numeric($this->data['status']) ? $this->data['status'] : $parameter['status'];
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
        if ($getdata) { //caso existe data como parametro ele pega o range da data selecionada com satatus 1
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

            $conditions = $query->setParameters([
                'opportunity_Id' => $opportunity_id,
                'startDate' => $startDate,
                'finishDate' => $finishDate,
                'status' => $status,
            ]);
            $registrations = $query->getResult();
        } else { //Se não exister data como parametro ele retorna todos os registros com status 1
            $dql = "
            SELECT
                e
            FROM
                MapasCulturais\Entities\Registration e
            WHERE
                e.status = :status AND
                e.opportunity = :opportunity_Id";

            $query = $app->em->createQuery($dql);

            $conditions = $query->setParameters([
                'opportunity_Id' => $opportunity_id,
                'status' => $status,
            ]);
            $registrations = $query->getResult();
        }

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

                $options = $csv_conf['atuacoes-culturais']['artes-cenicas'];

                $result = 0;
                foreach ($options as $value) {
                    if (in_array($value, $registrations->$field_id)) {
                        $result = 1;
                    }
                }

                return $result;
            },
            "FLAG_ATUACAO_AUDIOVISUAL" => function ($registrations) use ($csv_conf) {
                $field_id = $csv_conf["FLAG_ATUACAO_AUDIOVISUAL"];

                $options = $csv_conf['atuacoes-culturais']['audiovisual'];

                $result = 0;
                foreach ($options as $value) {
                    if (in_array($value, $registrations->$field_id)) {
                        $result = 1;
                    }
                }

                return $result;
            },
            "FLAG_ATUACAO_MUSICA" => function ($registrations) use ($csv_conf) {
                $field_id = $csv_conf["FLAG_ATUACAO_MUSICA"];

                $options = $csv_conf['atuacoes-culturais']['musica'];

                $result = 0;
                foreach ($options as $value) {
                    if (in_array($value, $registrations->$field_id)) {
                        $result = 1;
                    }
                }

                return $result;
            },
            "FLAG_ATUACAO_ARTES_VISUAIS" => function ($registrations) use ($csv_conf) {
                $field_id = $csv_conf["FLAG_ATUACAO_MUSICA"];

                $options = $csv_conf['atuacoes-culturais']['artes-visuais'];

                $result = 0;
                foreach ($options as $value) {
                    if (in_array($value, $registrations->$field_id)) {
                        $result = 1;
                    }
                }

                return $result;
            },
            "FLAG_ATUACAO_PATRIMONIO_CULTURAL" => function ($registrations) use ($csv_conf) {
                $field_id = $csv_conf["FLAG_ATUACAO_PATRIMONIO_CULTURAL"];

                $options = $csv_conf['atuacoes-culturais']['patrimonio-cultural'];

                $result = 0;
                foreach ($options as $value) {
                    if (in_array($value, $registrations->$field_id)) {
                        $result = 1;
                    }
                }
                return $result;
            },
            "FLAG_ATUACAO_MUSEUS_MEMORIA" => function ($registrations) use ($csv_conf) {
                $field_id = $csv_conf["FLAG_ATUACAO_MUSEUS_MEMORIA"];

                $options = $csv_conf['atuacoes-culturais']['museu-memoria'];

                $result = 0;
                foreach ($options as $value) {
                    if (in_array($value, $registrations->$field_id)) {
                        $result = 1;
                    }
                }
                return $result;
            },
            "FLAG_ATUACAO_HUMANIDADES" => function ($registrations) use ($csv_conf) {
                $field_id = $csv_conf["FLAG_ATUACAO_MUSEUS_MEMORIA"];

                $options = $csv_conf['atuacoes-culturais']['humanidades'];

                $result = 0;
                foreach ($options as $value) {
                    if (in_array($value, $registrations->$field_id)) {
                        $result = 1;
                    }
                }

                return $result;
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

                    if (is_array($registration->$_field)) {
                        foreach ($registration->$_field as $key_familyGroup => $familyGroup) {
                            if (!isset($familyGroup->cpf) || !isset($familyGroup->relationship)) {
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

        $file_name = 'inciso1-' . md5(json_encode($data)) . '.csv';

        $dir = PRIVATE_FILES_PATH . 'aldirblanc/inciso1/';

        $patch = $dir . $file_name;

        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }

        $stream = fopen($patch, 'w');

        $csv = Writer::createFromStream($stream);

        $csv->insertOne($headers);

        foreach ($data as $key_csv => $csv_line) {
            $csv->insertOne($csv_line);
        }

        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename=' . $file_name);
        header('Pragma: no-cache');
        readfile($patch);
    }

    /**
     * Exportador para o inciso 2
     *
     * Implementa o sistema de exportação para a lei AldirBlanc no inciso 2
     * http://localhost:8080/dataprev/export_inciso2/opportunity:6/status:1/type:cpf/from:2020-01-01/to:2020-01-30
     *
     * Parametros to e from não são obrigatórios, caso nao informado retorna os últimos 7 dias de registros
     *
     * Paramentro type se alterna entre cpf e cnpj
     *
     * Paramentro status não é obrigatorio, caso não informado retorna todos com status 1
     *
     */
    public function ALL_export_inciso2()
    {

        //Seta o timeout
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '768M');

        $this->requireAuthentication();
        $app = App::i();
        if (!$app->user->is("admin")) {
            throw new Exception("Não autorizado");
        }

        //Oportunidade que a query deve filtrar
        $opportunity_id = $this->config['inciso2_opportunity_ids'];

        //Satatus que a query deve filtrar
        $status = 1;
        
        /**
         * Recebe e verifica os dados contidos no endpoint
         * https://localhost:8080/dataprev_inciso2/export/opportunity:2/from:2020-09-01/to:2020-09-30/
         * @var string $startDate
         * @var string $finishDate
         * @var \DateTime $date
         */
        $getData = false;
        if (!empty($this->data)) {

            if (isset($this->data['from']) && isset($this->data['to'])) {

                if (!empty($this->data['from']) && !empty($this->data['to'])) {
                    if (!preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/", $this->data['from']) ||
                        !preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/", $this->data['to'])) {

                        throw new \Exception("O formato da data é inválido.");

                    } else {
                        //Data ínicial
                        $startDate = new DateTime($this->data['from']);
                        $startDate = $startDate->format('Y-m-d 00:00');

                        //Data final
                        $finishDate = new DateTime($this->data['to']);
                        $finishDate = $finishDate->format('Y-m-d 23:59');
                    }
                    $getData = true;
                }

            }

            //Pega o status do endpoint
            $status = isset($this->data['status']) && is_numeric($this->data['status']) ? $this->data['status'] : 1;

            //Pega a oportunidade do endpoint
            if (!isset($this->data['opportunity']) || empty($this->data['opportunity'])) {
                throw new Exception("Informe a oportunidade! Ex.: opportunity:2");

            } elseif (!is_numeric($this->data['opportunity']) || !in_array($this->data['opportunity'], $this->config['inciso2_opportunity_ids'])) {
                throw new Exception("Oportunidade inválida");

            } else {
                $opportunity_id = $this->data['opportunity'];
            }

            if (isset($this->data['type']) && preg_match("/^[a-z]{3,4}$/", $this->data['type'])) {
                $type = $this->data['type'];

            } else {
                throw new Exception("Informe o tipo de exportação EX.: type:cpf ou type:cnpj");
            }

        } else {
            throw new Exception("Informe a oportunidade! Ex.: opportunity:2");

        }

        /**
         * Pega a oprtunidade
         */
        $opportunity = $app->repo('Opportunity')->find($opportunity_id);
        $this->registerRegistrationMetadata($opportunity);

        /**
         * Busca os registros no banco de dados
         * @var string $startDate
         * @var string $finishDate
         * @var string $dql
         * @var int $opportunity_id
         * @var array $key_registrations
         */
        if ($getData) { //caso existe data como parametro ele pega o range da data selecionada com satatus 1
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

        } else { //Se não exister data como parametro ele retorna todos os registros com status 1
            $dql = "
            SELECT
                e
            FROM
                MapasCulturais\Entities\Registration e
            WHERE
                e.status = :status AND
                e.opportunity = :opportunity_Id";

            $query = $app->em->createQuery($dql);
            $query->setParameters([
                'opportunity_Id' => $opportunity_id,
                'status' => $status,
            ]);
            $registrations = $query->getResult();
        }

        if (empty($registrations)) {
            echo "Não existe registros para o intervalo selecionado " . $startDate . " - " . $finishDate;
            die();
        }

        /**
         * pega as configurações do CSV no arquivo config-csv-inciso2.php
         */
        $csv_conf = $this->config['csv_inciso2'];
        $inscricoes = $this->config['csv_inciso2']['inscricoes_culturais'];
        $atuacoes = $this->config['csv_inciso2']['atuacoes-culturais'];
        $category = $this->config['csv_inciso2']['category'];

        /**
         * Mapeamento de fielsds_id pelo label do campo
         */
        foreach ($opportunity->registrationFieldConfigurations as $field) {
            $field_labelMap["field_" . $field->id] = trim($field->title);

        }

        /**
         * Faz o mapeamento do field_id pelo label do campo para requerentes do tipo CPF
         *
         * Esta sendo feito uma comparação de string, coloque no arquivo de configuração
         * exatamente o texto do label desejado
         */
        foreach ($csv_conf['fields_cpf'] as $key_csv_conf => $field) {
            if (is_array($field)) {
                $value = array_unique($field);

                if (count($value) == 1) {
                    foreach ($field as $key => $value) {
                        $field_temp = array_keys($field_labelMap, $value);
                    }

                } else {

                    $field_temp = [];
                    foreach ($field as $key => $value) {
                        $field_temp[] = array_search(trim($value), $field_labelMap);

                    }

                }
                $fields_cpf[$key_csv_conf] = $field_temp;

            } else {
                $field_temp = array_search(trim($field), $field_labelMap);
                $fields_cpf[$key_csv_conf] = $field_temp ? $field_temp : $field;

            }
        }

        /**
         * Faz o mapeamento do field_id pelo label do campo para requerentes do tipo CPF
         *
         * Esta sendo feito uma comparação de string, coloque no arquivo de configuração
         * exatamente o texto do label desejado
         */
        foreach ($csv_conf['fields_cnpj'] as $key_csv_conf => $field) {
            if (is_array($field)) {

                $value = array_unique($field);

                if (count($value) == 1) {
                    foreach ($field as $key => $value) {
                        $field_temp = array_keys($field_labelMap, $value);
                    }

                } else {

                    $field_temp = [];
                    foreach ($field as $key => $value) {
                        $field_temp[] = array_search(trim($value), $field_labelMap);

                    }

                }
                $fields_cnpj[$key_csv_conf] = $field_temp;

            } else {
                $field_temp = array_search(trim($field), $field_labelMap);
                $fields_cnpj[$key_csv_conf] = $field_temp ? $field_temp : $field;

            }
        }

        /**
         * Mapeia os fields para um requerente pessoa física
         */
        $fields_cpf_ = [
            'CPF' => function ($registrations) use ($fields_cpf) {
                $field_id = $fields_cpf['CPF'];
                return str_replace(['.', '-'], '', $registrations->$field_id);

            },
            'SEXO' => function ($registrations) use ($fields_cpf) {
                $field_id = $fields_cpf['SEXO'];

                if ($registrations->$field_id == 'Masculino') {
                    return 1;

                } else if ($registrations->$field_id == 'Feminino') {
                    return 2;

                } else {
                    return 0;
                }

            },
            'NOME_ESPACO_CULTURAL' => function ($registrations) use ($fields_cpf) {
                $field_id = $fields_cpf['NOME_ESPACO_CULTURAL'];

                $result = "";
                if (is_array($field_id)) {
                    foreach ($field_id as $value) {
                        $result = $registrations->$value;

                    }
                } else {
                    $result = $registrations->$field_id ? $registrations->$field_id : '';

                }

                return $result;
            },
            'FLAG_CAD_ESTADUAL' => function ($registrations) use ($fields_cpf, $inscricoes) {
                $field_id = $fields_cpf["FLAG_CAD_ESTADUAL"];

                $option = $inscricoes['mapa-cultural'];

                $result = 0;

                if (is_array($registrations->$field_id)) {
                    if ($field_id && in_array($option, $registrations->$field_id)) {
                        $result = 1;
                    }

                } else {
                    if ($field_id && $registrations->$field_id == $option) {
                        $result = 1;
                    }

                }

                return $result;

            },
            'SISTEMA_CAD_ESTADUAL' => function ($registrations) use ($fields_cpf, $app, $inscricoes) {
                $field_id = $fields_cpf["FLAG_CAD_ESTADUAL"];

                $option = $inscricoes['mapa-cultural'];

                $result = '';

                if (is_array($registrations->$field_id)) {
                    if ($field_id && in_array($option, $registrations->$field_id)) {
                        $result = $app->view->dict('site: name', false);
                    }

                } else {
                    if ($field_id && $registrations->$field_id == $option) {
                        $result = $app->view->dict('site: name', false);
                    }

                }

                return $result;

            },
            'IDENTIFICADOR_CAD_ESTADUAL' => function ($registrations) use ($fields_cpf, $inscricoes) {
                $field_id = $fields_cpf["FLAG_CAD_ESTADUAL"];

                $option = $inscricoes['mapa-cultural'];

                $result = '';

                if (is_array($registrations->$field_id)) {
                    if ($field_id && in_array($option, $registrations->$field_id)) {
                        $result = $registrations->number;
                    }

                } else {
                    if ($field_id && $registrations->$field_id == $option) {
                        $result = $registrations->number;
                    }

                }

                return $result;

            },
            'FLAG_CAD_MUNICIPAL' => function ($registrations) use ($fields_cpf, $inscricoes) {
                $field_id = $fields_cpf["FLAG_CAD_MUNICIPAL"];

                $option = $inscricoes['mapa-cultural'];

                $result = 0;

                if (is_array($registrations->$field_id)) {
                    if ($field_id && in_array($option, $registrations->$field_id)) {
                        $result = 1;
                    }

                } else {
                    if ($field_id && $registrations->$field_id == $option) {
                        $result = 1;
                    }

                }

                return $result;

            },
            'SISTEMA_CAD_MUNICIPAL' => function ($registrations) use ($fields_cpf, $app, $inscricoes) {
                $field_id = $fields_cpf["FLAG_CAD_MUNICIPAL"];

                $option = $option = $inscricoes['mapa-cultural'];

                $result = '';

                if (is_array($registrations->$field_id)) {
                    if ($field_id && in_array($option, $registrations->$field_id)) {
                        $result = $app->view->dict('site: name', false);
                    }

                } else {
                    if ($field_id && $registrations->$field_id == $option) {
                        $result = $app->view->dict('site: name', false);
                    }

                }

                return $result;

            },
            'IDENTIFICADOR_CAD_MUNICIPAL' => function ($registrations) use ($fields_cpf, $inscricoes) {
                $field_id = $fields_cpf["FLAG_CAD_MUNICIPAL"];

                $option = $option = $inscricoes['mapa-cultural'];

                $result = '';

                if (is_array($registrations->$field_id)) {
                    if ($field_id && in_array($option, $registrations->$field_id)) {
                        $result = $registrations->number;
                    }

                } else {
                    if ($field_id && $registrations->$field_id == $option) {
                        $result = $registrations->number;
                    }

                }

                return $result;

            },
            'FLAG_CAD_DISTRITAL' => function ($registrations) use ($fields_cpf) {
                return $fields_cpf['FLAG_CAD_DISTRITAL'] ? $fields_cpf['FLAG_CAD_DISTRITAL'] : 0;

            },
            'SISTEMA_CAD_DISTRITAL' => function ($registrations) use ($fields_cpf, $app) {
                return $fields_cpf['FLAG_CAD_DISTRITAL'] ? $fields_cpf['SISTEMA_CAD_DISTRITAL'] : '';

            },
            'IDENTIFICADOR_CAD_DISTRITAL' => function ($registrations) use ($fields_cpf) {
                return $fields_cpf['FLAG_CAD_DISTRITAL'] ? $fields_cpf['SISTEMA_CAD_DISTRITAL'] : '';

            },
            'FLAG_CAD_NA_PONTOS_PONTOES' => function ($registrations) use ($fields_cnpj) {
                $field_id = $fields_cnpj["FLAG_CAD_NA_PONTOS_PONTOES"];

                $option = 'Cadastro Nacional de Pontos e Pontões de Cultura';

                $result = 0;

                if (is_array($registrations->$field_id)) {
                    if ($field_id && in_array($option, $registrations->$field_id)) {
                        $result = 1;
                    }

                } else {
                    if ($field_id && $registrations->$field_id == $option) {
                        $result = 1;
                    }

                }
                return $result;

            },
            'FLAG_CAD_ES_PONTOS_PONTOES' => function ($registrations) use ($fields_cpf) {
                return $fields_cpf["FLAG_CAD_ES_PONTOS_PONTOES"] ? $fields_cpf["FLAG_CAD_ES_PONTOS_PONTOES"] : 0;
            },
            'SISTEMA_CAD_ES_PONTOS_PONTOES' => function ($registrations) use ($fields_cpf) {
                return $fields_cpf["FLAG_CAD_ES_PONTOS_PONTOES"] ? $fields_cpf["SISTEMA_CAD_ES_PONTOS_PONTOES"] : '';
            },
            'IDENTIFICADOR_CAD_ES_PONTOS_PONTOES' => function ($registrations) use ($fields_cpf) {
                return $fields_cpf["FLAG_CAD_ES_PONTOS_PONTOES"] ? $fields_cpf["SISTEMA_CAD_ES_PONTOS_PONTOES"] : '';
            },
            'FLAG_CAD_SNIIC' => function ($registrations) use ($fields_cpf, $inscricoes) {
                $field_id = $fields_cpf["FLAG_CAD_SNIIC"];

                $option = $inscricoes['sniic'];

                $result = 0;

                if (is_array($registrations->$field_id)) {
                    if ($field_id && in_array($option, $registrations->$field_id)) {
                        $result = 1;
                    }

                } else {
                    if ($field_id && $registrations->$field_id == $option) {
                        $result = 1;
                    }

                }

                return $result;

            },
            'SISTEMA_CAD_SNIIC' => function ($registrations) use ($fields_cpf, $inscricoes) {
                $field_temp = $fields_cpf["FLAG_CAD_SNIIC"];
                $field_id = $fields_cpf["SISTEMA_CAD_SNIIC"];

                $option = $inscricoes['sniic'];

                $result = "";

                if (is_array($registrations->$field_temp)) {
                    if ($field_temp && in_array($option, $registrations->$field_temp)) {
                        $result = $field_id;
                    }

                } else {
                    if ($field_temp && $registrations->$field_temp == $option) {
                        $result = $field_id;
                    }

                }

                return $result;

            },
            'IDENTIFICADOR_CAD_SNIIC' => function ($registrations) use ($fields_cpf, $inscricoes) {
                $field_temp = $fields_cpf["FLAG_CAD_SNIIC"];
                $field_id = $fields_cpf["SISTEMA_CAD_SNIIC"];

                $option = $inscricoes['sniic'];

                $result = "";

                if (is_array($registrations->$field_temp)) {
                    if ($field_temp && in_array($option, $registrations->$field_temp)) {
                        $result = $field_id;
                    }

                } else {
                    if ($field_temp && $registrations->$field_temp == $option) {
                        $result = $field_id;
                    }

                }

                return $result;
            },
            'FLAG_CAD_SALIC' => function ($registrations) use ($fields_cnpj, $inscricoes) {
                $field_id = $fields_cnpj["FLAG_CAD_SALIC"];

                $option = $inscricoes['salic'];

                $result = 0;

                if (is_array($registrations->$field_id)) {
                    if ($field_id && in_array($option, $registrations->$field_id)) {
                        $result = 1;
                    }

                } else {
                    if ($field_id && $registrations->$field_id == $option) {
                        $result = 1;
                    }

                }

                return $result;
            },
            'FLAG_CAD_SICAB' => function ($registrations) use ($fields_cnpj, $inscricoes) {
                $field_id = $fields_cnpj["FLAG_CAD_SICAB"];

                $option = $inscricoes['sicab'];

                $result = 0;

                if (is_array($registrations->$field_id)) {
                    if ($field_id && in_array($option, $registrations->$field_id)) {
                        $result = 1;
                    }

                } else {
                    if ($field_id && $registrations->$field_id == $option) {
                        $result = 1;
                    }

                }

                return $result;

            },
            'FLAG_CAD_OUTROS' => function ($registrations) use ($fields_cnpj, $inscricoes) {
                $field_id = $fields_cnpj["FLAG_CAD_OUTROS"];

                $option = $inscricoes['outros'];

                $result = 0;

                if (is_array($registrations->$field_id)) {
                    if ($field_id && in_array($option, $registrations->$field_id)) {
                        $result = 1;
                    }

                } else {
                    if ($field_id && $registrations->$field_id == $option) {
                        $result = 1;
                    }

                }

                return $result;

            },
            'SISTEMA_CAD_OUTROS' => function ($registrations) use ($fields_cnpj, $inscricoes) {
                $field_temp = $fields_cnpj["FLAG_CAD_OUTROS"];
                $field_id = $fields_cnpj["SISTEMA_CAD_OUTROS"];

                $option = $inscricoes['outros'];

                $result = "";

                if (is_array($registrations->$field_temp)) {
                    if ($field_temp && in_array($option, $registrations->$field_temp)) {
                        $result = $field_id;
                    }

                } else {
                    if ($field_temp && $registrations->$field_temp == $option) {
                        $result = $field_id;
                    }

                }

                return $result;

            },
            'IDENTIFICADOR_CAD_OUTROS' => function ($registrations) use ($fields_cnpj, $inscricoes) {
                $field_temp = $fields_cnpj["FLAG_CAD_OUTROS"];
                $field_id = $fields_cnpj["SISTEMA_CAD_OUTROS"];

                $option = $inscricoes['outros'];

                $result = "";

                if (is_array($registrations->$field_temp)) {
                    if ($field_temp && in_array($option, $registrations->$field_temp)) {
                        $result = $field_id;
                    }

                } else {
                    if ($field_temp && $registrations->$field_temp == $option) {
                        $result = $field_id;
                    }

                }

                return $result;

            },
            'FLAG_ATUACAO_ARTES_CENICAS' => function ($registrations) use ($csv_conf, $fields_cnpj, $atuacoes, $category) {
                $field_temp = $fields_cnpj['FLAG_ATUACAO_ARTES_CENICAS'];

                if (is_array($field_temp)) {
                    foreach (array_filter($field_temp) as $key => $value) {
                        if ($registrations->$value) {
                            $field_id = $registrations->$value;

                        } else {
                            $field_id = "";

                        }
                    }
                } else {
                    $field_id = $registrations->$field_temp;
                }

                $options = $atuacoes['artes-cenicas'];

                $result = 0;
                foreach ($options as $value) {

                    if (in_array($value, $options)) {
                        $result = 1;
                    }
                }

                return $result;
            },
            'FLAG_ATUACAO_AUDIOVISUAL' => function ($registrations) use ($csv_conf, $fields_cnpj, $atuacoes) {
                $field_temp = $fields_cnpj['FLAG_ATUACAO_AUDIOVISUAL'];

                if (is_array($field_temp)) {
                    foreach (array_filter($field_temp) as $key => $value) {
                        if ($registrations->$value) {
                            $field_id = $registrations->$value;

                        } else {
                            $field_id = "";

                        }
                    }
                } else {
                    $field_id = $registrations->$field_temp;
                }

                $options = $atuacoes['audiovisual'];

                $result = 0;
                foreach ($options as $value) {

                    if (in_array($value, $options)) {
                        $result = 1;
                    }
                }

                return $result;

            },
            'FLAG_ATUACAO_MUSICA' => function ($registrations) use ($csv_conf, $fields_cnpj, $atuacoes) {
                $field_temp = $fields_cnpj['FLAG_ATUACAO_MUSICA'];

                if (is_array($field_temp)) {
                    foreach (array_filter($field_temp) as $key => $value) {
                        if ($registrations->$value) {
                            $field_id = $registrations->$value;

                        } else {
                            $field_id = "";

                        }
                    }
                } else {
                    $field_id = $registrations->$field_temp;
                }

                $options = $atuacoes['musica'];

                $result = 0;
                foreach ($options as $value) {

                    if (in_array($value, $options)) {
                        $result = 1;
                    }
                }

                return $result;
            },
            'FLAG_ATUACAO_ARTES_VISUAIS' => function ($registrations) use ($csv_conf, $fields_cnpj, $atuacoes) {
                $field_temp = $fields_cnpj['FLAG_ATUACAO_ARTES_VISUAIS'];

                if (is_array($field_temp)) {
                    foreach (array_filter($field_temp) as $key => $value) {
                        if ($registrations->$value) {
                            $field_id = $registrations->$value;

                        } else {
                            $field_id = "";

                        }
                    }
                } else {
                    $field_id = $registrations->$field_temp;
                }

                $options = $atuacoes['artes-visuais'];

                $result = 0;
                foreach ($options as $value) {

                    if (in_array($value, $options)) {
                        $result = 1;
                    }
                }

                return $result;

            },
            'FLAG_ATUACAO_PATRIMONIO_CULTURAL' => function ($registrations) use ($csv_conf, $fields_cnpj, $atuacoes) {
                $field_temp = $fields_cnpj['FLAG_ATUACAO_PATRIMONIO_CULTURAL'];

                if (is_array($field_temp)) {
                    foreach (array_filter($field_temp) as $key => $value) {
                        if ($registrations->$value) {
                            $field_id = $registrations->$value;

                        } else {
                            $field_id = "";

                        }
                    }
                } else {
                    $field_id = $registrations->$field_temp;
                }

                $options = $atuacoes['patrimonio-cultural'];

                $result = 0;
                foreach ($options as $value) {

                    if (in_array($value, $options)) {
                        $result = 1;
                    }
                }

                return $result;
            },
            'FLAG_ATUACAO_MUSEUS_MEMORIA' => function ($registrations) use ($csv_conf, $fields_cnpj, $atuacoes) {
                $field_temp = $fields_cnpj['FLAG_ATUACAO_MUSEUS_MEMORIA'];

                if (is_array($field_temp)) {
                    foreach (array_filter($field_temp) as $key => $value) {
                        if ($registrations->$value) {
                            $field_id = $registrations->$value;

                        } else {
                            $field_id = "";

                        }
                    }
                } else {
                    $field_id = $registrations->$field_temp;
                }

                $options = $atuacoes['museu-memoria'];

                $result = 0;
                foreach ($options as $value) {

                    if (in_array($value, $options)) {
                        $result = 1;
                    }
                }

                return $result;
            },
            'FLAG_ATUACAO_HUMANIDADES' => function ($registrations) use ($csv_conf, $fields_cnpj, $atuacoes) {
                $field_temp = $fields_cnpj['FLAG_ATUACAO_HUMANIDADES'];

                if (is_array($field_temp)) {
                    foreach (array_filter($field_temp) as $key => $value) {
                        if ($registrations->$value) {
                            $field_id = $registrations->$value;

                        } else {
                            $field_id = "";

                        }
                    }
                } else {
                    $field_id = $registrations->$field_temp;
                }

                $options = $atuacoes['humanidades'];

                $result = 0;
                foreach ($options as $value) {

                    if (in_array($value, $options)) {
                        $result = 1;
                    }
                }

                return $result;
            },
        ];

        /**
         * Mapeia os fields para um requerente pessoa jurídica
         */
        $fields_cnpj_ = [
            'CNPJ' => function ($registrations) use ($fields_cnpj) {
                $field_temp = $fields_cnpj['CNPJ'];

                if (is_array($field_temp)) {
                    foreach ($field_temp as $value) {

                        if ($registrations->$value) {
                            $field_id = $value;
                        }
                    }
                } else {
                    $field_id = $field_temp;
                }
                return str_replace(['.', '-', '/'], '', $registrations->$field_id);

            }, 'FLAG_CAD_ESTADUAL' => function ($registrations) use ($fields_cnpj, $inscricoes) {
                $field_id = $fields_cnpj["FLAG_CAD_ESTADUAL"];

                $option = $option = $inscricoes['mapa-cultural'];

                $result = 0;

                if (is_array($registrations->$field_id)) {
                    if ($field_id && in_array($option, $registrations->$field_id)) {
                        $result = 1;
                    }

                } else {
                    if ($field_id && $registrations->$field_id == $option) {
                        $result = 1;
                    }

                }
                return $result;

            },
            'SISTEMA_CAD_ESTADUAL' => function ($registrations) use ($fields_cnpj, $app, $inscricoes) {
                $field_id = $fields_cnpj["FLAG_CAD_ESTADUAL"];

                $option = $option = $inscricoes['mapa-cultural'];

                $result = "";

                if (is_array($registrations->$field_id)) {
                    if ($field_id && in_array($option, $registrations->$field_id)) {
                        $result = $app->view->dict('site: name', false);
                    }

                } else {
                    if ($field_id && $registrations->$field_id == $option) {
                        $result = $app->view->dict('site: name', false);
                    }

                }
                return $result;

            },
            'IDENTIFICADOR_CAD_ESTADUAL' => function ($registrations) use ($fields_cnpj, $inscricoes) {
                $field_id = $fields_cnpj["FLAG_CAD_ESTADUAL"];

                $option = $option = $inscricoes['mapa-cultural'];

                $result = "";

                if (is_array($registrations->$field_id)) {
                    if ($field_id && in_array($option, $registrations->$field_id)) {
                        $result = $registrations->number;
                    }

                } else {
                    if ($field_id && $registrations->$field_id == $option) {
                        $result = $registrations->number;
                    }

                }
                return $result;

            },
            'FLAG_CAD_MUNICIPAL' => function ($registrations) use ($fields_cnpj, $inscricoes) {
                $field_id = $fields_cnpj["FLAG_CAD_MUNICIPAL"];

                $option = $inscricoes['cadastro-municipal'];

                $result = 0;

                if (is_array($registrations->$field_id)) {
                    if ($field_id && in_array($option, $registrations->$field_id)) {
                        $result = 1;
                    }

                } else {
                    if ($field_id && $registrations->$field_id == $option) {
                        $result = 1;
                    }

                }
                return $result;

            },
            'SISTEMA_CAD_MUNICIPAL' => function ($registrations) use ($fields_cnpj, $inscricoes) {
                $field_id = $fields_cnpj["FLAG_CAD_MUNICIPAL"];

                $option = $inscricoes['cadastro-municipal'];

                $result = "";

                if (is_array($registrations->$field_id)) {
                    if ($field_id && in_array($option, $registrations->$field_id)) {
                        $result = $app->view->dict('site: name', false);
                    }

                } else {
                    if ($field_id && $registrations->$field_id == $option) {
                        $result = $app->view->dict('site: name', false);
                    }

                }
                return $result;

            },
            'IDENTIFICADOR_CAD_MUNICIPAL' => function ($registrations) use ($fields_cnpj, $inscricoes) {
                $field_id = $fields_cnpj["FLAG_CAD_MUNICIPAL"];

                $option = $inscricoes['cadastro-municipal'];

                $result = '';

                if (is_array($registrations->$field_id)) {
                    if ($field_id && in_array($option, $registrations->$field_id)) {
                        $result = $registrations->number;
                    }

                } else {
                    if ($field_id && $registrations->$field_id == $option) {
                        $result = $registrations->number;
                    }

                }
                return $result;

            },
            'FLAG_CAD_DISTRITAL' => function ($registrations) use ($fields_cnpj) {
                $field_id = $fields_cnpj["FLAG_CAD_DISTRITAL"];
                return $field_id;

            },
            'SISTEMA_CAD_DISTRITAL' => function ($registrations) use ($fields_cnpj, $app) {
                return $fields_cnpj['FLAG_CAD_DISTRITAL'] ? $app->view->dict('site: name', false) : '';

            },
            'IDENTIFICADOR_CAD_DISTRITAL' => function ($registrations) use ($fields_cnpj) {
                return $fields_cnpj['FLAG_CAD_DISTRITAL'] ? $registrations->number : '';

            },
            'FLAG_CAD_NA_PONTOS_PONTOES' => function ($registrations) use ($fields_cnpj) {
                $field_id = $fields_cnpj["FLAG_CAD_NA_PONTOS_PONTOES"];

                $option = 'Cadastro Nacional de Pontos e Pontões de Cultura';

                $result = 0;

                if (is_array($registrations->$field_id)) {
                    if ($field_id && in_array($option, $registrations->$field_id)) {
                        $result = 1;
                    }

                } else {
                    if ($field_id && $registrations->$field_id == $option) {
                        $result = 1;
                    }

                }
                return $result;

            },
            'FLAG_CAD_ES_PONTOS_PONTOES' => function ($registrations) use ($fields_cnpj) {
                return $fields_cnpj["FLAG_CAD_ES_PONTOS_PONTOES"];
            },
            'SISTEMA_CAD_ES_PONTOS_PONTOES' => function ($registrations) use ($fields_cnpj) {
                return $fields_cnpj["SISTEMA_CAD_ES_PONTOS_PONTOES"];
            },
            'IDENTIFICADOR_CAD_ES_PONTOS_PONTOES' => function ($registrations) use ($fields_cnpj) {
                return $fields_cnpj["IDENTIFICADOR_CAD_ES_PONTOS_PONTOES"];
            },
            'FLAG_CAD_SNIIC' => function ($registrations) use ($fields_cnpj, $inscricoes) {
                $field_id = $fields_cnpj["FLAG_CAD_SNIIC"];

                $option = $inscricoes['sniic'];

                $result = 0;

                if (is_array($registrations->$field_id)) {
                    if ($field_id && in_array($option, $registrations->$field_id)) {
                        $result = 1;
                    }

                } else {
                    if ($field_id && $registrations->$field_id == $option) {
                        $result = 1;
                    }

                }

                return $result;

            },
            'SISTEMA_CAD_SNIIC' => function ($registrations) use ($fields_cnpj, $inscricoes) {
                $field_temp = $fields_cnpj["FLAG_CAD_SNIIC"];
                $field_id = $fields_cnpj["SISTEMA_CAD_SNIIC"];

                $option = $inscricoes['sniic'];

                $result = "";

                if (is_array($registrations->$field_temp)) {
                    if ($field_temp && in_array($option, $registrations->$field_temp)) {
                        $result = $field_id;
                    }

                } else {
                    if ($field_temp && $registrations->$field_temp == $option) {
                        $result = $field_id;
                    }

                }

                return $result;

            },
            'IDENTIFICADOR_CAD_SNIIC' => function ($registrations) use ($fields_cnpj, $inscricoes) {
                $field_temp = $fields_cnpj["FLAG_CAD_SNIIC"];
                $field_id = $fields_cnpj["SISTEMA_CAD_SNIIC"];

                $option = $inscricoes['sniic'];

                $result = "";

                if (is_array($registrations->$field_temp)) {
                    if ($field_temp && in_array($option, $registrations->$field_temp)) {
                        $result = $field_id;
                    }

                } else {
                    if ($field_temp && $registrations->$field_temp == $option) {
                        $result = $field_id;
                    }

                }

                return $result;

            },
            'FLAG_CAD_SALIC' => function ($registrations) use ($fields_cnpj, $inscricoes) {
                $field_id = $fields_cnpj["FLAG_CAD_SALIC"];

                $option = $inscricoes['salic'];

                $result = 0;

                if (is_array($registrations->$field_id)) {
                    if ($field_id && in_array($option, $registrations->$field_id)) {
                        $result = 1;
                    }

                } else {
                    if ($field_id && $registrations->$field_id == $option) {
                        $result = 1;
                    }

                }

                return $result;
            },
            'FLAG_CAD_SICAB' => function ($registrations) use ($fields_cnpj, $inscricoes) {
                $field_id = $fields_cnpj["FLAG_CAD_SICAB"];

                $option = $inscricoes['sicab'];

                $result = 0;

                if (is_array($registrations->$field_id)) {
                    if ($field_id && in_array($option, $registrations->$field_id)) {
                        $result = 1;
                    }

                } else {
                    if ($field_id && $registrations->$field_id == $option) {
                        $result = 1;
                    }

                }

                return $result;

            },
            'FLAG_CAD_OUTROS' => function ($registrations) use ($fields_cnpj, $inscricoes) {
                $field_id = $fields_cnpj["FLAG_CAD_OUTROS"];

                $option = $inscricoes['outros'];

                $result = 0;

                if (is_array($registrations->$field_id)) {
                    if ($field_id && in_array($option, $registrations->$field_id)) {
                        $result = 1;
                    }

                } else {
                    if ($field_id && $registrations->$field_id == $option) {
                        $result = 1;
                    }

                }

                return $result;

            },
            'SISTEMA_CAD_OUTROS' => function ($registrations) use ($fields_cnpj, $inscricoes) {
                $field_temp = $fields_cnpj["FLAG_CAD_OUTROS"];
                $field_id = $fields_cnpj["SISTEMA_CAD_OUTROS"];

                $option = $inscricoes['outros'];

                $result = "";

                if (is_array($registrations->$field_temp)) {
                    if ($field_temp && in_array($option, $registrations->$field_temp)) {
                        $result = $field_id;
                    }

                } else {
                    if ($field_temp && $registrations->$field_temp == $option) {
                        $result = $field_id;
                    }

                }

                return $result;

            },
            'IDENTIFICADOR_CAD_OUTROS' => function ($registrations) use ($fields_cnpj, $inscricoes) {
                $field_temp = $fields_cnpj["FLAG_CAD_OUTROS"];
                $field_id = $fields_cnpj["SISTEMA_CAD_OUTROS"];

                $option = $inscricoes['outros'];

                $result = "";

                if (is_array($registrations->$field_temp)) {
                    if ($field_temp && in_array($option, $registrations->$field_temp)) {
                        $result = $field_id;
                    }

                } else {
                    if ($field_temp && $registrations->$field_temp == $option) {
                        $result = $field_id;
                    }

                }

                return $result;

            },
            'FLAG_ATUACAO_ARTES_CENICAS' => function ($registrations) use ($csv_conf, $fields_cnpj, $atuacoes, $category) {
                $field_temp = $fields_cnpj['FLAG_ATUACAO_ARTES_CENICAS'];

                if (is_array($field_temp)) {
                    foreach (array_filter($field_temp) as $key => $value) {
                        if ($registrations->$value) {
                            $field_id = $registrations->$value;

                        } else {
                            $field_id = "";

                        }
                    }
                } else {
                    $field_id = $registrations->$field_temp;
                }

                $options = $atuacoes['artes-cenicas'];

                $result = 0;
                foreach ($options as $value) {

                    if (in_array($value, $options)) {
                        $result = 1;
                    }
                }

                return $result;
            },
            'FLAG_ATUACAO_AUDIOVISUAL' => function ($registrations) use ($csv_conf, $fields_cnpj, $atuacoes) {
                $field_temp = $fields_cnpj['FLAG_ATUACAO_AUDIOVISUAL'];

                if (is_array($field_temp)) {
                    foreach (array_filter($field_temp) as $key => $value) {
                        if ($registrations->$value) {
                            $field_id = $registrations->$value;

                        } else {
                            $field_id = "";

                        }
                    }
                } else {
                    $field_id = $registrations->$field_temp;
                }

                $options = $atuacoes['audiovisual'];

                $result = 0;
                foreach ($options as $value) {

                    if (in_array($value, $options)) {
                        $result = 1;
                    }
                }

                return $result;

            },
            'FLAG_ATUACAO_MUSICA' => function ($registrations) use ($csv_conf, $fields_cnpj, $atuacoes) {
                $field_temp = $fields_cnpj['FLAG_ATUACAO_MUSICA'];

                if (is_array($field_temp)) {
                    foreach (array_filter($field_temp) as $key => $value) {
                        if ($registrations->$value) {
                            $field_id = $registrations->$value;

                        } else {
                            $field_id = "";

                        }
                    }
                } else {
                    $field_id = $registrations->$field_temp;
                }

                $options = $atuacoes['musica'];

                $result = 0;
                foreach ($options as $value) {

                    if (in_array($value, $options)) {
                        $result = 1;
                    }
                }

                return $result;
            },
            'FLAG_ATUACAO_ARTES_VISUAIS' => function ($registrations) use ($csv_conf, $fields_cnpj, $atuacoes) {
                $field_temp = $fields_cnpj['FLAG_ATUACAO_ARTES_VISUAIS'];

                if (is_array($field_temp)) {
                    foreach (array_filter($field_temp) as $key => $value) {
                        if ($registrations->$value) {
                            $field_id = $registrations->$value;

                        } else {
                            $field_id = "";

                        }
                    }
                } else {
                    $field_id = $registrations->$field_temp;
                }

                $options = $atuacoes['artes-visuais'];

                $result = 0;
                foreach ($options as $value) {

                    if (in_array($value, $options)) {
                        $result = 1;
                    }
                }

                return $result;

            },
            'FLAG_ATUACAO_PATRIMONIO_CULTURAL' => function ($registrations) use ($csv_conf, $fields_cnpj, $atuacoes) {
                $field_temp = $fields_cnpj['FLAG_ATUACAO_PATRIMONIO_CULTURAL'];

                if (is_array($field_temp)) {
                    foreach (array_filter($field_temp) as $key => $value) {
                        if ($registrations->$value) {
                            $field_id = $registrations->$value;

                        } else {
                            $field_id = "";

                        }
                    }
                } else {
                    $field_id = $registrations->$field_temp;
                }

                $options = $atuacoes['patrimonio-cultural'];

                $result = 0;
                foreach ($options as $value) {

                    if (in_array($value, $options)) {
                        $result = 1;
                    }
                }

                return $result;
            },
            'FLAG_ATUACAO_MUSEUS_MEMORIA' => function ($registrations) use ($csv_conf, $fields_cnpj, $atuacoes) {
                $field_temp = $fields_cnpj['FLAG_ATUACAO_MUSEUS_MEMORIA'];

                if (is_array($field_temp)) {
                    foreach (array_filter($field_temp) as $key => $value) {
                        if ($registrations->$value) {
                            $field_id = $registrations->$value;

                        } else {
                            $field_id = "";

                        }
                    }
                } else {
                    $field_id = $registrations->$field_temp;
                }

                $options = $atuacoes['museu-memoria'];

                $result = 0;
                foreach ($options as $value) {

                    if (in_array($value, $options)) {
                        $result = 1;
                    }
                }

                return $result;
            },
            'FLAG_ATUACAO_HUMANIDADES' => function ($registrations) use ($csv_conf, $fields_cnpj, $atuacoes) {
                $field_temp = $fields_cnpj['FLAG_ATUACAO_HUMANIDADES'];

                if (is_array($field_temp)) {
                    foreach (array_filter($field_temp) as $key => $value) {
                        if ($registrations->$value) {
                            $field_id = $registrations->$value;

                        } else {
                            $field_id = "";

                        }
                    }
                } else {
                    $field_id = $registrations->$field_temp;
                }

                $options = $atuacoes['humanidades'];

                $result = 0;
                foreach ($options as $value) {

                    if (in_array($value, $options)) {
                        $result = 1;
                    }
                }

                return $result;
            },
        ];

        /**
         * Itera sobre os dados mapeados
         */
        $data_candidate_cpf = [];
        $data_candidate_cnpj = [];
        foreach ($registrations as $key_registration => $registration) {

            //Verifica qual tipo de candidato se trata no  cadastro se e pessoa física ou pessoa jurídica
            $field_temp = $fields_cnpj['CNPJ'];

            $type_candidate = "fields_cpf_";
            foreach ($field_temp as $value) {
                if ($registration->$value) {
                    $type_candidate = 'fields_cnpj_';

                }
            }

            /**
             * Faz a separação dos candidatos
             *
             * $data_candidate_cpf recebe pessoas físicas
             * $data_candidate_cnpj recebe pessoas jurídicas
             */
            foreach ($$type_candidate as $key_fields => $field) {

                if ($type_candidate == 'fields_cnpj_') {

                    if (is_callable($field)) {
                        $data_candidate_cnpj[$key_registration][$key_fields] = $field($registration);

                    } else if (is_string($field) && strlen($field) > 0) {

                        $data_candidate_cnpj[$key_registration][$key_fields] = $registration->$field;

                    } else {

                        $data_candidate_cnpj[$key_registration][$key_fields] = $field;

                    }
                } else {
                    if (is_callable($field)) {
                        $data_candidate_cpf[$key_registration][$key_fields] = $field($registration);

                    } else if (is_string($field) && strlen($field) > 0) {

                        $data_candidate_cpf[$key_registration][$key_fields] = $registration->$field;

                    } else {

                        $data_candidate_cpf[$key_registration][$key_fields] = $field;

                    }
                }

            }
        }

        //Cria o CSV para pessoa jurídica
        if ($type == 'cnpj') {
            $file_name = 'inciso2-CNPJ-' . md5(json_encode($data_candidate_cnpj)) . '.csv';

            $dir = PRIVATE_FILES_PATH . 'aldirblanc/inciso2/cnpj/';

            $patch = $dir . $file_name;

            if (!is_dir($dir)) {
                mkdir($dir, 0700, true);
            }

            $stream = fopen($patch, 'w');

            $csv = Writer::createFromStream($stream);

            $field_temp = $csv_conf['fields_cnpj'];

            foreach ($field_temp as $key => $value) {
                $header_cnpj[] = $key;
            }

            $csv->insertOne($header_cnpj);

            foreach ($data_candidate_cnpj as $key_csv => $csv_line) {
                $csv->insertOne($csv_line);
            }

            header('Content-Type: application/csv');
            header('Content-Disposition: attachment; filename=' . $file_name);
            header('Pragma: no-cache');
            readfile($patch);
        }

        //Cria o CSV para pessoa física
        if ($type == 'cpf') {
            $file_name = 'inciso2-CPF-' . md5(json_encode($data_candidate_cpf)) . '.csv';

            $dir = PRIVATE_FILES_PATH . 'aldirblanc/inciso2/cpf/';

            $patch = $dir . $file_name;

            if (!is_dir($dir)) {
                mkdir($dir, 0700, true);
            }

            $stream = fopen($patch, 'w');

            $csv = Writer::createFromStream($stream);

            $field_temp = $csv_conf['fields_cpf'];

            foreach ($field_temp as $key => $value) {
                $header_cpf[] = $key;
            }

            $csv->insertOne($header_cpf);

            foreach ($data_candidate_cpf as $key_csv => $csv_line) {
                $csv->insertOne($csv_line);
            }

            header('Content-Type: application/csv');
            header('Content-Disposition: attachment; filename=' . $file_name);
            header('Pragma: no-cache');
            readfile($patch);
        }
    }

    /**
     * Importador para o inciso 1
     *
     * Implementa o sistema de importação dos dados da dataprev para a lei AldirBlanc no inciso 1
     * http://localhost:8080/dataprev/import_inciso1/
     *
     * Parametros to e from não são obrigatórios, caso nao informado retorna os últimos 7 dias de registros
     *
     * Paramentro type se alterna entre cpf e cnpj
     *
     * Paramentro status não é obrigatorio, caso não informado retorna todos com status 1
     *
     */
    public function ALL_import_inciso1()
    {

        /**
         * Seta o timeout e limite de memoria
         */
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '768M');

        //Verifica se o usuário esta autenticado como ADMIN
        $this->requireAuthentication();
        $app = App::i();
        if (!$app->user->is("admin")) {
            throw new Exception("Não autorizado");
        }

        // Pega as configurações no arquivo config-csv-inciso1.php
        $conf_csv = $this->config['csv_inciso1'];

        //Nome do arquivo enviado
        $file_name = "ResultadoConsultaAuxilio_2020-10-3.csv";

        //Define o diretório onde o arquivo será armazenado
        $dir = __DIR__ . '/../csv/';

        //verifica se o mesmo esta no servidor
        if (!file_exists($dir . $file_name)) {
            throw new Exception("Erro ao processar o arquivo.");
        }

        //Abre o arquivo em modo de leitura
        $stream = fopen($dir . $file_name, "r");

        //Faz a leitura do arquivo
        $csv = Reader::createFromStream($stream);

        //Define o limitador do arqivo (, ou ;)
        $csv->setDelimiter(";");

        //Seta em que linha deve se iniciar a leitura
        $header_temp = $csv->setHeaderOffset(0);

        //Faz o processamento dos dados
        $stmt = (new Statement());
        $results = $stmt->process($csv);

        //Verifica a extenção do arquivo
        $ext = pathinfo($dir . $file_name, PATHINFO_EXTENSION);

        if ($ext != "csv") {
            throw new Exception("Arquivo não permitido.");
        }

        //Verifica se o arquivo esta dentro layout
        foreach ($header_temp as $key => $value) {
            $header_file[] = $value;
            break;

        }

        foreach ($header_file[0] as $key => $value) {
            $header_line_csv[] = $key;

        }

        //Verifica se o layout do arquivo esta nos padroes enviados pela dataprev
        $herder_layout = $conf_csv['herder_layout'];

       
        if ($error_layout = array_diff_assoc($herder_layout, $header_line_csv)) {
            throw new Exception("os campos " . json_encode($error_layout) . " estão divergentes do layout necessário.");

        }

        //Inicia a verificação dos dados do requerente
        $evaluation = [];
        $parameters = $conf_csv['acceptance_parameters'];
        foreach ($results as $results_key => $result) {

            $candidate = $result;
            foreach ($candidate as $key_candidate => $value) {
                if ($key_candidate == 'IDENTIF_CAD_ESTAD_CULT') {
                    $evaluation[$results_key]['DADOS_DO_REQUERENTE']['N_INSCRICAO'] = $value;
                }

                if ($key_candidate == 'REQUERENTE_CPF') {
                    $evaluation[$results_key]['DADOS_DO_REQUERENTE']['CPF'] = $value;
                }

                $field = isset($parameters[$key_candidate]) ? $parameters[$key_candidate] : "";
                if (is_array($field)) {

                    if ($key_candidate == "REQUERENTE_DATA_NASCIMENTO") {
                        $date = explode("/", $value);
                        $date = new DateTime($date[2] . '-' . $date[1] . '-' . $date[0]);
                        $idade = $date->diff(new DateTime(date('Y-m-d')));

                        if ($idade->format('%Y') >= $field['positive'][0]) {
                            $evaluation[$results_key]['VALIDATION'][$key_candidate] = true;

                        } else {
                            $evaluation[$results_key]['VALIDATION'][$key_candidate] = $field['response'];
                        }

                    }elseif ($key_candidate == "SITUACAO_CADASTRO") {

                        if (in_array(trim($value), $field['positive'])) {
                            $evaluation[$results_key]['VALIDATION']['SITUACAO_CADASTRO'] = true;

                        } elseif (in_array(trim($value), $field['negative'])) {
                            if(is_array($field['response'])){
                                $evaluation[$results_key]['VALIDATION']['SITUACAO_CADASTRO'] = $field['response'][$value];

                            }else{
                                $evaluation[$results_key]['VALIDATION']['SITUACAO_CADASTRO'] = $field['response'];
                                
                            }
                            

                        }

                    }elseif (in_array($key_candidate,  $conf_csv['validation_cad_cultural'] )){
                        
                        if (in_array(trim($value), $field['positive'])) {
                            $evaluation[$results_key]['VALIDATION']['VALIDA_CAD_CULTURAL'] = true;

                        } elseif (in_array(trim($value), $field['negative'])) {
                            $evaluation[$results_key]['VALIDATION']['VALIDA_CAD_CULTURAL'] = $field['response'];

                        }
                      
                    }else {

                        if (in_array(trim($value), $field['positive'])) {
                            $evaluation[$results_key]['VALIDATION'][$key_candidate] = true;

                        } elseif (in_array(trim($value), $field['negative'])) {
                            $evaluation[$results_key]['VALIDATION'][$key_candidate] = $field['response'];

                        }

                    }

                } else {

                    if ($field) {
                        if ($value === $field) {
                            $evaluation[$results_key]['VALIDATION'][$key_candidate] = true;
                        }

                    }

                }

            }

        }
        
        //Define se o requerente esta apto ou inapto levando em consideração as diretrizes de negocio
        $result_aptUnfit = [];       
        foreach ($evaluation as $key_evaluetion => $value) {
            $result_validation = array_diff($value['VALIDATION'], $conf_csv['validation_reference']);
            if (!$result_validation) {
                $result_aptUnfit[$key_evaluetion] = $value['DADOS_DO_REQUERENTE'];
                $result_aptUnfit[$key_evaluetion]['ACCEPT'] = true;
            } else {
                $result_aptUnfit[$key_evaluetion] = $value['DADOS_DO_REQUERENTE'];                
                $result_aptUnfit[$key_evaluetion]['ACCEPT'] = false;
                foreach ($value['VALIDATION'] as $value) {
                    if (is_string($value)) {
                        $result_aptUnfit[$key_evaluetion]['REASONS'][] = $value;
                    }
                }
            }

        }

        var_dump($result_aptUnfit);
        exit();

       

    }

}
