<?php
/**
 * Explicação da estrutura de cada campo
 * 1) - length = A quantidade de caracteres que esse registro deve ter no TXT. 
 *      Caso seja um registro do tipo numérico e esse registro tenha uma quantidade menor que a quantidade de caracteres exigida, deve se completar com zeros a esquerda
 *      Caso Sejaum registro do tipo texto esse registro tenha uma quantidade menor que a quantidade de caracteres exigida, deve se completar com espaços em branco a direita.
 * 
 * 
 * 2) - default = (Um valor fixo, que será exibido diretamente no TXT)
 * 
 * 
 * 3) - field_id = Caso um registro estaja na base de dados, deve informar aqui o field_id onde se deve buscar os registros. 
 *      OBS.: Caso use o field_id, favor setar como null o campo default
 * 
 * 
 * 4) - type = tipo de dado que sera inserido. 
 *      int = a numérico 
 *      string = texto
 * 
 * OBSERVAÇÕES
 * - Os campos textos, não deve conter quaqlquer tipo de caracter especial ou acentuação. 
 *   Já está sendo feito um tratamento para isso, porem sempre se atentar no arquivo TXT para que o mesmo nao seja recusado pelo banco
 * 
 * - O Fromato de data e hora, deve ser na sequência
 *   dia/mes/ano = 01/01/2020. => No arquivo a formatação não deve conter caracteres especiais.
 *   hora : minutos : segundos = 00:00:00 => No arquivo a formatação não deve conter caracteres especiais.
 */
