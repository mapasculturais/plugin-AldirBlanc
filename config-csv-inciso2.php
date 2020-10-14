<?php
/**
 * Arquivo de configuração para o exportador dataprev no inciso 2
 * 
 * O Exportador do inciso 2 utiliza o nome dos campos para fazer a busca dos valores na base de dados na maiora dos casos, 
 * exeto para os capos abaixo. 
 * 
 * Apenas marque 1 nos capos que lhe se enquadrem na sua situação. lembramos que a base Mapas Culturais é uma mase homologada 
 * e pode ser usada para esse caso. Para isso marque 1 em FLAG_CAD_ESTADUAL
 * 
 * FLAG_CAD_ESTADUAL
 * FLAG_CAD_MUNICIPAL
 * FLAG_CAD_DISTRITAL
 * FLAG_CAD_SNIIC
 * 
 * Caso so capos acima sejam flegados como 1, os respectivos campos abaixo se preenchem automáticamente como nos exemplos.
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
    "fields_cpf" => [ // Campos para CPF
        'CPF' => '',
        'SEXO' => '',
        'NOME_ESPACO_CULTURAL' => [],
        'FLAG_CAD_ESTADUAL' => '',
        'SISTEMA_CAD_ESTADUAL' => '',
        'IDENTIFICADOR_CAD_ESTADUAL' => '',
        'FLAG_CAD_MUNICIPAL' => '',
        'SISTEMA_CAD_MUNICIPAL' => '',
        'IDENTIFICADOR_CAD_MUNICIPAL' => '',
        'FLAG_CAD_DISTRITAL' => '',
        'SISTEMA_CAD_DISTRITAL' => '',
        'IDENTIFICADOR_CAD_DISTRITAL' => '',
        'FLAG_CAD_NA_PONTOS_PONTOES' => '',
        'FLAG_CAD_ES_PONTOS_PONTOES' => '',
        'SISTEMA_CAD_ES_PONTOS_PONTOES' => '',
        'IDENTIFICADOR_CAD_ES_PONTOS_PONTOES' => '',
        'FLAG_CAD_SNIIC' => '',
        'SISTEMA_CAD_SNIIC' => '',
        'IDENTIFICADOR_CAD_SNIIC' => '',
        'FLAG_CAD_SALIC' => '',
        'FLAG_CAD_SICAB' => '',
        'FLAG_CAD_OUTROS' => '',
        'SISTEMA_CAD_OUTROS' => '',
        'IDENTIFICADOR_CAD_OUTROS' => '',
        'FLAG_ATUACAO_ARTES_CENICAS' => [],
        'FLAG_ATUACAO_AUDIOVISUAL' => [],
        'FLAG_ATUACAO_MUSICA' => [],
        'FLAG_ATUACAO_ARTES_VISUAIS' => [],
        'FLAG_ATUACAO_PATRIMONIO_CULTURAL' => [],
        'FLAG_ATUACAO_MUSEUS_MEMORIA' => [],
        'FLAG_ATUACAO_HUMANIDADES' => [],
    ],
    "fields_cnpj" => [ // Campos para CNPJ
        'CNPJ' => [],
        'FLAG_CAD_ESTADUAL' => '',
        'SISTEMA_CAD_ESTADUAL' => '',
        'IDENTIFICADOR_CAD_ESTADUAL' => '',
        'FLAG_CAD_MUNICIPAL' => '',
        'SISTEMA_CAD_MUNICIPAL' => '',
        'IDENTIFICADOR_CAD_MUNICIPAL' => '',
        'FLAG_CAD_DISTRITAL' => '',
        'SISTEMA_CAD_DISTRITAL' => '',
        'IDENTIFICADOR_CAD_DISTRITAL' => '',
        'FLAG_CAD_NA_PONTOS_PONTOES' => '',
        'FLAG_CAD_ES_PONTOS_PONTOES' => '',
        'SISTEMA_CAD_ES_PONTOS_PONTOES' => '',
        'IDENTIFICADOR_CAD_ES_PONTOS_PONTOES' => '',
        'FLAG_CAD_SNIIC' => '',
        'SISTEMA_CAD_SNIIC' => '',
        'IDENTIFICADOR_CAD_SNIIC' => '',
        'FLAG_CAD_SALIC' => '',
        'FLAG_CAD_SICAB' => '',
        'FLAG_CAD_OUTROS' => '',
        'SISTEMA_CAD_OUTROS' => '',
        'IDENTIFICADOR_CAD_OUTROS' => '',
        'FLAG_ATUACAO_ARTES_CENICAS' => [],
        'FLAG_ATUACAO_AUDIOVISUAL' => [],
        'FLAG_ATUACAO_MUSICA' => [],
        'FLAG_ATUACAO_ARTES_VISUAIS' => [],
        'FLAG_ATUACAO_PATRIMONIO_CULTURAL' => [],
        'FLAG_ATUACAO_MUSEUS_MEMORIA' => [],
        'FLAG_ATUACAO_HUMANIDADES' => [],
    ],
    "inscricoes_culturais" => [ // Opções para incrições culturais
        'mapa-cultural' => '',
        'cadastro-municipal' => '',
        'sniic' => '',
        'salic' => '',
        'sicab' => '',
        'outros' => '',
        'pontoes' => '',
    ],
    "category" => [ // Categorias
        'com_espaco_fisico' => [],
        'sem_espaco_fisico' => [],
    ],
    'atuacoes-culturais' => [ // Opções para área de atuações culturais
        'artes-cenicas' => [],
        'artes-visuais' => [],
        'audiovisual' => [
            'Audiovisual',
        ],
        'humanidades' => [],
        'museu-memoria' => [],
        'musica' => [],
        'patrimonio-cultural' => [],

    ],
];
