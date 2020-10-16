<?php
/**
 * Arquivo de configuração para o exportador dataprev no inciso 1
 * 
 * O Exportador do inciso 1 utiliza o field_id dos campos para fazer a busca dos valores na base de dados na maiora dos casos, 
 * exeto para os campos abaixo. 
 * 
 * A forma mais fácil de se conseguir o field_id de um campo, e olhando o formulário. Ao lado do nome do campo existe 
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
 * 1) O array (atuacoes-culturais) deve conter todos os valores das açoes culturais existentes no formulário separadas entre as categorias abaixo
 * - artes-cenicas
 * - artes-visuais
 * - audiovisual
 * - humanidades
 * - museu-memoria
 * - musica
 * - patrimonio-cultural * 
 * 
 */

return [ //Configuração dos campos
    "CPF" => 'field_21',
    "SEXO" => "field_7",
    "FLAG_CAD_ESTADUAL" => 1,
    "FLAG_CAD_MUNICIPAL" => 0,
    "FLAG_CAD_DISTRITAL" => 0,
    "FLAG_CAD_SNIIC" => 0,
    "FLAG_CAD_SALIC" => 0,
    "FLAG_CAD_SICAB" => 0,
    "FLAG_CAD_OUTROS" => 0,
    "SISTEMA_CAD_OUTROS" => null,
    "IDENTIFICADOR_CAD_OUTROS" => null,
    "FLAG_ATUACAO_ARTES_CENICAS" => "field_12",
    "FLAG_ATUACAO_AUDIOVISUAL" => "field_12",
    "FLAG_ATUACAO_MUSICA" => "field_12",
    "FLAG_ATUACAO_ARTES_VISUAIS" => "field_12",
    "FLAG_ATUACAO_PATRIMONIO_CULTURAL" => "field_12",
    "FLAG_ATUACAO_MUSEUS_MEMORIA" => "field_12",
    "FLAG_ATUACAO_HUMANIDADES" => "field_12",
    "FAMILIARCPF" => 'field_36',
    "GRAUPARENTESCO" => 'field_36',
    "parameters_csv_default" => [
        "status" => 1,
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
    ]
];