return [
    'HEADER1' => [
        'BANCO' => [ // Numero do banco da entidade pagadora, deve ser confirmado de qual banco sairá o recurso, casso seja Banco Do Brasil seria 001
            'length' => 3,
            'default' => '001',
            'field_id' => null,
            'type' => 'int',
        ],
        'LOTE' => [ // Valor default segundo planilha = 0000
            'length' => 4,
            'default' => '0000',
            'field_id' => null,
            'type' => 'int',
        ],
        'REGISTRO' => [ //Valor default segundo planilha = 0
            'length' => 1,
            'default' => '0',
            'field_id' => null,
            'type' => 'int',
        ],
        'USO_BANCO_12' => [// Inserido 9 espaços vazios
            'length' => 9,
            'default' => null,
            'field_id' => null,
            'type' => 'string',
        ],
        'INSCRICAO_TIPO' => [ // CPF ou CNPJ da fonte pagadora (1 = CPF ou 2 = CNPJ) deve ser perguntado ao banco
            'length' => 1,
            'default' => '2',
            'field_id' => null,
            'type' => 'int',
        ],
        'CPF_CNPJ_FONTE_PAG' => [ //CPF ou CNPJ da fonte pagadora, colocar com base no que foi informado no campo INSCRICAO_TIPO
            'length' => 14,
            'default' => null,
            'field_id' => null,
            'type' => 'int',
        ],
        'CONVENIO_BB1' => [ // Número do convênio da fonte pagadora junto ao BB. Deve-ser verificar com secretaria da cultura
            'length' => 9,
            'default' => null,
            'field_id' => null,
            'type' => 'int',
        ],
        'CONVENIO_BB2' => [ // Campo deve ser por defalt 0126, como orientado no particularidades BB
            'length' => 4,
            'default' => '0126',
            'field_id' => null,
            'type' => 'int',
        ],
        'CONVENIO_BB3' => [ // Campo defalt null, nesse caso será inserido 5 espaços em branco conforme orientação particularidades BB
            'length' => 5,
            'default' => null,
            'field_id' => null,
            'type' => 'string',
        ],
        'CONVENIO_BB4' => [ // Campo dedicado a testes, Em teoria quando um arquivo for enviado ao BB como TESTE deve-se insetir as letras TS exite opção na janela de exportação para informar se o arquivo é teste ou não 
            'length' => 2,
            'default' => null,
            'field_id' => 'mapped',
            'type' => 'string',
        ],
        'AGENCIA' => [ //Agência bancária de fonte pagadora
            'length' => 5,
            'default' => null,
            'field_id' => 'mapped',
            'type' => 'int',
        ],
        'AGENCIA_DIGITO' => [ //Dígito da agência bancária da fonte pacadora
            'length' => 1,
            'default' => '8',
            'field_id' => 'mapped',
            'type' => 'string',
        ],
        'CONTA' => [ //Conta bancária da fonte pagadora
            'length' => 12,
            'default' => null,
            'field_id' => 'mapped',
            'type' => 'int',
        ],
        'CONTA_DIGITO' => [ //Digito da conta bancária da fonte pagadora
            'length' => 1,
            'default' => '0',
            'field_id' => 'mapped',
            'type' => 'int',
        ],
        'USO_BANCO_20' => [ //Não usar, uso exclusivo do banco
            'length' => 1,
            'default' => null,
            'field_id' => null,
            'type' => 'int',
        ],
        'NOME_EMPRESA' => [ //Nome da fonte pagadora
            'length' => 30,
            'default' => null,
            'field_id' => null,
            'type' => 'string',
        ],
        'NOME_BANCO' => [ //Nome do banco que fará o pagamento. Nesse caso por default será BANCO DO BRASIL S. A.
            'length' => 30,
            'default' => 'BANCO DO BRASIL S. A.',
            'field_id' => null,
            'type' => 'string',
        ],
        'USO_BANCO_23' => [ //Não usar, uso exclusivo do banco
            'length' => 10,
            'default' => null,
            'field_id' => null,
            'type' => 'string',
        ],
        'CODIGO_REMESSA' => [ // Parametro que define o tipo do arquivo (Arquivo Remessa = '1', Arquivo Retorno = '2')
            'length' => 1,
            'default' => '1',
            'field_id' => null,
            'type' => 'int',
        ],
        'DATA_GER_ARQUIVO' => [ // Data de geração do arquivo dado mapeado, será inserido a data corrente do dia da geração do arquivo.
            'length' => 8,
            'default' => null,
            'field_id' => 'mapped',
            'type' => 'int',
        ],
        'HORA_GER_ARQUIVO' => [ //Horario de geração do arquivo, dado mapeado, será inserido o horário corrente do dia da geração do arquivo.
            'length' => 6,
            'default' => null,
            'field_id' => 'mapped',
            'type' => 'int',
        ],
        'NUM_SERQUNCIAL_ARQUIVO' => [ // nformação a cargo da empresa. O campo não é criticado pelo sistema do Banco do Brasil. Informar Zeros OU um número sequencial, incrementando a cada novo arquivo header de arquivo.
            'length' => 6,
            'default' => '000001',
            'field_id' => null,
            'type' => 'int',
        ],
        'LAYOUT_ARQUIVO' => [ // Por default sempre preencher com 030
            'length' => 3,
            'default' => '030',
            'field_id' => null,
            'type' => 'int',
        ],
        'DENCIDADE_GER_ARQUIVO' => [ // Por default preencher com 5 numeros zero
            'length' => 5,
            'default' => null,
            'field_id' => null,
            'type' => 'int',
        ],
        'USO_BANCO_30' => [ //Não usar, uso exclusivo do banco
            'length' => 54,
            'default' => null,
            'field_id' => null,
            'type' => 'string',
        ],
        'USO_BANCO_31' => [ //Não usar, uso exclusivo do banco
            'length' => 15,
            'default' => null,
            'field_id' => null,
            'type' => 'int',
        ],
    ],
    'HEADER2' => [
        'BANCO' => [ // Numero do banco que fará os pagamentos. Nesse caso 001 referente ao Banco do Brasil.
            'length' => 3,
            'default' => '001',
            'field_id' => null,
            'type' => 'int',
        ],
        'LOTE' => [
            'length' => 4,
            'default' => '0001', //Manter sempre 0001, o exportador ja faz o incremento a cada lote
            'field_id' => null,
            'type' => 'int',
        ],
        'REGISTRO' => [ //Manter por default 1, como informado no particularidades
            'length' => 1,
            'default' => '1',
            'field_id' => null,
            'type' => 'int',
        ],
        'OPERACAO' => [ //Manter por default C, como informado no particularidades
            'length' => 1,
            'default' => 'C',
            'field_id' => null,
            'type' => 'string',
        ],
        'SERVICO' => [ // para secretarias estaduais sempre deve ser 98 caso seja municípios deve ser confirmardo com bancon Ref.: Particularidade (Pagamento a Fornecedor = '20', Pagamento de Salário = '30', Pagamentos Diversos = '98')
            'length' => 2,
            'default' => '98',
            'field_id' => null,
            'type' => 'int',
        ],
        'FORMA_LANCAMENTO' => [
            'length' => 2,
            'default' => null, // A separação desse dado hoje ocorre de forma automática dúvidas analisar particularidades BB
            'field_id' =>  null,
            'type' => 'int',
        ],
        'LAYOUT_LOTE' => [ // Por default fomos orientados a deichar sempre 020, porem o campo não e criticado pelo BB segundo particularidades
            'length' => 3,
            'default' => '020',
            'field_id' => null,
            'type' => 'int',
        ],
        'USO_BANCO_43' => [ //Não usar, uso exclusivo do banco
            'length' => 1,
            'default' => null,
            'field_id' => null,
            'type' => 'string',
        ],
        'INSCRICAO_TIPO' => [ // CPF ou CNPJ da entidade pagadora (1 = CPF ou 2 = CNPJ) 
            'length' => 1,
            'default' => null,
            'field_id' => null,
            'type' => 'int',
        ],
        'INSCRICAO_NUMERO' => [ //CPF ou CNPJ da fonte pagadora, colocar com base no que foi informado no campo INSCRICAO_TIPO
            'length' => 14,
            'default' => null,
            'field_id' => null,
            'type' => 'int',
        ],        
        'CONVENIO_BB1' => [ // Número do convênio da fonte pagadora junto ao BB. Deve-ser verificar com secretaria da cultura
            'length' => 9,
            'default' => null,
            'field_id' => null,
            'type' => 'int',
        ],
        'CONVENIO_BB2' => [ // Campo deve ser por defalt 0126, como orientado no particularidades BB
            'length' => 4,
            'default' => '0126',
            'field_id' => null,
            'type' => 'int',
        ],
        'CONVENIO_BB3' => [ // Campo defalt null, nesse caso será inserido 5 espaços em branco conforme orientação particularidades BB
            'length' => 5,
            'default' => null,
            'field_id' => null,
            'type' => 'string',
        ],
        'CONVENIO_BB4' => [ // Campo dedicado a testes, Em teoria quando um arquivo for enviado ao BB como TESTE deve-se insetir as letras TS exite opção na janela de exportação para informar se o arquivo é teste ou não 
            'length' => 2,
            'default' => null,
            'field_id' => null,
            'type' => 'string',
        ],
        'AGENCIA' => [ //Agência bancária de fonte pagadora
            'length' => 5,
            'default' => null,
            'field_id' => 'mapped',
            'type' => 'int',
        ],
        'AGENCIA' => [ //Agência bancária de fonte pagadora
            'length' => 5,
            'default' => null,
            'field_id' => 'mapped',
            'type' => 'int',
        ],
        'AGENCIA_DIGITO' => [ //Dígito da agência bancária da fonte pacadora
            'length' => 1,
            'default' => '8',
            'field_id' => 'mapped',
            'type' => 'string',
        ],
        'CONTA' => [ //Conta bancária da fonte pagadora
            'length' => 12,
            'default' => null,
            'field_id' => 'mapped',
            'type' => 'int',
        ],
        'CONTA_DIGITO' => [ //Digito da conta bancária da fonte pagadora
            'length' => 1,
            'default' => '0',
            'field_id' => 'mapped',
            'type' => 'int',
        ],
        'USO_BANCO_51' => [ //Não usar, uso exclusivo do banco
            'length' => 1,
            'default' => null,
            'field_id' => null,
            'type' => 'int',
        ],
        'NOME_EMPRESA' => [ // Nome da fonte pagadora
            'length' => 30,
            'default' => null,
            'field_id' => 'mapped',
            'type' => 'string',
        ],
        'USO_BANCO_40' => [ //Não usar, uso exclusivo do banco
            'length' => 40,
            'default' => null,
            'field_id' => null,
            'type' => 'string',
        ],
        'LOGRADOURO' => [ // Logradouro do endereço da fonte pagadora
            'length' => 30,
            'default' => null,
            'field_id' => null,
            'type' => 'string',
        ],
        'NUMERO' => [ // Número do endereço da fonte pagadora
            'length' => 5,
            'default' => null,
            'field_id' => null,
            'type' => 'int',
        ],
        'COMPLEMENTO' => [ // Complemento do endereço da fonte pagadora
            'length' => 15,
            'default' => null,
            'field_id' => null,
            'type' => 'string',
        ],
        'CIDADE' => [ // Cidade do endereço da fonte pagadora
            'length' => 20,
            'default' => null,
            'field_id' => null,
            'type' => 'string',
        ],
        'CEP' => [  // CEP do endereço da fonte pagadora
            'length' => 8,
            'default' => null,
            'field_id' => null,
            'type' => 'int',
        ],
        'ESTADO' => [  // Estado do endereço da fonte pagadora
            'length' => 2,
            'default' => null,
            'field_id' => null,
            'type' => 'string',
        ],
        'USO_BANCO_60' => [  //Não usar, uso exclusivo do banco
            'length' => 8,
            'default' => null,
            'field_id' => null,
            'type' => 'string',
        ],
        'USO_BANCO_61' => [  //Não usar, uso exclusivo do banco
            'length' => 10,
            'default' => null,
            'field_id' => null,
            'type' => 'int',
        ],
    ],    
    'DETALHE1' => [
        'BANCO' => [ // Banco que faŕa o pagamento, nesse caso por default 001 que se refere ao Banco do Brasil 
            'length' => 3,
            'default' => '001',
            'field_id' => null,
            'type' => 'int',
        ],
        'LOTE' => [ //Informação de qual lote o registro pertence, deve-se colocar aqui nas configs sempre 0001, o incremento acontece no exportador
            'length' => 4,
            'default' => '0001',
            'field_id' => null,
            'type' => 'int',
        ],
        'REGISTRO' => [ // Por default manter sempre 3, por orientação do particularidades
            'length' => 1,
            'default' => '3',
            'field_id' => null,
            'type' => 'int',
        ],
        'NUMERO_REGISTRO' => [ // Manter aqui nas configs sempre 00001, o incremento acontece no exportador
            'length' => 5,
            'default' => '00001',
            'field_id' => null,
            'type' => 'int',
        ],
        'SEGMENTO' => [ // Por defaalt sempre manter A, por orientação do particularidades
            'length' => 1,
            'default' => 'A',
            'field_id' => null,
            'type' => 'string',
        ],
        'TIPO_MOVIMENTO' => [ // Nesse caso deve ser default 0 REF.: particularidades (Inclusão = '0', Exclusão = '9') 
            'length' => 1,
            'default' => '0',
            'field_id' => null,
            'type' => 'int',
        ],
        'CODIGO_MOVIMENTO' => [ // Nesse caso deve ser default 00 REF.: particularidades (Inclusão = '00', Exclusão = '99') 
            'length' => 2,
            'default' => '00',
            'field_id' => null,
            'type' => 'int',
        ],       
        'CAMARA_CENTRALIZADORA' => [ //Deixar por default o fild_id em mapped. Exportador se encarrega de processar
            'length' => 3,
            'default' => null,
            'field_id' => 'mapped',
            'type' => 'int',
        ],
        'BEN_CODIGO_BANCO' => [ // Field_id do campo da instituição bancária do beneficiário
            'length' => 3,
            'default' => null,
            'field_id' => null,
            'type' => 'int',
        ],
        'BEN_AGENCIA' => [ // Field_id  do campo da agência bancária do beneficiário
            'length' => 5,
            'default' => null,
            'field_id' => null,
            'type' => 'int',
        ],
        'BEN_AGENCIA_DIGITO' => [ // Field_id do campo do dígito da agência bancária do beneficiário
            'length' => 1,
            'default' => null,
            'field_id' => null,
            'type' => 'int',
        ],
        'BEN_CONTA' => [ // Field_id do campo da conta bancária do beneficiário
            'length' => 12,
            'default' => null,
            'field_id' => null,
            'type' => 'int',
        ],
        'BEN_CONTA_DIGITO' => [ // Field_id do campo do dígito da conta bancária do beneficiário
            'length' => 1,
            'default' => null,
            'field_id' => null,
            'type' => 'int',
        ],
        'BEN_DIGITO_CONTA_AGENCIA_80' => [ //Por default manter sempre como null
            'length' => 1,
            'default' => null,
            'field_id' => null,
            'type' => 'string',
        ],
        'BEN_NOME' => [ // Field_id do campo nome do beneficiário
            'length' => 30,
            'default' => null,
            'field_id' => null,
            'type' => 'string',
        ],
        'BEN_DOC_ATRIB_EMPRESA_82' => [ //por default sempre deixar null
            'length' => 20,
            'default' => null,
            'field_id' => null,
            'type' => 'string',
        ],
        'DATA_PAGAMENTO' => [ //Data que será realizada o pagamento, valor preencido automaticamente no exportador
            'length' => 8,
            'default' => null,
            'field_id' => 'mapped',
            'type' => 'int',
        ],
        'TIPO_MOEDA' => [ //Tipo de moeda usada, por default manter BRL
            'length' => 3,
            'default' => 'BRL',
            'field_id' => null,
            'type' => 'string',
        ],
        'USO_BANCO_85' => [ //Uso do banco, não utilizar
            'length' => 15,
            'default' => null,
            'field_id' => null,
            'type' => 'int',
        ],
        'VALOR_INTEIRO' => [ //Valor que será pago para o requerente, campo preenchido automáticamente no exportador
            'length' => 15,
            'default' => null,
            'field_id' => 'mapped',
            'type' => 'int',
        ],        
        'USO_BANCO_88' => [ //Uso do banco, não utilizar
            'length' => 20,
            'default' => null,
            'field_id' => null,
            'type' => 'string',
        ],
        'USO_BANCO_89' => [ //Uso do banco, não utilizar
            'length' => 23,
            'default' => null,
            'field_id' => null,
            'type' => 'int',
        ],
        'USO_BANCO_90' => [ //Uso do banco, não utilizar
            'length' => 42,
            'default' => null,
            'field_id' => null,
            'type' => 'string',
        ],
        'CODIGO_FINALIDADE_TED' => [ // preenchido automaticamente pelo exportador, (Contas corrente/poupança BB = especço em banco, outros bancos = 10)
            'length' => 5,
            'default' => null,
            'field_id' => 'mapped',
            'type' => 'string',
        ],
        'USO_BANCO_92' => [  //Uso do banco, não utilizar
            'length' => 5,
            'default' => null,
            'field_id' => null,
            'type' => 'string',
        ],
        'USO_BANCO_93' => [  //Uso do banco, não utilizar
            'length' => 11,
            'default' => null,
            'field_id' => null,
            'type' => 'int',
        ],
        'TIPO_CONTA' => [ // Field_id do campo que contenha o tipo de conta do benefíciario, (Corrente, Poupança)
            'length' => 11,
            'default' => null,
            'field_id' => null,
            'type' => 'int',
        ]
        

    ],
    'DETALHE2' => [
        'BANCO' => [ // banco que ferá o pagamento do benefício, nesse caso 001 que se refere ao Banco do Brasil
            'length' => 3,
            'default' => '001',
            'field_id' => null,
            'type' => 'int',
        ],
        'LOTE' => [ //por default manter sempre 0001, o incremento do lote e feito automaticamente no exportador
            'length' => 4,
            'default' => '0001',
            'field_id' => null,
            'type' => 'int',
        ],
        'REGISTRO' => [ //por default manter sempre 3 por orientação particularidades BB
            'length' => 1,
            'default' => '3',
            'field_id' => null,
            'type' => 'int',
        ],
        'NUMERO_REGISTRO' => [ //por default manter sempre 00001, o incremento do lote e feito automaticamente no exportador
            'length' => 5,
            'default' => '00001',
            'field_id' => null,
            'type' => 'int',
        ],
        'SEGMENTO' => [   //por default manter sempre B por orientação particularidades BB
            'length' => 1,
            'default' => 'B',
            'field_id' => null,
            'type' => 'string',
        ],
        'USO_BANCO_104' => [ //Uso do banco, não usar
            'length' => 3,
            'default' => null,
            'field_id' => null,
            'type' => 'string',
        ],
        'BEN_TIPO_DOC' => [ // Tipo de documento do requerente, CPF ou CNPJ, (1 = CPF, 2 = CNPJ). processo automatizado no exportador. Conta-se a quantidade de char do documento se for menor que 11 char, insere 1 no campo se for maisor insere 2
            'length' => 1,
            'default' => null,
            'field_id' => 'mapped',
            'type' => 'int',
        ],
        'BEN_CPF' => [ // Field_id do campo do CPF ou CNPJ do beneficiário, se atentar ao respondido no BEN_TIPO_DOC
            'length' => 14,
            'default' => null,
            'field_id' => null,
            'type' => 'int',
        ],
        'BEN_ENDERECO_LOGRADOURO' => [ // Field_id do campo do logradouro do beneficiário (Para se garantir o retorno do número da inscrção, fomos orientados pelo BB a inserir o número da inscrição nesse pnto. Caso queira o logradouro, remover o mapped do field_id e inserir o field_id do logradouro) LEMBRANDO QUE ESSA CAMPO NÃO É CRITICADO PELO BB
            'length' => 30,
            'default' => null,
            'field_id' => 'mapped',
            'type' => 'string',
        ],
        'BEN_ENDERECO_NUMERO' => [ // Field_id do campo do numero endereço do beneficiário
            'length' => 5,
            'default' => null,
            'field_id' => null,
            'type' => 'int',
        ],
        'BEN_ENDERECO_COMPLEMENTO' => [// Field_id do campo do complemento endereço do beneficiário
            'length' => 15,
            'default' => null,
            'field_id' => null,
            'type' => 'string',
        ],        
        'BEN_ENDERECO_BAIRRO' => [ // Field_id do campo do bairro do beneficiário
            'length' => 15,
            'default' => null,
            'field_id' => null,
            'type' => 'string',
        ],        
        'BEN_ENDERECO_CIDADE' => [ // Field_id do campo do cidade do beneficiário
            'length' => 20,
            'default' => null,
            'field_id' => null,
            'type' => 'string',
        ],
        'BEN_ENDERECO_CEP' => [ //Field_id do campo do CEP do beneficiário
            'length' => 8,
            'default' => null,
            'field_id' => null,
            'type' => 'int',
        ],
        'BEN_ENDERECO_ESTADO' => [ // Field_id do campo do estado do beneficiário
            'length' => 2,
            'default' => null,
            'field_id' => null,
            'type' => 'string',
        ],
        'USO_BANCO_114' => [ // usuo do banco, nao utilizar
            'length' => 83,
            'default' => null,
            'field_id' => null,
            'type' => 'int',
        ],
        'USO_BANCO_115' => [ // Autorizado também a inserir o número da inscrição. Porem sem garatias de retorno. Para inserir a inscrição deixar o field_id como mapped
            'length' => 15,
            'default' => null,
            'field_id' => 'mapped',
            'type' => 'string',
        ],
        'USO_BANCO_116' => [ // usuo do banco, nao utilizar
            'length' => 7,
            'default' => null,
            'field_id' => null,
            'type' => 'int',
        ],
        'USO_BANCO_117' => [ // usuo do banco, nao utilizar
            'length' => 8,
            'default' => null,
            'field_id' => null,
            'type' => 'string',
        ],
    ],
    'TRAILER1' => [
        'BANCO' => [ //Banco que fará o pagamento do benefício. Nesse caso 001 que faz referência ao Banco do Brasil
            'length' => 3,
            'default' => '001',
            'field_id' => null,
            'type' => 'int',
        ],
        'LOTE' => [ // Por default manter 0001, incremento acontece no exportador
            'length' => 4,
            'default' => '0001',
            'field_id' => null,
            'type' => 'int',
        ],
        'REGISTRO' => [ //Por default sempre manter 5, por orientação do particularidades BB
            'length' => 1,
            'default' => '5',
            'field_id' => null,
            'type' => 'int',
        ],
        'USO_BANCO_126' => [ // Uso do banco, nao utilizar
            'length' => 9,
            'default' => null,
            'field_id' => null,
            'type' => 'string',
        ],
        'QUANTIDADE_REGISTROS_127' => [ //Soma da quantidade de registro existentes no lote
            'length' => 6,
            'default' => null,
            'field_id' => null,
            'type' => 'int',
        ],
        'VALOR_TOTAL_DOC_INTEIRO' => [ //Soma do valor total a ser pago no lote
            'length' => 18,
            'default' => null,
            'field_id' => null,
            'type' => 'int',
        ],
        'USO_BANCO_130' => [// Uso do banco, nao utilizar
            'length' => 24,
            'default' => null,
            'field_id' => null,
            'type' => 'int',
        ],
        'USO_BANCO_131' => [// Uso do banco, nao utilizar
            'length' => 165,
            'default' => null,
            'field_id' => null,
            'type' => 'string',
        ],
        'USO_BANCO_132' => [// Uso do banco, nao utilizar
            'length' => 10,
            'default' => null,
            'field_id' => null,
            'type' => 'string',
        ],
    ],
    'TRAILER2' => [//Banco que fará o pagamento do benefício. Nesse caso 001 que faz referência ao Banco do Brasil
        'BANCO' => [
            'length' => 3,
            'default' => '001',
            'field_id' => null,
            'type' => 'int',
        ],
        'LOTE' => [ //Por default inserir 9999 por orientação do particularidades BB
            'length' => 4,
            'default' => '9999',
            'field_id' => null,
            'type' => 'int',
        ],
        'REGISTRO' => [ //Por default inserir 9 por orientação do particularidades BB
            'length' => 1,
            'default' => '9',
            'field_id' => null,
            'type' => 'int',
        ],
        'USO_BANCO_141' => [ //Uso do abnco, nao utilizar
            'length' => 9,
            'default' => null,
            'field_id' => null,
            'type' => 'string',
        ],
        'QUANTIDADE_LOTES-ARQUIVO' => [ //Soma total de lotes no arquivo. A separação e feita em no maximo 3 (c/c BB, C/poupança BB, Outros bancos). Então o numero maior será 3
            'length' => 6,
            'default' => null,
            'field_id' => null,
            'type' => 'int',
        ],
        'QUANTIDADE_REGISTROS_ARQUIVOS' => [ //Soma total de linhas do arquivo
            'length' => 6,
            'default' => null,
            'field_id' => null,
            'type' => 'int',
        ],       
        'USO_BANCO_144' => [ //Uso do banco, nao utilizar
            'length' => 6,
            'default' => null,
            'field_id' => null,
            'type' => 'int',
        ],
        'USO_BANCO_145' => [ //Uso do banco, nao utilizar
            'length' => 205,
            'default' => null,
            'field_id' => null,
            'type' => 'string',
        ]       
    ],   
    'parameters_default' => [
        'status' => 1,
        'defaultBank' => false, // caso exista banco padrão para pagamento, alterar flag => (true = sim, false = não);
        'informDefaultBank' => false, // Caso exista um banco padrão para pagamento, informar numero do banco aqui 
        'typesAccount' => [ //Tipos de contas existentes no formulário
            'corrente' => 'Conta corrente',
            'poupanca' => 'Conta poupança',
        ],
        'ducumentsType' => [  // Documentos que existirá(cnab240 = Bancarizados, unbanked = Desbancarizados)
            "cnab240" => true,
            "unbanked" => true 
        ],
        'selfDeclaredBB' => false, // Preencher esse campo, caso exista a pergunta se o requerente é correntista BB ou Não
        'formoReceipt' => null, // Campo para informar onde buscar opções de recebimento EX.: CARTEIRA DIGITAL BB ou CONTA BANCÁRIA NO BANCO DO BRASIL ABERTA PELA SECULT-ES
        'savingsDigit' => [
            '0' => '3',
            '1' => '4',
            '2' => '5',
            '3' => '6',
            '4' => '7',
            '5' => '8',
            '6' => '9',
            '7' => 'X',
            '8' => '0',
            '9' => '1',
            'X' => '2',
        ],
        'field_TipoConta' => null,// Field_id que busca o tipo de do benefíciario conta corrente ou poupança
        'field_banco' => null, // Field_id que busca o banco do benefpiciario
        'field_agency' => null,
        'fieldsWalletDigital' =>[ //Caso exista campos para carteira digital BB, inserir aqui o field_id 
            'agency' => false,
            'account' =>  false
        ],
        'monoParentIgnore' => true, //caso queira barrar o envio de pessoas monoparentais no arquivo, deixar setado com true, em outros casos setar false
        'womanMonoParent' => null,
        'fromToAccounts' => 'CSV/fromToAccounts.csv', // Caso exista um arquivo para captura de contas bancárioas, colocar o aqruivo na raiz AldirBlanc e passar o caminho aqui 
        'typesReceipt'=> [//Faz a separação de bancarizado e desbancarixado, informar segundo campos do formulário
            'banked' => [
                'Depósito bancário',
                'CARTEIRA DIGITAL BB'
            ],
            'unbanked' => [
                'Ordem de pagamento para saque nos caixas da rede 24H',
                'CONTA BANCÁRIA NO BANCO DO BRASIL ABERTA PELA SECULT-ES'
            ]
        ]
    ],
];