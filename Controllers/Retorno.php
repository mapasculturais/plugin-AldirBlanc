<?php

namespace AldirBlanc\Controllers;

// use DateTime;
use Exception;
use League\Csv\Reader;
use League\Csv\Statement;
use MapasCulturais\App;
use RegistrationPayments\Payment;
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

class Retorno extends \MapasCulturais\Controllers\Registration
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
     * 
     * Importa na tabela `payment` as informções de retorno sobre os pagamentos
     * 
     * O CSV retorna dois RETORNOS, sendo 00 para pagamento efetuado e 99 para pagamento não efetuado.
     * O arquivo de importação (em formato CSV) deve ser adicionado na pasta `PRIVATE_FILES_PATH` e seu nome informado na variável de ambiente `AB_CSV_RETORNO`
     * Endpoint de acesso /retorno/genericReturnImport
     * 
     * Funcionamento:
     * Busca por pagamentos na tabela `payment` através do ID da inscrição recebida pelo CSV.
     * Se o status do registro for diferente de 10, verifica se o RETORNO é 00 e adiciona no campo metadata da tabela as informações RETORNO, AUTENTICAÇÃO e MENSAGEM. E muda o status para 10.
     * Se o RETORNO for 99, adiciona as informações RETORNO e MENSAGEM. E muda o status para 2.
     *
     * @return void
     * 
     */
    public function ALL_genericReturnImport() {

        // Verifica se o usuário está autenticado
        $this->requireAuthentication();
        $app = App::i();

        // Seta o timeout e limite de memória
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '768M');

        // Localização do CSV para importação
        $filepath = PRIVATE_FILES_PATH;
        $filename = $this->config['csv_retorno'];
        
        // Verifica se o arquivo está no servidor
        if (!file_exists($filepath . $filename) || empty($filepath . $filename)) {
            throw new Exception("Erro ao processar o arquivo. Arquivo inexistente");
        }

        // Abre o arquivo em modo de leitura
        $stream = fopen($filepath . $filename, "r");

        // Faz a leitura do arquivo
        $csv = Reader::createFromStream($stream);

        // Define o limitador do arqivo (, ou ;)
        $csv->setDelimiter(";");

        // Seta em que linha deve se iniciar a leitura
        $header_temp = $csv->setHeaderOffset(0);

        // Faz o processamento dos dados
        $stmt = (new Statement());

        $results = $stmt->process($csv);

        // Verifica a extenção do arquivo
        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        if ($ext != "csv") {
            throw new Exception("Formato de arquivo não permitido.");
        }

        // Verifica se o arquivo esta dentro layout
        foreach ($header_temp as $key => $value) {
            $header_file[] = $value;
            break;
        }

        foreach ($header_file[0] as $key => $value) {
            $header_line_csv[] = $key;
        }

        // Verifica se o layout do arquivo esta nos padroes esperados pelo importador
        $header_layout = [
            'CPF_CNPJ',
            'INSCRICAO_ID',
            'RETORNO',
            'AUTENTICACAO',
            'MENSAGEM'
        ];

        if ($error_layout = array_diff_assoc($header_layout, $header_line_csv)) {
            throw new Exception("Os campos " . json_encode($error_layout) . " estão divergentes do layout necessário.");
        }

        $count = 0;

        foreach ($results as $result) {

            $payment = $app->em->getRepository('\\RegistrationPayments\\Payment')->findOneBy([
                'registration' => preg_replace("/[^0-9]/", "", $result['INSCRICAO_ID'])
            ]);
        
            if ($result['RETORNO'] == '00' || $result['RETORNO'] == '0') {

                if (isset($payment)) {

                    if ($payment->status != 10) {
                        $count++;

                        // Verifica se o metadata não é um array
                        if (!is_array($payment->metadata)) {
                            $metadata = json_decode($payment->metadata);
                        }

                        $metadata = $payment->metadata;

                        $metadata['retorno'] = [
                            'RETORNO'      => $result['RETORNO'],
                            'AUTENTICACAO' => $result['AUTENTICACAO'],
                            'MENSAGEM'     => $result['MENSAGEM']
                        ];

                        $payment->metadata = $metadata;

                        // Altera o status do pagamento para STATUS_PAID = 10
                        $payment->status = 10;

                        $payment->save(true);

                        // Imprime log informando que o pagamento foi efetuado
                        $app->log->info('#' . $count . ' >> ' . $payment->registration->number . ' - pagamento efetuado');
                    }

                } else {
                    $app->log->info('#### >> ' . $result['INSCRICAO_ID'] . ' - não encontrado na tabela de pagamentos');
                }

            } elseif ($result['RETORNO'] == '99') {

                if (isset($payment)) {

                    if ($payment->status != 10) {

                        $count++;

                        // Verifica se o metadata não é um array
                        if (!is_array($payment->metadata)) {
                            $metadata = json_decode($payment->metadata);
                        }

                        $metadata = $payment->metadata;

                        // Adiciona os valores do CSV no metadata
                        $metadata['retorno'] = [
                            'RETORNO' => $result['RETORNO'],
                            'MENSAGEM' => $result['MENSAGEM']
                        ];

                        $payment->metadata = $metadata;

                        // Altera o status do pagamento para STATUS_FAILED = 2
                        $payment->status = 2;

                        $payment->save(true);

                        // Imprime log informando que o pagamento falhou
                        $app->log->info('#' . $count . ' >> ' . $payment->registration->number . ' - pagamento recusado');
                    }

                } else {
                    $app->log->info('#### >> ' . $result['INSCRICAO_ID'] . ' - não encontrado na tabela de pagamentos');
                }

            }

        }

        $app->log->info('Arquivo processado com sucesso! ' . $count . ' pagamentos foram processados');

    }

}