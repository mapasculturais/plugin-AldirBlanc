<?php

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
        //'TIPO_CONTA_BANCO',        
        'AGENCIA_BANCO',
        'CONTA_BANCO',
        //'OPERACAO_BANCO',

        'VALOR',
        'INSCRICAO_ID',
        'INCISO',
    ],
    'fields' => [        

        'CPF' => 'CPF',
        'NOME_SOCIAL' => 'Nome completo', 
        'CNPJ' => [
            'CNPJ',
            'CNPJ'
            ],
        'RAZAO_SOCIAL' => [
            'Razão social',
            'Razão Social'
        ],                
        'LOGRADOURO' => 'Endereço completo',
        'NUMERO' => 'Endereço completo',
        'COMPLEMENTO' => 'Endereço completo',
        'BAIRRO' => 'Endereço completo',
        'MUNICIPIO' => 'Endereço completo',
        'CEP' => 'Endereço completo',
        'ESTADO' => 'Endereço completo',
        'TELEFONE' => [
            'Telefone comercial',
            'Telefone'
        ],
        'NUM_BANCO' => 'Banco' ,
        'TIPO_CONTA_BANCO' => 0,        
        'AGENCIA_BANCO' => 'Agência',
        'CONTA_BANCO'  => 'Conta Corrente',
        'OPERACAO_BANCO'  => '',

        'VALOR' => '600',        
        'INCISO' => 1288,
    ],
    'parameters_default' => [
        'status' => '10'
    ],
    'categories' => [
        'CPF' => [
            'BENEFICIÁRIO COM CPF E ESPAÇO FÍSICO',

            'BENEFICIÁRIO COM CPF E SEM ESPAÇO FÍSICO'

        ],
        'CNPJ' => [
            'BENEFICIÁRIO COM CNPJ E ESPAÇO FÍSICO',
            'BENEFICIÁRIO COM CNPJ E SEM ESPAÇO FÍSICO'
        ]
    ]
        

];