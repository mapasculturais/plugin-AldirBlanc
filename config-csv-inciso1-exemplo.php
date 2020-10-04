<?php
return [
    "CPF" => 'field_30',
    "SEXO" => "field_17",
    "FLAG_CAD_ESTADUAL" => 1,
    "FLAG_CAD_MUNICIPAL" => 0,
    "FLAG_CAD_DISTRITAL" => 0,
    "FLAG_CAD_SNIIC" => 0,
    "FLAG_CAD_SALIC" => 0,
    "FLAG_CAD_SICAB" => 0,
    "FLAG_CAD_OUTROS" => 0,
    "SISTEMA_CAD_OUTROS" => null,
    "IDENTIFICADOR_CAD_OUTROS" => null,
    "FLAG_ATUACAO_ARTES_CENICAS" => "field_10",
    "FLAG_ATUACAO_AUDIOVISUAL" => "field_10",
    "FLAG_ATUACAO_MUSICA" =>"field_10",
    "FLAG_ATUACAO_ARTES_VISUAIS" => "field_10",
    "FLAG_ATUACAO_PATRIMONIO_CULTURAL" => "field_10",
    "FLAG_ATUACAO_MUSEUS_MEMORIA" => "field_10",
    "FLAG_ATUACAO_HUMANIDADES" => "field_10",
    "FAMILIARCPF" => 'field_5',
    "GRAUPARENTESCO" => 'field_5',
    "parameters_csv_defalt" => [
        "status" => 1
    ],
    'atuacoes-culturais' => [ // Opções para área de atuações culturais

        'artes-cenicas' => [
            'Artes Circenses',
            'Dança',
            'Teatro',
            'Artes Visuais',
            'Artesanato',
            'Ópera',
        ],
        'audiovisual' => [
            'Audiovisual',
        ],
        'musica' => [
            'Música',
        ],
        'artes-visuais' => [
            'Design',
            'Moda',
            'Fotografia',
        ],
        'patrimonio-cultural' => [
            'Cultura Popular',
            'Gastronomia',
            'Outros',
            'Patrimônio Cultural',
        ],
        'museu-memoria' => [
            'Museu',
        ],
        'humanidades' => [
            'Literatura',
        ],

    ],
];