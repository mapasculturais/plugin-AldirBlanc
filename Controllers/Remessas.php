<?php

namespace AldirBlanc\Controllers;

use DateTime;
use Exception;
use Normalizer;
use DateInterval;
use MapasCulturais\i;
use League\Csv\Reader;
use League\Csv\Writer;
use MapasCulturais\App;
use League\Csv\Statement;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\Opportunity;

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
     * Retorna a oportunidade
     * 
     * @return \MapasCulturais\Entities\Opportunity 
     * @throws Exception 
     */
    function getOpportunity() {
        $app = App::i();
        
        $opportunity_id = $this->data['opportunity'];

        /**
         * Pega informações da oportunidade
         */
        $opportunity = $app->repo('Opportunity')->find($opportunity_id);
        $this->registerRegistrationMetadata($opportunity);

        if (!$opportunity->canUser('@control')) {
            echo "Não autorizado";
            die();
        }

        return $opportunity;
    }

    /**
     * Retorna as inscrições
     * 
     * @param mixed $opportunity 
     * @return \MapasCulturais\Entities\Registration[]
     * @throws Exception 
     */
    function getRegistrations(Opportunity $opportunity) {
        $app = App::i();
        /**
         * Pega os parâmetros do endpoint
         */
        $finishDate = null;
        $startDate = null;

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
            }
        }
        /**
         * Busca as inscrições com status 10 (Selecionada)
         * lembrando que o botão para exportar esses dados, so estrá disponível se existir inscrições nesse status
         */
        if ($startDate && $finishDate) {
            $dql = "SELECT r FROM MapasCulturais\\Entities\\Registration r 
                    JOIN RegistrationPayments\\Payment p WITH r.id = p.registration WHERE 
                    r.status > 0 AND
                    r.opportunity = :opportunity AND
                    p.status = 0 AND
                    r.sentTimestamp >=:startDate AND
                    r.sentTimestamp <= :finishDate";

            $query = $app->em->createQuery($dql);
            $query->setParameters([
                'opportunity' => $opportunity,
                'startDate' => $startDate,
                'finishDate' => $finishDate,
            ]);

            $registrations = $query->getResult();
        } else {
            $dql = "SELECT r FROM MapasCulturais\\Entities\\Registration r 
            JOIN RegistrationPayments\\Payment p WITH r.id = p.registration WHERE 
            r.status > 0 AND 
            p.status = 0 AND
            r.opportunity = :opportunity";

            $query = $app->em->createQuery($dql);
            $query->setParameters([
                'opportunity' => $opportunity,
            ]);

            $registrations = $query->getResult();
        }

        if (empty($registrations)) {
            echo "Não foram encontrados registros.";
            die();
        }

        return $registrations;
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

        $opportunity = $this->getOpportunity();
        $opportunity_id = $opportunity->id;
        $registrations = $this->getRegistrations($opportunity);

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
                $result = " ";
                if (in_array($registrations->category, $categories['CPF'])) {
                    $field_id = $fieldsID['CPF'];
                    $result = $this->normalizeString($registrations->$field_id);

                    if (strlen($result) != 11) {
                        $app->log->info($registrations->number . " CPF inválido");
                    }
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
                $result = " ";
                if (in_array($registrations->category, $categories['CNPJ'])) {
                    $field_id = $fieldsID['CNPJ'];                    
                    if (is_array($field_id)) {                        
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
                } 

                return $result;

            },
            'RAZAO_SOCIAL' => function ($registrations) use ($fieldsID, $categories) {
                $result = " ";
                if (in_array($registrations->category, $categories['CNPJ'])) {
                    $field_id = $fieldsID['RAZAO_SOCIAL'];                    
                    if (is_array($field_id)) {                        
                        foreach ($field_id as $key => $value) {
                            if ($registrations->$value) {
                                $result = $this->normalizeString($registrations->$value);
                            }
                        }                       
                    } else {
                        $result = $this->normalizeString($registrations->$field_id);
                    }
                }

                return $result;
            },
            'LOGRADOURO' => function ($registrations) use ($fieldsID, $app) {
                $result = $this->getAddress('LOGRADOURO', 'En_Nome_Logradouro', $fieldsID, $registrations, $app, null);                
                return $result;
                
            },
            'NUMERO' => function ($registrations) use ($fieldsID, $app) {
                $result = $this->getAddress('NUMERO', 'En_Num', $fieldsID, $registrations, $app, 5);
                return substr($result, 0, 5);
            },
            'COMPLEMENTO' => function ($registrations) use ($fieldsID, $app) {
                $result = $this->getAddress('COMPLEMENTO', 'En_Complemento', $fieldsID, $registrations, $app, 20);
                return substr($result, 0, 20);
            
            },
            'BAIRRO' => function ($registrations) use ($fieldsID, $app) {
                $result = $this->getAddress('BAIRRO', 'En_Bairro', $fieldsID, $registrations, $app, null);
                return $result;
                
            },
            'MUNICIPIO' => function ($registrations) use ($fieldsID, $app) {
                $result = $this->getAddress('MUNICIPIO', 'En_Municipio', $fieldsID, $registrations, $app, null);
                return $result;
                
            },
            'CEP' => function ($registrations) use ($fieldsID, $app) {
                $result = $this->getAddress('CEP', 'En_CEP', $fieldsID, $registrations, $app, null);
                return $result;
                
            },
            'ESTADO' => function ($registrations) use ($fieldsID, $app) {
                $result = $this->getAddress('ESTADO', 'En_Estado', $fieldsID, $registrations, $app, null);
                return $result;
               
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

        
        $opportunity = $this->getOpportunity();
        $opportunity_id = $opportunity->id;
        $registrations = $this->getRegistrations($opportunity);
        
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
                $result = " ";
                if ($temp) {
                    $propType = trim($registrations->$temp);
                    if ($propType === $proponentTypes['fisica'] || empty($propType) || $propType === $proponentTypes['coletivo']) {
                        $result = $this->normalizeString($registrations->$field_id);
                        
                        if (strlen($result) != 11) {
                            $app->log->info("\n".$registrations->number . " CPF inválido");
                        }
                    } 
                } else {
                    $result = $this->normalizeString($registrations->$field_id);
                }

                return $result;
            },
            'NOME_SOCIAL' => function ($registrations) use ($fieldsID, $proponentTypes) {
                $temp = $fieldsID['TIPO_PROPONENTE'];
                $field_id = $fieldsID['NOME_SOCIAL'];                
                $propType = $temp ? trim($registrations->$temp) : null;
                
                $result = " ";
                if ($propType == trim($proponentTypes['fisica']) || empty($propType) || $propType == trim($proponentTypes['coletivo'])) {
                    if(is_array($field_id)){
                        foreach($field_id as $value){
                            if($registrations->$value){
                                $result = $registrations->$value; 
                            }
                        }
                    }else{
                        $result = $registrations->$field_id;  

                    }
                }
                
                return $this->normalizeString($result);
            },
            'CNPJ' => function ($registrations) use ($fieldsID, $app, $proponentTypes) {
                $temp = $fieldsID['TIPO_PROPONENTE'];
                $field_id = $fieldsID['CNPJ'];
                $result = " ";

                $propType = $temp ? trim($registrations->$temp) : null; 
                
                if ($propType === trim($proponentTypes['juridica']) || $propType === trim($proponentTypes['juridica-mei'])) {
                    if (is_array($field_id)) {                            
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

                    if (strlen($result) != 14) {
                        $app->log->info("\n".$registrations->number . " CNPJ inválido");
                    }
                }
                
                return $result;

            },
            'RAZAO_SOCIAL' => function ($registrations) use ($fieldsID, $proponentTypes) {
                $temp = $fieldsID['TIPO_PROPONENTE'];
                $field_id = $fieldsID['RAZAO_SOCIAL'];

                $result = " ";
                $propType = $temp ? trim($registrations->$temp) : null;               

                if ($propType === trim($proponentTypes['juridica']) || $propType === trim($proponentTypes['juridica-mei'])) {
                    if (is_array($field_id)) {                           
                        foreach ($field_id as $key => $value) {
                            if ($registrations->$value) {
                                $result = $registrations->$value;
                            }

                        }                           
                    } else {
                        $result = $registrations->$field_id;

                    }

                } 
                
                return $this->normalizeString($result);

            },
            'LOGRADOURO' => function ($registrations) use ($fieldsID, $app) {
                $result = $this->getAddress('LOGRADOURO', 'En_Nome_Logradouro', $fieldsID, $registrations, $app, null);                
                return $result;

            },
            'NUMERO' => function ($registrations) use ($fieldsID, $app) {
                $result = $this->getAddress('NUMERO', 'En_Num', $fieldsID, $registrations, $app, 5);
                return $result ? substr($result, 0, 5) : " ";

            },
            'COMPLEMENTO' => function ($registrations) use ($fieldsID, $app) {
                $result = $this->getAddress('COMPLEMENTO', 'En_Complemento', $fieldsID, $registrations, $app, 20);
                return $result ? substr($result, 0, 20) : " ";

            },
            'BAIRRO' => function ($registrations) use ($fieldsID, $app) {
                $result = $this->getAddress('BAIRRO', 'En_Bairro', $fieldsID, $registrations, $app, null);
                return $result;
            },
            'MUNICIPIO' => function ($registrations) use ($fieldsID, $app) {
                $result = $this->getAddress('MUNICIPIO', 'En_Municipio', $fieldsID, $registrations, $app, null);
                return $result;               

            },
            'CEP' => function ($registrations) use ($fieldsID, $app) {
                $result = $this->getAddress('CEP', 'En_CEP', $fieldsID, $registrations, $app, null);
                return $result;
            
            },
            'ESTADO' => function ($registrations) use ($fieldsID, $app) {
                $result = $this->getAddress('ESTADO', 'En_Estado', $fieldsID, $registrations, $app, null);
                return $result;
            
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
                    $app->log->info("\n".$registrations->number . " Número do banco não encontrado");
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
     * Implementa o exportador TXT no modelo CNAB 240, para envio de remessas ao banco do Brasil inciso1
     *
     *
     */
    public function ALL_exportCnab240Inciso1()
    {
        //Seta o timeout
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '768M');

        /**
         * Verifica se o usuário está autenticado
         */
        $this->requireAuthentication();
        $app = App::i();

        $opportunity = $this->getOpportunity();
        $opportunity_id = $opportunity->id;
        $registrations = $this->getRegistrations($opportunity);

        /**
         * Pega os dados das configurações
         */
        $txt_config = $this->config['config-cnab240-inciso1'];
        $default = $txt_config['parameters_default'];       
        $header1 = $txt_config['HEADER1'];
        $header2 = $txt_config['HEADER2'];
        $detahe1 = $txt_config['DETALHE1'];
        $detahe2 = $txt_config['DETALHE2'];
        $trailer1 = $txt_config['TRAILER1'];
        $trailer2 = $txt_config['TRAILER2'];
        $deParaContasbb = $default['deParaContasbb'];

        $dePara = $this->readingCsvAccounts($deParaContasbb);
        $cpfCsv = $this->cpfCsv($deParaContasbb);       
       
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
            'AGENCIA' => function ($registrations) use ($header1) {
                $result = "";
                $field_id = $header1['AGENCIA'];
                $value = $this->normalizeString($field_id['default']);
                return substr($value, 0, 4);

            },
            'AGENCIA_DIGITO' => function ($registrations) use ($header1) {
                $result = "";
                $field_id = $header1['AGENCIA_DIGITO'];
                $value = $this->normalizeString($field_id['default']);
                $result = is_string($value) ? strtoupper($value) : $value;
                return $result;

            },
            'CONTA' => function ($registrations) use ($header1) {
                $result = "";
                $field_id = $header1['CONTA'];
                $value = $this->normalizeString($field_id['default']);
                return substr($value, 0, 12);
                

            },
            'CONTA_DIGITO' => function ($registrations) use ($header1) {
                $result = "";
                $field_id = $header1['CONTA_DIGITO'];
                $value = $this->normalizeString($field_id['default']);
                $result = is_string($value) ? strtoupper($value) : $value;
                return $result;

            },
            'USO_BANCO_20' => '',
            'NOME_EMPRESA' => function ($registrations) use ($header1, $app) {
                $result =  $header1['NOME_EMPRESA']['default'];
                return substr($result, 0, 30);
            },
            'NOME_BANCO' => '',
            'USO_BANCO_23' => '',
            'CODIGO_REMESSA' => '',
            'DATA_GER_ARQUIVO' => function ($registrations) use ($detahe1) {
                $date = new DateTime();
                return $date->format('dmY');
            },
            'HORA_GER_ARQUIVO' => function ($registrations) use ($detahe1) {
                $date = new DateTime();
                return $date->format('His');
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
            'AGENCIA' => function ($registrations) use ($header2) {
                $result = "";
                $field_id = $header2['AGENCIA'];
                $value = $this->normalizeString($field_id['default']);
                return substr($value, 0, 4);

            },
            'AGENCIA_DIGITO' => function ($registrations) use ($header2) {
                $result = "";
                $field_id = $header2['AGENCIA_DIGITO'];
                $value = $this->normalizeString($field_id['default']);
                $result = is_string($value) ? strtoupper($value) : $value;
                return $result;

            },
            'CONTA' => function ($registrations) use ($header2) {
                $result = "";
                $field_id = $header2['CONTA'];
                $value = $this->normalizeString($field_id['default']);
                return substr($value, 0, 12);
              

            },
            'CONTA_DIGITO' => function ($registrations) use ($header2) {
                $result = "";
                $field_id = $header2['CONTA_DIGITO'];
                $value = $this->normalizeString($field_id['default']);
                $result = is_string($value) ? strtoupper($value) : $value;
                return $result;

            },
            'USO_BANCO_51' => '',
            'NOME_EMPRESA' => function ($registrations) use ($header2, $app) {
                $result =  $header2['NOME_EMPRESA']['default'];
                return substr($result, 0, 30);
            },
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
            'BEN_AGENCIA' => function ($registrations) use ($detahe2, $detahe1, $default, $app, $dePara, $cpfCsv) {
                $result = "";
                $field_cpf = $detahe2['BEN_CPF']['field_id'];
                $cpfBase = preg_replace('/[^0-9]/i', '',$registrations->$field_cpf);
                
                $pos = array_search($cpfBase,$cpfCsv);
               
                if($pos){                    
                    $agencia = $dePara[$pos]['BEN_AGENCIA'];
                    
                }else{
                    $temp = $default['formoReceipt'];
                    $formoReceipt = $temp ? $registrations->$temp : false;
    
                    if($formoReceipt == "CARTEIRA DIGITAL BB"){
                        $field_id = $default['fieldsWalletDigital']['agency'];

                    }else{
                        $field_id = $detahe1['BEN_AGENCIA']['field_id'];

                    }

                    $agencia = $registrations->$field_id;
                }
                
                

                $age = explode("-", $agencia);
                
                if(count($age)>1){
                    $result = $age[0];

                }else{
                    if (strlen($age[0]) > 4) {
                    
                        $result = substr($age[0], 0, 4);
                    } else {
                        $result = $age[0];
                    }
                }
               
                $result = $this->normalizeString($result);
                return is_string($result) ? strtoupper($result) : $result;
            },
            'BEN_AGENCIA_DIGITO' => function ($registrations) use ($detahe2, $detahe1, $default, $app, $dePara, $cpfCsv) {
                $result = "";
                $field_cpf = $detahe2['BEN_CPF']['field_id'];
                $cpfBase = preg_replace('/[^0-9]/i', '',$registrations->$field_cpf);
                
                $pos = array_search($cpfBase,$cpfCsv);               
                if($pos){                    
                    $agencia = $dePara[$pos]['BEN_AGENCIA'];
                    
                }else{
                    $temp = $default['formoReceipt'];
                    $formoReceipt = $temp ? $registrations->$temp : false; 
    
                    if($formoReceipt == "CARTEIRA DIGITAL BB"){
                        $field_id = $default['fieldsWalletDigital']['agency'];                    
                    }else{
                        $field_id = $detahe1['BEN_AGENCIA_DIGITO']['field_id'];
                    }

                    $agencia = $registrations->$field_id;
                }
                
                
                $age = explode("-", $agencia);

                if(count($age)>1){
                    $result = $age[1];
                }else{
                    if (strlen($age[0]) > 4) {
                        $result = substr($age[0], -1);
                    } else {
                        $result = "";
                    }
                }
                
                $result = $this->normalizeString($result);
                return is_string($result) ? strtoupper($result) : $result;
            },
            'BEN_CONTA' => function ($registrations) use ($detahe2, $detahe1, $default, $app, $dePara, $cpfCsv) {    
                $result  = ""; 
                $field_cpf = $detahe2['BEN_CPF']['field_id'];
                $cpfBase = preg_replace('/[^0-9]/i', '',$registrations->$field_cpf);

                $field_conta = $detahe1['TIPO_CONTA']['field_id'];
                $typeAccount = $registrations->$field_conta;

                $dig = $detahe1['BEN_CONTA_DIGITO']['field_id']; //pega o field_id do digito da conta

                $temp = $detahe1['BEN_CODIGO_BANCO']['field_id'];
                if($temp){
                    $numberBank = $this->numberBank($registrations->$temp);
                }else{
                    $numberBank = $default['defaultBank'];
                }

                $pos = array_search($cpfBase,$cpfCsv);               
                if($pos){                    
                    $temp_account = $dePara[$pos]['BEN_CONTA'];
                    
                }else{
                    $temp = $default['formoReceipt'];
                    $formoReceipt = $temp ? $registrations->$temp : false;
                   
                    if($formoReceipt == "CARTEIRA DIGITAL BB"){
                        $field_id = $default['fieldsWalletDigital']['account'];                    
                    }else{
                        $field_id = $detahe1['BEN_CONTA']['field_id'];
                    }

                    $temp_account = $registrations->$field_id;
                }
                
                $temp_account = explode("-", $temp_account);
                if(count($temp_account)>1){
                    $account = $temp_account[0];
                }else{
                    $account = substr($temp_account[0], 0, -1);
                }
               
                if(!$account){
                    $app->log->info($registrations->number . " Conta bancária não informada");
                    return " ";
                }

                
                if($typeAccount == $default['typesAccount']['poupanca']){

                    if (($numberBank == '001') && (substr($account, 0, 3) != "510")) {

                        $result = "510" . $account;
                         
                    }else{
                        $result = $account;
                    }
                }else{
                    $result = $account;
                }
                
                $result = preg_replace('/[^0-9]/i', '',$result);

                if($dig === $field_conta && $temp_account == 1){
                    return substr($this->normalizeString($result), 0, -1); // Remove o ultimo caracter. Intende -se que o ultimo caracter é o DV da conta

                }else{
                    return $this->normalizeString($result);

                }
                
            },
            'BEN_CONTA_DIGITO' => function ($registrations) use ($detahe2, $detahe1, $default, $app, $dePara, $cpfCsv) {
                $result = "";
                $temp = $detahe1['BEN_CODIGO_BANCO']['field_id'];
                $field_id = $detahe1['BEN_CONTA']['field_id'];
                $field_cpf = $detahe2['BEN_CPF']['field_id'];
                $cpfBase = preg_replace('/[^0-9]/i', '',$registrations->$field_cpf);
                

                if($temp){
                    $numberBank = $this->numberBank($registrations->$temp);
                }else{
                    $numberBank = $default['defaultBank'];
                }


                $temp = $detahe1['TIPO_CONTA']['field_id'];
                $typeAccount = $registrations->$temp;

                $pos = array_search($cpfBase,$cpfCsv);               
                if($pos){                    
                    $temp_account = $dePara[$pos]['BEN_CONTA'];
                    
                }else{
                    $temp = $default['formoReceipt'];
                    $formoReceipt = $temp ? $registrations->$temp : false;
                    
                    if($formoReceipt == "CARTEIRA DIGITAL BB"){
                        $temp = $default['fieldsWalletDigital']['account'];                    
                    }else{
                        $temp = $detahe1['BEN_CONTA_DIGITO']['field_id'];
                    }
                    $temp_account = $registrations->$field_id;
                }
                
                $temp_account = explode("-", $temp_account);
                if(count($temp_account)>1){
                    $dig = $temp_account[1];

                }else{
                    $dig = substr($temp_account[0], -1);
                }
                
                if ($numberBank == '001' && $typeAccount == $default['typesAccount']['poupanca']) {                   
                    if (substr($temp_account[0], 0, 3) == "510") {
                        $result = $dig;
                    } else {
                        $dig = trim(strtoupper($dig));                       
                        $result = $default['savingsDigit'][$dig];
                    }
                } else {

                    $result = $dig;
                }
                
                $result = $this->normalizeString(preg_replace('/[^0-9]/i', '',$result));
                return is_string($result) ? strtoupper($result) : $result;
               
            },
            'BEN_DIGITO_CONTA_AGENCIA_80' => '',
            'BEN_NOME' => function ($registrations) use ($detahe1) {
                $field_id = $detahe1['BEN_NOME']['field_id'];
                $result = substr($this->normalizeString($registrations->$field_id), 0, $detahe1['BEN_NOME']['length']);                            
                return $result;
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
                
                return $date->format('dmY');
            },
            'TIPO_MOEDA' => '',
            'USO_BANCO_85' => '',
            'VALOR_INTEIRO' => function ($registrations) use ($detahe1, $app) {
                $payment = $app->em->getRepository('\\RegistrationPayments\\Payment')->findOneBy([
                    'registration' => $registrations->id,
                    'status' => 0,
                ]);

                if(!$payment){
                    $app->log->info("\n Pagamento nao encontrado");
                }

                $amount =  preg_replace('/[^0-9]/i', '', $payment->amount);
                return number_format($amount, 2, '.', '');
                
            },
            'USO_BANCO_88' => '',
            'USO_BANCO_89' => '',
            'USO_BANCO_90' => '',
            'CODIGO_FINALIDADE_TED' => function ($registrations) use ($detahe1, $default) {
                $temp = $detahe1['BEN_CODIGO_BANCO']['field_id'];
                if($temp){
                    $numberBank = $this->numberBank($registrations->$temp);
                }else{
                    $numberBank = $default['defaultBank'];
                }
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
            'BEN_CPF' => function ($registrations) use ($detahe2) {
                $field_id = $detahe2['BEN_CPF']['field_id'];
                $data = $registrations->$field_id;
                if (strlen($this->normalizeString($data)) != 11) {
                    $_SESSION['problems'][$registrations->number] = "CPF Inválido";
                }
                return $data;
            },
            'BEN_ENDERECO_LOGRADOURO' => function ($registrations) use ($detahe2, $app) {
                $field_id = $detahe2['BEN_ENDERECO_LOGRADOURO']['field_id'];
                $length = $detahe2['BEN_ENDERECO_LOGRADOURO']['length'];
                $data = $registrations->$field_id;
                $result = $data['En_Nome_Logradouro'];
                
                $result = substr($result, 0, $length);

                return $result;

            },
            'BEN_ENDERECO_NUMERO' => function ($registrations) use ($detahe2, $app) {
                $field_id = $detahe2['BEN_ENDERECO_NUMERO']['field_id'];
                $length = $detahe2['BEN_ENDERECO_NUMERO']['length'];
                $data = $registrations->$field_id;
                $result = $data['En_Num'];
                
                $result = substr($result, 0, $length);

                return $result;
            },
            'BEN_ENDERECO_COMPLEMENTO' => function ($registrations) use ($detahe2, $app) {
                $field_id = $detahe2['BEN_ENDERECO_COMPLEMENTO']['field_id'];
                $length = $detahe2['BEN_ENDERECO_COMPLEMENTO']['length'];
                $data = $registrations->$field_id;
                $result = $data['En_Complemento'];
                
                $result = substr($result, 0, $length);

                return $result;
            },
            'BEN_ENDERECO_BAIRRO' => function ($registrations) use ($detahe2, $app) {
                $field_id = $detahe2['BEN_ENDERECO_BAIRRO']['field_id'];
                $length = $detahe2['BEN_ENDERECO_BAIRRO']['length'];
                $data = $registrations->$field_id;
                $result = $data['En_Bairro'];
                
                $result = substr($result, 0, $length);

                return $result;
            },
            'BEN_ENDERECO_CIDADE' => function ($registrations) use ($detahe2, $app) {
                $field_id = $detahe2['BEN_ENDERECO_CIDADE']['field_id'];
                $length = $detahe2['BEN_ENDERECO_CIDADE']['length'];
                $data = $registrations->$field_id;
                $result = $data['En_Municipio'];
                
                $result = substr($result, 0, $length);

                return $result;
            },
            'BEN_ENDERECO_CEP' => function ($registrations) use ($detahe2, $app) {
                $field_id = $detahe2['BEN_ENDERECO_CEP']['field_id'];
                $length = $detahe2['BEN_ENDERECO_CEP']['length'];
                $data = $registrations->$field_id;
                $result = $data['En_CEP'];
                
                $result = substr($result, 0, $length);

                return $result;
            },
            'BEN_ENDERECO_ESTADO' => function ($registrations) use ($detahe2, $app) {
                $field_id = $detahe2['BEN_ENDERECO_ESTADO']['field_id'];
                $length = $detahe2['BEN_ENDERECO_CIDADE']['length'];
                $data = $registrations->$field_id;
                $result = $data['En_Estado'];
                
                $result = substr($result, 0, $length);

                return $result;
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
        $field_TipoConta = $default['field_TipoConta'];
        $field_banco = $default['field_banco'];
        $field_agency = $default['field_agency'];
        $defaultBank = $default['defaultBank'];
        $informDefaultBank = $default['informDefaultBank'];       
        $selfDeclaredBB = $default['selfDeclaredBB'];
        $typesReceipt = $default['typesReceipt'];
        $formoReceipt = $default['formoReceipt'];
        $countBanked = 0;
        $countUnbanked = 0;
        $countUnbanked = 0;
        $noFormoReceipt = 0;
        if($default['ducumentsType']['unbanked']){ // Caso exista separação entre bancarizados e desbancarizados
            foreach($registrations as $value){
                
                //Caso nao exista informações bancárias
                if(!$value->$formoReceipt){                   
                    $app->log->info("\n".$value->number . " - Forma de recebimento não encontrada.");
                    $noFormoReceipt ++;                   
                    continue;
                }
                
                //Verifica se a inscrição é bancarizada ou desbancarizada               
                if(in_array(trim($value->$formoReceipt), $typesReceipt['banked'])){
                    $Banked = true;     
                    $countBanked ++;

                }else if(in_array(trim($value->$formoReceipt) , $typesReceipt['unbanked'])){
                    $Banked = false;
                    $countUnbanked ++; 
                               
                }

                
                // Veirifica se existe a pergunta se o requerente é correntista BB ou não no formulário. Se sim, pega a resposta  
                $accountHolderBB = "NÃO";              
                if($selfDeclaredBB){
                    $accountHolderBB = $value->$selfDeclaredBB;
                }

                if($Banked){
                    if($defaultBank){                        
                        if(($defaultBank == $informDefaultBank) || $accountHolderBB == "SIM"){
                            if (trim($value->$field_TipoConta) == "Conta corrente") { 
                                $recordsBBCorrente[] = $value;
        
                            }  else if (trim($value->$field_TipoConta) == "Conta poupança"){
                                $recordsBBPoupanca[] = $value;
        
                            }
                        }else{
                            $recordsOthers[] = $value;

                        }
                    }else{                        
                        if(($this->numberBank($value->$field_banco) == "001") || $accountHolderBB == "SIM"){
                            if (trim($value->$field_TipoConta) == "Conta corrente") { 
                                $recordsBBCorrente[] = $value;
        
                            } else if (trim($value->$field_TipoConta) == "Conta poupança"){
                                $recordsBBPoupanca[] = $value;
        
                            }
                        }else{                            
                            $recordsOthers[] = $value;
                        }
                    }
                }else{
                    
                    continue;
                }
            }
        }else{
            foreach ($registrations as $value) {
                if ($this->numberBank($value->$field_banco) == "001") {               
                    if ($value->$field_TipoConta == "Conta corrente") {
                        $recordsBBCorrente[] = $value;
                    } else {
                        $recordsBBPoupanca[] = $value;
                    }
    
                } else {
                    $recordsOthers[] = $value;
                }
            }
        }
       
        //Caso exista separação de bancarizados ou desbancarizados, mostra no terminal o resumo
        if($default['ducumentsType']['unbanked']){           
            $app->log->info("\nResumo da separação entre bancarizados e desbancarizados.");
            $app->log->info($countBanked . " BANCARIZADOS");
            $app->log->info($countUnbanked . " DESBANCARIZADOS");
        }

        //Mostra no terminal resumo da separação entre CORRENTE BB, POUPANÇA BB OUTROS BANCOS e SEM INFORMAÇÃO BANCÁRIA
        $app->log->info("\nResumo da separação entre CORRENTE BB, POUPANÇA BB, OUTROS BANCOS e SEM INFORMAÇÃO BANCÁRIA");
        $app->log->info(count($recordsBBCorrente) . " CORRENTE BB");
        $app->log->info(count($recordsBBPoupanca) . " POUPANÇA BB");
        $app->log->info(count($recordsOthers) . " OUTROS BANCOS");
        $app->log->info($noFormoReceipt . " SEM INFORMAÇÃO BANCÁRIA");
        sleep(3);
        
        
        //Verifica se existe registros em algum dos arrays. Caso não exista exibe a mensagem
        $validaExist = array_merge($recordsBBCorrente, $recordsOthers, $recordsBBPoupanca);
        if(empty($validaExist)){
            echo "Não foram encontrados registros analise os logs";
            exit();
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
        $txt_data = $this->mountTxt($header1, $mappedHeader1, $txt_data, null, null, $app);
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

            $txt_data = $this->mountTxt($header2, $mappedHeader2, $txt_data, null, $complement, $app);
            $txt_data .= "\r\n";

            $lotBBCorrente += 1;

            $_SESSION['valor'] = 0;

            $totaLotes++;
            $numSeqRegistro = 0;

            //Detalhes 1 e 2

            foreach ($recordsBBCorrente as $key_records => $records) {
                $numSeqRegistro++;
                $complement = [
                    'LOTE' => $numLote,
                    'NUMERO_REGISTRO' => $numSeqRegistro,
                ];
                $txt_data = $this->mountTxt($detahe1, $mappedDeletalhe1, $txt_data, $records, $complement, $app);
                $txt_data .= "\r\n";

                $txt_data = $this->mountTxt($detahe2, $mappedDeletalhe2, $txt_data, $records, $complement, $app);
                $txt_data .= "\r\n";

                $lotBBCorrente += 2;

            }

            //treiller 1
            $lotBBCorrente += 1; // Adiciona 1 para obedecer a regra de somar o treiller 1
            $valor = explode(".", $_SESSION['valor']);
            $valor = preg_replace('/[^0-9]/i', '', $valor[0]);
            $complement = [
                'QUANTIDADE_REGISTROS_127' => $lotBBCorrente,
                'VALOR_TOTAL_DOC_INTEIRO' => $valor,

            ];

            $txt_data = $this->mountTxt($trailer1, $mappedTrailer1, $txt_data, null, $complement, $app);
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
                'FORMA_LANCAMENTO' => 05,
                'LOTE' => $numLote,
            ];
            $txt_data = $this->mountTxt($header2, $mappedHeader2, $txt_data, null, $complement, $app);
            $txt_data .= "\r\n";

            $lotBBPoupanca += 1;

            $_SESSION['valor'] = 0;

            $totaLotes++;
            $numSeqRegistro = 0;

            //Detalhes 1 e 2

            foreach ($recordsBBPoupanca as $key_records => $records) {
                $numSeqRegistro++;
                $complement = [
                    'LOTE' => $numLote,
                    'NUMERO_REGISTRO' => $numSeqRegistro,
                ];

                $txt_data = $this->mountTxt($detahe1, $mappedDeletalhe1, $txt_data, $records, $complement, $app);
                $txt_data .= "\r\n";

                $txt_data = $this->mountTxt($detahe2, $mappedDeletalhe2, $txt_data, $records, $complement, $app);
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

            $txt_data = $this->mountTxt($trailer1, $mappedTrailer1, $txt_data, null, $complement, $app);
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

            $txt_data = $this->mountTxt($header2, $mappedHeader2, $txt_data, null, $complement, $app);

            $txt_data .= "\r\n";

            $lotOthers += 1;

            $_SESSION['valor'] = 0;

            $totaLotes++;
            $numSeqRegistro = 0;

            //Detalhes 1 e 2

            foreach ($recordsOthers as $key_records => $records) {
                $numSeqRegistro++;
                $complement = [
                    'LOTE' => $numLote,
                    'NUMERO_REGISTRO' => $numSeqRegistro,
                ];
                $txt_data = $this->mountTxt($detahe1, $mappedDeletalhe1, $txt_data, $records, $complement, $app);

                $txt_data .= "\r\n";

                $txt_data = $this->mountTxt($detahe2, $mappedDeletalhe2, $txt_data, $records, $complement, $app);
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
            $txt_data = $this->mountTxt($trailer1, $mappedTrailer1, $txt_data, null, $complement, $app);
            $txt_data .= "\r\n";
            $totalRegistros += $lotOthers;
        }

        //treiller do arquivo
        $totalRegistros += 1; // Adiciona 1 para obedecer a regra de somar o treiller
        $complement = [
            'QUANTIDADE_LOTES-ARQUIVO' => $totaLotes,
            'QUANTIDADE_REGISTROS_ARQUIVOS' => $totalRegistros,
        ];

        $txt_data = $this->mountTxt($trailer2, $mappedTrailer2, $txt_data, null, $complement, $app);

        if (isset($_SESSION['problems'])) {
            foreach ($_SESSION['problems'] as $key => $value) {
                $app->log->info("Problemas na inscrição " . $key . " => " . $value);
            }
            unset($_SESSION['problems']);
        }
        
        /**
         * cria o arquivo no servidor e insere o conteuto da váriavel $txt_data
         */
        $file_name = 'inciso1-cnab240- '.$opportunity_id.'-' . md5(json_encode($txt_data)) . '.txt';

        $dir = PRIVATE_FILES_PATH . 'aldirblanc/inciso1/remessas/cnab240/';

        $patch = $dir . $file_name;

        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }

        $stream = fopen($patch, 'w');

        fwrite($stream, $txt_data);

        fclose($stream);

        header('Content-Type: application/csv');
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
        //Seta o timeout
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '768M');

        /**
         * Verifica se o usuário está autenticado
         */
        $this->requireAuthentication();
        $app = App::i();

        $opportunity = $this->getOpportunity();
        $opportunity_id = $opportunity->id;
        $registrations = $this->getRegistrations($opportunity);

        /**
         * Mapeamento de fielsds_id pelo label do campo
         */
        foreach ($opportunity->registrationFieldConfigurations as $field) {
            $field_labelMap["field_" . $field->id] = trim($field->title);

        }

        /**
         * Pega os dados das configurações
         */
        $txt_config = $this->config['config-cnab240-inciso2'];
        $default = $txt_config['parameters_default'];        
        $header1 = $txt_config['HEADER1'];
        $header2 = $txt_config['HEADER2'];
        $detahe1 = $txt_config['DETALHE1'];
        $detahe2 = $txt_config['DETALHE2'];
        $trailer1 = $txt_config['TRAILER1'];
        $trailer2 = $txt_config['TRAILER2'];
        $deParaContasbb = $default['deParaContasbb'];
        
        $dePara = $this->readingCsvAccounts($deParaContasbb);
        $cpfCsv = $this->cpfCsv($deParaContasbb);  

        foreach ($header1 as $key_config => $value) {
            if (is_string($value['field_id']) && strlen($value['field_id']) > 0 && $value['field_id'] != 'mapped') {
                $field_id = array_search(trim($value['field_id']), $field_labelMap);
                $header1[$key_config]['field_id'] = $field_id;
            }
        }

        foreach ($header2 as $key_config => $value) {
            if (is_string($value['field_id']) && strlen($value['field_id']) > 0 && $value['field_id'] != 'mapped') {
                $field_id = array_search(trim($value['field_id']), $field_labelMap);
                $header2[$key_config]['field_id'] = $field_id;
            }
        }

        foreach ($detahe1 as $key_config => $value) {
            if (is_string($value['field_id']) && strlen($value['field_id']) > 0 && $value['field_id'] != 'mapped') {
                $field_id = array_search(trim($value['field_id']), $field_labelMap);
                $detahe1[$key_config]['field_id'] = $field_id;
            }
        }

        foreach ($detahe2 as $key_config => $value) {
            if (is_string($value['field_id']) && strlen($value['field_id']) > 0 && $value['field_id'] != 'mapped') {
                $field_id = array_search(trim($value['field_id']), $field_labelMap);
                $detahe2[$key_config]['field_id'] = $field_id;
            }
        }

        foreach ($trailer1 as $key_config => $value) {
            if (is_string($value['field_id']) && strlen($value['field_id']) > 0 && $value['field_id'] != 'mapped') {
                $field_id = array_search(trim($value['field_id']), $field_labelMap);
                $trailer1[$key_config]['field_id'] = $field_id;
            }
        }

        foreach ($trailer2 as $key_config => $value) {
            if (is_string($value['field_id']) && strlen($value['field_id']) > 0 && $value['field_id'] != 'mapped') {
                $field_id = array_search(trim($value['field_id']), $field_labelMap);
                $trailer2[$key_config]['field_id'] = $field_id;
            }
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
            'AGENCIA' => function ($registrations) use ($header1) {
                $result = "";
                $field_id = $header1['AGENCIA'];
                $value = $this->normalizeString($field_id['default']);
                return substr($value, 0, 4);

            },
            'AGENCIA_DIGITO' => function ($registrations) use ($header1) {
                $result = "";
                $field_id = $header1['AGENCIA_DIGITO'];
                $value = $this->normalizeString($field_id['default']);
                $result = is_string($value) ? strtoupper($value) : $value;
                return $result;

            },
            'CONTA' => function ($registrations) use ($header1) {
                $result = "";
                $field_id = $header1['CONTA'];
                $value = $this->normalizeString($field_id['default']);
                return substr($value, 0, 12);
                

            },
            'CONTA_DIGITO' => function ($registrations) use ($header1) {
                $result = "";
                $field_id = $header1['CONTA_DIGITO'];
                $value = $this->normalizeString($field_id['default']);
                $result = is_string($value) ? strtoupper($value) : $value;
                return $result;

            },
            'USO_BANCO_20' => '',
            'NOME_EMPRESA' => function ($registrations) use ($header1, $app) {
                $result =  $header1['NOME_EMPRESA']['default'];
                return substr($result, 0, 30);
            },
            'NOME_BANCO' => '',
            'USO_BANCO_23' => '',
            'CODIGO_REMESSA' => '',
            'DATA_GER_ARQUIVO' => function ($registrations) use ($detahe1) {
                $date = new DateTime();
                return $date->format('dmY');
            },
            'HORA_GER_ARQUIVO' => function ($registrations) use ($detahe1) {
                $date = new DateTime();
                return $date->format('His');
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
            'AGENCIA' => function ($registrations) use ($header2) {
                $result = "";
                $field_id = $header2['AGENCIA'];
                $value = $this->normalizeString($field_id['default']);
                return substr($value, 0, 4);

            },
            'AGENCIA_DIGITO' => function ($registrations) use ($header2) {
                $result = "";
                $field_id = $header2['AGENCIA_DIGITO'];
                $value = $this->normalizeString($field_id['default']);
                $result = is_string($value) ? strtoupper($value) : $value;
                return $result;

            },
            'CONTA' => function ($registrations) use ($header2) {
                $result = "";
                $field_id = $header2['CONTA'];
                $value = $this->normalizeString($field_id['default']);
                return substr($value, 0, 12);
              

            },
            'CONTA_DIGITO' => function ($registrations) use ($header2) {
                $result = "";
                $field_id = $header2['CONTA_DIGITO'];
                $value = $this->normalizeString($field_id['default']);
                $result = is_string($value) ? strtoupper($value) : $value;
                return $result;

            },
            'USO_BANCO_51' => '',
            'NOME_EMPRESA' => function ($registrations) use ($header2, $app) {
                $result =  $header2['NOME_EMPRESA']['default'];
                return substr($result, 0, 30);
            },
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
            'BEN_AGENCIA' => function ($registrations) use ($detahe2, $detahe1, $default, $app, $dePara, $cpfCsv) {
                $result = "";
                $field_cpf = $detahe2['BEN_CPF']['field_id'];
                $cpfBase = preg_replace('/[^0-9]/i', '',$registrations->$field_cpf);
                
                $pos = array_search($cpfBase,$cpfCsv);
               
                if($pos){                    
                    $agencia = $dePara[$pos]['BEN_AGENCIA'];
                    
                }else{
                    $temp = $default['formoReceipt'];
                    $formoReceipt = $registrations->$temp; 
    
                    if($formoReceipt == "CARTEIRA DIGITAL BB"){
                        $field_id = $default['fieldsWalletDigital']['agency'];                    
                    }else{
                        $field_id = $detahe1['BEN_AGENCIA']['field_id'];
                    }

                    $agencia = $registrations->$field_id;
                }
                
                

                $age = explode("-", $agencia);
                
                if(count($age)>1){
                    $result = $age[0];

                }else{
                    if (strlen($age[0]) > 4) {
                    
                        $result = substr($age[0], 0, 4);
                    } else {
                        $result = $age[0];
                    }
                }
               
                $result = $this->normalizeString($result);
                return is_string($result) ? strtoupper($result) : $result;
            },
            'BEN_AGENCIA_DIGITO' => function ($registrations) use ($detahe2, $detahe1, $default, $app, $dePara, $cpfCsv) {
                $result = "";
                $field_cpf = $detahe2['BEN_CPF']['field_id'];
                $cpfBase = preg_replace('/[^0-9]/i', '',$registrations->$field_cpf);
                
                $pos = array_search($cpfBase,$cpfCsv);               
                if($pos){                    
                    $agencia = $dePara[$pos]['BEN_AGENCIA'];
                    
                }else{
                    $temp = $default['formoReceipt'];
                    $formoReceipt = $registrations->$temp; 
    
                    if($formoReceipt == "CARTEIRA DIGITAL BB"){
                        $field_id = $default['fieldsWalletDigital']['agency'];                    
                    }else{
                        $field_id = $detahe1['BEN_AGENCIA_DIGITO']['field_id'];
                    }

                    $agencia = $registrations->$field_id;
                }
                
                
                $age = explode("-", $agencia);

                if(count($age)>1){
                    $result = $age[1];
                }else{
                    if (strlen($age[0]) > 4) {
                        $result = substr($age[0], -1);
                    } else {
                        $result = "";
                    }
                }
                
                $result = $this->normalizeString($result);
                return is_string($result) ? strtoupper($result) : $result;
            },
            'BEN_CONTA' => function ($registrations) use ($detahe2, $detahe1, $default, $app, $dePara, $cpfCsv) {    
                $result  = ""; 
                $field_cpf = $detahe2['BEN_CPF']['field_id'];
                $cpfBase = preg_replace('/[^0-9]/i', '',$registrations->$field_cpf);

                $field_conta = $detahe1['TIPO_CONTA']['field_id'];
                $typeAccount = $registrations->$field_conta;

                $dig = $detahe1['BEN_CONTA_DIGITO']['field_id']; //pega o field_id do digito da conta

                $temp = $detahe1['BEN_CODIGO_BANCO']['field_id'];
                if($temp){
                    $numberBank = $this->numberBank($registrations->$temp);
                }else{
                    $numberBank = $default['defaultBank'];
                }

                $pos = array_search($cpfBase,$cpfCsv);               
                if($pos){                    
                    $temp_account = $dePara[$pos]['BEN_CONTA'];
                    
                }else{
                    $temp = $default['formoReceipt'];
                    $formoReceipt = $registrations->$temp;

                    if($formoReceipt == "CARTEIRA DIGITAL BB"){
                        $field_id = $default['fieldsWalletDigital']['account'];                    
                    }else{
                        $field_id = $detahe1['BEN_CONTA']['field_id'];
                    }

                    $temp_account = $registrations->$field_id;
                }
                
                $temp_account = explode("-", $temp_account);
                if(count($temp_account)>1){
                    $account = $temp_account[0];
                }else{
                    $account = substr($temp_account[0], 0, -1);
                }
                
                if(!$account){
                    $app->log->info($registrations->number . " Conta bancária não informada");
                    return " ";
                }

                
                if($typeAccount == $default['typesAccount']['poupanca']){

                    if (($numberBank == '001') && (substr($account, 0, 3) != "510")) {

                        $result = "510" . $account;
                         
                    }else{
                        $result = $account;
                    }
                }else{
                    $result = $account;
                }
                
                $result = preg_replace('/[^0-9]/i', '',$result);

                if($dig === $field_conta && $temp_account == 1){
                    return substr($this->normalizeString($result), 0, -1); // Remove o ultimo caracter. Intende -se que o ultimo caracter é o DV da conta

                }else{
                    return $this->normalizeString($result);

                }
                
            },
            'BEN_CONTA_DIGITO' => function ($registrations) use ($detahe2, $detahe1, $default, $app, $dePara, $cpfCsv) {
                $result = "";
                $temp = $detahe1['BEN_CODIGO_BANCO']['field_id'];
                $field_id = $detahe1['BEN_CONTA']['field_id'];
                $field_cpf = $detahe2['BEN_CPF']['field_id'];
                $cpfBase = preg_replace('/[^0-9]/i', '',$registrations->$field_cpf);
                

                if($temp){
                    $numberBank = $this->numberBank($registrations->$temp);
                }else{
                    $numberBank = $default['defaultBank'];
                }


                $temp = $detahe1['TIPO_CONTA']['field_id'];
                $typeAccount = $registrations->$temp;

                $pos = array_search($cpfBase,$cpfCsv);               
                if($pos){                    
                    $temp_account = $dePara[$pos]['BEN_CONTA'];
                    
                }else{
                    $temp = $default['formoReceipt'];
                    $formoReceipt = $registrations->$temp;
                    
                    if($formoReceipt == "CARTEIRA DIGITAL BB"){
                        $temp = $default['fieldsWalletDigital']['account'];                    
                    }else{
                        $temp = $detahe1['BEN_CONTA_DIGITO']['field_id'];
                    }
                    $temp_account = $registrations->$field_id;
                }
                
                $temp_account = explode("-", $temp_account);
                if(count($temp_account)>1){
                    $dig = $temp_account[1];

                }else{
                    $dig = substr($temp_account[0], -1);
                }
                
                if ($numberBank == '001' && $typeAccount == $default['typesAccount']['poupanca']) {                   
                    if (substr($temp_account[0], 0, 3) == "510") {
                        $result = $dig;
                    } else {
                        $dig = trim(strtoupper($dig));                       
                        $result = $default['savingsDigit'][$dig];
                    }
                } else {

                    $result = $dig;
                }
                
                $result = $this->normalizeString(preg_replace('/[^0-9]/i', '',$result));
                return is_string($result) ? strtoupper($result) : $result;
               
            },
            'BEN_DIGITO_CONTA_AGENCIA_80' => '',
            'BEN_NOME' => function ($registrations) use ($detahe1) {
                $field_id = $detahe1['BEN_NOME']['field_id'];
                $result = substr($this->normalizeString($registrations->$field_id), 0, $detahe1['BEN_NOME']['length']);                            
                return $result;
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
                
                return $date->format('dmY');
            },
            'TIPO_MOEDA' => '',
            'USO_BANCO_85' => '',
            'VALOR_INTEIRO' => function ($registrations) use ($detahe1, $app) {
                $payment = $app->em->getRepository('\\RegistrationPayments\\Payment')->findOneBy([
                    'registration' => $registrations->id,
                    'status' => 0,
                ]);

                if(!$payment){
                    $app->log->info("\n Pagamento nao encontrado");
                }

                $amount =  preg_replace('/[^0-9]/i', '', $payment->amount);
                return number_format($amount, 2, '.', '');
                
            },
            'USO_BANCO_88' => '',
            'USO_BANCO_89' => '',
            'USO_BANCO_90' => '',
            'CODIGO_FINALIDADE_TED' => function ($registrations) use ($detahe1, $default) {
                $temp = $detahe1['BEN_CODIGO_BANCO']['field_id'];
                if($temp){
                    $numberBank = $this->numberBank($registrations->$temp);
                }else{
                    $numberBank = $default['defaultBank'];
                }
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
            'BEN_CPF' => function ($registrations) use ($detahe2) {
                $field_id = $detahe2['BEN_CPF']['field_id'];
                $data = $registrations->$field_id;
                if (strlen($this->normalizeString($data)) != 11) {
                    $_SESSION['problems'][$registrations->number] = "CPF Inválido";
                }
                return $data;
            },
            'BEN_ENDERECO_LOGRADOURO' => function ($registrations) use ($detahe2, $app) {
                $field_id = $detahe2['BEN_ENDERECO_LOGRADOURO']['field_id'];
                $length = $detahe2['BEN_ENDERECO_LOGRADOURO']['length'];
                $data = $registrations->$field_id;
                $result = $data['En_Nome_Logradouro'];
                
                $result = substr($result, 0, $length);

                return $result;

            },
            'BEN_ENDERECO_NUMERO' => function ($registrations) use ($detahe2, $app) {
                $field_id = $detahe2['BEN_ENDERECO_NUMERO']['field_id'];
                $length = $detahe2['BEN_ENDERECO_NUMERO']['length'];
                $data = $registrations->$field_id;
                $result = $data['En_Num'];
                
                $result = substr($result, 0, $length);

                return $result;
            },
            'BEN_ENDERECO_COMPLEMENTO' => function ($registrations) use ($detahe2, $app) {
                $field_id = $detahe2['BEN_ENDERECO_COMPLEMENTO']['field_id'];
                $length = $detahe2['BEN_ENDERECO_COMPLEMENTO']['length'];
                $data = $registrations->$field_id;
                $result = $data['En_Complemento'];
                
                $result = substr($result, 0, $length);

                return $result;
            },
            'BEN_ENDERECO_BAIRRO' => function ($registrations) use ($detahe2, $app) {
                $field_id = $detahe2['BEN_ENDERECO_BAIRRO']['field_id'];
                $length = $detahe2['BEN_ENDERECO_BAIRRO']['length'];
                $data = $registrations->$field_id;
                $result = $data['En_Bairro'];
                
                $result = substr($result, 0, $length);

                return $result;
            },
            'BEN_ENDERECO_CIDADE' => function ($registrations) use ($detahe2, $app) {
                $field_id = $detahe2['BEN_ENDERECO_CIDADE']['field_id'];
                $length = $detahe2['BEN_ENDERECO_CIDADE']['length'];
                $data = $registrations->$field_id;
                $result = $data['En_Municipio'];
                
                $result = substr($result, 0, $length);

                return $result;
            },
            'BEN_ENDERECO_CEP' => function ($registrations) use ($detahe2, $app) {
                $field_id = $detahe2['BEN_ENDERECO_CEP']['field_id'];
                $length = $detahe2['BEN_ENDERECO_CEP']['length'];
                $data = $registrations->$field_id;
                $result = $data['En_CEP'];
                
                $result = substr($result, 0, $length);

                return $result;
            },
            'BEN_ENDERECO_ESTADO' => function ($registrations) use ($detahe2, $app) {
                $field_id = $detahe2['BEN_ENDERECO_ESTADO']['field_id'];
                $length = $detahe2['BEN_ENDERECO_CIDADE']['length'];
                $data = $registrations->$field_id;
                $result = $data['En_Estado'];
                
                $result = substr($result, 0, $length);

                return $result;
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
        $field_TipoConta = array_search(trim($default['field_TipoConta']), $field_labelMap);
        $field_banco = array_search(trim($default['field_banco']), $field_labelMap);
        $defaultBank = $default['defaultBank'];       
        $correntistabb = $default['correntistabb'];
        $countMci460 = 0;

        if($default['ducumentsType']['unbanked']){ // Caso exista separação entre bancarizados e desbancarizados
            
            if($defaultBank && $defaultBank ==  '001'){

                foreach ($registrations as $value) {   
                    
                    if ($value->$field_TipoConta == "Conta corrente" && $value->$correntistabb == "SIM") {
                        $recordsBBCorrente[] = $value;

                    } else if ($value->$field_TipoConta == "Conta poupança" && $value->$correntistabb == "SIM"){
                        $recordsBBPoupanca[] = $value;
                    
                    }else{
                        $countMci460 ++;
                        $recordsOthers = []; 
                        $app->log->info($value->number . " - Não incluída no CNAB240 pertence ao MCI460.");
                    }
                }
            
            }else{
                foreach ($registrations as $value) {          
                    if ($this->numberBank($value->$field_banco) == "001" && $value->$correntistabb == "SIM") {             
                        if ($value->$field_TipoConta == "Conta corrente") {
                            $recordsBBCorrente[] = $value;
                        } else {
                            $recordsBBPoupanca[] = $value;
                        }
        
                    } else {
                        $countMci460 ++;
                        $recordsOthers = [];
                        $app->log->info($value->number . "Não incluída no CNAB240 pertence ao MCI460.");
                    }
                } 
            }
        }else{
            foreach ($registrations as $value) {          
                if ($this->numberBank($value->$field_banco) == "001") {               
                    if ($value->$field_TipoConta == "Conta corrente") {
                        $recordsBBCorrente[] = $value;
                    } else {
                        $recordsBBPoupanca[] = $value;
                    }
    
                } else {
                    $recordsOthers[] = $value;
                }
            }
        }

        //Mostra no terminal a quantidade de docs em cada documento MCI460, CNAB240
        if($default['ducumentsType']['unbanked']){
            $app->log->info((count($recordsBBPoupanca) + count($recordsBBCorrente)) . " CNAB240");
            $app->log->info($countMci460 . " MCI460");
            sleep(5);
        }

         //Verifica se existe registros em algum dos arrays
         $validaExist = array_merge($recordsBBCorrente, $recordsOthers, $recordsBBPoupanca);
         if(empty($validaExist)){
             echo "Não foram encontrados registros analise os logs";
             exit();
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
        $txt_data = $this->mountTxt($header1, $mappedHeader1, $txt_data, null, null, $app);
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

            $txt_data = $this->mountTxt($header2, $mappedHeader2, $txt_data, null, $complement, $app);
            $txt_data .= "\r\n";

            $lotBBCorrente += 1;

            $_SESSION['valor'] = 0;

            $totaLotes++;
            $numSeqRegistro = 0;

            //Detalhes 1 e 2

            foreach ($recordsBBCorrente as $key_records => $records) {
                $numSeqRegistro++;
                $complement = [
                    'LOTE' => $numLote,
                    'NUMERO_REGISTRO' => $numSeqRegistro,
                ];
                $txt_data = $this->mountTxt($detahe1, $mappedDeletalhe1, $txt_data, $records, $complement, $app);
                $txt_data .= "\r\n";

                $txt_data = $this->mountTxt($detahe2, $mappedDeletalhe2, $txt_data, $records, $complement, $app);
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

            $txt_data = $this->mountTxt($trailer1, $mappedTrailer1, $txt_data, null, $complement, $app);
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
            $txt_data = $this->mountTxt($header2, $mappedHeader2, $txt_data, null, $complement, $app);
            $txt_data .= "\r\n";

            $lotBBPoupanca += 1;

            $_SESSION['valor'] = 0;

            $totaLotes++;
            $numSeqRegistro = 0;

            //Detalhes 1 e 2

            foreach ($recordsBBPoupanca as $key_records => $records) {
                $numSeqRegistro++;
                $complement = [
                    'LOTE' => $numLote,
                    'NUMERO_REGISTRO' => $numSeqRegistro,
                ];

                $txt_data = $this->mountTxt($detahe1, $mappedDeletalhe1, $txt_data, $records, $complement, $app);
                $txt_data .= "\r\n";

                $txt_data = $this->mountTxt($detahe2, $mappedDeletalhe2, $txt_data, $records, $complement, $app);
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

            $txt_data = $this->mountTxt($trailer1, $mappedTrailer1, $txt_data, null, $complement, $app);
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

            $txt_data = $this->mountTxt($header2, $mappedHeader2, $txt_data, null, $complement, $app);

            $txt_data .= "\r\n";

            $lotOthers += 1;

            $_SESSION['valor'] = 0;

            $totaLotes++;
            $numSeqRegistro = 0;

            //Detalhes 1 e 2

            foreach ($recordsOthers as $key_records => $records) {
                $numSeqRegistro++;
                $complement = [
                    'LOTE' => $numLote,
                    'NUMERO_REGISTRO' => $numSeqRegistro,
                ];
                $txt_data = $this->mountTxt($detahe1, $mappedDeletalhe1, $txt_data, $records, $complement, $app);

                $txt_data .= "\r\n";

                $txt_data = $this->mountTxt($detahe2, $mappedDeletalhe2, $txt_data, $records, $complement, $app);
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
            $txt_data = $this->mountTxt($trailer1, $mappedTrailer1, $txt_data, null, $complement, $app);
            $txt_data .= "\r\n";
            $totalRegistros += $lotOthers;
        }

        //treiller do arquivo
        $totalRegistros += 1; // Adiciona 1 para obedecer a regra de somar o treiller
        $complement = [
            'QUANTIDADE_LOTES-ARQUIVO' => $totaLotes,
            'QUANTIDADE_REGISTROS_ARQUIVOS' => $totalRegistros,
        ];

        $txt_data = $this->mountTxt($trailer2, $mappedTrailer2, $txt_data, null, $complement, $app);

        if (isset($_SESSION['problems'])) {
            foreach ($_SESSION['problems'] as $key => $value) {
                $app->log->info("Problemas na inscrição " . $key . " => " . $value);
            }
            unset($_SESSION['problems']);
        }
        
        /**
         * cria o arquivo no servidor e insere o conteuto da váriavel $txt_data
         */
        $file_name = 'inciso2-cnab240-'.$opportunity_id.'-' . md5(json_encode($txt_data)) . '.txt';

        $dir = PRIVATE_FILES_PATH . 'aldirblanc/inciso1/remessas/cnab240/';

        $patch = $dir . $file_name;

        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }

        $stream = fopen($patch, 'w');

        fwrite($stream, $txt_data);

        fclose($stream);

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
            'NU PAGAMENTOS S.A' => '260',
            'BANCO DO BRASIL S.A' => '001',
            'BANCO INTER' => '077',
            'CAIXA ECONOMICA FEDERAL' => '104',
            'BCO BRADESCO S.A' => '237',
            'ITAÚ UNIBANCO S.A..' => '341',
            'PAGSEGURO' => '290',
            'BCO SANTANDER (BRASIL) S.A' => '033',
            'BCO DO EST. DO PA S.A' => '037',
            'BCO C6 S.A' => '336'

        ];

        $return = 0;
        foreach ($bankList as $key => $value) {
            if ($this->normalizeString(strtolower($key)) === $this->normalizeString(strtolower($bankName))) {
                $return = $value;
            }
        }

        return $return;

    }
    /**
     * Retorna o valor do objeto endereço de uma registration
     *     
     * @return string
     */
    private function getAddress($field, $attribute, $fieldsID, $registrations, $app, $length){
        $field_id = $fieldsID[$field];

        $result = " ";
        if ($field_id) {
            if (is_string($registrations->$field_id)) {                    
                $result = $registrations->$field_id;

            } elseif (is_array($registrations->$field_id)) {
                $result = $registrations->$field_id[$attribute];

            } else {
                
                $address = $registrations->$field_id;
                if(!$address){
                    $address = json_decode($registrations->getMetadata($field_id));
                }

                if($address){
                    $result =  $address->$attribute ?? " ";   
                }else{
                    $result = " ";
                } 
            }
        }
        
        if($length){
            if (strlen($result) > $length) {
                $app->log->info("\n".$registrations->number ." ". $field . " > que ". $length . " Char. Truncado!");
            }
        }elseif(empty($result)){
            $app->log->info("\n".$registrations->id . $attribute . " Não encontrado");
        }
        
        return $this->normalizeString($result);
                  
    }

    /**
     * Normaliza uma string
     *
     * @param string $valor
     * @return string
     */
    private function normalizeString($valor): string
    {
        $valor = Normalizer::normalize($valor, Normalizer::FORM_D);
        return preg_replace('/[^A-Za-z0-9 ]/i', '', $valor);
    }

     /**
     * Pega o valor da config e do mapeamento e monta a string.
     * Sempre será respeitado os valores de tamanho de string e tipo que estão no arquivo de config
     *
     */
    private function mountTxt($array, $mapped, $txt_data, $register, $complement, $app)
    {
        if ($complement) {
            foreach ($complement as $key => $value) {
                $array[$key]['default'] = $value;
            }
        }
        
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

    private function readingCsvAccounts($filename){

        $filename = __DIR__."/../".$filename;

        //Verifica se o arquivo existe
        if(!file_exists($filename)){
            return false;
        }

        $data = [];
         //Abre o arquivo em modo de leitura
         $stream = fopen($filename, "r");

         //Faz a leitura do arquivo
         $csv = Reader::createFromStream($stream);
 
         //Define o limitador do arqivo (, ou ;)
         $csv->setDelimiter(";");
 
         //Seta em que linha deve se iniciar a leitura
         $header_temp = $csv->setHeaderOffset(0);
 
         //Faz o processamento dos dados
         $stmt = (new Statement());
         $results = $stmt->process($csv);
        foreach($results as $key => $value){
            $data[$key] = $value;
        }
         return $data;
 
    }

    private function cpfCsv($filename){

        $filename = __DIR__."/../".$filename;

        //Verifica se o arquivo existe
        if(!file_exists($filename)){
            return false;
        }

        $data = [];
         //Abre o arquivo em modo de leitura
         $stream = fopen($filename, "r");

         //Faz a leitura do arquivo
         $csv = Reader::createFromStream($stream);
 
         //Define o limitador do arqivo (, ou ;)
         $csv->setDelimiter(";");
 
         //Seta em que linha deve se iniciar a leitura
         $header_temp = $csv->setHeaderOffset(0);
 
         //Faz o processamento dos dados
         $stmt = (new Statement());
         $results = $stmt->process($csv);
        
        foreach($results as $key => $value){
            $data[$key] = $value['CPF'];
        }
         return $data;
 
    }
}
