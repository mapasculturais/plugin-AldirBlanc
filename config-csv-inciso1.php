<?php
/**
 * Array com header do documento CSV
 * @var array $headers
 */
$headers = [
    "CPF",
    "SEXO",
    "FLAG_CAD_ESTADUAL",
    "SISTEMA_CAD_ESTADUAL",
    "IDENTIFICADOR_CAD_ESTADUAL",
    "FLAG_CAD_MUNICIPAL",
    "SISTEMA_CAD_MUNICIPAL",
    "IDENTIFICADOR_CAD_MUNICIPAL",
    "FLAG_CAD_DISTRITAL",
    "SISTEMA_CAD_DISTRITAL",
    "IDENTIFICADOR_CAD_DISTRITAL",
    "FLAG_CAD_SNIIC",
    "SISTEMA_CAD_SNIIC",
    "IDENTIFICADOR_CAD_SNIIC",
    "FLAG_CAD_SALIC",
    "FLAG_CAD_SICAB",
    "FLAG_CAD_OUTROS",
    "SISTEMA_CAD_OUTROS",
    "IDENTIFICADOR_CAD_OUTROS",
    "FLAG_ATUACAO_ARTES_CENICAS",
    "FLAG_ATUACAO_AUDIOVISUAL",
    "FLAG_ATUACAO_MUSICA",
    "FLAG_ATUACAO_ARTES_VISUAIS",
    "FLAG_ATUACAO_PATRIMONIO_CULTURAL",
    "FLAG_ATUACAO_MUSEUS_MEMORIA",
    "FLAG_ATUACAO_HUMANIDADES",
    "FAMILIARCPF",
    "GRAUPARENTESCO",
];

/**
 * Mapeamento de campos do documento CSV
 * @var array $fields
 */
$fields = [
    "CPF" => function ($registrations) {
        return str_replace(['.', '-'], '', $registrations->field_30);
    },
    'SEXO' => function ($registrations) {
        if ($registrations->field_17 == 'Masculino') {
            return 1;
        } else if ($registrations->field_17 == 'Feminino') {
            return 2;
        } else {
            return 0;
        }
    },
    "FLAG_CAD_ESTADUAL" => 1,
    "SISTEMA_CAD_ESTADUAL" => function () {
        $app = \MapasCulturais\App::i();
        return $app->view->dict('site: name', false);
    },
    "IDENTIFICADOR_CAD_ESTADUAL" => function () {
        $app = \MapasCulturais\App::i();
        return $app->view->dict('site: name', false);
    },
    "FLAG_CAD_MUNICIPAL" => 0,
    "SISTEMA_CAD_MUNICIPAL" => null,
    "IDENTIFICADOR_CAD_MUNICIPAL" => null,
    "FLAG_CAD_DISTRITAL" => 0,
    "SISTEMA_CAD_DISTRITAL" => null,
    "IDENTIFICADOR_CAD_DISTRITAL" => null,
    "FLAG_CAD_SNIIC" => 0,
    "SISTEMA_CAD_SNIIC" => null,
    "IDENTIFICADOR_CAD_SNIIC" => null,
    "FLAG_CAD_SALIC" => 0,
    "FLAG_CAD_SICAB" => 0,
    "FLAG_CAD_OUTROS" => 0,
    "SISTEMA_CAD_OUTROS" => null,
    "IDENTIFICADOR_CAD_OUTROS" => null,
    "FLAG_ATUACAO_ARTES_CENICAS" => function ($registrations) {
        $options = [
            'Artes Circenses',
            'Dança',
            'Teatro',
            'Artes Visuais',
            'Artesanato',
            'Ópera',
        ];
        foreach ($options as $key => $value) {
            if (in_array($value, $registrations->field_10)) {
                return 1;
            } else {
                return 0;
            }
        }
    },
    "FLAG_ATUACAO_AUDIOVISUAL" => function ($registrations) {
        $options = [
            'Audiovisual',
        ];
        foreach ($options as $key => $value) {
            if (in_array($value, $registrations->field_10)) {
                return 1;
            } else {
                return 0;
            }
        }
    },
    "FLAG_ATUACAO_MUSICA" => function ($registrations) {
        $options = [
            'Música',
        ];
        foreach ($options as $key => $value) {
            if (in_array($value, $registrations->field_10)) {
                return 1;
            } else {
                return 0;
            }
        }
    },
    "FLAG_ATUACAO_ARTES_VISUAIS" => function ($registrations) {
        $options = [
            'Design',
            'Moda',
            'Fotografia',
        ];
        foreach ($options as $key => $value) {
            if (in_array($value, $registrations->field_10)) {
                return 1;
            } else {
                return 0;
            }
        }
    },
    "FLAG_ATUACAO_PATRIMONIO_CULTURAL" => function ($registrations) {
        $options = [
            'Cultura Popular',
            'Gastronomia',
            'Outros',
        ];
        foreach ($options as $key => $value) {
            if (in_array($value, $registrations->field_10)) {
                return 1;
            } else {
                return 0;
            }
        }
    },
    "FLAG_ATUACAO_MUSEUS_MEMORIA" => function ($registrations) {
        $options = [
            'Museu',
        ];
        foreach ($options as $key => $value) {
            if (in_array($value, $registrations->field_10)) {
                return 1;
            } else {
                return 0;
            }
        }
    },
    "FLAG_ATUACAO_HUMANIDADES" => function ($registrations) {
        $options = [
            'Literatura',
        ];
        foreach ($options as $key => $value) {
            if (in_array($value, $registrations->field_10)) {
                return 1;
            } else {
                return 0;
            }
        }
    },
    "FAMILIARCPF" => 'field_5',
    "GRAUPARENTESCO" => 'field_5',
];
