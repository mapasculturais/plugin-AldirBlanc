<?php
/**
 * Arquivo de configuração para o exportador dataprev no inciso 2
 * 
 * O Exportador do inciso 2 utiliza o nome dos campos para fazer a busca dos valores na base de dados na maiora dos casos, 
 * exeto para os campos abaixo. 
 * 
 * Apenas marque 1 nos campos que lhe se enquadrem na sua situação. lembramos que a base Mapas Culturais é uma mase homologada 
 * e pode ser usada para esse caso. Para isso marque 1 em FLAG_CAD_ESTADUAL
 * 
 * FLAG_CAD_ESTADUAL
 * FLAG_CAD_MUNICIPAL
 * FLAG_CAD_DISTRITAL
 * FLAG_CAD_SNIIC
 * 
 * Caso so campos acima sejam flegados como 1, os respectivos campos abaixo se preenchem automáticamente como nos exemplos.
 * Então basta deixalos como null aqui no arquivo de configuração.
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
 * 1) No array (catergory), deve conter as categorias contidas no formulário do inciso 2, separadas em 2 em 2 chaves com_espaco_fisico
 * e sem_espaco_fisico
 * 
 * 2) No array (inscricoes_culturais), deve conter Os valores do campo INSCRIÇÃO EM CADASTRO CULTURAL do formilário. Caso esse não seja
 * o nome do campo, procure o campo que contenha os orgãos de cadastros culturais homologados e pegue as opções de respostas desse campo.
 * 
 * 3) O array (atuacoes-culturais) deve conter todos os valores das açoes culturais existentes no formulário separadas entre as categorias abaixo
 * - artes-cenicas
 * - artes-visuais
 * - audiovisual
 * - humanidades
 * - museu-memoria
 * - musica
 * - patrimonio-cultural
 * 
 * 4) Em caso de um determinado registro ser oriundo de vários campos com nomes diferentes, todos os campos que possam conter o valor devem 
 * ser colocados em um array, como por exemplo no campo NOME_ESPACO_CULTURAL citados nesse arquivo.
 */

