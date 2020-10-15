<?php

namespace AldirBlanc\Controllers;

use DateTime;
use Exception;
use Normalizer;
use MapasCulturais\i;
use League\Csv\Writer;
use MapasCulturais\App;
use MapasCulturais\Entities\Registration;

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
    public function ALL_genericExportInciso2(){
        
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
            'opportunity_Id' => $opportunity_id,
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
            'CPF' => function ($registrations) use ($fieldsID, $categories){
                if(in_array($registrations->category, $categories['CPF'])){
                    $field_id = $fieldsID['CPF'];
                    return str_replace(['.', '-'], '', $registrations->$field_id);
                }else{
                    return 0;
                }              
            },
            'NOME_SOCIAL' => function ($registrations) use ($fieldsID, $categories){
                if(in_array($registrations->category, $categories['CPF'])){
                    $field_id = $fieldsID['NOME_SOCIAL'];
                    return  $this->normalizeString($registrations->$field_id);
                }else{
                    return "";
                }
             },
             'CNPJ' => function ($registrations) use ($fieldsID, $categories){
                if(in_array($registrations->category, $categories['CNPJ'])){ 
                    $field_id = $fieldsID['CNPJ'];   
                    if(is_array($field_id)){
                        $result = "";
                        foreach($field_id as $key => $value){
                            if($registrations->$value){
                                $result = str_replace(['.', '-','/'], '', $registrations->$value);
                            }
                        }
                        return $this->normalizeString($result);
                    }else{
                        return str_replace(['.', '-','/'], '', $registrations->$field_id);
                    }
                }else{
                    return 0;
                }               
             },
             'RAZAO_SOCIAL' => function ($registrations) use ($fieldsID, $categories){
                if(in_array($registrations->category, $categories['CNPJ'])){ 
                    $field_id = $fieldsID['RAZAO_SOCIAL'];
                    if(is_array($field_id)){
                        $result = "";
                        foreach($field_id as $key => $value){
                            if($registrations->$value){
                                $result = $registrations->$value;
                            }
                        }
                        return $this->normalizeString($result);
                    }else{
                        return  $this->normalizeString($registrations->$field_id);
                    }
                }
                else{
                    return ""; 
                }
              },
             'LOGRADOURO' => function ($registrations) use ($fieldsID){
                $field_id = $fieldsID['LOGRADOURO'];
                return  $this->normalizeString($registrations->$field_id['En_Nome_Logradouro']);
             },
             'NUMERO' => function ($registrations) use ($fieldsID){
                $field_id = $fieldsID['NUMERO'];
                return  preg_replace("/[^0-9]/", "",$registrations->$field_id['En_Num']);
             },
             'COMPLEMENTO' => function ($registrations) use ($fieldsID){
                $field_id = $fieldsID['COMPLEMENTO'];
                return  $this->normalizeString($registrations->$field_id['En_Complemento']);
             },
             'BAIRRO' => function ($registrations) use ($fieldsID){
                $field_id = $fieldsID['BAIRRO'];
                return  $this->normalizeString($registrations->$field_id['En_Bairro']);
             },
             'MUNICIPIO' => function ($registrations) use ($fieldsID){
                $field_id = $fieldsID['MUNICIPIO'];
                return  $this->normalizeString($registrations->$field_id['En_Municipio']);
             },             
             'CEP' => function ($registrations) use ($fieldsID){
                $field_id = $fieldsID['CEP'];
                return  preg_replace("/[^0-9]/", "",$registrations->$field_id['En_CEP']);
             },
             'ESTADO' => function ($registrations) use ($fieldsID){
                $field_id = $fieldsID['ESTADO'];
                return  $this->normalizeString($registrations->$field_id['En_Estado']);
             },
             'NUM_BANCO' => function ($registrations) use ($fieldsID){
                $field_id = $fieldsID['NUM_BANCO'];                
                return  $this->numberBank($registrations->$field_id);                
             },
             'TIPO_CONTA_BANCO' => function ($registrations) use ($fieldsID){
                $field_id = $fieldsID['TIPO_CONTA_BANCO'];
                return  $this->normalizeString($registrations->$field_id);
             },
             'AGENCIA_BANCO' => function ($registrations) use ($fieldsID){
                $field_id = $fieldsID['AGENCIA_BANCO'];
                return  preg_replace("/[^0-9]/", "",$registrations->$field_id);
             },
             'CONTA_BANCO' => function ($registrations) use ($fieldsID){
                $field_id = $fieldsID['CONTA_BANCO'];
                return  preg_replace("/[^0-9]/", "",$registrations->$field_id);
             },            
             'OPERACAO_BANCO' => function ($registrations) use ($fieldsID){
                $field_id = $fieldsID['OPERACAO_BANCO'];
                return  preg_replace("/[^0-9]/", "",$registrations->$field_id);
             },             
             'VALOR' => $fieldsID['VALOR'],
             'INSCRICAO_ID' => function ($registrations) use ($fieldsID) {
                return preg_replace("/[^0-9]/", "", $registrations->number);

            },
             'INCISO' => function ($registrations) use ($fieldsID) {
                $field_id = $fieldsID['INCISO'];
                return $this->normalizeString($field_id);
            }
           
        ];
        
        //Itera sobre os dados mapeados
        $csv_data = [];
        foreach ($registrations as $key_registration => $registration) {
            foreach ($mappedRecords as $key_fields => $field) {
                if (is_callable($field)) {
                    $csv_data[$key_registration][$key_fields] = $field($registration);
                
                } else if (is_string($field) && strlen($field) > 0) {
                    if($registration->$field){
                        $csv_data[$key_registration][$key_fields] = $registration->$field;
                    }else{
                        $csv_data[$key_registration][$key_fields] = $field;
                    }
                    
                } else {
                    if(strstr($field, 'field_')){
                        $csv_data[$key_registration][$key_fields] = null;
                    }else{
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
     * Todos os textos que entram pelo parâmetro $bankName, são primeiro colocados em lowercase em seguida a primeira letra 
     * de cada palavra e passado para upercase
     *  
     */
    private function numberBank($bankName){

        $bankList = [
            'Bco Do Brasil S.A' => '001' , 
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
            'Banco Pan' => '623'
        ];
        
        $return = "";
        foreach($bankList as $key => $value){
            if($key==ucwords(strtolower($bankName))){
                $return = $value;            
            }
        }
        
        return $return;
        
    }

    private function normalizeString(string $valor) : string
    {
        $valor = Normalizer::normalize( $valor, Normalizer::FORM_D );
        return preg_replace('/[^A-Za-z0-9 ]/i', '', $valor);
    }
    
}