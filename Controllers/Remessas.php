<?php

namespace AldirBlanc\Controllers;

use DateTime;
use Exception;
use Normalizer;
use DateInterval;
use SplFileObject;
use stdClass;
use MapasCulturais\i;
use League\Csv\Reader;
use League\Csv\Writer;
use MapasCulturais\App;
use League\Csv\Statement;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\Opportunity;
use RegistrationPayments\Payment;

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
    const ACCOUNT_CREATION_PENDING = 0;
    const ACCOUNT_CREATION_PROCESSING = 1;
    const ACCOUNT_CREATION_FAILED = 2;
    const ACCOUNT_CREATION_SUCCESS = 10;

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
    function getRegistrations(Opportunity $opportunity, $asIterator=false) {
        $app = App::i();
        
        /**
         * Pega os parâmetros do endpoint
         */
        $statusPayment = [];
        $finishDate = null;
        $startDate = null;
        $paymentDate = null;
        $extra = ""; 
        $params = [];        
        
        //Pega as referências de qual form esta vindo os dados, CNAB ou GENÉRICO
        $parametersForms = $this->getParametersForms();
        $typeExport = $parametersForms['typeExport'];
        $datePayment = $parametersForms['datePayment'];
        $typeSelect = $parametersForms['typeSelect'];
        $listSelect = $parametersForms['listSelect'];

        //Pega os parâmetros de filtro por data
        if(empty($this->data[$datePayment]) && $this->data[$typeExport] === '0'){
            echo "Informe a data de pagamento que deseja exportar.";
            die();
        }

        if( $this->data[$typeExport] === '0'){
            //Verifica se a data tem um formato correto
            if (!preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/", $this->data[$datePayment])){
                throw new \Exception("O formato da data de pagamento é inválido.");
            }
        }

        if(isset($this->data[$datePayment]) && !empty($this->data[$datePayment])){
            $paymentDate = new DateTime($this->data[$datePayment]);
            $paymentDate = $paymentDate->format('Y-m-d');
            $extra .=" AND p.paymentDate = :paymentDate ";
            $params['paymentDate'] = $paymentDate;
        }

        //Pega o status solicitado no formulário
        if($this->data[$typeExport] === "all"){
            $statusPayment = ['0','1', '2', '3', '8', '10'];

        }else{
            $statusPayment = [$this->data[$typeExport]];
            
        }
        
        //Pega uma lista seleta de inscrições para exportar
        if(isset($this->data[$typeSelect]) && !empty($this->data[$listSelect])){          
            $reg = array_filter(explode(",", $this->data[$listSelect]));
            if($this->data[$typeSelect] ==="ignore"){
                $extra .= " AND r.id NOT IN (:registrations)";
            }else{
                $extra .= " AND r.id IN (:registrations)";
            }
           
            $params['registrations']  = $reg;
        }
        
        /**
         * Busca as inscrições com refêrencia ao status passado no formulário
         * 
         */ 
        
        $dql = "SELECT r FROM MapasCulturais\\Entities\\Registration r
            JOIN RegistrationPayments\\Payment p WITH r.id = p.registration WHERE
            r.status > 0 AND
            r.opportunity = :opportunity AND
            p.status IN (:statusPayment) " . $extra . " GROUP BY r";

        $query = $app->em->createQuery($dql);

        $params += [
            'opportunity' => $opportunity,                
            'statusPayment' => $statusPayment
        ];
            
        $query->setParameters($params);        
        
        $registrations = $asIterator ? $query->iterate() : $query->getResult();          

        if (!$asIterator && empty($registrations)) {
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
        $fromToAccounts = $csv_conf['fields']['fromToAccounts'];
        $dePara = $this->readingCsvFromTo($fromToAccounts);
        $cpfCsv = $this->cpfCsv($fromToAccounts);    
        
        $opportunity = $this->getOpportunity();
        $opportunity_id = $opportunity->id;
        $registrations = $this->getRegistrations($opportunity);
        $parametersForms = $this->getParametersForms();

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

                return str_replace(['.', '-', '/', ' '], '', $result);

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

                return str_replace(['.', '-', '/', ' '], '', $result);

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
            'NUM_BANCO' => function ($registrations) use ($fieldsID , $dePara, $cpfCsv, $categories) {
                if (in_array($registrations->category, $categories['CPF'])) {
                    $field_id = $fieldsID['CPF'];
                }else if (in_array($registrations->category, $categories['CNPJ'])){
                    $field_id = $fieldsID['CNPJ'];
                }
                $cpfBase = 0;
                if(is_array($field_id)){
                    foreach($field_id as $value){
                        if($registrations->$value){
                            $cpfBase = preg_replace('/[^0-9]/i', '',$registrations->$value);
                            break;
                        }
                    }
                }else{
                    $cpfBase = preg_replace('/[^0-9]/i', '',$registrations->$field_id);
                }                
                $pos = array_search($cpfBase,$cpfCsv);
                
                if($pos){                    
                    $result = $dePara[$pos]['BEN_NUM_BANCO'];
                    
                }else{
                    $field_id = $fieldsID['NUM_BANCO'];
                    $result = $this->numberBank($registrations->$field_id);
                }

                return $this->normalizeString($result);
                
            },            
            'AGENCIA_BANCO' => function ($registrations) use ($fieldsID, $app, $dePara, $cpfCsv, $categories) {                
                if (in_array($registrations->category, $categories['CPF'])) {
                    $field_id = $fieldsID['CPF'];
                }else if (in_array($registrations->category, $categories['CNPJ'])){
                    $field_id = $fieldsID['CNPJ'];
                }
                $cpfBase = 0;
                if(is_array($field_id)){
                    foreach($field_id as $value){
                        if($registrations->$value){
                            $cpfBase = preg_replace('/[^0-9]/i', '',$registrations->$value);
                            break;
                        }
                    }
                }else{
                    $cpfBase = preg_replace('/[^0-9]/i', '',$registrations->$field_id);
                }                
                $pos = array_search($cpfBase,$cpfCsv);
                
                if($pos){                    
                    $result = $dePara[$pos]['BEN_AGENCIA'];
                    
                }else{
                    $field_id = $fieldsID['AGENCIA_BANCO'];
                    $result =  $registrations->$field_id;
                }
                
                return $this->normalizeString(substr($result, 0, 4));
            },
            'CONTA_BANCO' => function ($registrations) use ($fieldsID, $app , $dePara, $cpfCsv, $categories) {
                if (in_array($registrations->category, $categories['CPF'])) {
                    $field_id = $fieldsID['CPF'];
                }else if (in_array($registrations->category, $categories['CNPJ'])){
                    $field_id = $fieldsID['CNPJ'];
                }
                $cpfBase = 0;
                if(is_array($field_id)){
                    foreach($field_id as $value){
                        if($registrations->$value){
                            $cpfBase = preg_replace('/[^0-9]/i', '',$registrations->$value);
                            break;
                        }
                    }
                }else{
                    $cpfBase = preg_replace('/[^0-9]/i', '',$registrations->$field_id);
                }                
                $pos = array_search($cpfBase,$cpfCsv);
                
                if($pos){                    
                    $result = $dePara[$pos]['BEN_CONTA'];
                    
                }else{
                    $field_id = $fieldsID['CONTA_BANCO'];

                    $result = $registrations->$field_id;
                }

                return $this->normalizeString($result);
            },            
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
            $amount = $this->processesPayment($registration, $app);
            if(!$amount){
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
            $csv_data[$key_registration]['VALOR'] = $amount;
        }

        /**
         * Salva o arquivo no servidor e faz o dispatch dele em um formato CSV
         * O arquivo e salvo no deretório docker-data/private-files/aldirblanc/inciso2/remessas
         */
        
        $file_name = 'inciso2-genCsv-' . $this->getStatus($this->data[$parametersForms['typeExport']]) . $opportunity_id . '-' . md5(json_encode($csv_data)) . '.csv';

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
        $parametersForms = $this->getParametersForms();

        $fromToAccounts = $csv_conf[$opportunity_id]['fromToAccounts'];
        $dePara = $this->readingCsvFromTo($fromToAccounts);
        $cpfCsv = $this->cpfCsv($fromToAccounts);
        
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

                return str_replace(['.', '-', '/', ' '], '', $result);
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
                                $result = str_replace(['.', '-', '/', ' '], '', $registrations->$value);
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
                
                return str_replace(['.', '-', '/', ' '], '', $result);

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
            'NUM_BANCO' => function ($registrations) use ($fieldsID, $app, $proponentTypes, $dePara, $cpfCsv) {
                $temp = $fieldsID['TIPO_PROPONENTE'];
                if($temp){
                    $propType = trim($registrations->$temp);
                    if ($propType === $proponentTypes['fisica'] || empty($propType) || $propType === $proponentTypes['coletivo']) {
                        $field_temp = $fieldsID['CPF'];
                    }else if($propType === trim($proponentTypes['juridica']) || $propType === trim($proponentTypes['juridica-mei'])){
                        $field_temp = $fieldsID['CNPJ'];
                    }
                }else{
                    $field_temp = $fieldsID['CPF'];
                }

                    $cpfBase = 0;
                    if(is_array($field_temp)){
                        foreach($field_temp as $value){
                            if($registrations->$value){
                                $cpfBase = preg_replace('/[^0-9]/i', '',$registrations->$value);
                                break;
                            }
                        }
                    }else{
                        $cpfBase = preg_replace('/[^0-9]/i', '',$registrations->$field_temp);
                    }     

                    $pos = array_search($cpfBase,$cpfCsv);
                    
                    if($pos){
                        $result = $dePara[$pos]['BEN_NUM_BANCO'];
                    }else{
                        $field_id = $fieldsID['NUM_BANCO'];
                        $result = $this->numberBank($registrations->$field_id);
                    }
                
                $result = $result;

                if (empty($result)) {
                    $app->log->info("\n".$registrations->number . " Número do banco não encontrado");
                }

                return $result;
            },            
            'AGENCIA_BANCO' => function ($registrations) use ($fieldsID, $proponentTypes, $dePara, $cpfCsv) {
                $temp = $fieldsID['TIPO_PROPONENTE'];
                if($temp){
                    $propType = trim($registrations->$temp);
                    if ($propType === $proponentTypes['fisica'] || empty($propType) || $propType === $proponentTypes['coletivo']) {
                        $field_temp = $fieldsID['CPF'];
                    }else if($propType === trim($proponentTypes['juridica']) || $propType === trim($proponentTypes['juridica-mei'])){
                        $field_temp = $fieldsID['CNPJ'];
                    }
                }else{
                    $field_temp = $fieldsID['CPF'];
                }

                    $cpfBase = 0;
                    if(is_array($field_temp)){
                        foreach($field_temp as $value){
                            if($registrations->$value){
                                $cpfBase = preg_replace('/[^0-9]/i', '',$registrations->$value);
                                break;
                            }
                        }
                    }else{
                        $cpfBase = preg_replace('/[^0-9]/i', '',$registrations->$field_temp);
                    }     

                    $pos = array_search($cpfBase,$cpfCsv);
                    if($pos){
                        $result = $dePara[$pos]['BEN_AGENCIA'];
                    }else{
                        $field_id = $fieldsID['AGENCIA_BANCO'];
                        $result = $registrations->$field_id;
                    }
                    
              
                return $result = $this->normalizeString(substr($result, 0, 4));
            },
            'CONTA_BANCO' => function ($registrations) use ($fieldsID , $proponentTypes, $dePara, $cpfCsv) {
                 $temp = $fieldsID['TIPO_PROPONENTE'];
                if($temp){
                    $propType = trim($registrations->$temp);
                    if ($propType === $proponentTypes['fisica'] || empty($propType) || $propType === $proponentTypes['coletivo']) {
                        $field_temp = $fieldsID['CPF'];
                    }else if($propType === trim($proponentTypes['juridica']) || $propType === trim($proponentTypes['juridica-mei'])){
                        $field_temp = $fieldsID['CNPJ'];
                    }
                }else{
                    $field_temp = $fieldsID['CPF'];
                }

                    $cpfBase = 0;
                    if(is_array($field_temp)){
                        foreach($field_temp as $value){
                            if($registrations->$value){
                                $cpfBase = preg_replace('/[^0-9]/i', '',$registrations->$value);
                                break;
                            }
                        }
                    }else{
                        $cpfBase = preg_replace('/[^0-9]/i', '',$registrations->$field_temp);
                    }     

                    $pos = array_search($cpfBase,$cpfCsv);
                    if($pos){
                        $result = $dePara[$pos]['BEN_CONTA'];
                    }else{
                        $field_id = $fieldsID['CONTA_BANCO'];
                        $result = $registrations->$field_id;
                    }                
               
                

                return $this->normalizeString($result);
            },            
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
        $inscricoes = $this->inscricoes();
        $csv_data = [];
        foreach ($registrations as $key_registration => $registration) {
            //Busca as informaçoes de pagamento
            $amount = $this->processesPayment($registration, $app);

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
            $csv_data[$key_registration]['VALOR'] = $amount;
            
            
        }
        
        /**
         * Salva o arquivo no servidor e faz o dispatch dele em um formato CSV
         * O arquivo e salvo no deretório docker-data/private-files/aldirblanc/inciso2/remessas
         */
        $file_name = 'inciso3-genCsv-' . $this->getStatus($this->data[$parametersForms['typeExport']]) . $opportunity_id . '-' . md5(json_encode($csv_data)) . '.csv';

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

    public function ALL_exportBankless()
    {
        $this->requireAuthentication();
        $app = App::i();
        $parameters = $this->getURLParameters([
            "opportunity" => "intArray",
            "type" => "string"
        ]);
        // pega oportunidades via ORM
        $opportunities = [];
        if (isset($parameters["opportunity"])) {
            $opportunities = $app->repo("Opportunity")->findBy([
                "id" => $parameters["opportunity"],
            ]);
        } else {
            $opportunities = $app->repo("Opportunity")->findAll();
        }
        foreach ($opportunities as $opportunity) {
            if (!$opportunity->canUser("@control")) {
                echo "Não autorizado.";
                die();
            }
        }
        if (!isset($parameters["type"])) {
            throw new Exception("O parâmetro \"type\" é obrigatório.");
        }
        switch ($parameters["type"]) {
            case "mci460":
                $this->exportMCI460($opportunities);
                break;
            case "ppg100":
                $this->exportPPG100($opportunities);
                break;
            case "addressReport":
                $this->addressReport($opportunities);
                break;
            default:
                throw new Exception("Arquivo desconhecido: " .
                                    $parameters["type"]);
        }
        return;
    }

    public function GET_importBankless()
    {
        $this->requireAuthentication();
        $app = App::i();
        $parameters = $this->getURLParameters([
            "file" => "int",
            "opportunity" => "intArray",
        ]);
        $opportunities = [];
        if (isset($parameters["opportunity"])) {
            $opportunities = $app->repo("Opportunity")->findBy([
                "id" => $parameters["opportunity"],
            ]);
        } else {
            $opportunities = $app->repo("Opportunity")->findAll();
        }
        foreach ($opportunities as $opportunity) {
            $opportunity->checkPermission("@control");
        }
        foreach ($opportunities as $opportunity) {
            $files = $opportunity->getFiles("bankless");
            foreach ($files as $file) {
                if ($file->id == $parameters["file"]) {
                    $this->importGeneric($file->getPath(), $opportunity);
                    break;
                }
            }
        }
        return;
    }

    /**
     * Implementa o exportador TXT no modelo CNAB 240, para envio de remessas ao banco do Brasil inciso1
     *
     *
     */
    public function ALL_exportCnab240Inciso1()
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '768M');

        /**
         * Verifica se o usuário está autenticado
         */
        $this->requireAuthentication();
        $app = App::i();

        //Captura se deve ser gerado um arquivo do tipo teste
        $typeFile =  null;
        if(isset($this->data['typeFile'])){
            $typeFile = $this->data['typeFile'];
        }

        $opportunity = $this->getOpportunity();
        $opportunity_id = $opportunity->id;
        $registrations = $this->getRegistrations($opportunity);
        $parametersForms = $this->getParametersForms();
        
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
        $fromToAccounts = $default['fromToAccounts'];
        $dePara = $this->readingCsvFromTo($fromToAccounts);
        $cpfCsv = $this->cpfCsv($fromToAccounts);       
       
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
            'CONVENIO_BB4' => function ($registrations) use ($typeFile) {
                if($typeFile == "TS"){
                    return "TS";
                }else{
                    return "";
                }
            }, 
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
            'CONVENIO_BB4' => function ($registrations) use ($typeFile) {
                if($typeFile == "TS"){
                    return "TS";
                }else{
                    return "";
                }
            },
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
            'CAMARA_CENTRALIZADORA' => function ($registrations) use ($detahe1) {
                $field_id = $detahe1['BEN_CODIGO_BANCO']['field_id'];
                $numberBank = $this->numberBank($registrations->$field_id);
                if($numberBank === "001"){
                    $result = "000";

                }else{
                    $result = "018";
                    
                }
                return $result;

            },
            'BEN_CODIGO_BANCO' => function ($registrations) use ($detahe2, $detahe1, $dePara, $cpfCsv) {
                $field_cpf = $detahe2['BEN_CPF']['field_id'];
                $cpfBase = preg_replace('/[^0-9]/i', '',$registrations->$field_cpf);
                
                $pos = array_search($cpfBase,$cpfCsv);

                if($pos){                    
                    $result = $dePara[$pos]['BEN_NUM_BANCO'];
                    
                }else{
                    $field_id = $detahe1['BEN_CODIGO_BANCO']['field_id'];
                    $result = $this->numberBank($registrations->$field_id);
                }
               
                return $result;

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

                    if (($numberBank == '001') && (substr($account, 0, 2) != "51")) {

                        $account_temp = "51" . $account;

                        if(strlen($account_temp) < 9){
                            $result = "51".str_pad($account, 9, 0, STR_PAD_LEFT);
                        
                        }else{
                            $result = "51" . $account;

                        }
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
                $field_id = $detahe1['BEN_CONTA']['field_id'];
                
                $field_cpf = $detahe2['BEN_CPF']['field_id'];
                $cpfBase = preg_replace('/[^0-9]/i', '',$registrations->$field_cpf);
                
                /**
                 * Caso use um banco padrão para recebimento, pega o número do banco das configs
                 * Caso contrario busca o número do banco na base de dados
                 */
                $fieldBanco = $detahe1['BEN_CODIGO_BANCO']['field_id'];
                if($fieldBanco){
                    $numberBank = $this->numberBank($registrations->$fieldBanco);
                }else{
                    $numberBank = $default['defaultBank']; 
                }
                
                /**
                 * Verifica se o CPF do requerente consta na lista de de-para dos bancos
                 * se existir, pega os dados bancários do arquivo
                 */
                $pos = array_search($cpfBase,$cpfCsv);               
                if($pos){                    
                    $temp_account = $dePara[$pos]['BEN_CONTA'];
                    
                }else{
                    /**
                     * Verifica se existe a opção de forma de recebimento
                     * Caso exista, e seja CARTEIRA DIGITAL BB pega o field id nas configs em (fieldsWalletDigital)
                     */
                    $formaRecebimento = $default['formoReceipt'];
                    $formoReceipt = $formaRecebimento ? $registrations->$formaRecebimento : false;
                  
                    if($formoReceipt == "CARTEIRA DIGITAL BB"){
                        $temp = $default['fieldsWalletDigital']['account'];                    
                    }else{
                        $temp = $detahe1['BEN_CONTA_DIGITO']['field_id'];
                    }
                    $temp_account = $registrations->$temp;
                }
                
                $temp_account = explode("-", $temp_account);
                
                if(count($temp_account)>1){
                    $dig = substr($temp_account[1], -1);

                }else{
                    $dig = substr($temp_account[0], -1);
                }
                
                /**
                 * Pega o tipo de conta que o beneficiário tem Poupança ou corrente
                 */
                $fiieldTipoConta = $detahe1['TIPO_CONTA']['field_id'];
                $typeAccount = $registrations->$fiieldTipoConta;

                /**
                 * Verifica se o usuário é do banco do Brasil, se sim verifica se a conta é poupança
                 * Se a conta for poupança e iniciar com o 510, ele mantem conta e DV como estão
                 * Caso contrario, ele pega o DV do De-Para das configs (savingsDigit)
                 */
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
                
                return is_string($result) ? strtoupper(trim($result)) : $this->normalizeString(trim($result));
               
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
                $date->add(new DateInterval('P5D'));
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
                $payment = $app->em->getRepository('\RegistrationPayments\Payment')->findOneBy([
                    'registration' => $registrations->id
                ]);

                return preg_replace('/[^0-9]/i', '',number_format($payment->amount,2,",","."));
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
            'BEN_TIPO_DOC' => function ($registrations) use ($detahe2) {
                $field_id = $detahe2['BEN_CPF']['field_id'];
                $data = preg_replace('/[^0-9]/i', '',$registrations->$field_id);
                if (strlen($this->normalizeString($data)) <= 11) {
                    return 1;
                }else{
                    return 2;
                }
               
            },
            'BEN_CPF' => function ($registrations) use ($detahe2) {
                $field_id = $detahe2['BEN_CPF']['field_id'];
                $data = preg_replace('/[^0-9]/i', '',$registrations->$field_id);
                if (strlen($this->normalizeString($data)) != 11) {
                    $_SESSION['problems'][$registrations->number] = "CPF Inválido";
                }
                return $data;
            },
            'BEN_ENDERECO_LOGRADOURO' => function ($registrations) use ($detahe2, $app) {
                return strtoupper($this->normalizeString($registrations->number));
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
            'USO_BANCO_115' => function ($registrations) use ($detahe2, $app) {
                return $this->normalizeString($registrations->number);
            },
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
        $womanMonoParent = $default['womanMonoParent'];
        $monoParentIgnore = $default['monoParentIgnore'];
        $countBanked = 0;
        $countUnbanked = 0;
        $countUnbanked = 0;
        $noFormoReceipt = 0;

        if($default['ducumentsType']['unbanked']){ // Caso exista separação entre bancarizados e desbancarizados
            foreach($registrations as $value){
                
                //Caso nao exista pagamento para a inscrição, ele a ignora e notifica na tela                
                if(!$this->validatedPayment($value)){
                    $app->log->info("\n".$value->number . " - Pagamento nao encontrado.");
                    continue;
                } 
                
                // Veirifica se existe a pergunta se o requerente é correntista BB ou não no formulário. Se sim, pega a resposta  
                $accountHolderBB = "NÃO";              
                if($selfDeclaredBB){
                    $accountHolderBB = trim($value->$selfDeclaredBB);
                   
                }
                
                //Caso nao exista informações bancárias
                if(!$value->$formoReceipt && $selfDeclaredBB === "NÃO"){                                   
                    $app->log->info("\n".$value->number . " - Forma de recebimento não encontrada.");
                    $noFormoReceipt ++;                   
                    continue;
                }
                
                //Verifica se a inscrição é bancarizada ou desbancarizada               
                if(in_array(trim($value->$formoReceipt), $typesReceipt['banked']) || $accountHolderBB === "SIM"){
                    $Banked = true;     
                    $countBanked ++;

                }else if(in_array(trim($value->$formoReceipt) , $typesReceipt['unbanked']) || $accountHolderBB === "NÃO"){
                    $Banked = false;
                    $countUnbanked ++; 
                               
                }
               
                if($Banked){
                    if($defaultBank){                          
                        if($informDefaultBank === "001" || $accountHolderBB === "SIM"){
                            
                            if (trim($value->$field_TipoConta) === "Conta corrente" || $value->$formoReceipt === "CARTEIRA DIGITAL BB") { 
                                $recordsBBCorrente[] = $value;
                                
                            }  else if (trim($value->$field_TipoConta) === "Conta poupança"){
                                
                                $recordsBBPoupanca[] = $value;                               
        
                            }else{
                                $recordsBBCorrente[] = $value;
                            }
                        }else{
                            $recordsOthers[] = $value;
                        }
                        
                    }else{    
                                           
                        if(($this->numberBank($value->$field_banco) == "001") || $accountHolderBB == "SIM"){
                            if (trim($value->$field_TipoConta) === "Conta corrente" || $value->$formoReceipt === "CARTEIRA DIGITAL BB") { 
                                $recordsBBCorrente[] = $value;
        
                            } else if (trim($value->$field_TipoConta) === "Conta poupança"){
                                $recordsBBPoupanca[] = $value;
        
                            }else{
                                $recordsBBCorrente[] = $value;
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
                //Caso nao exista pagamento para a inscrição, ele a ignora e notifica na tela
                if(!$this->validatedPayment($value)){
                    $app->log->info("\n".$value->number . " - Pagamento nao encontrado.");
                    continue;
                }

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
        sleep(1);
        
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
        // $numSeqRegistro = 0;

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
            $numSeqRegistro = 0;
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
            // $numSeqRegistro = 0;

            //Detalhes 1 e 2

            foreach ($recordsBBCorrente as $key_records => $records) {
                $numSeqRegistro++;
                $complement = [
                    'LOTE' => $numLote,
                    'NUMERO_REGISTRO' => $numSeqRegistro,
                ];
                $txt_data = $this->mountTxt($detahe1, $mappedDeletalhe1, $txt_data, $records, $complement, $app);
                $txt_data .= "\r\n";

                $numSeqRegistro++;
                $complement['NUMERO_REGISTRO'] = $numSeqRegistro;

                $txt_data = $this->mountTxt($detahe2, $mappedDeletalhe2, $txt_data, $records, $complement, $app);
                $txt_data .= "\r\n";

                $lotBBCorrente += 2;
                $this->processesPayment($records, $app);
            }

            //treiller 1
            $lotBBCorrente += 1; // Adiciona 1 para obedecer a regra de somar o treiller 1
            $valor = $_SESSION['valor'];             
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
            $numSeqRegistro = 0;
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
            // $numSeqRegistro = 0;

            //Detalhes 1 e 2

            foreach ($recordsBBPoupanca as $key_records => $records) {               
                $numSeqRegistro++;
                $complement = [
                    'LOTE' => $numLote,
                    'NUMERO_REGISTRO' => $numSeqRegistro,
                ];

                $txt_data = $this->mountTxt($detahe1, $mappedDeletalhe1, $txt_data, $records, $complement, $app);
                $txt_data .= "\r\n";

                $numSeqRegistro++;
                $complement['NUMERO_REGISTRO'] = $numSeqRegistro;

                $txt_data = $this->mountTxt($detahe2, $mappedDeletalhe2, $txt_data, $records, $complement, $app);
                $txt_data .= "\r\n";

                $lotBBPoupanca += 2;
                $this->processesPayment($records, $app);
            }

            //treiller 1
            $lotBBPoupanca += 1; // Adiciona 1 para obedecer a regra de somar o treiller 1
            $valor = $_SESSION['valor'];
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
            $numSeqRegistro = 0;
            $complement = [];
            $numLote++;
            $complement = [
                'FORMA_LANCAMENTO' => 41,
                'LOTE' => $numLote,
            ];

            $txt_data = $this->mountTxt($header2, $mappedHeader2, $txt_data, null, $complement, $app);

            $txt_data .= "\r\n";

            $lotOthers += 1;

            $_SESSION['valor'] = 0;

            $totaLotes++;
            // $numSeqRegistro = 0;

            //Detalhes 1 e 2

            foreach ($recordsOthers as $key_records => $records) {                
                $numSeqRegistro++;
                $complement = [
                    'LOTE' => $numLote,
                    'NUMERO_REGISTRO' => $numSeqRegistro,
                ];
                $txt_data = $this->mountTxt($detahe1, $mappedDeletalhe1, $txt_data, $records, $complement, $app);
                $txt_data .= "\r\n";

                $numSeqRegistro++;
                $complement['NUMERO_REGISTRO'] = $numSeqRegistro;

                $txt_data = $this->mountTxt($detahe2, $mappedDeletalhe2, $txt_data, $records, $complement, $app);
                $txt_data .= "\r\n";
                $lotOthers += 2;
                $this->processesPayment($records, $app);
                

            }

            //treiller 1
            $lotOthers += 1; // Adiciona 1 para obedecer a regra de somar o treiller 1
            $valor = $_SESSION['valor'];         
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
        $file_name = 'inciso1-cnab240-'. $this->getStatus($this->data[$parametersForms['typeExport']]) .$opportunity_id.'-' . md5(json_encode($txt_data)) . '.txt';

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
        $fromToAccounts = $default['fromToAccounts'];
        
        $dePara = $this->readingCsvFromTo($fromToAccounts);
        $cpfCsv = $this->cpfCsv($fromToAccounts);  

        $header1 = $this->getFieldId($header1, $field_labelMap);
        $header2 = $this->getFieldId($header2, $field_labelMap);
        $detahe1 = $this->getFieldId($detahe1, $field_labelMap);
        $detahe2 = $this->getFieldId($detahe2, $field_labelMap);
        $trailer1 = $this->getFieldId($trailer1, $field_labelMap);
        $trailer2 = $this->getFieldId($trailer2, $field_labelMap);
                

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
            'CAMARA_CENTRALIZADORA' => function ($registrations) use ($detahe1) {
                $field_id = $detahe1['BEN_CODIGO_BANCO']['field_id'];
                $numberBank = $this->numberBank($registrations->$field_id);
                if($numberBank === "001"){
                    $result = "000";

                }else{
                    $result = "018";
                    
                }
                return $result;

            },
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
                    $app->log->info("\n".$registrations->id . " Pagamento nao encontrado");
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
            'USO_BANCO_115' => function ($registrations) use ($detahe2, $app) {
                return $this->normalizeString($registrations->number);
            },
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
      * Implementa o importador CNAB240
      */

      public function ALL_importCnab240(){

        $result = [];
        $countLine = 1;
        $countSeg = 1;
        
        $file = __DIR__."../../CSV/IEDPAG8241120200.txt";    
        $data = $this->mappedCnab($file);

        //Pega a linha do header do lote
        $LOTE1_H = isset($data['LOTE_1']) ? min($data['LOTE_1']) : null;
        $LOTE2_H = isset($data['LOTE_2']) ? min($data['LOTE_2']) : null;
        $LOTE3_H = isset($data['LOTE_3']) ? min($data['LOTE_3']) : null;

        //Pela a linha do trailler do lote
        $LOTE1_T = isset($data['LOTE_1']) ? max($data['LOTE_1']) : null;
        $LOTE2_T = isset($data['LOTE_2']) ? max($data['LOTE_2']) : null;
        $LOTE3_T = isset($data['LOTE_3']) ? max($data['LOTE_3']) : null;
               
        foreach($data as $key => $value){
            $seg = null;
            $cpf = null;
            if($key === "HEADER_DATA_ARQ"){
                foreach($value as $key => $r){
                    //Valida o arquivo
                    $n = $this->getLineData($r, 230, 231);
                    $result['AQURIVO'] = $this->validatedCanb($n, $seg, $cpf);
                   
                }
            }else if($key === "LOTE_1_DATA"){                
                foreach($value as $key => $r){
                    if($key == $LOTE1_H){ 
                        //Valida se o lote 2 esta válido
                        $n = $this->getLineData($r, 230, 231);
                        $result['LOTE_1'] = $this->validatedCanb($n, $seg, $cpf);

                    }elseif($key == $LOTE1_T){ 
                       

                    }else{ 
                        $seg = ($key % 2) == true ? "A" : "B";

                        if($seg === "A"){
                            //Valida as inscrições
                            $code = $this->getLineData($r, 230, 231);
                            $result['LOTE_1'][] = $this->validatedCanb($code, $seg, $cpf);
                        }else{
                            $cpf = $this->getLineData($r, 20, 33);
                            $result['LOTE_1'][] = $this->validatedCanb($code, $seg, $cpf);
                        }
                        
                    }
                   
                   
                }
            }else if($key === "LOTE_2_DATA"){
                
                foreach($value as $key => $r){
                    
                    if($key == $LOTE2_H){ 
                        //Valida se o lote 2 esta válido
                        $n = $this->getLineData($r, 230, 231);
                        $result['LOTE_2'] = $this->validatedCanb($n, $seg, $cpf);

                    }elseif($key == $LOTE2_T){ 
                      

                    }else{ 
                        if($seg === "A"){
                            //Valida as inscrições
                            $n = $this->getLineData($r, 230, 231);
                            $result['LOTE_1'][] = $this->validatedCanb($n, $seg, $cpf);
                        }
                    }
                   
                    

                }
            }else if($key === "LOTE_3_DATA"){
                foreach($value as $key => $r){
                    if($key == $LOTE3_H){ 
                        //Valida se o lote 2 esta válido
                        $n = $this->getLineData($r, 230, 231);
                        $result['LOTE_3'] = $this->validatedCanb($n, $seg, $cpf);

                    }elseif($key == $LOTE3_T){
                      

                    }else{
                        if($seg === "A"){
                            //Valida as inscrições
                            $n = $this->getLineData($r, 230, 231);
                            $result['LOTE_1'][] = $this->validatedCanb($n, $seg, $cpf);
                        }
                    }
                    
                }
            }else if($key === "TREILLER_DATA_ARQ"){
                
            }
           
        }
    }

    private function validatedCanb($code, $seg, $cpf){
        $returnCode = $returnCode = $this->config['config-cnab240-inciso1']['returnCode'];
        $positive = $returnCode['positive'];
        $negative = $returnCode['negative'];
        foreach($positive as $key => $value){
            if($key === $code){
                return [
                    'seg' => $seg,
                    'cpf' => $cpf,
                    'status' => true,
                    'reason' => ''
                ];
            }
        }

        foreach($negative as $key => $value){
            if($key === $code){
                return [
                    'seg' => $seg,
                    'cpf' => $cpf,
                    'status' => false,
                    'reason' => $value
                ];
            }
        }
    }

      /**
       * faz o mapeamento do CNAB20... separa os lotes, treiller e header
       */
      private function mappedCnab($file){
        $stream = fopen($file,"r");
        $result = [];
        $countLine = 1;
          while(!feof($stream)){
              $linha = fgets($stream);
              if(!empty($linha)){
                  $value = $this->getLineData($linha, 0, 7);
                  switch ($value) {
                      case '00100000':
                          $result['HEADER_ARQ'][$countLine] = $countLine;
                          $result['HEADER_DATA_ARQ'][$countLine] = $linha;
                          break;
                      case '00100011':
                      case '00100013':
                      case '00100015':
                          $result['LOTE_1'][$countLine] = $countLine;
                          $result['LOTE_1_DATA'][$countLine] = $linha;
                          break;
                      case '00100021':
                      case '00100023':
                      case '00100025':
                          $result['LOTE_2'][$countLine] = $countLine;
                          $result['LOTE_2_DATA'][$countLine] = $linha;
                          break;
                      case '00100031':
                      case '00100033':
                      case '00100035':
                          $result['LOTE_3'][$countLine] = $countLine;
                          $result['LOTE_3_DATA'][$countLine] = $linha;
                          break;
                      case '00199999':
                          $result['TREILLER_ARQ'][$countLine] = $countLine;
                          $result['TREILLER_DATA_ARQ'][$countLine] = $linha;
                          break;
                      
                  }
              }

              $countLine ++;
          }

          return $result;
      }

      private function getLineData($line, $start, $end){              
        $data = "";
        $char = strlen($line);       
        if(!empty($line)){
            for($i=0; $i<$char; $i++){
                if($i>=$start && $i<=$end){
                    $data .= $line[$i];
                    
                }
            }
        }

        return $data;
  }
    //###################################################################################################################################

    /**
     * Função para retornar o número do banco, levando como base de pesquisa o nome do banco
     * Todos os textos que entram pelo parâmetro $bankName, são primeiro colocados em lowercase e comparado com o array $bankList também em lowercase
     *
     *
     */
    private function numberBank($bankName)
    {
        $bankName = strtolower(preg_replace('/\\s\\s+/', ' ',$this->normalizeString($bankName)));

        $bankList = $this->readingCsvFromTo('CSV/fromToNumberBank.csv');
        $list = [];
        foreach ($bankList as $key => $value) {
            $list[$key]['BANK'] = strtolower(preg_replace('/\\s\\s+/', ' ',$this->normalizeString($value['BANK'])));

            $list[$key]['NUMBER'] = strtolower(preg_replace('/\\s\\s+/', ' ',$this->normalizeString($value['NUMBER'])));
        }
        $result = 0;
        foreach ($list as $key => $value) {
            if($value['BANK'] === $bankName){
                $result = $value['NUMBER'];
                break;
            }
        }

        return $result;
    }
    
    /**
     * Retorna o valor do objeto endereço de uma registration
     *     
     * @return string
     */
    private function getAddress($field, $attribute, $fieldsID, $registrations, $app, $length){
        $field_id = $fieldsID[$field];
       
        $fromToAdress = $fieldsID['fromToAdress'];
        
        $result = " ";
        if($fromToAdress){
            $adress = $this->readingCsvFromTo($fromToAdress);
            foreach($adress as $key => $value){
                if($registrations->number === $value['INSCRICAO_ID']){
                    $result = $value[$field];
                    break;
                }
            }
        }else{
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
     * Processa os pagamentos para uma inscrição. Para uso com foreach.
     */
    private function getNextPayment($registration)
    {
        $app = App::i();
        $repo = $app->repo("\\RegistrationPayments\\Payment");
        $keys = $this->getParametersForms();
        $date = $keys["datePayment"];
        $select = ["registration" => $registration->id];
        if (isset($this->data[$date])) {
            $select["paymentDate"] = new DateTime($this->data[$date]);
        }
        $payments = $repo->findBy($select);
        foreach ($payments as $payment) {
            if ($this->processSinglePayment($registration, $app, $keys,
                                            $payment)) {
                yield $payment;
            }
        }
        return;
    }

    /**
     * Processa pagamento
     */
    private function processesPayment($register, $app)
    {
        $payment = $app->em->getRepository('\\RegistrationPayments\\Payment')->findOneBy([
            'registration' => $register->id
        ]);
        return $this->processSinglePayment($register, $app,
                                           $this->getParametersForms(),
                                           $payment);
    }

    private function processSinglePayment($register, $app, $parametersForms,
                                          $payment)
    {
        $result = 0;
        if ($payment && ($this->data[$parametersForms['typeExport']] === '0')) {
            $payment->status = 3;
            $payment->save(true);
            $app->log->info($register->number . " - EXPORTADA E PROCESSADA PARA PAGAMENTO");
            $result = $payment->amount;

        } else if ($payment && $this->data[$parametersForms['typeExport']] === '3') {
            $app->log->info($register->number . " - JÁ EXPORTADA PARA PAGAMENTO");
            $result = $payment->amount;

        } else if($payment && $this->data[$parametersForms['typeExport']] === 'all') {
            if ($payment->status == 0) {
                $app->log->info($register->number . " - PAGAMENTO CADASTRADO - AINDA NÃO EXPORTADO PARA PAGAMENTO");
            } else if ($payment->status == 3) {
                $app->log->info($register->number . " - PAGAMENTO CADASTRADO - JÁ EXPORTADO PARA PAGAMENTO");
            }

            $result = $payment->amount;

        } else {
            $app->log->info($register->number . " - PAGAMENTO NÃO ENCONTRADO");
            $result = false;
        }

        return $result;
    }

    /**
     * Valida se o pagament existe
     */
    private function validatedPayment($register){
        $app = App::i();
        $payment = $app->em->getRepository('\\RegistrationPayments\\Payment')->findOneBy([
            'registration' => $register->id
        ]);
      
        if(!$payment){                   
            return false;
        }

        return true;
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
        $regex = isset($value['filter']) ? $value['filter'] : '/[^a-z0-9 ]/i';
        $value['default'] = preg_replace($regex, '', $value['default']);

        if ($type === 'int') {
            $data .= str_pad($value['default'], $length, '0', STR_PAD_LEFT);
        } else {
            $data .= str_pad($value['default'], $length, " ");
        }

        return substr($data, 0, $length);
    }

    //Retorna o status da sportação
    private function getStatus($value){
        $status = [
            0 => 'inscricoes_pendentes_',
            3 => 'inscricoes_em_pagamento_',
            'all' => 'todas_inscricoes_'
        ];

        return $status[$value];
    }

    private function readingCsvFromTo($filename){

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

        $results = $this->readingCsvFromTo($filename);
        
        $data = [];
        foreach($results as $key => $value){
            $data[$key] = $value['CPF'];
        }
         return $data;
 
    }

    // define de qual form a requisição está vindo e pega os dados do request
    private function getParametersForms() {
        // pega as referências de qual form estão vindo os dados, CNAB ou GENÉRICO
        if (isset($this->data["generic"])) {
            $typeExport = "statusPaymentGeneric";
            $datePayment = "paymentDateGeneric";
            $typeSelect = "genericSelect";
            $listSelect = "listGeneric";

        } elseif(isset($this->data["cnab240"])) {
            $typeExport = "statusPaymentCnab240";
            $datePayment = "paymentDateCnab240";
            $typeSelect = "cnabSelect";
            $listSelect = "listCnab";

        } elseif (isset($this->data["type"])) {
            $typeExport = "statusPayment";
            $datePayment = "paymentDate";
            $typeSelect = "select";
            $listSelect = "list";
        }
        return [
            "typeExport" => $typeExport,
            "datePayment" => $datePayment,
            "typeSelect" => $typeSelect,
            "listSelect" => $listSelect
        ];
    }

    /**
     * Retorna o field ID dos campos com referencia no arrqy de configuração
     *
     */
    private function getFieldId($array, $field_labelMap){
        $result = [];
        foreach ($array as $key_config => $value) {            
            if (is_string($value['field_id']) && strlen($value['field_id']) > 0 && $value['field_id'] != 'mapped') {
                $field_id = array_search(trim($value['field_id']), $field_labelMap);
                $result[$key_config]['field_id'] = $field_id;
            }
        }

        return $result;
    }

    /**
     * Placeholder para o número de seqüência dos arquivos de remessa.
     */
    private function sequenceNumber()
    {
        if (isset($this->data["serial"]) &&
            is_numeric($this->data["serial"])) {
            return $this->data["serial"];
        }
        $n = 0;
        $type = isset($this->data["type"]) ? $this->data["type"] : "mci460";
        switch ($type) {
            case "cnab240": break;
            case "mci460":
                $n = $this->config["config-mci460"]["serial"];
                break;
            case "ppg100":
                $n = $this->config["config-ppg10x"]["serial"];
                break;
            default: break;
        }
        return $n;
    }

    /**
     * Lê dados de um CSV. Se o parâmetro key for passado, usa a coluna de mesmo
     * nome como chave do dicionário, caso contrário as linhas são retornadas em
     * seqüência.
     */
    private function getCSVData($file, $separator, $key=null)
    {
        $filename = __DIR__ . "/../" . $file;
        if (!file_exists($filename)) {
            return false;
        }
        $data = [];
        $stream = fopen($filename, "r");
        $csv = Reader::createFromStream($stream);
        $csv->setDelimiter($separator);
        $csv->setHeaderOffset(0);
        $stmt = new Statement();
        $results = $stmt->process($csv);
        if ($key == null) {
            foreach ($results as $line) {
                $data[] = $line;
            }
        } else {
            foreach ($results as $line) {
                $actualKey = $line[$key];
                unset($line[$key]);
                $data[$actualKey] = $line;
            }
        }
        return $data;
    }

    /**
     * Salva dados em um CSV. Se o parâmetro keyOrColumns for um array, assume
     * que os dados estão corretamente ordenados em data. Caso contrário, assume
     * que data é um dicionário cujas chaves devem ser armazenadas na coluna
     * indicada, e os valores são dados organizados com os nomes das demais
     * colunas.
     */
    private function saveCSVData($file, $separator, $keyOrColumns, $data)
    {
        $filename = __DIR__ . "/../" . $file;
        $dir = substr($filename, 0, strrpos($filename, "/"));
        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }
        $stream = fopen($filename, "w");
        $csv = Writer::createFromStream($stream);
        $csv->setDelimiter($separator);
        if (is_array($keyOrColumns)) {
            $csv->insertOne($keyOrColumns);
            $csv->insertAll($data);
        } else {
            $keys = array_keys($data);
            $header = array_merge([$keyOrColumns], array_keys($data[$keys[0]]));
            $body = [];
            foreach ($keys as $key) {
                $entry = [];
                $data[$key][$keyOrColumns] = $key;
                foreach ($header as $column) {
                    $entry[] = $data[$key][$column];
                }
                $body[] = $entry;
            }
            $csv->insertOne($header);
            $csv->insertAll($body);
        }
        fclose($stream);
        return;
    }

    /**
     * Valida e retorna os parâmetros da URL. Recebe um dicionário com os nomes
     * e tipos dos parâmetros. Tipos possíveis: date, int, intArray, string,
     * stringArray.
    */
    private function getURLParameters($list)
    {
        $parameters = [];
        if (empty($this->data)) {
            return $parameters;
        }
        $app = App::i();
        foreach ($list as $name => $type) {
            if (!isset($this->data[$name]) || empty($this->data[$name])) {
                continue;
            }
            switch ($type) {
                case "date":
                    if (!preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/",
                        $this->data[$name])) {
                        throw new \Exception("O formato da data em $name é " .
                                             "inválido.");
                    } else {
                        $date = new DateTime($this->data[$name]);
                        $parameters[$name] = $date->format("Y-m-d 00:00");
                    }
                    break;
                case "int":
                    if (!is_numeric($this->data[$name])) {
                        throw new Exception("Parâmetro inválido em $name.");
                    }
                    $parameters[$name] = $this->data[$name];
                    break;
                case "intArray":
                    $array = explode(",", $this->data[$name]);
                    foreach ($array as $element) {
                        if (!is_numeric($element)) {
                            throw new Exception("Parâmetro inválido em " .
                                                "$name: $element.");
                        }
                    }
                    $parameters[$name] = $array;
                    break;
                case "string":
                    $parameters[$name] = $this->data[$name];
                    break;
                case "stringArray":
                    $parameters[$name] = [];
                    $array = explode(",", $this->data[$name]);
                    for ($i = 0; $i < sizeof($array); ++$i) {
                        $body = $array[$i];
                        while (str_ends_with($body, "\\")) {
                            $suffix = ",";
                            if (($i + 1) < sizeof($array)) {
                                ++$i;
                                $suffix .=  $array[$i];
                            }
                            $body = substr($body, 0, -1) . $suffix;
                        }
                        $parameters[$name][] = $body;
                    }
                    break;
                default:
                    $app->log->warning("Tipo de parâmetro desconhecido: " .
                                       "$type.");
            }
        }
        return $parameters;
    }

    /** #########################################################################
     * Funções para exportadores - genéricas
     */

    /**
     * Chama outro método com dois parâmetros.
     */
    private function genericThunk2($func, $parm0, $parm1)
    {
        if (!method_exists($this, $func)) {
            throw new Exception("Configuração inválida: $func não existe.");
        }
        return $this->$func($parm0, $parm1);
    }

    /**
     * Gera o cabeçalho do arquivo exportado de acordo com a configuração.
     */
    private function genericHeader($config)
    {
        $out = "";
        foreach ($config["header"] as $field) {
            if (!isset($field["default"])) {
                if (isset($field["function"])) {
                    $field["default"] = $this->genericThunk2($field["function"],
                                                             null, null);
                } else {
                    throw new Exception("Configuração inválida: $field");
                }
            }
            $out .= $this->createString($field);
        }
        return $out;
    }

    /**
     * Gera os detalhes do arquivo exportado de acordo com a configuração.
     */
    private function genericDetails($config, $registration, $extraData)
    {
        $out = [];
        // itera sobre definições de detalhes
        foreach ($config["details"] as $detail) {
            // pula detalhes cuja condição o registro não atende
            if (isset($detail["condition"])) {
                if (!$this->genericThunk2($detail["condition"], $config,
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
                        $line .= $this->genericMetaField($config, $field,
                                                         $registration,
                                                         $extraData);
                        continue;
                    }
                    $field["default"] = $this->genericField($field, $config["fieldMap"],
                                                            $registration, $extraData);
                }
                $line .= $this->createString($field);
            }
            $out[] = $line;
        }
        return $out;
    }

    private function genericField($field, $fieldMap, $registration,
                                  $extraData)
    {
        $fieldName = $field["name"];
        // campos externos (por exemplo, o contador de clientes)
        if (!isset($fieldMap[$fieldName])) {
            return $extraData[$fieldName];
        }
        // campos do banco de dados
        $fieldName = $fieldMap[$fieldName];
        return (isset($field["function"]) ?
                $this->genericThunk2($field["function"],
                                     $registration->$fieldName, null) :
                $registration->$fieldName);
    }

    /**
     * Retorna um metacampo para o arquivo exportado de acordo com a configuração.
     */
    private function genericMetaField($config, $metafieldConfig, $registration,
                                      $extraData)
    {
        $out = "";
        $fieldMap = $config["fieldMap"];
        $metaname = $metafieldConfig["name"];
        if (isset($metafieldConfig["function"])) {
            $value = isset($fieldMap[$metaname]) ?
                     $registration->{$fieldMap[$metaname]} :
                     $extraData[$metaname];
            return $this->genericThunk2($metafieldConfig["function"],
                                        $metafieldConfig, $value);
        }
        foreach ($metafieldConfig["fields"] as $field) {
            if (!isset($field["default"])) {
                $fieldName = $field["name"];
                if (!isset($fieldMap[$metaname])) { // metacampo não mapeado
                    if (isset($extraData[$metaname])) { // metacampo no extraData
                        $meta = $extraData[$metaname];
                        $field["default"] = is_array($meta) ? $meta[$fieldName] :
                                            $meta->$fieldName;
                    } else { // trata subcampo como campo comum
                        $field["default"] = $this->genericField($field, $fieldMap,
                                                                $registration, $extraData);
                    }

                } else {
                    $meta = $registration->$metaname;
                    $field["default"] = is_array($meta) ? $meta[$fieldName] :
                                        $meta->$fieldName;
                }
            }
            $out .= $this->createString($field);
        }
        return $out;
    }

    /**
     * Cria o arquivo no servidor com o conteúdo do parâmetro $out e retorna na
     * resposta do request.
     */
    private function genericOutput($out, $type, $part, $opportunityIDs)
    {
        $fileName = "$type-" . (new DateTime())->format("Ymd") . "-op" .
                    implode("-", $opportunityIDs) . "-" .
                    md5(json_encode($out)) . ".txt";
        $dir = PRIVATE_FILES_PATH . "aldirblanc/$part/remessas/$type/";
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

    /**
     * Gera o rodapé do arquivo exportado de acordo com a configuração.
     */
    private function genericTrailer($config, $counters)
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

    private function genericCondition($fieldMap, $registration, $condition)
    {
        if (!is_array($condition)) {
            $field = $fieldMap[$condition];
            return $registration->$field;
        }
        if (!isset($condition["operator"])) {
            return $condition["const"];
        }
        switch ($condition["operator"]) {
            case "and":
                foreach ($condition["operands"] as $op) {
                    if (!$this->genericCondition($fieldMap, $registration, $op)) {
                        return false;
                    }
                }
                return true;
            case "or":
                foreach ($condition["operands"] as $op) {
                    if (!!$this->genericCondition($fieldMap, $registration, $op)) {
                        return true;
                    }
                }
                return false;
            case "xor":
                return (!!$this->genericCondition($fieldMap, $registration,
                                                  $condition["operands"][0]) !=
                        !!$this->genericcondition($fieldMap, $registration,
                                                  $condition["operands"][1]));
            case "not":
                return !$this->genericCondition($fieldMap, $registration,
                                                $condition["operands"][0]);
            case "exists":
                $value = $this->genericCondition($fieldMap, $registration,
                                                 $condition["operands"][0]);
                return (($value != null) && !empty($value));
            case "equals":
                return ($this->genericCondition($fieldMap, $registration,
                                                $condition["operands"][0]) ==
                        $this->genericCondition($fieldMap, $registration,
                                                $condition["operands"][1]));
            case "in":
                return in_array($this->genericCondition($fieldMap, $registration,
                                                        $condition["operands"][0]),
                                $this->genericCondition($fieldMap, $registration,
                                                        $condition["operands"][1]));
            case "prefix":
                return str_starts_with($this->genericCondition($fieldMap, $registration,
                                                               $condition["operands"][0]),
                                       $this->genericCondition($fieldMap, $registration,
                                                               $condition["operands"][1]));
        }
        return null;
    }

    private function genericFalse()
    {
        return false;
    }

    private function genericDateDDMMYYYY()
    {
        return (new DateTime())->format("dmY");
    }

    private function genericMod10Digit($value)
    {
        $sum = 0;
        $mult = 2;
        $len = mb_strlen($value);
        for ($i = 0; $i < $len; ++$i) {
            $d = intval(mb_substr($value, ($len - $i - 1), 1)) * $mult;
            if ($d > 9) {
                $d = $d - 9;
            }
            $sum += $d;
            $mult = 1 + !($mult - 1);
        }
        return ((10 - ($sum % 10)) % 10);
    }

    private function genericPaymentAmount($payment)
    {
        return ((int) round(($payment->amount * 100), 0));
    }

    private function genericTimeHHMM()
    {
        return (new DateTime())->format("Hi");
    }

    /** #########################################################################
     * Funções para importadores - genéricas
     */

    private function importGeneric($filePath, $opportunityID)
    {
        $app = App::i();
        $config = null;
        $type = null;
        $data = [
            [
                "filename" => basename($filePath),
                "importTS" => (new DateTime())->format("YmdHis"),
            ],
        ];
        foreach (new SplFileObject($filePath) as $line) {
            if (strlen($line) < 3) {
                continue;
            }
            if ($config == null) {
                $fileID = substr($line, 13, 8);
                if (str_starts_with($fileID, "MCIF470")) {
                    $type = "config-mci460";
                    $subtype = "return";
                } else if (str_starts_with($fileID, "PPG101")) {
                    $type = "config-ppg10x";
                    $subtype = "return";
                } else {
                    // WIP: não reconhece PPG102 enquanto não acertarmos o formato do arquivo
                    // $fileID = substr($line, 15, 6);
                    // if (str_starts_with($fileID, "PPG102")) {
                    //     $type = "config-ppg10x";
                    //     $subtype = "followup";
                    // } else {
                        echo("Formato de remessa desconhecido.");
                        die();
                    // }
                }
                $config = $this->config[$type][$subtype];
            }
            $offset = 0;
            foreach ($config["topLevel"] as $spec) {
                $item = substr($line, $offset, $spec["length"]);
                if (isset($spec["match"])) {
                    $spec["default"] = $spec["match"];
                    $test = $this->createString($spec);
                    if ($item !== $test) {
                        $app->log->debug("Nonmatch $item (should be $test) ".
                                         "on line:\n$line");
                    }
                }
                if (isset($spec["map"])) {
                    $map = $spec["map"];
                    $lineSpec = $config[$map][$item] ?? $config[$map]["default"];
                    $data[] = $this->importGenericLine($line, $lineSpec);
                }
                $offset += $spec["length"];
            }
        }
        if (sizeof($data) < 3) {
            echo("Nada a importar.");
            die();
        }
        if ($type == "config-mci460") {
            $this->importMCI470($data);
        } else { // if ($type == "config-ppg10x")
            if ($subtype == "return") {
                $this->importPPG101($data);
            } else { // if ($subtype == "followup")
                $this->importPPG102($data);
            }
        }
        $app->disableAccessControl();
        $opportunity = $app->repo("Opportunity")->find($opportunityID);
        $opportunity->refresh();
        $files = $opportunity->bankless_processed_files;
        $files->{basename($filePath)} = date("d/m/Y \à\s H:i");
        $opportunity->bankless_processed_files = $files;
        $opportunity->save(true);
        $app->enableAccessControl();
        $this->finish("ok");
        return;
    }

    private function importGenericLine($line, $specs)
    {
        $app = App::i();
        $data = [];
        $offset = 0;
        foreach ($specs as $spec) {
            $item = substr($line, $offset, $spec["length"]);
            if (isset($spec["match"])) {
                $spec["default"] = $spec["match"];
                $test = $this->createString($spec);
                if ($item !== $test) {
                    $app->log->debug("Nonmatch $item (should be $test) ".
                                     "on line:\n$line");
                }
            }
            if (isset($spec["capture"])) {
                if ($spec["type"] == "text") {
                    $item = trim($item);
                } else if ($spec["type"] == "int") {
                    $item = intval($item);
                }
                $data[$spec["capture"]] = $item;
            }
            $offset += $spec["length"];
        }
        return ["raw" => substr($line, 0, $offset), "payload" => $data];
    }

    /** #########################################################################
     * Funções para os PPG10x
     */

     private function ppg10xIdMap($key)
     {
        $config = $this->config["config-ppg10x"];
        $idMap = null;
        if (isset($config["idMap"]) &&
            !isset($this->data["ignore_ppg_idmap"])) {
            $idMap = $this->getCSVData($config["idMap"], ",", $key);
            if (!$idMap) {
                App::i()->log->info("Mapeamento de identificadores ausente.");
                $idMap = [];
            }
        }
        return $idMap;
     }

     private function ppg10xResolvePayment($idMap, $reference, $status)
     {
        $app = App::i();
        $paymentRepo = $app->repo("\\RegistrationPayments\\Payment");
        if ($idMap != null) {
            $registrationID = $idMap[$reference]["registrationID"];
            $payment = $paymentRepo->findOneBy([
                "registration" => $registrationID,
                "status" => $status,
            ]);
        } else {
            $payment = $paymentRepo->find($reference);
            $registrationID = $payment->registration->id;
        }
        if (!isset($payment)) {
            if ($idMap != null) {
                $app->log->info("Pagamento não encontrado para inscrição " .
                                $registrationID);
            } else {
                $app->log->info("Pagamento não encontrado: $reference");
            }
        } else {
            $app->log->info("Processando pagamento para inscrição " .
                             $registrationID);
        }
        return $payment;
     }

    /** #########################################################################
     * Funções para o PPG100
     * Os métodos ppg100* são referenciados pelas configurações e não devem ser
     * removidos sem ajuste nas mesmas.
     */

    private function exportPPG100($opportunities)
    {
        $app = App::i();
        $config = $this->config["config-ppg10x"];
        if (!isset($config["condition"])) {
            throw new Exception("Configuração inválida: \"condition\" não " .
                                "configurada");
        }
        $newline = "\r\n";
        set_time_limit(0);
        // inicializa contadores
        $nLines = 1;
        $totalAmount = 0;
        // gera o header
        $out = $this->genericHeader($config) . $newline;
        $opportunityIDs = [];
        // percorre as oportunidades
        foreach ($opportunities as $opportunity) {
            $registrations = $this->getRegistrations($opportunity, true);
            /**
             * Mapeamento de fielsds_id pelo label do campo
             */
            $this->registerRegistrationMetadata($opportunity);
            // processa inscrições
            $linesBefore = $nLines;
            while ($registration = $registrations->next()[0]) {
                // testa se é desbancarizado
                if (!$this->genericCondition($config["fieldMap"], $registration,
                                             $config["condition"])) {
                    $app->log->info("Ignorando - condição não satisfeita: " .
                                    $registration->number);
                    continue;
                }
                foreach ($this->getNextPayment($registration) as $payment) {
                    $amount = $this->genericPaymentAmount($payment);
                    $details = $this->genericDetails($config, $registration, [
                        "numeroRegistro" => ($nLines + 1),
                        "valorCarga" => $amount,
                        "numeroProtocolo" => ["idCliente" => $payment->id],
                    ]);
                    $nLines += sizeof($details);
                    $totalAmount += $amount;
                    $out .= implode($newline, $details) . $newline;
                }
                $app->em->clear();
            }
            if ($nLines > $linesBefore) {
                $opportunityIDs[] = $this->createString([
                    "default" => $opportunity->id,
                    "length" => 3,
                    "type" => "int",
                ]);
            }
        }
        ++$nLines;
        $out .= $this->genericTrailer($config, [
            "totalDetalhes" => ($nLines - 2),
            "totalCarga" => $totalAmount,
            "numeroRegistro" => $nLines,
        ]) . $newline;
        $this->genericOutput($out, "ppg100", "inciso1", $opportunityIDs);
        return;
    }

    // formato antigo, sem uso se permanecer o novo
    private function ppg100ProtocolNumberPA($fieldSpec, $registrationNumber)
    {
        $out = "";
        foreach ($this->config["config-ppg10x"]["header"] as $field) {
            if ($field["name"] == "codigoParametroCliente") {
                $idBB = $field["default"];
                break;
            }
        }
        $components = [
            "idBB" => "$idBB",
            "idCliente" => "$registrationNumber",
        ];
        foreach ($fieldSpec["fields"] as $field) {
            if (!isset($components[$field["name"]])) {
                $field["default"] = $this->genericMod10Digit($out);
            } else {
                $field["default"] = $components[$field["name"]];
            }
            $out .= $this->createString($field);
        }
        return $out;
    }

    private function ppg100PIN($value)
    {
        return random_int(0, 999999);
    }

    /** #########################################################################
     * Funções para o PPG101
     */

    private function importPPG101($data)
    {
        $app = App::i();
        // carrega mapeamento de identificadores
        $idMap = $this->ppg10xIdMap("idCliente");
        $meta = $data[0];
        $header = $data[1]["payload"];
        $footer = $data[sizeof($data) - 1]["payload"];
        $data = array_splice($data, 2, -1);
        $app->log->info("Resultado geral: " . $header["fileResultCode"]);
        $app->log->info("Mensagem geral: " . $header["fileResultMessage"]);
        set_time_limit(0);
        foreach ($data as $item) {
            $entry = $item["payload"];
            $payment = $this->ppg10xResolvePayment($idMap, $entry["reference"],
                                                   Payment::STATUS_PENDING);
            if (!isset($payment)) {
                continue;
            }
            $payment->status = ($entry["paymentCode"] == 0) ?
                               Payment::STATUS_AVAILABLE :
                               Payment::STATUS_FAILED;
            $metadata = is_array($payment->metadata) ? $payment->metadata :
                        json_decode($payment->metadata);
            $metadata["ppg101"] = [
                "raw" => $item["raw"],
                "processed" => $entry,
                "filename" => $meta["filename"],
            ];
            $payment->metadata = $metadata;
            $payment->save(true);
            $app->em->clear();
        }
        $app->log->info("Total aceito: " . $footer["countAccepted"]);
        $app->log->info("Total rejeitado: " . $footer["countRejected"]);
        return;
    }

    /** #########################################################################
     * Funções para o PPG102
     */

    private function importPPG102($data)
    {
        $app = App::i();
        // carrega mapeamento de identificadores
        $idMap = $this->ppg10xIdMap("idCliente");
        $meta = $data[0];
        $footer = $data[sizeof($data) - 1]["payload"];
        $data = array_splice($data, 2, -1);
        set_time_limit(0);
        foreach ($data as $item) {
            $entry = $item["payload"];
            $payment = $this->ppg10xResolvePayment($idMap, $entry["reference"],
                                                   Payment::STATUS_AVAILABLE);
            if (!isset($payment)) {
                continue;
            }
            // $payment->status = ($entry["paymentCode"] == 0) ?
            //                    Payment::STATUS_AVAILABLE :
            //                    Payment::STATUS_FAILED;
            $metadata = is_array($payment->metadata) ? $payment->metadata :
                        json_decode($payment->metadata);
            $metadata["ppg102"] = [
                "raw" => $item["raw"],
                "processed" => $entry,
                "filename" => $meta["filename"],
            ];
            $payment->metadata = $metadata;
            // $payment->save(true); // WIP: não salvar nada enquanto não acertarmos o formato do arquivo
            $app->em->clear();
        }
        $app->log->info("Total de registros: " . $footer["countEntries"]);
        return;
    }

    /** ########################################################################
     * Funções para o MCI460
     * Os métodos mci460* são referenciados pelas configurações e não devem ser
     * removidos sem ajuste nas mesmas.
     */

    private function exportMCI460($opportunities)
    {
        $app = App::i();
        $config = $this->config["config-mci460"];
        if (!isset($config["condition"])) {
            throw new Exception("Configuração inválida: \"condition\" não configurada.");
        }
        $exportControl = isset($this->data["statusPayment"]) &&
                         ($this->data["statusPayment"] == "0");
        $newline = "\r\n";
        set_time_limit(0);
        // carrega mapeamento de agências
        $branchMap = $this->getCSVData($config["branchMap"], ",", "CEP");
        // inicializa contadores
        $nLines = 1;
        $nClients = 0;
        // gera o header
        $out = $this->genericHeader($config) . $newline;
        $opportunityIDs = [];
        // percorre as oportunidades
        foreach ($opportunities as $opportunity) {
            $registrations = $this->getRegistrations($opportunity, true);
            /**
             * Mapeamento de fielsds_id pelo label do campo
             */
            $this->registerRegistrationMetadata($opportunity);
            // processa inscrições
            $clientsBefore = $nClients;
            while ($registration = $registrations->next()[0]) {
                // caso a exportação seja para pagamento, testa se não tem solicitação em aberto
                if ($exportControl &&
                    isset($registration->owner->account_creation->status) &&
                    ($registration->owner->account_creation->status !=
                     self::ACCOUNT_CREATION_PENDING)) {
                    $app->log->info("Ignorando - já exportado: " .
                                     $registration->number);
                    continue;
                }
                // testa se é desbancarizado
                if (!$this->genericCondition($config["fieldMap"], $registration,
                                             $config["condition"])) {
                    $app->log->info("Ignorando - condição não satisfeita: " .
                                    $registration->number);
                    continue;
                }
                // testa se o CEP está mapeado
                $branchSetex = $this->mci460BranchSetexES($config["fieldMap"],
                                                          $registration,
                                                          $branchMap);
                if (!$branchSetex) {
                    $app->log->info("Ignorando - sem agência: " .
                                     $registration->number);
                    continue;
                }
                ++$nClients;
                $extraData = array_merge(["sequencialCliente" => $nClients],
                                         $branchSetex);
                $details = $this->genericDetails($config, $registration,
                                                 $extraData);
                $nLines += sizeof($details);
                $raw = implode($newline, $details);
                $out .= $raw . $newline;
                if ($exportControl) {
                    $app->log->info("Processando: " . $registration->number);
                    $registration->owner->account_creation = [
                        "status" => self::ACCOUNT_CREATION_PROCESSING,
                        "type" => "mci460",
                        "sent_raw" => $raw,
                    ];
                    $registration->owner->save(true);
                }
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
        $out .= $this->genericTrailer($config, [
            "totalClientes" => $nClients,
            "totalRegistros" => $nLines,
        ]) . $newline;
        $this->genericOutput($out, "mci460", "inciso1", $opportunityIDs);
        return;
    }

    private function addressReport($opportunities)
    {
        $app = App::i();
        set_time_limit(0);
        $exportControl = isset($this->data["statusPayment"]) &&
                         ($this->data["statusPayment"] == "0");
        $header = ["Inscrição", "Nome", "Logradouro", "Número", "Complemento",
                   "Bairro", "Município", "Estado", "CEP"];
        $report = [];
        $opportunityIDs = [];
        $config = $this->config["config-mci460"];
        $address = $config["fieldMap"]["endereco"];
        $name = $config["fieldMap"]["nomeCliente"];
        foreach ($opportunities as $opportunity) {
            $registrations = $this->getRegistrations($opportunity, true);
            /**
             * Mapeamento de fielsds_id pelo label do campo
             */
            $this->registerRegistrationMetadata($opportunity);
            $linesBefore = sizeof($report);
            while ($registration = $registrations->next()[0]) {
                // caso a exportação seja para pagamento, testa se não tem solicitação em aberto
                if ($exportControl &&
                    isset($registration->owner->account_creation->status) &&
                    ($registration->owner->account_creation->status !=
                     self::ACCOUNT_CREATION_PENDING)) {
                    $app->log->info("Ignorando - já exportado: " .
                                     $registration->number);
                    continue;
                }
                // testa se é desbancarizado
                if (!$this->genericCondition($config["fieldMap"], $registration,
                                             $config["condition"])) {
                    $app->log->info("Ignorando - condição não satisfeita: " .
                                    $registration->number);
                    continue;
                }
                $app->log->info("Incluindo: " . $registration->number);
                $addressFields = [];
                $source = $registration->$address;
                if (is_array($source)) {
                    $addressFields[] = $source["En_Nome_Logradouro"];
                    $addressFields[] = $source["En_Num"];
                    $addressFields[] = $source["En_Complemento"] ?? "";
                    $addressFields[] = $source["En_Bairro"];
                    $addressFields[] = $source["En_Municipio"];
                    $addressFields[] = $source["En_Estado"];
                    $addressFields[] = $source["En_CEP"];
                } else {
                    $addressFields[] = $source->En_Nome_Logradouro;
                    $addressFields[] = $source->En_Num;
                    $addressFields[] = $source->En_Complemento ?? "";
                    $addressFields[] = $source->En_Bairro;
                    $addressFields[] = $source->En_Municipio;
                    $addressFields[] = $source->En_Estado;
                    $addressFields[] = $source->En_CEP;
                }
                $report[] = array_merge([$registration->number,
                                        $registration->$name], $addressFields);
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
        $fileName = "addressReport-" . (new DateTime())->format("Ymd") . "-op" .
                    implode("-", $opportunityIDs) . "-" .
                    md5(json_encode(array_merge([$header], $report))) . ".csv";
        $dir = PRIVATE_FILES_PATH . "aldirblanc/inciso1/remessas/generics/";
        $path = $dir . $fileName;
        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }
        $stream = fopen($path, "w");
        $csv = Writer::createFromStream($stream);
        $csv->insertOne($header);
        $csv->insertAll($report);
        header("Content-Type: application/csv");
        header("Content-Disposition: attachment; filename=" . $fileName);
        header("Pragma: no-cache");
        readfile($path);
        fclose($stream);
        return;
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

    private function mci460ConditionDetail08ES($config, $registration)
    {
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

    private function mci460BranchSetexES($fieldMap, $registration, $branchMap)
    {
        $field = $fieldMap["endereco"];
        $address = $registration->$field;
        $zip = is_array($address) ? $address["En_CEP"] : $address->En_CEP;
        if (!isset($branchMap[$zip])) {
            return null;
        }
        $branchSetex = [];
        $branch = explode("-", trim($branchMap[$zip]["AGENCIA"]));
        $branchSetex["agencia"] = $branch[0];
        $branchSetex["dvAgencia"] = $branch[1];
        $setex = explode("-", trim($branchMap[$zip]["SETEX"]));
        $branchSetex["grupoSetex"] = $setex[0];
        $branchSetex["dvGrupoSetex"] = $setex[1];
        return $branchSetex;
    }

    private function mci460DateFormatDDMMYYYY($value)
    {
        return implode("", array_reverse(explode("-", $value)));
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

    /** #########################################################################
     * Funções para o MCI470
     */

    private function importMCI470($data)
    {
        $app = App::i();
        $config = $this->config["config-mci460"];
        $meta = $data[0];
        $footer = $data[sizeof($data) - 1]["payload"];
        $data = array_splice($data, 2, -1);
        $registrationRepo = $app->repo("Registration");
        $app->disableAccessControl();
        set_time_limit(0);
        foreach ($data as $item) {
            $entry = $item["payload"];
            $registrationID = substr($entry["registrationID"],
                                     strcspn($entry["registrationID"],
                                             "0123456789"));
            $registration = $registrationRepo->find($registrationID);
            if (!isset($registration)) {
                $app->log->info("Ignorando: não encontrada $registrationID");
                continue;
            }
            $accountCreation = $registration->owner->account_creation ??
                               new stdClass();
            if (($accountCreation->status ?? 1) == 10) {
                $app->log->info("Ignorando - conta já aberta: $registrationID");
                continue;
            }
            $app->log->info("Processando: $registrationID - " .
                            json_encode($entry));
            $accountCreation->status = ($entry["errorClient"] == 0) ?
                                       self::ACCOUNT_CREATION_SUCCESS :
                                       self::ACCOUNT_CREATION_FAILED;
            $accountCreation->received_raw = $item["raw"];
            $accountCreation->received_filename = $meta["filename"];
            $accountCreation->processed = $entry;
            $registration->owner->account_creation = $accountCreation;
            if ($accountCreation->status == self::ACCOUNT_CREATION_SUCCESS) {
                $registration->owner->payment_bank_account_type =
                    $config["defaults"]["accountType"];
                $registration->owner->payment_bank_number =
                    $config["defaults"]["bankNumber"];
                $registration->owner->payment_bank_branch =
                    $entry["branch"] . "-" . $entry["branchVC"];
                $registration->owner->payment_bank_account =
                    $entry["account"] . "-" . $entry["accountVC"];
            }
            $registration->owner->save(true);
            $app->em->clear();
        }
        $app->enableAccessControl();
        $app->log->info("Total registros: " . $footer["countEntries"]);
        return;
    }
}