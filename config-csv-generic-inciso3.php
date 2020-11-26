<?php
/**
 * Implementa o exportador genérico no inciso 3
 * 
 * Para a configuração do exportador genérico no inciso 3, deve ser usado o field_id do do campo para capturar os dados da base de dados.
 * 
 * Exemplos
 * 
 * 'fields' => [
 *        'CPF' => 'field_1098',
 * ];
 * 
 * Alguns casos podemos passar mais de um campo, fazemos isso passando dentro de um Array
* 'fields' => [
 *        'CNPJ' => ['field_1094', 'field_1120'],
 * ];
 * 
 * BOSERVAÇÕES
 * 1) Observe que no inciso 3, teremos um array para cada oportunidade. Todos os arrays irão conter os mesmos dados, diferenciando apenas os fields ids
 * 2) Observe tambem que a chave principal do array, contem o número da oportunidade.  
 */
return [
    'header' =>[       
        'CPF',
        'NOME_SOCIAL', 
        'CNPJ',
        'RAZAO_SOCIAL',             
        'LOGRADOURO',
        'NUMERO',
        'COMPLEMENTO',
        'BAIRRO',
        'MUNICIPIO',
        'CEP',
        'ESTADO',
        'TELEFONE',
        'NUM_BANCO',
        'AGENCIA_BANCO',
        'CONTA_BANCO',
        'VALOR',
        'INSCRICAO_ID',
        'INCISO',
    ],
    '4788' => [ // <= CHAVE PRINCIPAL, inserir aqui o número da oportunidade
        'TIPO_PROPONENTE' => '',        
        'CPF' => '',
        'NOME_SOCIAL' => [],    
        'CNPJ' => '',
        'RAZAO_SOCIAL' => '',                
        'LOGRADOURO' => '',
        'NUMERO' => '',
        'COMPLEMENTO' => '',
        'BAIRRO' => '',
        'MUNICIPIO' => '',
        'CEP' => '',
        'ESTADO' => '',
        'TELEFONE' => '',
        'NUM_BANCO' => '' ,
        'TIPO_CONTA_BANCO' => 0,        
        'AGENCIA_BANCO' => '',
        'CONTA_BANCO'  => '',
        'OPERACAO_BANCO'  => '',
        'VALOR' => '',        
        'INCISO' => '',
        'fromToAdress' => false, // <= Caso precise fazer alguma correção de endereço, inserir os dados no CSV que esta no plugin AldirBlanc dentro da pasta CSV, caso contrario deixar como false
        'fromToAccounts' => '/CSV/fromToAccounts.csv' // <= Caso precise fazer alguma correção de dados bancários, inserir os dados bancários no CSV que esta no plugin AldirBlanc dentro da pasta CSV
    ],
    'parameters_default' => [       
        'searchType' => 'field_id', // <= Valor default = field_id, Caso queira fazer a busca pelo nome do campo, colocar false nesse campo. Porem, caso feito isso nos arrays acima, deve ser informado o nome do campo ao invés do field id
        'proponentTypes' => [ // <= Informe aqui, os tipos de requerentes que existem no seu formulário. pessoa física, pessoa jurídica, Coletivo etc...
            'fisica' => 'Pessoa Física',
            'juridica' => 'Pessoa Jurídica',
            'coletivo' => 'Coletivo',
            'juridica-mei' => 'Pessoa Jurídica - MEI'
        ]
    ],
    
        

];