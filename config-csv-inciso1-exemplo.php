<?php
$app = \MapasCulturais\App::i();
$siteName = $app->view->dict('site: name', false);

return [
    "CPF" => 'field_30',
    "SEXO" => "field_17",
    "FLAG_CAD_ESTADUAL" => 1,
    "SISTEMA_CAD_ESTADUAL" => $siteName,
    "IDENTIFICADOR_CAD_ESTADUAL" => $siteName,
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
    "FLAG_ATUACAO_ARTES_CENICAS" => "field_10",
    "FLAG_ATUACAO_AUDIOVISUAL" => "field_10",
    "FLAG_ATUACAO_MUSICA" =>"field_10",
    "FLAG_ATUACAO_ARTES_VISUAIS" => "field_10",
    "FLAG_ATUACAO_PATRIMONIO_CULTURAL" => "field_10",
    "FLAG_ATUACAO_MUSEUS_MEMORIA" => "field_10",
    "FLAG_ATUACAO_HUMANIDADES" => "field_10",
    "FAMILIARCPF" => 'field_5',
    "GRAUPARENTESCO" => 'field_5',
];