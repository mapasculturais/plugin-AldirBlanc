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
];