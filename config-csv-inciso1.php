<?php

/**
 * Arquivo de configuração para o exportador dataprev no inciso 1
 * 
 * O Exportador do inciso 1 utiliza o field_id dos campos para fazer a busca dos valores na base de dados na maiora dos casos, 
 * exeto para os campos abaixo. 
 * 
 * A forma mais fácil de se conseguir o field_id de um campo, é olhando o formulário. Ao lado do nome do campo existe 
 * algo do tipo (#52 INSCRIÇÃO EM CADASTRO CULTURAL:). O número 52 cidado nesse explo, se trata do ID do campo, então basta concatenar
 * o texto field_ a esse ID, tendo como resultado no nosso exemplo, (field_52)
 * 
 * Apenas marque 1 nos campos que lhe se enquadrem na sua situação. lembramos que a base Mapas Culturais é uma mase homologada 
 * e pode ser usada para esse caso. Para isso marque 1 em FLAG_CAD_ESTADUAL
 * 
 * FLAG_CAD_ESTADUAL
 * FLAG_CAD_MUNICIPAL
 * FLAG_CAD_DISTRITAL
 * FLAG_CAD_SNIIC
 * 
 * Caso os campos acima sejam flegados como 1, os respectivos campos abaixo se preenchem automáticamente, como nos exemplos.
 * Então basta manter os mesmos como null aqui no arquivo de configuração.
 * 
 * SISTEMA_CAD_ESTADUAL = Nome do site ex.: (Mapa Cultural de São Paulo)
 * IDENTIFICADOR_CAD_ESTADUAL =  Numero da inscrição ex.: xx-47267711
 * 
 * SISTEMA_CAD_MUNICIPAL Nome do site ex.: (Mapa Cultural de São Paulo)
 * IDENTIFICADOR_CAD_MUNICIPAL Numero da inscrição ex.: xx-47267711
 * 
 * SISTEMA_CAD_DISTRITAL Nome do site ex.: (Mapa Cultural de São Paulo)
 * IDENTIFICADOR_CAD_DISTRITAL Numero da inscrição ex.: xx-47267711
 * 
 * INFORMAÇÕES IMPORTANTES 
 * 
 * 1) O array (atuacoes-culturais) deve conter todos os valores das ações culturais existentes no formulário, separadas entre as categorias abaixo
 * - artes-cenicas
 * - artes-visuais
 * - audiovisual
 * - humanidades
 * - museu-memoria
 * - musica
 * - patrimonio-cultural
 * 
 */

