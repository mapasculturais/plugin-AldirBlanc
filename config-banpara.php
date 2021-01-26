<?php

/**
 * Configuração do exportador do Banpará
 *
 * fieldMap: usado para localizar os diversos campos na oportunidade
 * details: configuração dos detalhes; lista seqüencial de especificações de
 *          registros de detalhes
 * detail: fields com lista seqüencial de campos com length, type, e name, mais
 *         default se for um campo constante; se o tipo do campo for "meta", o
 *         campo deve conter a lista de subcampos fields e uma function para
 *         preenchimento
 * condition: estrutura de verificações para decidir se o registro entra na
 *            remessa
 *
 * Tudo o que for "condition" (interna, não top-level) ou "function" precisa ser
 * o nome de um método no controller.
 * ToDo: documentar assinaturas para esses métodos.
 */
return [
    "fieldMap" => [
        "singleParent" => "field_119",
        "cpf" => "field_104",
        "nomeBeneficiario" => "field_109",
    ],
    "details" => [
        [ // detail01, informações básicas do cliente
            "fields" => [
                [
                    "length" => 5,
                    "type" => "int",
                    "name" => "sequencialRegistro",
                ],
                [
                    "length" => 11,
                    "type" => "int",
                    "name" => "cpf",
                ],
                [
                    "length" => 100,
                    "type" => "text",
                    "name" => "nomeBeneficiario",
                ],
                [
                    "length" => 100,
                    "type" => "text",
                    "name" => "nomePagador",
                    "default" => "SECRETARIA DE ESTADO DE CULTURA", // preenchimento SECULT
                ],
                [
                    "length" => 10,
                    "type" => "text",
                    "name" => "siglaPagador",
                    "default" => "SECULT", // preenchimento SECULT
                ],
                [
                    "length" => 17,
                    "type" => "int",
                    "name" => "valorBeneficio",
                ],
                [
                    "length" => 20,
                    "type" => "text",
                    "name" => "referencia",
                ],
            ],
        ],
    ],
    "condition" => [
        "const" => true,
    ],
    "return" => [
        "topLevel" => [
            [
                "length" => 5,
                "type" => "int",
                "name" => "numeroRegistro",
                "map" => "records",
            ],
            [
                "length" => 258,
                "type" => "text",
                "name" => "data",
            ],
        ],
        "records" => [
            "default" => [ // detail
                [
                    "length" => 5,
                    "type" => "int",
                    "name" => "numeroRegistro",
                ],
                [
                    "length" => 11,
                    "type" => "text",
                    "name" => "cpf",
                ],
                [
                    "length" => 100,
                    "type" => "text",
                    "name" => "nomeCliente",
                ],
                [
                    "length" => 117,
                    "type" => "text",
                    "name" => "padding",
                ],
                [
                    "length" => 20,
                    "type" => "text",
                    "name" => "referencia",
                    "capture" => "reference",
                ],
            ],
        ],
    ],
];
