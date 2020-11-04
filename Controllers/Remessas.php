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
                $result = " ";
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
                    if ($propType === trim($proponentTypes['juridica']) || $propType === trim($proponentTypes['juridica-mei'])) {
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

                    if ($propType === trim($proponentTypes['juridica']) || $propType === trim($proponentTypes['juridica-mei'])) {
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
            'LOGRADOURO' => function ($registrations) use ($fieldsID, $app) {
                $field_id = $fieldsID['LOGRADOURO'];                
                
                if (is_string($registrations->$field_id)) {                    
                    $result = $this->normalizeString($registrations->$field_id);
                } elseif (is_array($registrations->$field_id)) {
                    $result = $this->normalizeString($registrations->$field_id['En_Nome_Logradouro']);
                } else {
                    $endereco = $registrations->$field_id;
                    if(!$endereco){
                        $endereco = json_decode($registrations->getMetadata($field_id));
                    }

                    if($endereco){
                        $result =  $this->normalizeString($endereco->En_Nome_Logradouro);   
                    }else{
                        $result = " ";
                    } 
                }
                return $result;
            },
            'NUMERO' => function ($registrations) use ($fieldsID, $app) {
                $field_id = $fieldsID['NUMERO'];
                if ($field_id) {
                    
                    if (is_string($registrations->$field_id)) {                    
                        $result = $this->normalizeString($registrations->$field_id);
                    } elseif (is_array($registrations->$field_id)) {
                        $result = $this->normalizeString($registrations->$field_id['En_Num']);
                    } else {
                        $endereco = $registrations->$field_id;
                        if(!$endereco){
                            $endereco = json_decode($registrations->getMetadata($field_id));
                        }
    
                        if($endereco){                            
                           
                            $result =  $this->normalizeString($endereco->En_Num ?? "");   
                        }else{
                            $result = " ";
                        } 
                    }
                }

                if (strlen($result) > 5) {
                    $app->log->info($registrations->number . " campo NUMERO está maior que o permitido. Maximo deve ser 5 caracteres. O registro foi truncado.");
                }

                return $result ? substr($this->normalizeString($result), 0, 5) : " ";
            },
            'COMPLEMENTO' => function ($registrations) use ($fieldsID, $app) {
                $field_id = $fieldsID['COMPLEMENTO'];
                $result = " ";
                if ($field_id) {
                    if (is_string($registrations->$field_id)) {                    
                        $result = $this->normalizeString($registrations->$field_id);
                    } elseif (is_array($registrations->$field_id)) {
                        $result = $this->normalizeString($registrations->$field_id['En_Complemento']);
                    } else {
                        $endereco = $registrations->$field_id;
                       
                        if(!$endereco){
                            $endereco = json_decode($registrations->getMetadata($field_id));
                        }
    
                        if($endereco){
                            $result =  isset($endereco->En_Complemento) ? $this->normalizeString($endereco->En_Complemento) :  " ";   
                        }else{
                            $result = " ";
                        } 
                    }
                } 

                if (strlen($result) > 20) {
                    $app->log->info($registrations->number . " campo COMPLEMENTO está maior que o permitido. Maximo deve ser 20 caracteres. O registro foi truncado.");
                }

                return $result ? substr($this->normalizeString($result), 0, 20) : " ";
            },
            'BAIRRO' => function ($registrations) use ($fieldsID) {
                $field_id = $fieldsID['BAIRRO'];

                if ($field_id) {
                    if (is_string($registrations->$field_id)) {                    
                        $result = $this->normalizeString($registrations->$field_id);
                    } elseif (is_array($registrations->$field_id)) {
                        $result = $this->normalizeString($registrations->$field_id['En_Bairro']);
                    } else {
                        $endereco = $registrations->$field_id;
                        if(!$endereco){
                            $endereco = json_decode($registrations->getMetadata($field_id));
                        }
    
                        if($endereco){
                            $result =  $this->normalizeString($endereco->En_Bairro);   
                        }else{
                            $result = " ";
                        } 
                    }
                } else {
                    $result = " ";
                }

            },
            'MUNICIPIO' => function ($registrations) use ($fieldsID) {
                $field_id = $fieldsID['MUNICIPIO'];
                if ($field_id) {
                    if (is_string($registrations->$field_id)) {                    
                        $result = $this->normalizeString($registrations->$field_id);
                    } elseif (is_array($registrations->$field_id)) {
                        $result = $this->normalizeString($registrations->$field_id['En_Municipio']);
                    } else {
                        $endereco = $registrations->$field_id;
                        if(!$endereco){
                            $endereco = json_decode($registrations->getMetadata($field_id));
                        }
    
                        if($endereco){
                            $result =  $this->normalizeString($endereco->En_Municipio);   
                        }else{
                            $result = " ";
                        } 
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
                    $endereco = $registrations->$field_id;
                    if(!$endereco){
                        $endereco = json_decode($registrations->getMetadata($field_id));
                    }

                    if($endereco){
                        $result =  $this->normalizeString($endereco->En_CEP);   
                    }else{
                        $result = " ";
                    } 
                }

                return $result;

            },
            'ESTADO' => function ($registrations) use ($fieldsID) {
                $field_id = $fieldsID['ESTADO'];
                
                if ($field_id) {
                    if (is_string($registrations->$field_id)) {                    
                        $result = $this->normalizeString($registrations->$field_id);
                    } elseif (is_array($registrations->$field_id)) {
                        $result = $this->normalizeString($registrations->$field_id['En_Estado']);
                    } else {
                        $endereco = $registrations->$field_id;
                        if(!$endereco){
                            $endereco = json_decode($registrations->getMetadata($field_id));
                        }
    
                        if($endereco){
                            $result =  $this->normalizeString($endereco->En_Estado);   
                        }else{
                            $result = " ";
                        } 
                    }
                } else {
                    $result = " ";
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

    public function ALL_exportMCI460()
    {
        $this->requireAuthentication();
        $app = App::i();
        if (!empty($this->data)) {
            // oportunidades
            if (isset($this->data["opportunity"])) {
                $opportunityIDs = explode(",", $this->data["opportunity"]);
                foreach ($opportunityIDs as $oID) {
                    if (!is_numeric($oID)) {
                        throw new Exception("Oportunidade(s) inválida(s)");
                    }
                }
            }
        }
        // pega oportunidades via ORM
        $opportunities = [];
        if (isset($opportunityIDs)) {
            $opportunities = $app->repo("Opportunity")->findBy(["id" => $opportunityIDs]);
        } else {
            $opportunities = $app->repo("Opportunity")->findAll();
        }
        $config = $this->config["config-mci460"];
        if (!isset($config["condition"])) {
            throw new Exception("Configuração inválida: \"condition\" não configurada.");
        }
        $newline = "\r\n";
        set_time_limit(0);
        // inicializa contadores
        $nLines = 1;
        $nClients = 0;
        // gera o header
        $out = $this->mci460Header($config) . $newline;
        $opportunityIDs = [];
        // percorre as oportunidades
        foreach ($opportunities as $opportunity) {
            // pega inscrições via DQL seguindo recomendações do Doctrine para grandes volumes
            $dql = "SELECT e FROM MapasCulturais\Entities\Registration e
                             WHERE e.status IN (1, 10) AND e.opportunity=:oppId";
            $query = $app->em->createQuery($dql);
            $registrations = $query->iterate(["oppId" => $opportunity->id]);
            /**
             * Mapeamento de fielsds_id pelo label do campo
             */
            $this->registerRegistrationMetadata($opportunity);
            // processa inscrições
            $clientsBefore = $nClients;
            while ($registration = $registrations->next()[0]) {
                // testa se é desbancarizado
                if (!$this->mci460Thunk2($config["condition"], $config["fieldMap"], $registration)) {
                    continue;
                }
                ++$nClients;
                $details = $this->mci460Details($config, $registration, [
                    "sequencialCliente" => $nClients,
                    "agencia" => 6666, // placeholder
                    "dvAgencia" => "X", // placeholder
                    "grupoSetex" => 66, // placeholder
                    "dvGrupoSetex" => "X", // placeholder
                ]);
                $nLines += sizeof($details);
                $out .= implode($newline, $details) . $newline;
                $app->em->clear();
            }
            if ($nClients > $clientsBefore) {
                $opportunityIDs[] = $this->createString([
                    "default" => $opportunity->id,
                    "length" => 3,
                    "type" => "int",
                ]);
            }
        }
        ++$nLines;
        $out .= $this->mci460Trailer($config, [
            "totalClientes" => $nClients,
            "totalRegistros" => $nLines,
        ]) . $newline;
        /**
         * cria o arquivo no servidor e insere o conteuto da váriavel $out
         */
        $fileName = "mci460-" . (new DateTime())->format('Ymd') . "-op" .
                    implode("-", $opportunityIDs) . "-" .
                    md5(json_encode($out)) . '.txt';
        $dir = PRIVATE_FILES_PATH . "aldirblanc/inciso1/remessas/mci460/";
        $path = $dir . $fileName;
        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }
        $stream = fopen($path, "w");
        fwrite($stream, $out);
        fclose($stream);
        header("Content-Type: text/utf-8");
        header("Content-Disposition: attachment; filename=" . $fileName);
        header("Pragma: no-cache");
        readfile($path);
        return;
    }

public function ALL_addressReport()
{
    $this->requireAuthentication();
    $app = App::i();
    if (!empty($this->data)) {
        // oportunidades
        if (isset($this->data["opportunity"])) {
            $opportunityIDs = explode(",", $this->data["opportunity"]);
            foreach ($opportunityIDs as $oID) {
                if (!is_numeric($oID)) {
                    throw new Exception("Oportunidade(s) inválida(s)");
                }
            }
        }
    }
    // pega oportunidades via ORM
    if (isset($opportunityIDs)) {
        $opportunities = $app->repo("Opportunity")->findBy(["id" => $opportunityIDs]);
    } else {
        $opportunities = $app->repo("Opportunity")->findAll();
    }
    set_time_limit(0);
    $header = ["Inscrição", "Nome", "Logradouro", "Número", "Complemento",
               "Bairro", "Município", "Estado", "CEP"];
    $report = [];
    $opportunityIDs = [];
    $config = $this->config["config-mci460"];
    $address = $config["fieldMap"]["endereco"];
    foreach ($opportunities as $opportunity) {
        // pega inscrições via DQL seguindo recomendações do Doctrine para grandes volumes
        $dql = "SELECT e FROM MapasCulturais\Entities\Registration e
                         WHERE e.status IN (1, 10) AND e.opportunity=:oppId";
        $query = $app->em->createQuery($dql);
        $registrations = $query->iterate(["oppId" => $opportunity->id]);
        /**
         * Mapeamento de fielsds_id pelo label do campo
         */
        $this->registerRegistrationMetadata($opportunity);
        $linesBefore = sizeof($report);
        while ($registration = $registrations->next()[0]) {
            if (!$this->mci460Thunk2($config["condition"], $config["fieldMap"], $registration)) {
                continue;
            }
            $addressFields = [];
            $source = $registration->$address;
            if (is_array($source)) {
                $addressFields[] = $source["En_Nome_Logradouro"];
                $addressFields[] = $source["En_Num"];
                $addressFields[] = isset($source["En_Complemento"]) ? $source["En_Complemento"] : "";
                $addressFields[] = $source["En_Bairro"];
                $addressFields[] = $source["En_Municipio"];
                $addressFields[] = $source["En_Estado"];
                $addressFields[] = $source["En_CEP"];
            } else {
                $addressFields[] = $source->En_Nome_Logradouro;
                $addressFields[] = $source->En_Num;
                $addressFields[] = isset($source->En_Complemento) ? $source->En_Complemento : "";
                $addressFields[] = $source->En_Bairro;
                $addressFields[] = $source->En_Municipio;
                $addressFields[] = $source->En_Estado;
                $addressFields[] = $source->En_CEP;
            }
            $report[] = array_merge([$registration->number,
                                     $registration->field_22], $addressFields);
            $app->em->clear();
        }
        if (sizeof($report) > $linesBefore) {
            $opportunityIDs[] = $this->createString([
                "default" => $opportunity->id,
                "length" => 3,
                "type" => "int",
            ]);
        }
    }
    /**
     * cria o arquivo no servidor e insere o $header e as entradas do $report
     */
    $fileName = "addressReport-" . (new DateTime())->format('Ymd') . "-op" .
                implode("-", $opportunityIDs) . "-" .
                md5(json_encode(array_merge([$header], $report))) . '.csv';
    $dir = PRIVATE_FILES_PATH . "aldirblanc/inciso1/remessas/generics/";
    $path = $dir . $fileName;
    if (!is_dir($dir)) {
        mkdir($dir, 0700, true);
    }
    $stream = fopen($path, "w");
    $csv = Writer::createFromStream($stream);
    $csv->insertOne($header);
    foreach ($report as $line) {
        $csv->insertOne($line);
    }
    header("Content-Type: application/csv");
    header("Content-Disposition: attachment; filename=" . $fileName);
    header("Pragma: no-cache");
    readfile($path);
    fclose($stream);
    return;
}

    /**
     * Placeholder para o número de seqüência dos arquivos de remessa.
     */
    public function sequenceNumber($type)
    {
        $n = 0;
        switch ($type) {
            case "cnab240": break;
            case "mci460": break;
            default: break;
        }
        return $n;
    }

    //###################################################################################################################################

    /**
     * Pega a string e enquadra a mesma no formato necessario para tender o modelo CNAB 240
     * Caso a string nao atenda o numero de caracteres desejado, ela completa com zero ou espaço em banco
     */
    private function createString($value)
    {
        $data = "";
        $length = $value['length'];
        $type = $value['type'];
        $value['default'] = Normalizer::normalize($value['default'], Normalizer::FORM_D);
        $regex = isset($value['filter']) ? $value['filter'] : '/[^a-z0-9 ]/i';
        $value['default'] = preg_replace($regex, '', $value['default']);
        if ($type === 'int') {
            $data .= str_pad($value['default'], $length, '0', STR_PAD_LEFT);
        } else {
            $data .= str_pad($value['default'], $length, " ");
        }

        return substr($data, 0, $length);
    }

    /*
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

    /** #########################################################################
     * Funções para o MCI460
     */

    private function mci460Thunk2($func, $parm0, $parm1)
    {
        if (!method_exists($this, $func)) {
            throw new Exception("Configuração inválida: $func não existe.");
        }
        return $this->$func($parm0, $parm1);
    }

    private function mci460ConditionES($fieldMap, $registration)
    {
        $hasAccount = $fieldMap["hasAccount"];
        $wantsAccount = $fieldMap["wantsAccount"];
        if ($this->config["exportador_requer_homologacao"] &&
            !in_array($registration->consolidatedResult, [
                "10", "homologado, validado por Dataprev"
        ])) {
            return false;
        }
        return (($registration->$hasAccount != "SIM") &&
                ($registration->$wantsAccount != null) &&
                (str_starts_with($registration->$wantsAccount, "CONTA")));
    }

    private function mci460ConditionDetail04ES($config, $registration)
    {
        $field = $config["fieldMap"]["conjuge"];
        foreach ($registration->$field as $member) {
            if (property_exists($member, "relationship") &&
                ($member->relationship === "1")) {
                return true;
            }
        }
        return false;
    }

    private function mci460ConditionDetail09ES($config, $registration) {
        $field = $config["fieldMap"]["email"];
        return (strlen($registration->$field) > 0);
    }

    private function mci460AddressES($fieldSpec, $address)
    {
        $out = "";
        $components = [];
        if (is_array($address)) {
            $components["logradouro"] = $address["En_Nome_Logradouro"] . ", " .
                                        $address["En_Num"];
            $components["logradouro"] .= isset($address["En_Complemento"]) ?
                                         (", " . $address["En_Complemento"]) : "";
            $components["distritoBairro"] = $address["En_Bairro"];
            $components["cep"] = $address["En_CEP"];
        } else { // caminho não testado, todos os endereços no teste são dictionary
            $components["logradouro"] = $address->En_Nome_Logradouro . ", " .
                                        $address->En_Num;
            $components["logradouro"] .= isset($address->En_Complemento) ?
                                         (", " . $address->En_Complemento) : "";
            $components["distritoBairro"] = $address->En_Bairro;
            $components["cep"] = $address->En_CEP;
        }
        foreach ($fieldSpec["fields"] as $field) {
            $field["default"] = $components[$field["name"]];
            $out .= $this->createString($field);
        }
        return $out;
    }

    private function mci460DateFormatDDMMYYYY($value) {
        return implode("", array_reverse(explode("-", $value)));
    }

    private function mci460DateDDMMYYYY() {
        return (new DateTime())->format('dmY');
    }

    private function mci460NationalityES($value)
    {
        if (($value != null) && !str_starts_with($value, "Estrangeiro"))
            return 1;
        return 0;
    }

    private function mci460PhoneES($fieldSpec, $phone)
    {
        $out = "";
        $components = [];
        $phone = preg_replace("/[^0-9\(\)]/", "", $phone);
        if (strlen($phone) < 12) {
            $components["ddd"] = "";
            $components["telefone"] = "";
        } else {
            $components["ddd"] = substr($phone, 0, 4);
            $components["telefone"] = substr($phone, 4);
        }
        foreach ($fieldSpec["fields"] as $field) {
            $field["default"] = $components[$field["name"]];
            $out .= $this->createString($field);
        }
        return $out;
    }

    private function mci460SpouseES($fieldSpec, $family)
    {
        $out = "";
        foreach ($family as $member) {
            if (!property_exists($member, "relationship") ||
                ($member->relationship != "1")) {
                continue;
            }
            foreach ($fieldSpec["fields"] as $field) {
                if (!isset($field["default"])) {
                    $fieldName = $field["name"];
                    $field["default"] = $member->$fieldName;
                }
                $out .= $this->createString($field);
            }
            break;
        }
        return $out;
    }

    /**
     * Gera os detalhes do arquivo MCI460.
     */
    private function mci460Details($config, $registration, $extraData)
    {
        $out = [];
        // itera sobre definições de detalhes
        foreach ($config["details"] as $detail) {
            // pula detalhes cuja condição o registro não atende
            if (isset($detail["condition"])) {
                if (!$this->mci460Thunk2($detail["condition"], $config,
                                         $registration)) {
                    continue;
                }
            }
            $line = "";
            // itera sobre definições de campos
            foreach ($detail["fields"] as $field) {
                // processa campos variáveis
                if (!isset($field["default"])) {
                    if ($field["type"] === "meta") {
                        $line .= $this->mci460MetaField($config, $field, $registration);
                        continue;
                    }
                    $fieldName = $field["name"];
                    // campos externos (por exemplo, o contador de clientes)
                    if (!isset($config["fieldMap"][$fieldName])) {
                        $field["default"] = $extraData[$fieldName];
                    } else { // campos do banco de dados
                        $fieldName = $config["fieldMap"][$fieldName];
                        $field["default"] = isset($field["function"]) ?
                                            $this->mci460Thunk2($field["function"], $registration->$fieldName, null) :
                                            $registration->$fieldName;
                    }
                }
                $line .= $this->createString($field);
            }
            $out[] = $line;
        }
        return $out;
    }

    /**
     * Gera o cabeçalho do arquivo MCI460.
     */
    private function mci460Header($config)
    {
        $out = "";
        foreach ($config["header"] as $field) {
            if (!isset($field["default"])) {
                if (isset($field["function"])) {
                    $field["default"] = $this->mci460Thunk2($field["function"],
                                                            null, null);
                } else {
                    throw new Exception("Configuração inválida: $field");
                }
            }
            $out .= $this->createString($field);
        }
        return $out;
    }

    private function mci460MetaField($config, $metafieldConfig, $registration)
    {
        $out = "";
        $metaname = $metafieldConfig["name"];
        if (isset($metafieldConfig["function"])) {
            $field = $config["fieldMap"][$metaname];
            return $this->mci460Thunk2($metafieldConfig["function"],
                                       $metafieldConfig, $registration->$field);
        }
        // caminho não testado a seguir; todos os metacampos atualmente têm sua própria função geradora
        foreach ($metafieldConfig["fields"] as $field) {
            if (!isset($field["default"])) {
                $fieldName = $field["name"];
                if (!isset($config["fieldMap"][$metaname])) {
                    $field["default"] = $registration->$fieldName;
                } else {
                    $field["default"] = $registration->$metaname->$fieldName;
                }
            }
            $out[] .= $this->createString($field);
        }
        return $out;
    }

    /**
     * Gera o rodapé do arquivo MCI460.
     */
    private function mci460Trailer($config, $counters)
    {
        $out = "";
        foreach ($config["trailer"] as $field) {
            if (!isset($field["default"])) {
                $field["default"] = $counters[$field["name"]];
            }
            $out .= $this->createString($field);
        }
        return $out;
    }

}
