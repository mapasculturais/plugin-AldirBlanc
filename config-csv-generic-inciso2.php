<?php
/**
 * Implementa o exportador genérico no inciso 2
 * 
 * Para a configuração do exportador genérico no inciso 2, deve ser usado o nome do do campo para capturar os dados da base de dados.
 * 
 * Exemplos
 * 
 * 'fields' => [
 *        'CPF' => 'INSERIR_AQUI_NOME_DO_CAMPO',
 * ];
 * 
 * Alguns casos podemos passar mais de um campo, fazemos isso passando dentro de um Array
* 'fields' => [
 *        'CNPJ' => ['INSERIR_AQUI_NOME_DO_CAMPO1', 'INSERIR_AQUI_NOME_DO_CAMPO2'],
 * ];
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
    'fields' => [ 
        'CPF' => 'CPF',
        'NOME_SOCIAL' => '', 
        'CNPJ' => [],
        'RAZAO_SOCIAL' => [],                
        'LOGRADOURO' => '',
        'NUMERO' => '',
        'COMPLEMENTO' => '',
        'BAIRRO' => '',
        'MUNICIPIO' => '',
        'CEP' => '',
        'ESTADO' => '',
        'TELEFONE' => [],
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
    'parameters_default' => [], // <= Nao passar parametro algum
    'categories' => [ // <= Aconcelhado deixar como default, alterar somente caso as categorias do seu formulário mudar
        'CPF' => [
            'BENEFICIÁRIO COM CPF E ESPAÇO FÍSICO',
            'BENEFICIÁRIO COM CPF E SEM ESPAÇO FÍSICO'

        ],
        'CNPJ' => [
            'BENEFICIÁRIO COM CNPJ E ESPAÇO FÍSICO',
            'BENEFICIÁRIO COM CNPJ E SEM ESPAÇO FÍSICO'
        ]
        ],
       
        

];