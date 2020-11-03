<?php

namespace AldirBlanc\Controllers;

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
     *
     * O Parâmetro opportunity e identificado e incluido no endpiont automáricamente
     *
     */
    public function ALL_genericExportInciso2()
    {
        //Seta o timeout
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '768M');

        /**
         * Verifica se o usuário está autenticado
         */
        $this->requireAuthentication();
        $app = App::i();

        /**
         * Pega os dados da configuração
         */

        $csv_conf = $this->config['csv_generic_inciso2'];
        $categories = $csv_conf['categories'];
        $header = $csv_conf['header'];

        /**
         * Pega os parâmetros do endpoint
         */
        $getData = false;
        if (!empty($this->data)) {

            //Pega os parâmetros de filtro por data
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
        if ($getData) {
            $dql = "SELECT e FROM MapasCulturais\Entities\Registration e
            WHERE e.status = 1 AND
            e.opportunity = :opportunity_Id AND
            e.sentTimestamp >=:startDate AND
            e.sentTimestamp <= :finishDate";

            $query = $app->em->createQuery($dql);
            $query->setParameters([
                'opportunity_Id' => $opportunity_id,
                'startDate' => $startDate,
                'finishDate' => $finishDate,
            ]);

            $registrations = $query->getResult();
        } else {
            $dql = "SELECT e FROM MapasCulturais\Entities\Registration e
            WHERE e.status = 1 AND
            e.opportunity = :opportunity_Id";

            $query = $app->em->createQuery($dql);
            $query->setParameters([
                'opportunity_Id' => $opportunity_id,
            ]);

            $registrations = $query->getResult();
        }

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
            'CPF' => function ($registrations) use ($fieldsID, $categories, $app) {
                if (in_array($registrations->category, $categories['CPF'])) {
                    $field_id = $fieldsID['CPF'];
                    $result = $this->normalizeString($registrations->$field_id);

                    if (strlen($result) != 11) {
                        $app->log->info($registrations->number . " CPF inválido");
                    }

                } else {
                    $result = " ";
                }

                return $result;

            },
            'NOME_SOCIAL' => function ($registrations) use ($fieldsID, $categories) {
                if (in_array($registrations->category, $categories['CPF'])) {
                    $field_id = $fieldsID['NOME_SOCIAL'];
                    return $this->normalizeString($registrations->$field_id);
                } else {
                    return " ";
                }
            },
            'CNPJ' => function ($registrations) use ($fieldsID, $categories, $app) {
                if (in_array($registrations->category, $categories['CNPJ'])) {
                    $field_id = $fieldsID['CNPJ'];
                    if (is_array($field_id)) {
                        $result = " ";
                        foreach ($field_id as $key => $value) {
                            if ($registrations->$value) {
                                $result = str_replace(['.', '-', '/'], '', $registrations->$value);
                                break;
                            }
                        }
                        $result = $this->normalizeString($result);
                    } else {
                        $result = $this->normalizeString($registrations->$field_id);
                    }

                    if (strlen($result) != 14 && $result != " ") {
                        $app->log->info($registrations->number . " CNPJ inválido");
                    }
                } else {
                    $result = " ";
                }

                return $result;

            },
            'RAZAO_SOCIAL' => function ($registrations) use ($fieldsID, $categories) {
                if (in_array($registrations->category, $categories['CNPJ'])) {
                    $field_id = $fieldsID['RAZAO_SOCIAL'];
                    if (is_array($field_id)) {
                        $result = " ";
                        foreach ($field_id as $key => $value) {
                            if ($registrations->$value) {
                                $result = $registrations->$value;
                            }
                        }
                        return $this->normalizeString($result);
                    } else {
                        return $this->normalizeString($registrations->$field_id);
                    }
                } else {
                    return " ";
                }
            },
            'LOGRADOURO' => function ($registrations) use ($fieldsID) {
                $field_id = $fieldsID['LOGRADOURO'];
                return $this->normalizeString($registrations->$field_id['En_Nome_Logradouro']);
            },
            'NUMERO' => function ($registrations) use ($fieldsID, $app) {
                $field_id = $fieldsID['NUMERO'];
                $result = $this->normalizeString($registrations->$field_id['En_Num']);

                if (strlen($result) > 5) {
                    $app->log->info($registrations->number . " campo NUMERO está maior que o permitido. Maximo deve ser 5 caracteres. O registro foi truncado.");
                }

                return $result ? substr($this->normalizeString($result), 0, 5) : " ";
            },
            'COMPLEMENTO' => function ($registrations) use ($fieldsID, $app) {
                $field_id = $fieldsID['COMPLEMENTO'];
                $result = $registrations->$field_id['En_Complemento'];

                if (strlen($result) > 20) {
                    $app->log->info($registrations->number . " campo COMPLEMENTO está maior que o permitido. Maximo deve ser 20 caracteres. O registro foi truncado.");
                }

                return $result ? substr($this->normalizeString($result), 0, 20) : " ";
            },
            'BAIRRO' => function ($registrations) use ($fieldsID) {
                $field_id = $fieldsID['BAIRRO'];
                return $this->normalizeString($registrations->$field_id['En_Bairro']);
            },
            'MUNICIPIO' => function ($registrations) use ($fieldsID) {
                $field_id = $fieldsID['MUNICIPIO'];
                return $this->normalizeString($registrations->$field_id['En_Municipio']);
            },
            'CEP' => function ($registrations) use ($fieldsID) {
                $field_id = $fieldsID['CEP'];
                return $this->normalizeString($registrations->$field_id['En_CEP']);
            },
            'ESTADO' => function ($registrations) use ($fieldsID) {
                $field_id = $fieldsID['ESTADO'];
                return $this->normalizeString($registrations->$field_id['En_Estado']);
            },
            'TELEFONE' => function ($registrations) use ($fieldsID) {
                $field_id = $fieldsID['TELEFONE'];
                foreach ($field_id as $valor) {
                    if ($registrations->$valor) {
                        $result = $this->normalizeString($registrations->$valor);
                        break;
                    }
                }
                return $this->normalizeString(preg_replace('/[^0-9]/i', '', $result));
            },
            'NUM_BANCO' => function ($registrations) use ($fieldsID) {
                $field_id = $fieldsID['NUM_BANCO'];
                return $this->numberBank($registrations->$field_id);
            },
            //  'TIPO_CONTA_BANCO' => function ($registrations) use ($fieldsID){
            //     $field_id = $fieldsID['TIPO_CONTA_BANCO'];
            //     if($field_id){
            //         return  $this->normalizeString($registrations->$field_id);
            //     }else{
            //         return " ";
            //     }

            //  },
            'AGENCIA_BANCO' => function ($registrations) use ($fieldsID, $app) {
                $field_id = $fieldsID['AGENCIA_BANCO'];
                return $this->normalizeString(substr($registrations->$field_id, 0, 4));
            },
            'CONTA_BANCO' => function ($registrations) use ($fieldsID, $app) {
                $field_id = $fieldsID['CONTA_BANCO'];

                $result = $registrations->$field_id;

                return $this->normalizeString($result);
            },
            //  'OPERACAO_BANCO' => function ($registrations) use ($fieldsID){
            //     $field_id = $fieldsID['OPERACAO_BANCO'];
            //     if($field_id){
            //     return $registrations->$field_id ? $this->normalizeString($registrations->$field_id) : " ";
            //     }else{
            //         return " ";
            //     }
            //  },
            'VALOR' => '',
            'INSCRICAO_ID' => function ($registrations) use ($fieldsID) {
                return $this->normalizeString($registrations->number);

            },
            'INCISO' => function ($registrations) use ($fieldsID) {
                $field_id = $fieldsID['INCISO'];
                return $this->normalizeString($field_id);
            },

        ];

        //Itera sobre os dados mapeados
        $csv_data = [];
        foreach ($registrations as $key_registration => $registration) {

            //Pega as informações de pagamento
            $payment = $app->em->getRepository('\\RegistrationPayments\\Payment')->findOneBy([
                'registration' => $registration->id,
                'status' => 0,
            ]);

            if (!$payment) {
                $app->log->debug("\nPagamento nao encontrado para " . $registration->id);
                continue;
            }

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

            //Insere o valor a ser pago no CSV
            $csv_data[$key_registration]['VALOR'] = $payment->amount;
            

        }

        /**
         * Salva o arquivo no servidor e faz o dispatch dele em um formato CSV
         * O arquivo e salvo no deretório docker-data/private-files/aldirblanc/inciso2/remessas
         */
        $file_name = 'inciso2-genCsv-' . $opportunity_id . '-' . md5(json_encode($csv_data)) . '.csv';

        $dir = PRIVATE_FILES_PATH . 'aldirblanc/inciso2/remessas/generics/';

        $patch = $dir . $file_name;

        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }

        $stream = fopen($patch, 'w');

        $csv = Writer::createFromStream($stream);

        $csv->setDelimiter(';');

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
     * Implementa um exportador genérico, que de momento tem a intenção de antender os municipios que não vão enviar o arquivo de remessa
     * diretamente ao banco do Brasil.
     *
     *
     * http://localhost:8080/remessas/genericExportInciso3/opportunity:12/
     *
     * O Parâmetro opportunity e identificado e incluido no endpiont automáricamente
     *
     */
    public function ALL_genericExportInciso3()
    {
        //Seta o timeout
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '768M');

        /**
         * Verifica se o usuário está autenticado
         */
        $this->requireAuthentication();
        $app = App::i();

        /**
         * Pega os dados da configuração
         */

        $csv_conf = $this->config['csv_generic_inciso3'];
        $searchType = $csv_conf['parameters_default']['searchType'];
        $proponentTypes = $csv_conf['parameters_default']['proponentTypes'];
        $header = $csv_conf['header'];

        /**
         * Pega os parâmetros do endpoint
         */
        $getData = false;
        if (!empty($this->data)) {

            //Pega os parâmetros de filtro por data
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

            } elseif (!is_numeric($this->data['opportunity'])) {
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
        if ($getData) { //caso existe data como parametro ele pega o range da data selecionada com satatus 1
            $dql = "SELECT
                e
            FROM
                MapasCulturais\Entities\Registration e
            WHERE
                e.sentTimestamp >=:startDate AND
                e.sentTimestamp <= :finishDate AND
                e.status = 1 AND
                e.opportunity = :opportunity_Id";

            $query = $app->em->createQuery($dql);
            $query->setParameters([
                'opportunity_Id' => $opportunity_id,
                'startDate' => $startDate,
                'finishDate' => $finishDate,
            ]);
            $registrations = $query->getResult();

        } else { //Se não exister data como parametro ele retorna todos os registros com status 1
            $dql = "SELECT
                e
            FROM
                MapasCulturais\Entities\Registration e
            WHERE
                e.status = 1 AND
                e.opportunity = :opportunity_Id";

            $query = $app->em->createQuery($dql);
            $query->setParameters([
                'opportunity_Id' => $opportunity_id,
            ]);
            $registrations = $query->getResult();
        }

        if (empty($registrations)) {
            echo "Não foram encontrados registros.";
            die();
        }
        
        /**
         * Mapeamento de fields_id
         */
        $fieldsID = [];
        if ($searchType == 'field_id') {
            $fieldsID = $csv_conf[$opportunity_id];
        } else {

            /**
             * Monta a estrutura de field_id's e as coloca dentro de um array organizado para a busca dos dados
             *
             * Será feito uma comparação de string, coloque no arquivo de configuração
             * exatamente o texto do label desejado
             */

            foreach ($opportunity->registrationFieldConfigurations as $field) {
                $field_labelMap["field_" . $field->id] = trim($field->title);
            }

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
        }

        /**
         * Busca os dados em seus respecitivos registros com os fields mapeados
         */
        $mappedRecords = [
            'CPF' => function ($registrations) use ($fieldsID, $app, $proponentTypes) {
                $temp = $fieldsID['TIPO_PROPONENTE'];
                $field_id = $fieldsID['CPF'];
                if ($temp) {
                    $propType = trim($registrations->$temp);
                    if ($propType === $proponentTypes['fisica'] || empty($propType) || $propType === $proponentTypes['coletivo']) {

                        $result = $this->normalizeString($registrations->$field_id);

                        if (strlen($result) != 11) {
                            $app->log->info($registrations->number . " CPF inválido");
                        }
                    } else {
                        $result = " ";
                    }
                } else {
                    $result = $this->normalizeString($registrations->$field_id);
                }

                return $result;
            },
            'NOME_SOCIAL' => function ($registrations) use ($fieldsID, $proponentTypes) {
                $temp = $fieldsID['TIPO_PROPONENTE'];
                $field_id = $fieldsID['NOME_SOCIAL'];
                
                $propType = trim($registrations->$temp);
                
                if ($propType == $proponentTypes['fisica'] || empty($propType) || $propType == $proponentTypes['coletivo']) {
                    
                    if(is_array($field_id)){
                        foreach($field_id as $value){
                            if($registrations->$value){
                                $result = $this->normalizeString($registrations->$value); 
                            }
                        }
                    }else{
                        $result = $this->normalizeString($registrations->$field_id);  
                    }
                    
                }else{
                    $result = " ";
                }

                return $result;
            },
            'CNPJ' => function ($registrations) use ($fieldsID, $app, $proponentTypes) {
                $temp = $fieldsID['TIPO_PROPONENTE'];
                $field_id = $fieldsID['CNPJ'];

                if ($temp) {
                    $propType = $registrations->$temp;
                    if ($propType === trim($proponentTypes['juridica'])) {
                        if (is_array($field_id)) {
                            $result = " ";
                            foreach ($field_id as $key => $value) {
                                if ($registrations->$value) {
                                    $result = str_replace(['.', '-', '/'], '', $registrations->$value);
                                    break;
                                }
                            }

                            $result = $this->normalizeString($result);
                        } else {

                            $result = $this->normalizeString($registrations->$field_id);
                        }

                        if (strlen($result) != 14 && $result != " ") {
                            $app->log->info($registrations->number . " CNPJ inválido");
                        }
                    } else {
                        $result = " ";

                    }

                } else {
                    $result = " ";
                }

                return $result;

            },
            'RAZAO_SOCIAL' => function ($registrations) use ($fieldsID, $proponentTypes) {
                $temp = $fieldsID['TIPO_PROPONENTE'];
                $field_id = $fieldsID['RAZAO_SOCIAL'];

                if ($temp) {
                    $propType = $registrations->$temp;

                    if ($propType === trim($proponentTypes['juridica'])) {
                        if (is_array($field_id)) {
                            $result = " ";
                            foreach ($field_id as $key => $value) {
                                if ($registrations->$value) {
                                    $result = $registrations->$value;
                                }
                            }
                            return $this->normalizeString($result);
                        } else {
                            return $this->normalizeString($registrations->$field_id);
                        }
                    } else {
                        return " ";
                    }
                } else {
                    return " ";
                }

            },
            'LOGRADOURO' => function ($registrations) use ($fieldsID) {
                $field_id = $fieldsID['LOGRADOURO'];
                
                if (is_string($registrations->$field_id)) {                    
                    return $this->normalizeString($registrations->$field_id);
                } elseif (is_array($registrations->$field_id)) {
                    return $this->normalizeString($registrations->$field_id['En_Nome_Logradouro']);
                } else {
                    return $this->normalizeString($registrations->$field_id->En_Nome_Logradouro);
                }

            },
            'NUMERO' => function ($registrations) use ($fieldsID) {
                $field_id = $fieldsID['NUMERO'];
                if ($field_id) {
                    if (is_array($registrations->$field_id)) {
                        $result = $this->normalizeString($registrations->$field_id['En_Num']);
                    } else {
                        $result = $this->normalizeString($registrations->$field_id->En_Num);
                    }
                } else {
                    return " ";
                }

                if (strlen($result) > 5) {
                    $app->log->info($registrations->number . " campo NUMERO está maior que o permitido. Maximo deve ser 5 caracteres. O registro foi truncado.");
                }

                return $result ? substr($this->normalizeString($result), 0, 5) : " ";
            },
            'COMPLEMENTO' => function ($registrations) use ($fieldsID, $app) {
                $field_id = $fieldsID['COMPLEMENTO'];
                if ($field_id) {
                    if (is_array($registrations->$field_id)) {
                        $result = $this->normalizeString($registrations->$field_id['En_Complemento']);
                    } else {
                        $result = $this->normalizeString($registrations->$field_id->En_Complemento);
                    }
                } else {
                    $result = " ";
                }

                if (strlen($result) > 20) {
                    $app->log->info($registrations->number . " campo COMPLEMENTO está maior que o permitido. Maximo deve ser 20 caracteres. O registro foi truncado.");
                }

                return $result ? substr($this->normalizeString($result), 0, 20) : " ";
            },
            'BAIRRO' => function ($registrations) use ($fieldsID) {
                $field_id = $fieldsID['BAIRRO'];

                if ($field_id) {
                    if (is_array($registrations->$field_id)) {
                        return $this->normalizeString($registrations->$field_id['En_Bairro']);
                    } else {
                        return $this->normalizeString($registrations->$field_id->En_Bairro);
                    }
                } else {
                    $result = " ";
                }

            },
            'MUNICIPIO' => function ($registrations) use ($fieldsID) {
                $field_id = $fieldsID['MUNICIPIO'];
                if ($field_id) {
                    if (is_array($registrations->$field_id)) {
                        return $this->normalizeString($registrations->$field_id['En_Municipio']);
                    } else {
                        return $this->normalizeString($registrations->$field_id->En_Municipio);
                    }
                } else {
                    return " ";
                }

            },
            'CEP' => function ($registrations) use ($fieldsID) {
                $field_id = $fieldsID['CEP'];

                if (is_string($registrations->$field_id)) {
                    return $this->normalizeString($registrations->$field_id);
                } elseif (is_array($registrations->$field_id)) {
                    return $this->normalizeString($registrations->$field_id['En_CEP']);
                } else {
                    return $this->normalizeString($registrations->$field_id->En_CEP);
                }

            },
            'ESTADO' => function ($registrations) use ($fieldsID) {
                $field_id = $fieldsID['ESTADO'];
                
                if ($field_id) {
                    if (is_array($registrations->$field_id)) {
                        return $this->normalizeString($registrations->$field_id['En_Estado']);
                    } else {
                        return $this->normalizeString($registrations->$field_id->En_Estado);
                    }
                } else {
                    return " ";
                }

            },
            'TELEFONE' => function ($registrations) use ($fieldsID) {
                $field_id = $fieldsID['TELEFONE'];
                if (is_array($field_id)) {
                    foreach ($field_id as $valor) {
                        if ($registrations->$valor) {
                            $result = $this->normalizeString($registrations->$valor);
                            break;
                        }
                    }
                } else {
                    $result = $registrations->$field_id;
                }

                return $this->normalizeString(preg_replace('/[^0-9]/i', '', $result));
            },
            'NUM_BANCO' => function ($registrations) use ($fieldsID, $app) {
                $field_id = $fieldsID['NUM_BANCO'];

                $result = $this->numberBank($registrations->$field_id);

                if (empty($result)) {
                    $app->log->info($registrations->number . " Número do banco não encontrado");
                }
                return $result;
            },
            //  'TIPO_CONTA_BANCO' => function ($registrations) use ($fieldsID){
            //     $field_id = $fieldsID['TIPO_CONTA_BANCO'];
            //     if($field_id){
            //         return  $this->normalizeString($registrations->$field_id);
            //     }else{
            //         return " ";
            //     }

            //  },
            'AGENCIA_BANCO' => function ($registrations) use ($fieldsID) {
                $field_id = $fieldsID['AGENCIA_BANCO'];
                return $this->normalizeString(substr($registrations->$field_id, 0, 4));
            },
            'CONTA_BANCO' => function ($registrations) use ($fieldsID) {
                $field_id = $fieldsID['CONTA_BANCO'];

                $result = $registrations->$field_id;

                return $this->normalizeString($result);
            },
            //  'OPERACAO_BANCO' => function ($registrations) use ($fieldsID){
            //     $field_id = $fieldsID['OPERACAO_BANCO'];
            //     if($field_id){
            //     return $registrations->$field_id ? $this->normalizeString($registrations->$field_id) : " ";
            //     }else{
            //         return " ";
            //     }
            //  },
            'VALOR' => $fieldsID['VALOR'],
            'INSCRICAO_ID' => function ($registrations) use ($fieldsID) {
                return $this->normalizeString($registrations->number);

            },
            'INCISO' => function ($registrations) use ($fieldsID) {
                $field_id = $fieldsID['INCISO'];
                return $this->normalizeString($field_id);
            },

        ];

        //Itera sobre os dados mapeados
        $csv_data = [];
        foreach ($registrations as $key_registration => $registration) {

            //Busca as informaçoes de pagamento
            $payment = $app->em->getRepository('\\RegistrationPayments\\Payment')->findOneBy([
                'registration' => $registration->id,
                'status' => 0,
            ]);

            if (!$payment) {
                $app->log->debug("\nPagamento nao encontrado para " . $registration->id);
                continue;
            }

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

            //Insere o valor a ser pago no CSV
            $csv_data[$key_registration]['VALOR'] = $payment->amount;
            
        }
        
        /**
         * Salva o arquivo no servidor e faz o dispatch dele em um formato CSV
         * O arquivo e salvo no deretório docker-data/private-files/aldirblanc/inciso2/remessas
         */
        $file_name = 'inciso3-genCsv-' . $opportunity_id . '-' . md5(json_encode($csv_data)) . '.csv';

        $dir = PRIVATE_FILES_PATH . 'aldirblanc/inciso2/remessas/generics/';

        $patch = $dir . $file_name;

        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }
        $stream = fopen($patch, 'w');

        $csv = Writer::createFromStream($stream);

        $csv->setDelimiter(';');

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
     * Função para retornar o número do banco, levando como base de pesquisa o nome do banco
     * Todos os textos que entram pelo parâmetro $bankName, são primeiro colocados em lowercase e comparado com o array $bankList também em lowercase
     *
     *
     */
    private function numberBank($bankName)
    {

        $bankList = [
            'BCO ITAÚ BBA S.A.	184' => '184',
            'ITAÚ UNIBANCO S.A.	341' => '341',
            'BANCO DO BRASIL S.A. 001' => '001',
            '001 Bco Do Brasil S.A' => '001',
            '003 Bco Da Amazonia S.A' => '003',
            '004 Bco Do Nordeste Do Brasil S.A' => '004',
            '021 Bco Banestes S.A' => '021',
            '033 Bco Santander (Brasil) S.A' => '033',
            '037 Bco Do Est. Do Pa S.A' => '037',
            '041 Bco Do Estado Do Rs S.A' => '041',
            '047 Bco Do Est. De Se S.A' => '047',
            '070 Brb - Bco De Brasilia S.A' => '070',
            '077 Banco Inter' => '077',
            '083 Bco Da China Brasil S.A' => '083',
            '104 Caixa Economica Federal' => '104',
            '208 Banco Btg Pactual S.A' => '208',
            '212 Banco Original' => '212',
            '237 Bco Bradesco S.A' => '237',
            '318 Bco Bmg S.A' => '318',
            '341 Itaú Unibanco S.A' => '341',
            '422 Bco Safra S.A' => '422',
            '623 Banco Pan' => '623',
        ];

        $return = 0;
        foreach ($bankList as $key => $value) {
            if ($this->normalizeString(strtolower($key)) === $this->normalizeString(strtolower($bankName))) {
                $return = $value;
            }
        }

        return $return;

    }

    private function normalizeString($valor): string
    {
        $valor = Normalizer::normalize($valor, Normalizer::FORM_D);
        return preg_replace('/[^A-Za-z0-9 ]/i', '', $valor);
    }

}