return [ //Configuração dos campos
    "CPF" => '',
    "SEXO" => '',
    "FLAG_CAD_ESTADUAL" => '',
    "FLAG_CAD_MUNICIPAL" => '',
    "FLAG_CAD_DISTRITAL" => '',
    "FLAG_CAD_SNIIC" => '',
    "FLAG_CAD_SALIC" => '',
    "FLAG_CAD_SICAB" => '',
    "FLAG_CAD_OUTROS" => '',
    "SISTEMA_CAD_OUTROS" => '',
    "IDENTIFICADOR_CAD_OUTROS" => '',
    "FLAG_ATUACAO_ARTES_CENICAS" => "",
    "FLAG_ATUACAO_AUDIOVISUAL" => '',
    "FLAG_ATUACAO_MUSICA" => '',
    "FLAG_ATUACAO_ARTES_VISUAIS" => '',
    "FLAG_ATUACAO_PATRIMONIO_CULTURAL" => '',
    "FLAG_ATUACAO_MUSEUS_MEMORIA" => '',
    "FLAG_ATUACAO_HUMANIDADES" => '',
    "FAMILIARCPF" => '',
    "GRAUPARENTESCO" => '',
    "parameters_csv_default" => [
        "status" => ''
    ],
    'atuacoes-culturais' => [ // Opções para área de atuações culturais
        'artes-cenicas' => [],
        'audiovisual' => [],
        'musica' => [],
        'artes-visuais' => [],
        'patrimonio-cultural' => [],
        'museu-memoria' => [],
        'humanidades' => [],
    ],
    'herder_layout' => [ // Header padronizado segundo instruções do documento dataprev
        'REQUERENTE_CPF',
        'REQUERENTE_NOME',
        'REQUERENTE_SEXO',
        'REQUERENTE_DATA_NASCIMENTO',
        'REQUERENTE_CPF_SITUACAO',
        'DATA_CADASTRO',
        'ENTE_FEDERATIVO_CNPJ',
        'ENTE_FEDERATIVO_NOME',
        'SITUACAO_CADASTRO',
        'DATA_PROCESSAMENTO',
        'NM_ARQUIVO_LOTE',
        'SEGMENTO_ARTES_CENICAS',
        'SEGMENTO_ATUACAO_MUSICA',
        'SEGMENTO_ARTES_PATRIMONIO',
        'SEGMENTO_ARTES_HUMANI',
        'SEGMENTO_AUDIOVISUAL',
        'SEGMENTO_ARTES_VISUAIS',
        'SEGMENTO_MUSEUS_MEMORIAS',
        'IN_CAD_ESTAD_CULT',
        'SISTEMA_CAD_ESTAD_CULT',
        'IDENTIF_CAD_ESTAD_CULT',
        'IN_CAD_MUNIC_CULT',
        'SISTEMA_CAD_MUNIC_CULT',
        'IDENTIF_CAD_MUNIC_CULT',
        'IN_CAD_DISTR_CULT',
        'SISTEMA_CAD_DISTR_CULT',
        'IDENTIF_CAD_DISTR_CULT',
        'IN_SNIIC',
        'SISTEMA_SNIIC',
        'IDENTIF_SNIIC',
        'IN_SICAB',
        'IN_SALIC',
        'IN_OUTROS_CAD',
        'SISTEMA_OUTROS_CAD',
        'IDENTIF_OUTROS_CAD',
        'IN_EMPREGO_FORMAL_ATIVO',
        'IN_RGPS',
        'IN_RPPS',
        'IN_RAIS',
        'IN_SIAPE',
        'IN_INTERMITENTE',
        'IN_MANDATO_ELETIVO',
        'IN_MILITAR',
        'IN_TITULAR_BENEFICIO',
        'IN_SD',
        'IN_BENEF_INSS',
        'IN_AUX_EMERG_CIDAD',
        'IN_AUX_BEM',
        'IN_RENDA_GRUPO_FAMILIAR',
        'IN_RENDIM_TRIBUT_ACIMA_TETO',
        'IN_INSCRICAO_CADASTRO_CULT_HOMOLOG',
        'IN_INSCRIC_HOMOLOG_SICAB',
        'IN_INSCRIC_HOMOLOG_SALIC',
        'IN_OBITO',
        'IN_BRASILEIRO_NO_EXTERIOR',
        'IN_DETENTO_REG_FECHADO',
        'IN_PROCURADO_MJ',
        'IN_POLITICAMENTE_EXPOSTO',
        'IN_FAM_SOLIC_AUXILIO',
        'LISTA_FAM_SOLIC_AUXILIO',
        'IN_FAM_RECEB_AUXILIO',
        'LISTA_FAM_RECEB_AUXILIO',
        'IN_MULH_PROV_MONOPARENT',
        'IND_MONOPARENTAL_OUTRO_REQUERIMENTO',
        'CPF_OUTRO_REQUERENTE_CONJUGE_INFORMADO',
        'DATA_REPROCESSAMENTO',
    ],
    'acceptance_parameters' => [ // Parametros de aceitação planilha

        'REQUERENTE_DATA_NASCIMENTO' => [
            'positive' => [
                18, //No arquivo DataPrev.php é validado se o requerente é maior ou igual a 18 anos de idade
            ],
            'response' => 'O(A) requerente é menor de 18 anos',
            'impediment' => true,
        ],
        'REQUERENTE_CPF_SITUACAO' => [
            'positive' => [
                0,
            ],
            'negative' => [
                2,
                3,
                4,
                5,
                8,
                9,
            ],
            'response' => 'O requerente não está com o CPF regular perante à Secretaria da Receita Federal do Brasil - SRFB',
            'impediment' => true,
        ],
        'IN_CAD_ESTAD_CULT' => [
            'positive' => [
                'Sim',
            ],
            'negative' => [
                'Não',
            ],
            'response' => 'O requerente não comprova homologação em nenhum dos cadastros previstos no § 1º do art. 7º da Lei nº 14.017, de 2020.',
            'impediment' => true,

        ],
        'IN_SICAB' => [
            'positive' => [
                'sim',
            ],
            'negative' => [
                'Não',
            ],
            'response' => 'O requerente não comprova homologação em nenhum dos cadastros previstos no § 1º do art. 7º da Lei nº 14.017, de 2020.',
            'impediment' => true,

        ],
        'IN_SALIC' => [
            'positive' => [
                'Sim',
            ],
            'negative' => [
                'Não',
            ],
            'response' => 'O requerente não comprova homologação em nenhum dos cadastros previstos no § 1º do art. 7º da Lei nº 14.017, de 2020.',
            'impediment' => true,

        ],
        'IN_OBITO' => [
            'positive' => [
                'Não',
            ],
            'negative' => [
                'Sim',
            ],
            'response' => 'O requerente encontra-se inscrito no Sistema de Controle de Óbitos (SISOBI).',
            'impediment' => true,

        ],
        'IN_BRASILEIRO_NO_EXTERIOR' => [
            'positive' => [
                'Não',
            ],
            'negative' => [
                'Sim',
            ],
            'response' => 'O requerente não reside e nem está domiciliado no território nacional, conforme determina o § 2º do Art. 2º do Decreto nº 10.464/2020.',
            'impediment' => true,

        ],
        'IN_DETENTO_REG_FECHADO' => [
            'positive' => [
                'Não',
            ],
            'negative' => [
                'Sim',
            ],
            'response' => 'O requerente possui impedimento legal para o recebimento deste benefício (Cód.190dt).',
            'impediment' => true,
        ],
        'IN_PROCURADO_MJ' => [
            'positive' => [
                'Não',
            ],
            'negative' => [
                'Sim',
            ],
            'response' => 'O requerente possui impedimento legal para o recebimento deste benefício. (Cód.190pr).',
            'impediment' => true,
        ],
        'SITUACAO_CADASTRO' => [
            'positive' => [
                2,
            ],
            'negative' => [
                1,
                3,
                4,
                5,
                6,
                7,
                8,
            ],
            'response' => [
                1 => 'Não processado',
                3 => 'Aguardando reprocessamento',
                4 => 'Reprocessado,',
                5 => 'Cancelado',
                6 => 'Pagamento confirmado',
                7 => 'Prestação de Contas Confirmada',
                8 => 'Retido para avaliação',
            ],
            'impediment' => true,
        ],
        'IN_EMPREGO_FORMAL_ATIVO' => [
            'positive' => [
                'Não',
            ],
            'negative' => [
                'Sim',
            ],
            'response' => 'No preenchimento do Formulário de Inscrição, o requerente não atendeu ao Inciso II do Art. 6º da Lei nº 14.017/2020, e ao Inciso II do Art. 4º do Decreto 10.464/2020 conforme respectivo § 2º.',
            'impediment' => true,

        ],
        'IN_MANDATO_ELETIVO' => [
            'positive' => [
                'Não',
            ],
            'negative' => [
                'Sim',
            ],
            'response' => 'No preenchimento do Formulário de Inscrição, o requerente não atendeu ao Inciso II do Art. 4º do Decreto 10.464/2020 conforme respectivo § 2º. ',
            'impediment' => true,

        ],
        'IN_SD' => [
            'positive' => [
                'Não',
            ],
            'negative' => [
                'Sim',
            ],
            'response' => 'No preenchimento do Formulário de Inscrição, o requerente não atendeu ao Inciso III do Art. 6º da Lei nº 14.017/2020, e ao Inciso III do Art. 4º do Decreto 10.464/2020.',
            'impediment' => true,

        ],
        'IN_BENEF_INSS' => [
            'positive' => [
                'Não',
            ],
            'negative' => [
                'Sim',
            ],
            'response' => 'No preenchimento do Formulário de Inscrição, o requerente não atendeu ao Inciso III do Art. 6º da Lei nº 14.017/2020, e ao Inciso III do Art. 4º do Decreto 10.464/2020.',
            'impediment' => true,

        ],
        'IN_AUX_BEM' => [
            'positive' => [
                'Não',
            ],
            'negative' => [
                'Sim',
            ],
            'response' => 'No preenchimento do Formulário de Inscrição, o requerente não atendeu ao Inciso III do Art. 6º da Lei nº 14.017/2020, ao Inciso III do Art. 4º do Decreto 10.464/2020 e em seu Caput.',
            'impediment' => true,

        ],
        'IN_RENDA_GRUPO_FAMILIAR' => [
            'positive' => [
                'Não',
            ],
            'negative' => [
                'Sim',
            ],
            'response' => 'No preenchimento do Formulário de Inscrição, o requerente não atendeu ao Inciso IV do Art. 6º da Lei nº 14.017/2020, e ao Inciso IV do Art. 4º do Decreto 10.464/2020.',
            'impediment' => true,

        ],
        'IN_RENDIM_TRIBUT_ACIMA_TETO' => [
            'positive' => [
                'Não',
            ],
            'negative' => [
                'Sim',
            ],
            'response' => 'No preenchimento do Formulário de Inscrição, o requerente não atendeu ao Inciso V do Art. 6º da Lei nº 14.017/2020, e ao Inciso V do Art. 4º do Decreto 10.464/2020.',
            'impediment' => true,

        ],
        'IN_AUX_EMERG_CIDAD' => [
            'positive' => [
                'Não',
                'Bloqueado',
            ],
            'negative' => [
                'Sim',
            ],
            'response' => 'No preenchimento do Formulário de Inscrição, o requerente não atendeu ao Inciso VII do Art. 6º da Lei nº 14.017/2020, e ao Inciso VII do Art. 4º do Decreto 10.464/2020. No preenchimento do Formulário de Inscrição, o requerente não atendeu ao Inciso III do Art. 6º da Lei nº 14.017/2020, e ao Inciso III do Art. 4º do Decreto 10.464/2020.',
            'impediment' => true,

        ],
        'IN_FAM_RECEB_AUXILIO' => [
            'positive' => [
                'Não',
            ],
            'negative' => [
                'Sim',
            ],
            'response' => 'No preenchimento do Formulário de Inscrição, o requerente não atendeu ao § 1º do Inciso VII do Art. 6º da Lei nº 14.017/2020, e ao Inciso I do Art. 3º do Decreto nº 10.464/2020. ',
            'impediment' => true,

        ],
        'IN_MULH_PROV_MONOPARENT' => [
            'positive' => [
                'Não',
            ],
            'negative' => [
                'Sim',
            ],
            'response' => 'No preenchimento do Formulário de Inscrição, o requerente não atendeu ao § 2º do Art. 6º da Lei 14.017/2020 e ao Inciso II do Art. 3º do Decreto nº 10.464/2020.',
            'impediment' => true,

        ],
        'IND_MONOPARENTAL_OUTRO_REQUERIMENTO' => [
            'positive' => [
                'Não',
            ],
            'negative' => [
                'Sim',
            ],
            'response' => 'No preenchimento do Formulário de Inscrição, o requerente não atendeu ao § 2º do Art. 6º da Lei 14.017/2020 e ao Inciso II do Art. 3º do Decreto nº 10.464/2020.',
            'impediment' => true,

        ],
        'CPF_OUTRO_REQUERENTE_CONJUGE_INFORMADO' => [
            'positive' => [
                'Não',
            ],
            'negative' => [
                'Sim',
            ],
            'response' => 'No preenchimento do Formulário de Inscrição, o requerente não atendeu ao § 2º do Art. 6º da Lei 14.017/2020 e ao Inciso II do Art. 3º do Decreto nº 10.464/2020.',
            'impediment' => true,

        ],
        'IN_POLITICAMENTE_EXPOSTO' => [
            'positive' => [
                'Não',
            ],
            'negative' => [
                'Sim',
            ],
            'response' => '',
            'impediment' => true,

        ],
    ],
    'validation_reference' => [ // Array de comparação que se é esperado após a verificação dos dados
        'REQUERENTE_DATA_NASCIMENTO' => true,
        'REQUERENTE_CPF_SITUACAO' => true,
        'VALIDA_CAD_CULTURAL' => true,
        'IN_OBITO' => true,
        'IN_BRASILEIRO_NO_EXTERIOR' => true,
        'IN_DETENTO_REG_FECHADO' => true,
        'IN_PROCURADO_MJ' => true,
        'SITUACAO_CADASTRO' => true,
        'IN_EMPREGO_FORMAL_ATIVO' => true,
        'IN_MANDATO_ELETIVO' => true,
        'IN_SD' => true,
        'IN_BENEF_INSS' => true,
        'IN_AUX_BEM' => true,
        'IN_RENDA_GRUPO_FAMILIAR' => true,
        'IN_RENDIM_TRIBUT_ACIMA_TETO' => true,
        'IN_AUX_EMERG_CIDAD' => true,
        'IN_FAM_RECEB_AUXILIO' => true,
        'IN_MULH_PROV_MONOPARENT' => true,
        'IND_MONOPARENTAL_OUTRO_REQUERIMENTO' => true,
        'CPF_OUTRO_REQUERENTE_CONJUGE_INFORMADO' => true,
        'IN_POLITICAMENTE_EXPOSTO' => true,

    ],
    'validation_cad_cultural' => [ //Faz a verificação se o requerente esta incrito em um dos cadastros culturais listados nesse array
        'IN_CAD_ESTAD_CULT',
        'IN_CAD_MUNIC_CULT',
        'IN_CAD_DISTR_CULT',
        'IN_SNIIC',
        'IN_INSCRICAO_CADASTRO_CULT_HOMOLOG',
        'IN_INSCRIC_HOMOLOG_SICAB',
        'IN_INSCRIC_HOMOLOG_SALIC',
        'IN_SICAB',
        'IN_SALIC',
    ],

];
