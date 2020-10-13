<?php

namespace AldirBlanc\Controllers;

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
     * Implementa o exportador TXT no modelo CNAB 240, para envio de remessas ao banco do Brasil
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
        $status = $txt_config['parameters_default']['status'];
        $header1 = $txt_config['HEADER1'];
        $header2 = $txt_config['HEADER2'];
        $detahe1 = $txt_config['DETALHE1'];
        $detahe2 = $txt_config['DETALHE2'];
        $trailer1 = $txt_config['TRAILER1'];
        $trailer2 = $txt_config['TRAILER2'];

        /**
         * Busca as inscrições com status 10 (Selecionada)         
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
        
        $mappedHeader1 = [
            'BANCO' => '',
            'LOTE' => '',
            'REGISTRO' => '',
            'USO_BANCO_12' => '',
            'INSCRICAO_TIPO' => '',
            'CPF_FONTE_PAG' => '',
            'CONVENIO' => '',
            'AGENCIA' => '',
            'AGENCIA_DIGITO' => '',
            'CONTA' => '',
            'CONTA_DIGITO' => '',
            'USO_BANCO_20' => '',
            'NOME_EMPRESA' => '',
            'NOME_BANCO' => '',
            'USO_BANCO_23' => '',
            'CODIGO_REMESSA' => '',
            'DATA_GER_ARQUIVO' => '',
            'HORA_GER_ARQUIVO' => '',
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
            'CONVENIO' => '',
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
            'BEN_CODIGO_BANCO' => '',
            'BEN_AGENCIA' => function($registrations) use ($detahe1){
                $field_id = $detahe1['BEN_AGENCIA']['field_id'];
                return $registrations->$field_id;
            },
            'BEN_AGENCIA_DIGITO' => function($registrations) use ($detahe1){
                $field_id = $detahe1['BEN_AGENCIA_DIGITO']['field_id'];
                return $registrations->$field_id;
            },
            'BEN_CONTA' => function($registrations) use ($detahe1){
                $field_id = $detahe1['BEN_CONTA']['field_id'];
                return $registrations->$field_id;
            },
            'BEN_CONTA_DIGITO' => function($registrations) use ($detahe1){
                $field_id = $detahe1['BEN_CONTA_DIGITO']['field_id'];
                return $registrations->$field_id;
            },
            'BEN_DIGITO_CONTA_AGENCIA_80' => '',
            'BEN_NOME' => function($registrations) use ($detahe1){
                $field_id = $detahe1['BEN_NOME']['field_id'];
                return $registrations->$field_id;
            },
            'BEN_DOC_ATRIB_EMPRESA_82' => '',
            'DATA_PAGAMENTO' => '',
            'TIPO_MOEDA' => '',
            'USO_BANCO_85' => '',
            'VALOR_INTEIRO' => '',
            'VALOR_DECIMAL' => '',
            'USO_BANCO_88' => '',
            'USO_BANCO_89' => '',
            'USO_BANCO_90' => '',
            'CODIGO_FINALIDADE_TED' => '',
            'USO_BANCO_92' => '',
            'USO_BANCO_93' => '',
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
            'BEN_ENDERECO_LOGRADOURO' => '',
            'BEN_ENDERECO_NUMERO' => '',
            'BEN_ENDERECO_COMPLEMENTO' => '',
            'BEN_ENDERECO_BAIRRO' => '',
            'BEN_ENDERECO_CIDADE' => '',
            'BEN_ENDERECO_CEP' => '',
            'BEN_ENDERECO_ESTADO' => '',
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
         * Monta o txt analisando as configs. caso tenha que buscar algo no banco de dados, faz a pesquisa
         * atravez do arrau mapped. Caso contrario busca o valor default da configuração
         *
         */
        $txt_data = "";
        //$txt_data = $this->mountTxt($header1,$mappedHeader1, $txt_data); // Monta o header 1

        //$txt_data.= "\n"; //Acrescenta uma quebra de linha

        //$txt_data = $this->mountTxt($header2,$mappedHeader2, $txt_data); // Monta o header 2

        //$txt_data.= "\n"; //Acrescenta uma quebra de linha
        foreach ($registrations as $key_registrations => $registration) {

            $txt_data = $this->mountTxt($detahe1,$mappedDeletalhe1, $txt_data, $registration); // Monta o detalhe 1

            $txt_data.= "\n"; //Acrescenta uma quebra de linha

            $txt_data = $this->mountTxt($detahe2,$mappedDeletalhe2, $txt_data, $registration); // Monta o detalhe 2
            $txt_data.= "\n"; //Acrescenta uma quebra de linha
        
        }
        //$txt_data.= "\n"; //Acrescenta uma quebra de linha

        //$txt_data = $this->mountTxt($trailer1,$mappedTrailer1, $txt_data); // Monta o trailer 1

        //$txt_data.= "\n"; //Acrescenta uma quebra de linha

        //$txt_data = $this->mountTxt($trailer2,$mappedTrailer2, $txt_data); // Monta o trailer 2
        

        
        
      

        /**
         * cria o arquivo no servidor e insere o conteuto da váriavel $txt_data
         */
        $file_name = 'inciso1-cnab240-' . md5(json_encode($txt_data)) . '.txt';

        //$dir = PRIVATE_FILES_PATH . 'aldirblanc/inciso1/remessas/cnab240/';
        $dir = __DIR__.'/../txt/';

        $patch = $dir . $file_name;

        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }

        $stream = fopen($patch, 'w');

        fwrite($stream, $txt_data);

        fclose($stream);

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

        $value['default'] = Normalizer::normalize( $value['default'], Normalizer::FORM_D );
        $value['default'] = preg_replace('/[^a-z0-9]/i', '', $value['default']);

        if ($type === 'int') {
            $data .=  str_pad($value['default'], $length, '0', STR_PAD_LEFT);
        } else {
            $data .= str_pad($value['default'], $length, " ", STR_PAD_BOTH);
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
        ];

        $return = "";
        foreach ($bankList as $key => $value) {
            if ($key == ucwords(strtolower($bankName))) {
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
    private function mountTxt($array, $mapped, $txt_data, $register)
    {
        foreach($array as $key => $value){

            if($value['field_id']){
                if(is_callable($mapped[$key])){
                    $data = $mapped[$key];   
                    $value['default'] = $data($register);
                    $value['field_id'] = null;
                    $txt_data.= $this->createString($value);
                    $value['default'] = null;
                    $value['field_id'] = $value['field_id'];
                }
                
            }else{
                $txt_data.= $this->createString($value);
            }
            
         
        
        }   
        
       
        return $txt_data;
        
        
    }

}
