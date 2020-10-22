<?php

namespace AldirBlanc\Controllers;

use DateInterval;
use DateTime;
use Exception;
use League\Csv\Writer;
use MapasCulturais\App;
use MapasCulturais\Entities\Registration;
use MapasCulturais\i;
use Normalizer;

/**
 * Registration Controller
 *
 * By default this controller is registered with the id 'registration'.
 *
 *  @property-read \MapasCulturais\Entities\Registration $requestedEntity The Requested Entity
 */
// class AldirBlanc extends \MapasCulturais\Controllers\EntityController {
class Remessas extends \MapasCulturais\Controllers\Registration
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
     * Implementa um exportador genérico, que de momento tem a intenção de antender os municipios que não vão enviar o arquivo de remessa
     * diretamente ao banco do Brasil.
     * http://localhost:8080/remessas/genericExportInciso2/opportunity:12/
     *
     * O Parâmetro opportunity e identificado e incluido no endpiont automáricamente
     *
     */
    public function ALL_genericExportInciso2()
    {

        /**
         * Verifica se o usuário está autenticado
         */
        $this->requireAuthentication();
        $app = App::i();

        /**
         * Pega os dados da configuração
         */

        $csv_conf = $this->config['csv_generic_inciso2'];
        $status = $csv_conf['parameters_default']['status'];
        $categories = $csv_conf['categories'];
        $header = $csv_conf['header'];

        /**
         * Pega os parâmetros do endpoint
         */
        if (!empty($this->data)) {
            //Pega a oportunidade do endpoint
            if (!isset($this->data['opportunity']) || empty($this->data['opportunity'])) {
                throw new Exception("Informe a oportunidade! Ex.: opportunity:2");

            } elseif (!is_numeric($this->data['opportunity']) || !in_array($this->data['opportunity'], $this->config['inciso2_opportunity_ids'])) {
                throw new Exception("Oportunidade inválida");

            } else {
                $opportunity_id = $this->data['opportunity'];
            }
        }

        /**
         * Pega informações da oportunidade
         */
        $opportunity = $app->repo('Opportunity')->find($opportunity_id);
        $this->registerRegistrationMetadata($opportunity);

        if (!$opportunity->canUser('@control')) {
            echo "Não autorizado";
            die();
        }

        /**
         * Busca as inscrições com status 10 (Selecionada)
         * lembrando que o botão para exportar esses dados, so estrá disponível se existir inscrições nesse status
         */
        $dql = "SELECT e FROM MapasCulturais\Entities\Registration e WHERE e.status = :status AND e.opportunity = :opportunity_Id";

        $query = $app->em->createQuery($dql);
        $query->setParameters([
            'opportunity_id' => $opportunity_id,
            'status' => $status,
        ]);

        $registrations = $query->getResult();

        if (empty($registrations)) {
            echo "Não foram encontrados registros.";
            die();
        }

        /**
         * Mapeamento de fields_id pelo label do campo
         */
        foreach ($opportunity->registrationFieldConfigurations as $field) {
            $field_labelMap["field_" . $field->id] = trim($field->title);
        }

        /**
         * Monta a estrutura de field_id's e as coloca dentro de um array organizado para a busca dos dados
         *
         * Será feito uma comparação de string, coloque no arquivo de configuração
         * exatamente o texto do label desejado
         */
        $fieldsID = [];
        foreach ($csv_conf['fields'] as $key_csv_conf => $field) {
            if (is_array($field)) {
                $fields = array_unique($field);
                if (count($fields) == 1) {
                    foreach ($field as $key => $value) {
                        $field_temp = array_keys($field_labelMap, $value);

                    }

                } else {
                    $field_temp = [];
                    foreach ($field as $key => $value) {
                        $field_temp[] = array_search(trim($value), $field_labelMap);

                    }
                }
                $fieldsID[$key_csv_conf] = $field_temp;

            } else {
                $field_temp = array_search(trim($field), $field_labelMap);
                $fieldsID[$key_csv_conf] = $field_temp ? $field_temp : $field;

            }
        }

        /**
         * Busca os dados em seus respecitivos registros com os fields mapeados
         */
        $mappedRecords = [
            'CPF' => function ($registrations) use ($fieldsID, $categories) {
                if (in_array($registrations->category, $categories['CPF'])) {
                    $field_id = $fieldsID['CPF'];
                    return str_replace(['.', '-'], '', $registrations->$field_id);
                } else {
                    return 0;
                }
            },
            'NOME_SOCIAL' => function ($registrations) use ($fieldsID, $categories) {
                if (in_array($registrations->category, $categories['CPF'])) {
                    $field_id = $fieldsID['NOME_SOCIAL'];
                    return $registrations->$field_id;
                } else {
                    return "";
                }
            },
            'CNPJ' => function ($registrations) use ($fieldsID, $categories) {
                if (in_array($registrations->category, $categories['CNPJ'])) {
                    $field_id = $fieldsID['CNPJ'];
                    if (is_array($field_id)) {
                        $result = "";
                        foreach ($field_id as $key => $value) {
                            if ($registrations->$value) {
                                $result = str_replace(['.', '-', '/'], '', $registrations->$value);
                            }
                        }
                        return $result;
                    } else {
                        return str_replace(['.', '-', '/'], '', $registrations->$field_id);
                    }
                } else {
                    return 0;
                }
            },
            'RAZAO_SOCIAL' => function ($registrations) use ($fieldsID, $categories) {
                if (in_array($registrations->category, $categories['CNPJ'])) {
                    $field_id = $fieldsID['RAZAO_SOCIAL'];
                    if (is_array($field_id)) {
                        $result = "";
                        foreach ($field_id as $key => $value) {
                            if ($registrations->$value) {
                                $result = $registrations->$value;
                            }
                        }
                        return $result;
                    } else {
                        return $registrations->$field_id;
                    }
                } else {
                    return "";
                }
            },
            'LOGRADOURO' => function ($registrations) use ($fieldsID) {
                $field_id = $fieldsID['LOGRADOURO'];
                return $registrations->$field_id['En_Nome_Logradouro'];
            },
            'NUMERO' => function ($registrations) use ($fieldsID) {
                $field_id = $fieldsID['NUMERO'];
                return preg_replace("/[^0-9]/", "", $registrations->$field_id['En_Num']);
            },
            'COMPLEMENTO' => function ($registrations) use ($fieldsID) {
                $field_id = $fieldsID['COMPLEMENTO'];
                return $registrations->$field_id['En_Complemento'];
            },
            'BAIRRO' => function ($registrations) use ($fieldsID) {
                $field_id = $fieldsID['BAIRRO'];
                return $registrations->$field_id['En_Bairro'];
            },
            'MUNICIPIO' => function ($registrations) use ($fieldsID) {
                $field_id = $fieldsID['MUNICIPIO'];
                return $registrations->$field_id['En_Municipio'];
            },
            'CEP' => function ($registrations) use ($fieldsID) {
                $field_id = $fieldsID['CEP'];
                return preg_replace("/[^0-9]/", "", $registrations->$field_id['En_CEP']);
            },
            'ESTADO' => function ($registrations) use ($fieldsID) {
                $field_id = $fieldsID['ESTADO'];
                return $registrations->$field_id['En_Estado'];
            },
            'NUM_BANCO' => function ($registrations) use ($fieldsID) {
                $field_id = $fieldsID['NUM_BANCO'];
                return $this->numberBank($registrations->$field_id);
            },
            'TIPO_CONTA_BANCO' => function ($registrations) use ($fieldsID) {
                $field_id = $fieldsID['TIPO_CONTA_BANCO'];
                return $registrations->$field_id;
            },
            'AGENCIA_BANCO' => function ($registrations) use ($fieldsID) {
                $field_id = $fieldsID['AGENCIA_BANCO'];
                return preg_replace("/[^0-9]/", "", $registrations->$field_id);
            },
            'CONTA_BANCO' => function ($registrations) use ($fieldsID) {
                $field_id = $fieldsID['CONTA_BANCO'];
                return preg_replace("/[^0-9]/", "", $registrations->$field_id);
            },
            'OPERACAO_BANCO' => function ($registrations) use ($fieldsID) {
                $field_id = $fieldsID['OPERACAO_BANCO'];
                return preg_replace("/[^0-9]/", "", $registrations->$field_id);
            },
            'VALOR' => $fieldsID['VALOR'],
            'INSCRICAO_ID' => function ($registrations) use ($fieldsID) {
                return preg_replace("/[^0-9]/", "", $registrations->number);

            },
            'INCISO' => function ($registrations) use ($fieldsID) {
                $field_id = $fieldsID['INCISO'];
                return $field_id;
            },

        ];

        //Itera sobre os dados mapeados
        $csv_data = [];
        foreach ($registrations as $key_registration => $registration) {
            foreach ($mappedRecords as $key_fields => $field) {
                if (is_callable($field)) {
                    $csv_data[$key_registration][$key_fields] = $field($registration);

                } else if (is_string($field) && strlen($field) > 0) {
                    if ($registration->$field) {
                        $csv_data[$key_registration][$key_fields] = $registration->$field;
                    } else {
                        $csv_data[$key_registration][$key_fields] = $field;
                    }

                } else {
                    if (strstr($field, 'field_')) {
                        $csv_data[$key_registration][$key_fields] = null;
                    } else {
                        $csv_data[$key_registration][$key_fields] = $field;
                    }

                }
            }
        }

        /**
         * Salva o arquivo no servidor e faz o dispatch dele em um formato CSV
         * O arquivo e salvo no deretório docker-data/private-files/aldirblanc/inciso2/remessas
         */
        $file_name = 'inciso2-genCsv-' . md5(json_encode($csv_data)) . '.csv';

        $dir = PRIVATE_FILES_PATH . 'aldirblanc/inciso2/remessas/generics/';

        $patch = $dir . $file_name;

        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }

        $stream = fopen($patch, 'w');

        $csv = Writer::createFromStream($stream);

        $csv->insertOne($header);

        foreach ($csv_data as $key_csv => $csv_line) {
            $csv->insertOne($csv_line);
        }

        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename=' . $file_name);
        header('Pragma: no-cache');
        readfile($patch);
    }

    /**
     * Implementa o exportador TXT no modelo CNAB 240, para envio de remessas ao banco do Brasil inciso1
     *
     *
     */
    public function ALL_exportCnab240Inciso1()
    {

        /**
         * Verifica se o usuário está autenticado
         */
        $this->requireAuthentication();
        $app = App::i();

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
        }

        //Pega a oportunidade no array de config
        $opportunity_id = $this->config['inciso1_opportunity_id'];

        /**
         * Pega informações da oportunidade
         */
        $opportunity = $app->repo('Opportunity')->find($opportunity_id);
        $this->registerRegistrationMetadata($opportunity);

        if (!$opportunity->canUser('@control')) {
            echo "Não autorizado";
            die();
        }

        /**
         * Pega os dados das configurações
         */
        $txt_config = $this->config['config-cnab240-inciso1'];
        $default = $txt_config['parameters_default'];
        $status = $default['status'];
        $header1 = $txt_config['HEADER1'];
        $header2 = $txt_config['HEADER2'];
        $detahe1 = $txt_config['DETALHE1'];
        $detahe2 = $txt_config['DETALHE2'];
        $trailer1 = $txt_config['TRAILER1'];
        $trailer2 = $txt_config['TRAILER2'];

        /**
         * Busca as inscrições com status 10 (Selecionada)
         */
         if ($getData) {
            $dql = "SELECT e FROM MapasCulturais\Entities\Registration e
            WHERE e.status = :status AND
            e.opportunity = :opportunity_Id AND
            e.sentTimestamp >=:startDate AND
            e.sentTimestamp <= :finishDate";

            $query = $app->em->createQuery($dql);
            $query->setParameters([
                'opportunity_Id' => $opportunity_id,
                'status' => $status,
                'startDate' => $startDate,
                'finishDate' => $finishDate,
            ]);

            $registrations = $query->getResult();

        } else {
            $dql = "SELECT e FROM MapasCulturais\Entities\Registration e
            WHERE e.status = :status AND
            e.opportunity = :opportunity_Id";

            $query = $app->em->createQuery($dql);
            $query->setParameters([
                'opportunity_Id' => $opportunity_id,
                'status' => $status,
            ]);

            $registrations = $query->getResult();

        }

        if (empty($registrations)) {
            echo "Não foram encontrados registros.";
            die();
        }

        $mappedHeader1 = [
            'BANCO' => '',
            'LOTE' => '',
            'REGISTRO' => '',
            'USO_BANCO_12' => '',
            'INSCRICAO_TIPO' => '',
            'CPF_CNPJ_FONTE_PAG' => '',
            'CONVENIO_BB1' => '',
            'CONVENIO_BB2' => '',
            'CONVENIO_BB3' => '',
            'CONVENIO_BB4' => '',
            'AGENCIA' => '',
            'AGENCIA_DIGITO' => '',
            'CONTA' => '',
            'CONTA_DIGITO' => '',
            'USO_BANCO_20' => '',
            'NOME_EMPRESA' => '',
            'NOME_BANCO' => '',
            'USO_BANCO_23' => '',
            'CODIGO_REMESSA' => '',
            'DATA_GER_ARQUIVO' => function ($registrations) use ($detahe1) {
                $date = new DateTime();
                return $date->format('d/m/Y');
            },
            'HORA_GER_ARQUIVO' => function ($registrations) use ($detahe1) {
                $date = new DateTime();
                return $date->format('H:i:s');
            },
            'NUM_SERQUNCIAL_ARQUIVO' => '',
            'LAYOUT_ARQUIVO' => '',
            'DENCIDADE_GER_ARQUIVO' => '',
            'USO_BANCO_30' => '',
            'USO_BANCO_31' => '',
        ];

        $mappedHeader2 = [
            'BANCO' => '',
            'LOTE' => '',
            'REGISTRO' => '',
            'OPERACAO' => '',
            'SERVICO' => '',
            'FORMA_LANCAMENTO' => '',
            'LAYOUT_LOTE' => '',
            'USO_BANCO_43' => '',
            'INSCRICAO_TIPO' => '',
            'INSCRICAO_NUMERO' => '',
            'CONVENIO_BB1' => '',
            'CONVENIO_BB2' => '',
            'CONVENIO_BB3' => '',
            'CONVENIO_BB4' => '',
            'AGENCIA' => '',
            'AGENCIA_DIGITO' => '',
            'CONTA' => '',
            'CONTA_DIGITO' => '',
            'USO_BANCO_51' => '',
            'NOME_EMPRESA' => '',
            'USO_BANCO_40' => '',
            'LOGRADOURO' => '',
            'NUMERO' => '',
            'COMPLEMENTO' => '',
            'CIDADE' => '',
            'CEP' => '',
            'ESTADO' => '',
            'USO_BANCO_60' => '',
            'USO_BANCO_61' => '',
        ];

        $mappedDeletalhe1 = [
            'BANCO' => '',
            'LOTE' => '',
            'REGISTRO' => '',
            'NUMERO_REGISTRO' => '',
            'SEGMENTO' => '',
            'TIPO_MOVIMENTO' => '',
            'CODIGO_MOVIMENTO' => '',
            'CAMARA_CENTRALIZADORA' => '',
            'BEN_CODIGO_BANCO' => function ($registrations) use ($detahe1) {
                $field_id = $detahe1['BEN_CODIGO_BANCO']['field_id'];
                return $this->numberBank($registrations->$field_id);
            },
            'BEN_AGENCIA' => function ($registrations) use ($detahe1) {
                $field_id = $detahe1['BEN_AGENCIA']['field_id'];
                return $registrations->$field_id;
            },
            'BEN_AGENCIA_DIGITO' => function ($registrations) use ($detahe1) {
                $field_id = $detahe1['BEN_AGENCIA_DIGITO']['field_id'];
                return $registrations->$field_id;
            },
            'BEN_CONTA' => function ($registrations) use ($detahe1, $default) {
                $temp = $detahe1['BEN_CODIGO_BANCO']['field_id'];
                $numberBank = $this->numberBank($registrations->$temp);
                $temp = $detahe1['TIPO_CONTA']['field_id'];
                $typeAccount = $registrations->$temp;
                $field_id = $detahe1['BEN_CONTA']['field_id'];
                $account = $registrations->$field_id;

                if ($numberBank = '001' && $typeAccount == $default['typesAccount']['poupanca']) {

                    if (substr($account, 0, 3) != "510") {
                        return "510" . $account;
                    } else {

                        return $account;

                    }
                } else {

                    return $registrations->$field_id;
                }

                $field_id = $detahe1['BEN_CONTA_DIGITO']['field_id'];

            },
            'BEN_CONTA_DIGITO' => function ($registrations) use ($detahe1, $default) {
                $temp = $detahe1['BEN_CODIGO_BANCO']['field_id'];
                $numberBank = $this->numberBank($registrations->$temp);

                $temp = $detahe1['TIPO_CONTA']['field_id'];
                $typeAccount = $registrations->$temp;

                $temp = $detahe1['BEN_CONTA_DIGITO']['field_id'];
                $account = preg_replace('/[^0-9]/i', '', $registrations->$temp);

                $digit = substr($account, -1);

                if ($numberBank = '001' && $typeAccount == $default['typesAccount']['poupanca']) {

                    if (substr($account, 0, 3) == "510") {
                        return $digit;
                    } else {

                        return $default['savingsDigit'][$digit];

                    }
                } else {

                    return $digit;
                }

            },
            'BEN_DIGITO_CONTA_AGENCIA_80' => '',
            'BEN_NOME' => function ($registrations) use ($detahe1) {
                $field_id = $detahe1['BEN_NOME']['field_id'];
                return $registrations->$field_id;
            },
            'BEN_DOC_ATRIB_EMPRESA_82' => '',
            'DATA_PAGAMENTO' => function ($registrations) use ($detahe1) {
                $date = new DateTime();
                $date->add(new DateInterval('P1D'));
                $weekday = $date->format('D');

                $weekdayList = [
                    'Mon' => true,
                    'Tue' => true,
                    'Wed' => true,
                    'Thu' => true,
                    'Fri' => true,
                    'Sat' => false,
                    'Sun' => false,
                ];

                while (!$weekdayList[$weekday]) {
                    $date->add(new DateInterval('P1D'));
                    $weekday = $date->format('D');
                }

                return $date->format('d/m/Y');
            },
            'TIPO_MOEDA' => '',
            'USO_BANCO_85' => '',
            'VALOR_INTEIRO' => function ($registrations) use ($detahe1) {
                $valor = '100,50';
                $valor = preg_replace('/[^0-9]/i', '', $valor);

                return $valor;
            },
            'USO_BANCO_88' => '',
            'USO_BANCO_89' => '',
            'USO_BANCO_90' => '',
            'CODIGO_FINALIDADE_TED' => function ($registrations) use ($detahe1) {
                $temp = $detahe1['BEN_CODIGO_BANCO']['field_id'];
                $numberBank = $this->numberBank($registrations->$temp);
                if ($numberBank != "001") {
                    return '10';
                } else {
                    return "";
                }
            },
            'USO_BANCO_92' => '',
            'USO_BANCO_93' => '',
            'TIPO_CONTA' => '',
        ];

        $mappedDeletalhe2 = [
            'BANCO' => '',
            'LOTE' => '',
            'REGISTRO' => '',
            'NUMERO_REGISTRO' => '',
            'SEGMENTO' => '',
            'USO_BANCO_104' => '',
            'BEN_TIPO_DOC' => '',
            'BEN_CPF' => '',
            'BEN_ENDERECO_LOGRADOURO' => function ($registrations) use ($detahe2) {
                $field_id = $detahe2['BEN_ENDERECO_LOGRADOURO']['field_id'];
                $data = $registrations->$field_id;
                return $data['En_Nome_Logradouro'];
            },
            'BEN_ENDERECO_NUMERO' => function ($registrations) use ($detahe2) {
                $field_id = $detahe2['BEN_ENDERECO_NUMERO']['field_id'];
                $data = $registrations->$field_id;
                return $data['En_Num'];
            },
            'BEN_ENDERECO_COMPLEMENTO' => function ($registrations) use ($detahe2) {
                $field_id = $detahe2['BEN_ENDERECO_COMPLEMENTO']['field_id'];
                $data = $registrations->$field_id;
                return $data['En_Complemento'];
            },
            'BEN_ENDERECO_BAIRRO' => function ($registrations) use ($detahe2) {
                $field_id = $detahe2['BEN_ENDERECO_BAIRRO']['field_id'];
                $data = $registrations->$field_id;
                return $data['En_Bairro'];
            },
            'BEN_ENDERECO_CIDADE' => function ($registrations) use ($detahe2) {
                $field_id = $detahe2['BEN_ENDERECO_CIDADE']['field_id'];
                $data = $registrations->$field_id;
                return $data['En_Municipio'];
            },
            'BEN_ENDERECO_CEP' => function ($registrations) use ($detahe2) {
                $field_id = $detahe2['BEN_ENDERECO_CEP']['field_id'];
                $data = $registrations->$field_id;
                return $data['En_CEP'];
            },
            'BEN_ENDERECO_ESTADO' => function ($registrations) use ($detahe2) {
                $field_id = $detahe2['BEN_ENDERECO_ESTADO']['field_id'];
                $data = $registrations->$field_id;
                return $data['En_Estado'];
            },
            'USO_BANCO_114' => '',
            'USO_BANCO_115' => '',
            'USO_BANCO_116' => '',
            'USO_BANCO_117' => '',
        ];

        $mappedTrailer1 = [
            'BANCO' => '',
            'LOTE' => '',
            'REGISTRO' => '',
            'USO_BANCO_126' => '',
            'QUANTIDADE_REGISTROS_127' => '',
            'VALOR_TOTAL_DOC_INTEIRO' => '',
            'VALOR_TOTAL_DOC_DECIMAL' => '',
            'USO_BANCO_130' => '',
            'USO_BANCO_131' => '',
            'USO_BANCO_132' => '',
        ];

        $mappedTrailer2 = [
            'BANCO' => '',
            'LOTE' => '',
            'REGISTRO' => '',
            'USO_BANCO_141' => '',
            'QUANTIDADE_LOTES-ARQUIVO' => '',
            'QUANTIDADE_REGISTROS_ARQUIVOS' => '',
            'USO_BANCO_144' => '',
            'USO_BANCO_145' => '',
        ];

        /**
         * Separa os registros em 3 categorias
         * $recordsBBPoupanca =  Contas polpança BB
         * $recordsBBCorrente = Contas corrente BB
         * $recordsOthers = Contas outros bancos
         */
        $recordsBBPoupanca = [];
        $recordsBBCorrente = [];
        $recordsOthers = [];
        $field_conta = $default['field_conta'];
        $field_banco = $default['field_banco'];
        foreach ($registrations as $value) {
            if ($this->numberBank($value->$field_banco) == "001") {

                if ($value->$field_conta == "Conta corrente") {
                    $recordsBBCorrente[] = $value;
                } else {
                    $recordsBBPoupanca[] = $value;
                }

            } else {
                $recordsOthers[] = $value;
            }
        }

        /**
         * Monta o txt analisando as configs. caso tenha que buscar algo no banco de dados,
         * faz a pesquisa atravez do array mapped. Caso contrario busca o valor default da configuração
         *
         */
        $txt_data = "";
        $numLote = 0;
        $totaLotes = 0;
        $totalRegistros = 0;

        $complement = [];
        $txt_data = $this->mountTxt($header1, $mappedHeader1, $txt_data, null, null);
        $totalRegistros += 1;

        $txt_data .= "\r\n";

        /**
         * Inicio banco do Brasil Corrente
         */
        $lotBBCorrente = 0;
        if ($recordsBBCorrente) {
            // Header 2
            $complement = [];
            $numLote++;
            $complement = [
                'FORMA_LANCAMENTO' => 01,
                'LOTE' => $numLote,
            ];

            $txt_data = $this->mountTxt($header2, $mappedHeader2, $txt_data, null, $complement);
            $txt_data .= "\r\n";

            $lotBBCorrente += 1;

            $_SESSION['valor'] = 0;

            $totaLotes++;

            //Detalhes 1 e 2

            foreach ($recordsBBCorrente as $key_records => $records) {
                $complement = [
                    'LOTE' => $numLote,
                ];
                $txt_data = $this->mountTxt($detahe1, $mappedDeletalhe1, $txt_data, $records, $complement);
                $txt_data .= "\r\n";

                $txt_data = $this->mountTxt($detahe2, $mappedDeletalhe2, $txt_data, $records, $complement);
                $txt_data .= "\r\n";

                $lotBBCorrente += 2;

            }

            //treiller 1
            $lotBBCorrente + 1; // Adiciona 1 para obedecer a regra de somar o treiller 1
            $valor = explode(".", $_SESSION['valor']);
            $valor = preg_replace('/[^0-9]/i', '', $valor[0]);
            $complement = [
                'QUANTIDADE_REGISTROS_127' => $lotBBCorrente,
                'VALOR_TOTAL_DOC_INTEIRO' => $valor,

            ];

            $txt_data = $this->mountTxt($trailer1, $mappedTrailer1, $txt_data, null, $complement);
            $txt_data .= "\r\n";
            $totalRegistros += $lotBBCorrente;
        }

        /**
         * Inicio banco do Brasil Poupança
         */
        $lotBBPoupanca = 0;
        if ($recordsBBPoupanca) {
            // Header 2
            $complement = [];
            $numLote++;
            $complement = [
                'FORMA_LANCAMENTO' => 5,
                'LOTE' => $numLote,
            ];
            $txt_data = $this->mountTxt($header2, $mappedHeader2, $txt_data, null, $complement);
            $txt_data .= "\r\n";

            $lotBBPoupanca += 1;

            $_SESSION['valor'] = 0;

            $totaLotes++;

            //Detalhes 1 e 2

            foreach ($recordsBBPoupanca as $key_records => $records) {
                $complement = [
                    'LOTE' => $numLote,
                ];

                $txt_data = $this->mountTxt($detahe1, $mappedDeletalhe1, $txt_data, $records, $complement);
                $txt_data .= "\r\n";

                $txt_data = $this->mountTxt($detahe2, $mappedDeletalhe2, $txt_data, $records, $complement);
                $txt_data .= "\r\n";

                $lotBBPoupanca += 2;

            }

            //treiller 1
            $lotBBPoupanca += 1; // Adiciona 1 para obedecer a regra de somar o treiller 1
            $valor = explode(".", $_SESSION['valor']);
            $valor = preg_replace('/[^0-9]/i', '', $valor[0]);
            $complement = [
                'QUANTIDADE_REGISTROS_127' => $lotBBPoupanca,
                'VALOR_TOTAL_DOC_INTEIRO' => $valor,
                'LOTE' => $numLote,
            ];

            $txt_data = $this->mountTxt($trailer1, $mappedTrailer1, $txt_data, null, $complement);
            $txt_data .= "\r\n";

            $totalRegistros += $lotBBPoupanca;
        }

        /**
         * Inicio Outros bancos
         */
        $lotOthers = 0;
        if ($recordsOthers) {
            //Header 2
            $complement = [];
            $numLote++;
            $complement = [
                'FORMA_LANCAMENTO' => 03,
                'LOTE' => $numLote,
            ];

            $txt_data = $this->mountTxt($header2, $mappedHeader2, $txt_data, null, $complement);

            $txt_data .= "\r\n";

            $lotOthers += 1;

            $_SESSION['valor'] = 0;

            $totaLotes++;

            //Detalhes 1 e 2

            foreach ($recordsOthers as $key_records => $records) {
                $complement = [
                    'LOTE' => $numLote,
                ];
                $txt_data = $this->mountTxt($detahe1, $mappedDeletalhe1, $txt_data, $records, $complement);

                $txt_data .= "\r\n";

                $txt_data = $this->mountTxt($detahe2, $mappedDeletalhe2, $txt_data, $records, $complement);
                $txt_data .= "\r\n";
                $lotOthers += 2;

            }

            //treiller 1
            $lotOthers += 1; // Adiciona 1 para obedecer a regra de somar o treiller 1
            $valor = explode(".", $_SESSION['valor']);
            $valor = preg_replace('/[^0-9]/i', '', $valor[0]);
            $complement = [
                'QUANTIDADE_REGISTROS_127' => $lotOthers,
                'VALOR_TOTAL_DOC_INTEIRO' => $valor,
                'LOTE' => $numLote,
            ];
            $txt_data = $this->mountTxt($trailer1, $mappedTrailer1, $txt_data, null, $complement);
            $txt_data .= "\r\n";
            $totalRegistros += $lotOthers;
        }

        //treiller do arquivo
        $totalRegistros += 1; // Adiciona 1 para obedecer a regra de somar o treiller
        $complement = [
            'QUANTIDADE_LOTES-ARQUIVO' => $totaLotes,
            'QUANTIDADE_REGISTROS_ARQUIVOS' => $totalRegistros,
        ];

        $txt_data = $this->mountTxt($trailer2, $mappedTrailer2, $txt_data, null, $complement);

        /**
         * cria o arquivo no servidor e insere o conteuto da váriavel $txt_data
         */
        $file_name = 'inciso1-cnab240-' . md5(json_encode($txt_data)) . '.txt';        

        $dir = PRIVATE_FILES_PATH . 'aldirblanc/inciso1/remessas/cnab240/';   

        $patch = $dir . $file_name;

        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }

        $stream = fopen($patch, 'w');

        fwrite($stream, $txt_data);

        fclose($stream);

        header('Content-Type: application/txt');
        header('Content-Disposition: attachment; filename=' . $file_name);
        header('Pragma: no-cache');
        readfile($patch);

    }

    /**
     * Implementa o exportador TXT no modelo CNAB 240, para envio de remessas ao banco do Brasil Inciso 2
     *
     *
     */
    public function ALL_exportCnab240Inciso2()
    {
        /**
         * Verifica se o usuário está autenticado
         */
        $this->requireAuthentication();
        $app = App::i();

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

            //Pega a oportunidade do endpoint
            if (!isset($this->data['opportunity']) || empty($this->data['opportunity'])) {
                throw new Exception("Informe a oportunidade! Ex.: opportunity:2");

            } elseif (!is_numeric($this->data['opportunity']) || !in_array($this->data['opportunity'], $this->config['inciso2_opportunity_ids'])) {
                throw new Exception("Oportunidade inválida");

            } else {
                $opportunity_id = $this->data['opportunity'];
            }

        } else {
            throw new Exception("Informe a oportunidade! Ex.: opportunity:2");

        }

        /**
         * Pega informações da oportunidade
         */
        $opportunity = $app->repo('Opportunity')->find($opportunity_id);
        $this->registerRegistrationMetadata($opportunity);

         /**
         * Mapeamento de fielsds_id pelo label do campo
         */
        foreach ($opportunity->registrationFieldConfigurations as $field) {
            $field_labelMap["field_" . $field->id] = trim($field->title);

        }


        if (!$opportunity->canUser('@control')) {
            echo "Não autorizado";
            die();
        }

        /**
         * Pega os dados das configurações
         */
        $txt_config = $this->config['config-cnab240-inciso2'];
        $default = $txt_config['parameters_default'];
        $status = $default['status'];
        $header1 = $txt_config['HEADER1'];
        $header2 = $txt_config['HEADER2'];
        $detahe1 = $txt_config['DETALHE1'];
        $detahe2 = $txt_config['DETALHE2'];
        $trailer1 = $txt_config['TRAILER1'];
        $trailer2 = $txt_config['TRAILER2'];

        foreach($header1 as $key_config => $value){ 
            if(is_string($value['field_id']) && strlen($value['field_id'])>0 && $value['field_id'] != 'mapped'){
                $field_id = array_search(trim($value['field_id']), $field_labelMap);
                $header1[$key_config]['field_id'] = $field_id;                
            }
        }

        foreach($header2 as $key_config => $value){ 
            if(is_string($value['field_id']) && strlen($value['field_id'])>0 && $value['field_id'] != 'mapped'){
                $field_id = array_search(trim($value['field_id']), $field_labelMap);
                $header2[$key_config]['field_id'] = $field_id;                
            }
        }

        foreach($detahe1 as $key_config => $value){ 
            if(is_string($value['field_id']) && strlen($value['field_id'])>0 && $value['field_id'] != 'mapped'){
                $field_id = array_search(trim($value['field_id']), $field_labelMap);
                $detahe1[$key_config]['field_id'] = $field_id;                
            }
        }

        foreach($detahe2 as $key_config => $value){ 
            if(is_string($value['field_id']) && strlen($value['field_id'])>0 && $value['field_id'] != 'mapped'){
                $field_id = array_search(trim($value['field_id']), $field_labelMap);
                $detahe2[$key_config]['field_id'] = $field_id;                
            }
        }

        foreach($trailer1 as $key_config => $value){ 
            if(is_string($value['field_id']) && strlen($value['field_id'])>0 && $value['field_id'] != 'mapped'){
                $field_id = array_search(trim($value['field_id']), $field_labelMap);
                $trailer1[$key_config]['field_id'] = $field_id;                
            }
        }

        foreach($trailer2 as $key_config => $value){ 
            if(is_string($value['field_id']) && strlen($value['field_id'])>0 && $value['field_id'] != 'mapped'){
                $field_id = array_search(trim($value['field_id']), $field_labelMap);
                $trailer2[$key_config]['field_id'] = $field_id;                
            }
        }        
        

        /**
         * Busca as inscrições com status 10 (Selecionada)
         */
        if ($getData) {
            $dql = "SELECT e FROM MapasCulturais\Entities\Registration e
            WHERE e.status = :status AND
            e.opportunity = :opportunity_Id AND
            e.sentTimestamp >=:startDate AND
            e.sentTimestamp <= :finishDate";

            $query = $app->em->createQuery($dql);
            $query->setParameters([
                'opportunity_Id' => $opportunity_id,
                'status' => $status,
                'startDate' => $startDate,
                'finishDate' => $finishDate,
            ]);

            $registrations = $query->getResult();

        } else {
            $dql = "SELECT e FROM MapasCulturais\Entities\Registration e
            WHERE e.status = :status AND
            e.opportunity = :opportunity_Id";

            $query = $app->em->createQuery($dql);
            $query->setParameters([
                'opportunity_Id' => $opportunity_id,
                'status' => $status,
            ]);

            $registrations = $query->getResult();

        }
        
        if (empty($registrations)) {
            echo "Não foram encontrados registros.";
            die();
        }

        $mappedHeader1 = [
            'BANCO' => '',
            'LOTE' => '',
            'REGISTRO' => '',
            'USO_BANCO_12' => '',
            'INSCRICAO_TIPO' => '',
            'CPF_CNPJ_FONTE_PAG' => '',
            'CONVENIO_BB1' => '',
            'CONVENIO_BB2' => '',
            'CONVENIO_BB3' => '',
            'CONVENIO_BB4' => '',
            'AGENCIA' => '',
            'AGENCIA_DIGITO' => '',
            'CONTA' => '',
            'CONTA_DIGITO' => '',
            'USO_BANCO_20' => '',
            'NOME_EMPRESA' => '',
            'NOME_BANCO' => '',
            'USO_BANCO_23' => '',
            'CODIGO_REMESSA' => '',
            'DATA_GER_ARQUIVO' => function ($registrations) use ($detahe1) {
                $date = new DateTime();
                return $date->format('d/m/Y');
            },
            'HORA_GER_ARQUIVO' => function ($registrations) use ($detahe1) {
                $date = new DateTime();
                return $date->format('H:i:s');
            },
            'NUM_SERQUNCIAL_ARQUIVO' => '',
            'LAYOUT_ARQUIVO' => '',
            'DENCIDADE_GER_ARQUIVO' => '',
            'USO_BANCO_30' => '',
            'USO_BANCO_31' => '',
        ];

        $mappedHeader2 = [
            'BANCO' => '',
            'LOTE' => '',
            'REGISTRO' => '',
            'OPERACAO' => '',
            'SERVICO' => '',
            'FORMA_LANCAMENTO' => '',
            'LAYOUT_LOTE' => '',
            'USO_BANCO_43' => '',
            'INSCRICAO_TIPO' => '',
            'INSCRICAO_NUMERO' => '',
            'CONVENIO_BB1' => '',
            'CONVENIO_BB2' => '',
            'CONVENIO_BB3' => '',
            'CONVENIO_BB4' => '',
            'AGENCIA' => '',
            'AGENCIA_DIGITO' => '',
            'CONTA' => '',
            'CONTA_DIGITO' => '',
            'USO_BANCO_51' => '',
            'NOME_EMPRESA' => '',
            'USO_BANCO_40' => '',
            'LOGRADOURO' => '',
            'NUMERO' => '',
            'COMPLEMENTO' => '',
            'CIDADE' => '',
            'CEP' => '',
            'ESTADO' => '',
            'USO_BANCO_60' => '',
            'USO_BANCO_61' => '',
        ];

        $mappedDeletalhe1 = [
            'BANCO' => '',
            'LOTE' => '',
            'REGISTRO' => '',
            'NUMERO_REGISTRO' => '',
            'SEGMENTO' => '',
            'TIPO_MOVIMENTO' => '',
            'CODIGO_MOVIMENTO' => '',
            'CAMARA_CENTRALIZADORA' => '',
            'BEN_CODIGO_BANCO' => function ($registrations) use ($detahe1) {
                $field_id = $detahe1['BEN_CODIGO_BANCO']['field_id'];
                return $this->numberBank($registrations->$field_id);
            },
            'BEN_AGENCIA' => function ($registrations) use ($detahe1) {
                $field_id = $detahe1['BEN_AGENCIA']['field_id'];
                return $registrations->$field_id;
            },
            'BEN_AGENCIA_DIGITO' => function ($registrations) use ($detahe1) {
                $field_id = $detahe1['BEN_AGENCIA_DIGITO']['field_id'];
                return $registrations->$field_id;
            },
            'BEN_CONTA' => function ($registrations) use ($detahe1, $default) {
                $temp = $detahe1['BEN_CODIGO_BANCO']['field_id'];
                $numberBank = $this->numberBank($registrations->$temp);
                $temp = $detahe1['TIPO_CONTA']['field_id'];
                $typeAccount = $registrations->$temp;
                $field_id = $detahe1['BEN_CONTA']['field_id'];
                $account = $registrations->$field_id;

                if ($numberBank = '001' && $typeAccount == $default['typesAccount']['poupanca']) {

                    if (substr($account, 0, 3) != "510") {
                        return "510" . $account;
                    } else {

                        return $account;

                    }
                } else {

                    return $registrations->$field_id;
                }

                $field_id = $detahe1['BEN_CONTA_DIGITO']['field_id'];

            },
            'BEN_CONTA_DIGITO' => function ($registrations) use ($detahe1, $default) {
                $temp = $detahe1['BEN_CODIGO_BANCO']['field_id'];
                $numberBank = $this->numberBank($registrations->$temp);

                $temp = $detahe1['TIPO_CONTA']['field_id'];
                $typeAccount = $registrations->$temp;

                $temp = $detahe1['BEN_CONTA_DIGITO']['field_id'];
                $account = preg_replace('/[^0-9]/i', '', $registrations->$temp);

                $digit = substr($account, -1);

                if ($numberBank = '001' && $typeAccount == $default['typesAccount']['poupanca']) {

                    if (substr($account, 0, 3) == "510") {
                        return $digit;
                    } else {

                        return $default['savingsDigit'][$digit];

                    }
                } else {

                    return $digit;
                }

            },
            'BEN_DIGITO_CONTA_AGENCIA_80' => '',
            'BEN_NOME' => function ($registrations) use ($detahe1) {
                $field_id = $detahe1['BEN_NOME']['field_id'];
                return $registrations->$field_id;
            },
            'BEN_DOC_ATRIB_EMPRESA_82' => '',
            'DATA_PAGAMENTO' => function ($registrations) use ($detahe1) {
                $date = new DateTime();
                $date->add(new DateInterval('P1D'));
                $weekday = $date->format('D');

                $weekdayList = [
                    'Mon' => true,
                    'Tue' => true,
                    'Wed' => true,
                    'Thu' => true,
                    'Fri' => true,
                    'Sat' => false,
                    'Sun' => false,
                ];

                while (!$weekdayList[$weekday]) {
                    $date->add(new DateInterval('P1D'));
                    $weekday = $date->format('D');
                }

                return $date->format('d/m/Y');
            },
            'TIPO_MOEDA' => '',
            'USO_BANCO_85' => '',
            'VALOR_INTEIRO' => function ($registrations) use ($detahe1) {
                $valor = '100,98';
                $valor = preg_replace('/[^0-9]/i', '', $valor);

                return $valor;
            },
            'USO_BANCO_88' => '',
            'USO_BANCO_89' => '',
            'USO_BANCO_90' => '',
            'CODIGO_FINALIDADE_TED' => function ($registrations) use ($detahe1) {
                $temp = $detahe1['BEN_CODIGO_BANCO']['field_id'];
                $numberBank = $this->numberBank($registrations->$temp);
                if ($numberBank != "001") {
                    return '10';
                } else {
                    return "";
                }
            },
            'USO_BANCO_92' => '',
            'USO_BANCO_93' => '',
            'TIPO_CONTA' => '',
        ];

        $mappedDeletalhe2 = [
            'BANCO' => '',
            'LOTE' => '',
            'REGISTRO' => '',
            'NUMERO_REGISTRO' => '',
            'SEGMENTO' => '',
            'USO_BANCO_104' => '',
            'BEN_TIPO_DOC' => '',
            'BEN_CPF' => '',
            'BEN_ENDERECO_LOGRADOURO' => function ($registrations) use ($detahe2) {
                $field_id = $detahe2['BEN_ENDERECO_LOGRADOURO']['field_id'];
                $data = $registrations->$field_id;
                return $data['En_Nome_Logradouro'];
            },
            'BEN_ENDERECO_NUMERO' => function ($registrations) use ($detahe2) {
                $field_id = $detahe2['BEN_ENDERECO_NUMERO']['field_id'];
                $data = $registrations->$field_id;
                return $data['En_Num'];
            },
            'BEN_ENDERECO_COMPLEMENTO' => function ($registrations) use ($detahe2) {
                $field_id = $detahe2['BEN_ENDERECO_COMPLEMENTO']['field_id'];
                $data = $registrations->$field_id;
                return $data['En_Complemento'];
            },
            'BEN_ENDERECO_BAIRRO' => function ($registrations) use ($detahe2) {
                $field_id = $detahe2['BEN_ENDERECO_BAIRRO']['field_id'];
                $data = $registrations->$field_id;
                return $data['En_Bairro'];
            },
            'BEN_ENDERECO_CIDADE' => function ($registrations) use ($detahe2) {
                $field_id = $detahe2['BEN_ENDERECO_CIDADE']['field_id'];
                $data = $registrations->$field_id;
                return $data['En_Municipio'];
            },
            'BEN_ENDERECO_CEP' => function ($registrations) use ($detahe2) {
                $field_id = $detahe2['BEN_ENDERECO_CEP']['field_id'];
                $data = $registrations->$field_id;
                return $data['En_CEP'];
            },
            'BEN_ENDERECO_ESTADO' => function ($registrations) use ($detahe2) {
                $field_id = $detahe2['BEN_ENDERECO_ESTADO']['field_id'];
                $data = $registrations->$field_id;
                return $data['En_Estado'];
            },
            'USO_BANCO_114' => '',
            'USO_BANCO_115' => '',
            'USO_BANCO_116' => '',
            'USO_BANCO_117' => '',
        ];

        $mappedTrailer1 = [
            'BANCO' => '',
            'LOTE' => '',
            'REGISTRO' => '',
            'USO_BANCO_126' => '',
            'QUANTIDADE_REGISTROS_127' => '',
            'VALOR_TOTAL_DOC_INTEIRO' => '',
            'VALOR_TOTAL_DOC_DECIMAL' => '',
            'USO_BANCO_130' => '',
            'USO_BANCO_131' => '',
            'USO_BANCO_132' => '',
        ];

        $mappedTrailer2 = [
            'BANCO' => '',
            'LOTE' => '',
            'REGISTRO' => '',
            'USO_BANCO_141' => '',
            'QUANTIDADE_LOTES-ARQUIVO' => '',
            'QUANTIDADE_REGISTROS_ARQUIVOS' => '',
            'USO_BANCO_144' => '',
            'USO_BANCO_145' => '',
        ];

        /**
         * Separa os registros em 3 categorias
         * $recordsBBPoupanca =  Contas polpança BB
         * $recordsBBCorrente = Contas corrente BB
         * $recordsOthers = Contas outros bancos
         */
        $recordsBBPoupanca = [];
        $recordsBBCorrente = [];
        $recordsOthers = [];
        $field_conta = array_search(trim($default['field_conta']), $field_labelMap);
        $field_banco = array_search(trim($default['field_banco']), $field_labelMap);
        foreach ($registrations as $value) {
            if ($this->numberBank($value->$field_banco) == "001") {

                if ($value->$field_conta == "Conta corrente") {
                    $recordsBBCorrente[] = $value;
                } else {
                    $recordsBBPoupanca[] = $value;
                }

            } else {
                $recordsOthers[] = $value;
            }
        }

        /**
         * Monta o txt analisando as configs. caso tenha que buscar algo no banco de dados,
         * faz a pesquisa atravez do array mapped. Caso contrario busca o valor default da configuração
         *
         */
        $txt_data = "";
        $numLote = 0;
        $totaLotes = 0;
        $totalRegistros = 0;

        $complement = [];
        $txt_data = $this->mountTxt($header1, $mappedHeader1, $txt_data, null, null);
        $totalRegistros += 1;

        $txt_data .= "\r\n";

        /**
         * Inicio banco do Brasil Corrente
         */
        $lotBBCorrente = 0;
        if ($recordsBBCorrente) {
            // Header 2
            $complement = [];
            $numLote++;
            $complement = [
                'FORMA_LANCAMENTO' => 01,
                'LOTE' => $numLote,
            ];

            $txt_data = $this->mountTxt($header2, $mappedHeader2, $txt_data, null, $complement);
            $txt_data .= "\r\n";

            $lotBBCorrente += 1;

            $_SESSION['valor'] = 0;

            $totaLotes++;

            //Detalhes 1 e 2

            foreach ($recordsBBCorrente as $key_records => $records) {
                $complement = [
                    'LOTE' => $numLote,
                ];
                $txt_data = $this->mountTxt($detahe1, $mappedDeletalhe1, $txt_data, $records, $complement);
                $txt_data .= "\r\n";

                $txt_data = $this->mountTxt($detahe2, $mappedDeletalhe2, $txt_data, $records, $complement);
                $txt_data .= "\r\n";

                $lotBBCorrente += 2;

            }

            //treiller 1
            $lotBBCorrente + 1; // Adiciona 1 para obedecer a regra de somar o treiller 1
            $valor = explode(".", $_SESSION['valor']);
            $valor = preg_replace('/[^0-9]/i', '', $valor[0]);
            $complement = [
                'QUANTIDADE_REGISTROS_127' => $lotBBCorrente,
                'VALOR_TOTAL_DOC_INTEIRO' => $valor,

            ];

            $txt_data = $this->mountTxt($trailer1, $mappedTrailer1, $txt_data, null, $complement);
            $txt_data .= "\r\n";
            $totalRegistros += $lotBBCorrente;
        }

        /**
         * Inicio banco do Brasil Poupança
         */
        $lotBBPoupanca = 0;
        if ($recordsBBPoupanca) {
            // Header 2
            $complement = [];
            $numLote++;
            $complement = [
                'FORMA_LANCAMENTO' => 5,
                'LOTE' => $numLote,
            ];
            $txt_data = $this->mountTxt($header2, $mappedHeader2, $txt_data, null, $complement);
            $txt_data .= "\r\n";

            $lotBBPoupanca += 1;

            $_SESSION['valor'] = 0;

            $totaLotes++;

            //Detalhes 1 e 2

            foreach ($recordsBBPoupanca as $key_records => $records) {
                $complement = [
                    'LOTE' => $numLote,
                ];

                $txt_data = $this->mountTxt($detahe1, $mappedDeletalhe1, $txt_data, $records, $complement);
                $txt_data .= "\r\n";

                $txt_data = $this->mountTxt($detahe2, $mappedDeletalhe2, $txt_data, $records, $complement);
                $txt_data .= "\r\n";

                $lotBBPoupanca += 2;

            }

            //treiller 1
            $lotBBPoupanca += 1; // Adiciona 1 para obedecer a regra de somar o treiller 1
            $valor = explode(".", $_SESSION['valor']);
            $valor = preg_replace('/[^0-9]/i', '', $valor[0]);
            $complement = [
                'QUANTIDADE_REGISTROS_127' => $lotBBPoupanca,
                'VALOR_TOTAL_DOC_INTEIRO' => $valor,
                'LOTE' => $numLote,
            ];

            $txt_data = $this->mountTxt($trailer1, $mappedTrailer1, $txt_data, null, $complement);
            $txt_data .= "\r\n";

            $totalRegistros += $lotBBPoupanca;
        }

        /**
         * Inicio Outros bancos
         */
        $lotOthers = 0;
        if ($recordsOthers) {
            //Header 2
            $complement = [];
            $numLote++;
            $complement = [
                'FORMA_LANCAMENTO' => 03,
                'LOTE' => $numLote,
            ];

            $txt_data = $this->mountTxt($header2, $mappedHeader2, $txt_data, null, $complement);

            $txt_data .= "\r\n";

            $lotOthers += 1;

            $_SESSION['valor'] = 0;

            $totaLotes++;

            //Detalhes 1 e 2

            foreach ($recordsOthers as $key_records => $records) {
                $complement = [
                    'LOTE' => $numLote,
                ];
                $txt_data = $this->mountTxt($detahe1, $mappedDeletalhe1, $txt_data, $records, $complement);

                $txt_data .= "\r\n";

                $txt_data = $this->mountTxt($detahe2, $mappedDeletalhe2, $txt_data, $records, $complement);
                $txt_data .= "\r\n";
                $lotOthers += 2;

            }

            //treiller 1
            $lotOthers += 1; // Adiciona 1 para obedecer a regra de somar o treiller 1
            $valor = explode(".", $_SESSION['valor']);
            $valor = preg_replace('/[^0-9]/i', '', $valor[0]);
            $complement = [
                'QUANTIDADE_REGISTROS_127' => $lotOthers,
                'VALOR_TOTAL_DOC_INTEIRO' => $valor,
                'LOTE' => $numLote,
            ];
            $txt_data = $this->mountTxt($trailer1, $mappedTrailer1, $txt_data, null, $complement);
            $txt_data .= "\r\n";
            $totalRegistros += $lotOthers;
        }

        //treiller do arquivo
        $totalRegistros += 1; // Adiciona 1 para obedecer a regra de somar o treiller
        $complement = [
            'QUANTIDADE_LOTES-ARQUIVO' => $totaLotes,
            'QUANTIDADE_REGISTROS_ARQUIVOS' => $totalRegistros,
        ];

        $txt_data = $this->mountTxt($trailer2, $mappedTrailer2, $txt_data, null, $complement);
        
        /**
         * cria o arquivo no servidor e insere o conteuto da váriavel $txt_data
         */
        $file_name = 'inciso2-cnab240-' . md5(json_encode($txt_data)) . '.txt';      

        $dir = PRIVATE_FILES_PATH . 'aldirblanc/inciso1/remessas/cnab240/';        

        $patch = $dir . $file_name;

        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }

        $stream = fopen($patch, 'w');

        fwrite($stream, $txt_data);

        fclose($stream);

        header('Content-Type: application/txt');
        header('Content-Disposition: attachment; filename=' . $file_name);
        header('Pragma: no-cache');
        readfile($patch);

    }

    /**
     * Implementa o exportador TXT no modelo CNAB 240, para envio de remessas ao banco do Brasil Inciso 3
     *
     *
     */
    public function ALL_exportCnab240Inciso3()
    {

    }

    //###################################################################################################################################

    /**
     * Pega a string e enquadra a mesma no formato necessario para tender o modelo CNAB 240
     * Caso a string nao atenda o numero de caracteres desejado, ela completa com zero ou espaço em banco
     */
    private function createString($value)
    {

        $data = "";
        $qtd = strlen($value['default']);
        $length = $value['length'];
        $type = $value['type'];
        $diff = 0;
        $complet = "";

        if ($qtd < $length) {
            $diff = $length - $qtd;
        }

        $value['default'] = Normalizer::normalize($value['default'], Normalizer::FORM_D);
        $value['default'] = preg_replace('/[^a-z0-9 ]/i', '', $value['default']);

        if ($type === 'int') {
            $data .= str_pad($value['default'], $length, '0', STR_PAD_LEFT);
        } else {
            $data .= str_pad($value['default'], $length, " ");
        }

        return substr($data, 0, $length);
    }

    /*
     * Função para retornar o número do banco, levando como base de pesquisa o nome do banco
     * Todos os textos que entram pelo parâmetro $bankName, são primeiro colocados em lowercase em seguida a primeira letra
     * de cada palavra e passado para upercase
     *
     */
    private function numberBank($bankName)
    {

        $bankList = [
            'Bco Do Brasil S.A' => '001',
            'Bco Da Amazonia S.A' => '003',
            'Bco Do Nordeste Do Brasil S.A' => '004',
            'Bco Banestes S.A' => '021',
            'Bco Santander (Brasil) S.A' => '033',
            'Bco Do Est. Do Pa S.A' => '037',
            'Bco Do Estado Do Rs S.A' => '041',
            'Bco Do Est. De Se S.A' => '047',
            'Brb - Bco De Brasilia S.A' => '070',
            'Banco Inter' => '077',
            'Bco Da China Brasil S.A' => '083',
            'Caixa Economica Federal' => '104',
            'Banco Btg Pactual S.A' => '208',
            'Banco Original' => '212',
            'Bco Bradesco S.A ' => '237',
            'Bco Bmg S.A' => '318',
            'Itaú Unibanco S.A' => '341',
            'Bco Safra S.A' => '422',
            'Banco Pan' => '623',
            'BR Partners BI' => '666',
        ];

        $bankName = Normalizer::normalize($bankName, Normalizer::FORM_D);
        $bankName = preg_replace('/[^a-z0-9 ]/i', '', $bankName);

        $return = "";
        foreach ($bankList as $key => $value) {

            $temp = Normalizer::normalize($key, Normalizer::FORM_D);
            $temp = preg_replace('/[^a-z0-9 ]/i', '', $temp);

            if (strtolower($bankName) == strtolower($temp)) {
                $return = $value;
            }
        }

        return $return;

    }

    /**
     * Pega o valor da config e do mapeamento e monta a string.
     * Sempre será respeitado os valores de tamanho de string e tipo que estão no arquivo de config
     *
     */
    private function mountTxt($array, $mapped, $txt_data, $register, $complement)
    {

        if ($complement) {
            foreach ($complement as $key => $value) {
                $array[$key]['default'] = $value;
            }
        }
        //$_SESSION['valor'] = 0;
        foreach ($array as $key => $value) {
            if ($value['field_id']) {
                if (is_callable($mapped[$key])) {
                    $data = $mapped[$key];
                    $value['default'] = $data($register);
                    $value['field_id'] = null;
                    $txt_data .= $this->createString($value);
                    $value['default'] = null;
                    $value['field_id'] = $value['field_id'];

                    if ($key == "VALOR_INTEIRO") {
                        $inteiro = 0;

                        if ($key == "VALOR_INTEIRO") {
                            $inteiro = $data($register);
                        }

                        $valor = $inteiro;

                        $_SESSION['valor'] = $_SESSION['valor'] + $valor;
                    }

                }
            } else {
                $txt_data .= $this->createString($value);
            }
        }
        return $txt_data;
    }

}