return [
    "fields_cpf" => [ //Configuração para o arquivo CPF
        'CPF' => 'CPF DO RESPONSÁVEL PELO ESPAÇO CULTURAL, EMPRESA, ENTIDADE OU COOPERATIVA CULTURAL:',
        'SEXO' => 'SEXO DO RESPONSÁVEL PELO ESPAÇO CULTURAL, EMPRESA, ENTIDADE OU COOPERATIVA CULTURAL:',
        'NOME_ESPACO_CULTURAL' => [
            'NOME DO COLETIVO:',
            'NOME DO ESPAÇO CULTURAL VINCULADO OU MANTIDO PELO BENEFICIÁRIO DO SUBSÍDIO:',
        ],
        'FLAG_CAD_ESTADUAL' => 1,
        'SISTEMA_CAD_ESTADUAL' => null,
        'IDENTIFICADOR_CAD_ESTADUAL' => null,
        'FLAG_CAD_MUNICIPAL' => 0,
        'SISTEMA_CAD_MUNICIPAL' => null,
        'IDENTIFICADOR_CAD_MUNICIPAL' => null,
        'FLAG_CAD_DISTRITAL' => 0,
        'SISTEMA_CAD_DISTRITAL' => null,
        'IDENTIFICADOR_CAD_DISTRITAL' => null,
        'FLAG_CAD_NA_PONTOS_PONTOES' => 'INSCRIÇÃO EM CADASTRO CULTURAL:',
        'FLAG_CAD_ES_PONTOS_PONTOES' => 0,
        'SISTEMA_CAD_ES_PONTOS_PONTOES' => null,
        'IDENTIFICADOR_CAD_ES_PONTOS_PONTOES' => null,
        'FLAG_CAD_SNIIC' => 0,
        'SISTEMA_CAD_SNIIC' => null,
        'IDENTIFICADOR_CAD_SNIIC' => null,
        'FLAG_CAD_SALIC' => 'INSCRIÇÃO EM CADASTRO CULTURAL:',
        'FLAG_CAD_SICAB' => 'INSCRIÇÃO EM CADASTRO CULTURAL:',
        'FLAG_CAD_OUTROS' => 0,
        'SISTEMA_CAD_OUTROS' => null,
        'IDENTIFICADOR_CAD_OUTROS' => null,
        'FLAG_ATUACAO_ARTES_CENICAS' => [
            'ÁREA CULTURAL DE ATUAÇÃO PRINCIPAL DO BENEFICIÁRIO DO SUBSÍDIO:'
        ],
        'FLAG_ATUACAO_AUDIOVISUAL' => [
            'ÁREA CULTURAL DE ATUAÇÃO PRINCIPAL DO BENEFICIÁRIO DO SUBSÍDIO:'
        ],
        'FLAG_ATUACAO_MUSICA' => [
            'ÁREA CULTURAL DE ATUAÇÃO PRINCIPAL DO BENEFICIÁRIO DO SUBSÍDIO:'
        ],
        'FLAG_ATUACAO_ARTES_VISUAIS' => [
            'ÁREA CULTURAL DE ATUAÇÃO PRINCIPAL DO BENEFICIÁRIO DO SUBSÍDIO:'
        ],
        'FLAG_ATUACAO_PATRIMONIO_CULTURAL' => [
            'ÁREA CULTURAL DE ATUAÇÃO PRINCIPAL DO BENEFICIÁRIO DO SUBSÍDIO:'
        ],
        'FLAG_ATUACAO_MUSEUS_MEMORIA' => [
            'ÁREA CULTURAL DE ATUAÇÃO PRINCIPAL DO BENEFICIÁRIO DO SUBSÍDIO:'
        ],
        'FLAG_ATUACAO_HUMANIDADES' => [
            'ÁREA CULTURAL DE ATUAÇÃO PRINCIPAL DO BENEFICIÁRIO DO SUBSÍDIO:'
        ],
    ],
    "fields_cnpj" => [ //Configuração para o arquivo CNPJ
        'CNPJ' => [
            'NÚMERO DE INSCRIÇÃO EM CADASTRO NACIONAL DE PESSOA JURÍDICA – CNPJ:'
        ],
        'FLAG_CAD_ESTADUAL' => 1,
        'SISTEMA_CAD_ESTADUAL' => null,
        'IDENTIFICADOR_CAD_ESTADUAL' => null,
        'FLAG_CAD_MUNICIPAL' => 0,
        'SISTEMA_CAD_MUNICIPAL' => null,
        'IDENTIFICADOR_CAD_MUNICIPAL' => null,
        'FLAG_CAD_DISTRITAL' => 0,
        'SISTEMA_CAD_DISTRITAL' => null,
        'IDENTIFICADOR_CAD_DISTRITAL' => null,
        'FLAG_CAD_NA_PONTOS_PONTOES' => 'INSCRIÇÃO EM CADASTRO CULTURAL:',
        'FLAG_CAD_ES_PONTOS_PONTOES' => 0,
        'SISTEMA_CAD_ES_PONTOS_PONTOES' => null,
        'IDENTIFICADOR_CAD_ES_PONTOS_PONTOES' => null,
        'FLAG_CAD_SNIIC' => 0,
        'SISTEMA_CAD_SNIIC' => null,
        'IDENTIFICADOR_CAD_SNIIC' => null,
        'FLAG_CAD_SALIC' => 'INSCRIÇÃO EM CADASTRO CULTURAL:',
        'FLAG_CAD_SICAB' => 'INSCRIÇÃO EM CADASTRO CULTURAL:',
        'FLAG_CAD_OUTROS' => 0,
        'SISTEMA_CAD_OUTROS' => null,
        'IDENTIFICADOR_CAD_OUTROS' => null,
        'FLAG_ATUACAO_ARTES_CENICAS' => [
            'ÁREA CULTURAL DE ATUAÇÃO PRINCIPAL DO BENEFICIÁRIO DO SUBSÍDIO:'
        ],
        'FLAG_ATUACAO_AUDIOVISUAL' => [
            'ÁREA CULTURAL DE ATUAÇÃO PRINCIPAL DO BENEFICIÁRIO DO SUBSÍDIO:'
        ],
        'FLAG_ATUACAO_MUSICA' => [
            'ÁREA CULTURAL DE ATUAÇÃO PRINCIPAL DO BENEFICIÁRIO DO SUBSÍDIO:'
        ],
        'FLAG_ATUACAO_ARTES_VISUAIS' => [
            'ÁREA CULTURAL DE ATUAÇÃO PRINCIPAL DO BENEFICIÁRIO DO SUBSÍDIO:'
        ],
        'FLAG_ATUACAO_PATRIMONIO_CULTURAL' => [
            'ÁREA CULTURAL DE ATUAÇÃO PRINCIPAL DO BENEFICIÁRIO DO SUBSÍDIO:'
        ],
        'FLAG_ATUACAO_MUSEUS_MEMORIA' => [
            'ÁREA CULTURAL DE ATUAÇÃO PRINCIPAL DO BENEFICIÁRIO DO SUBSÍDIO:'
        ],
        'FLAG_ATUACAO_HUMANIDADES' => [
            'ÁREA CULTURAL DE ATUAÇÃO PRINCIPAL DO BENEFICIÁRIO DO SUBSÍDIO:'
        ],
    ],
    "inscricoes_culturais" => [ // Opções inscrições culturais
        'mapa-cultural' => 'Cadastro Estadual de Cultura (Mapa Cultural)',
        'cadastro-municipal' => 'Cadastros Municipais de Cultura',
        'sniic' => 'Sistema Nacional de Informações e Indicadores Culturais',
        'salic' => 'Sistema de Apoio às Leis de incentivo à Cultura (Salic)',
        'sicab' => 'Sistema de Informações Cadastrais do Artesanato Brasileiro',
        'outros' => 'Outros cadastros referentes a atividades culturais',
    ],
    "category" => [ // Opções para categorias
        'com_espaco_fisico' => [
            'BENEFICIÁRIO COM CNPJ E ESPAÇO FÍSICO',
            'BENEFICIÁRIO COM CPF E ESPAÇO FÍSICO',
        ],
        'sem_espaco_fisico' => [
            'BENEFICIÁRIO COM CNPJ E SEM ESPAÇO FÍSICO',
            'BENEFICIÁRIO COM CPF E SEM ESPAÇO FÍSICO',
        ],
    ],
    'atuacoes-culturais' => [ // Opções para área de atuações culturais
        'artes-cenicas' => [
            'Artes Circenses',
            'Dança',
            'Teatro',
            'Ópera',
        ],
        'artes-visuais' => [
            'Artes Visuais',
            'Artesanato',
            'Design',
            'Fotografia',
            'Moda',
        ],
        'audiovisual' => [
            'Audiovisual',
        ],
        'humanidades' => [
            'Literatura',
        ],
        'museu-memoria' => [
            'Museu',
        ],
        'musica' => [
            'Música',
        ],
        'patrimonio-cultural' => [
            'Cultura Popular',
            'Gastronomia',
            'Outros',
            'Patrimônio Cultural',
        ],

    ],
];