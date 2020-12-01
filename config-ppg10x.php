<?php

/**
 * Configuração do exportador do PPG100
 *
 * ppg100Serial: temporariamente usado para trocar o número seqüencial do
 *               arquivo PPG100
 * fieldMap: usado para localizar os diversos campos na oportunidade
 * header: configuração do header; lista seqüencial de campos com length, type e
 *         name, mais default se for um campo constante ou function para um
 *         campo dinâmico
 * details: configuração dos detalhes; este formato tem apenas um tipo de
 *          detalhe mas na configuração é lista por compatibilidade
 * detail: fields com lista seqüencial de campos com length, type, e name, mais
 *         default se for um campo constante; se o tipo do campo for "meta", o
 *         campo deve conter a lista de subcampos fields e uma function para
 *         preenchimento
 * trailer: configuração do trailer; lista seqüencial de campos com length, type
 *          e name, mais default se for um campo constante; os nomes dos campos
 *          dinâmicos são utilizados como chaves do último parâmetro passado
 *          para o método que gera o trailer
 * condition: estrutura de verificações para decidir se o registro entra na
 *            remessa
 *
 * Tudo o que for "function" precisa ser o nome de um método no controller.
 * ToDo: documentar assinaturas para esses métodos.
 */
return [
    "serial" => 0,
    "idMap" => "CSV/ppgIdMap.csv", // comentar a linha para mapear pelo id do pagamento
    "fieldMap" => [
        "wantsPaymentOrder" => "field_8564",
        "singleParent" => "field_119",
        //"numeroProtocolo" => "id",
        "senhaSaque" => "number",
        "cpf" => "field_104",
        "indicadorAcao" => "id",
    ],
    "header" => [
        [
            "length" => 1,
            "type" => "int",
            "name" => "tipoRegistro",
            "default" => 0,
        ],
        [
            "length" => 8,
            "type" => "int",
            "name" => "dataRemessa",
            "function" => "genericDateDDMMYYYY",
        ],
        [
            "length" => 4,
            "type" => "int",
            "name" => "horaRemessa",
            "function" => "genericTimeHHMM",
        ],
        [
            "length" => 6,
            "type" => "text",
            "name" => "nomeArquivo",
            "default" => "PPG100",
        ],
        [
            "length" => 9,
            "type" => "int",
            "name" => "mciCliente",
            "default" => 0, // preenchimento SECULT
        ],
        [
            "length" => 9,
            "type" => "int",
            "name" => "codigoParametroCliente",
            "default" => 0, // preenchimento SECULT
        ],
        [
            "length" => 3,
            "type" => "int",
            "name" => "versaoLayout",
            "default" => 11,
        ],
        [
            "length" => 9,
            "type" => "int",
            "name" => "sequencialRemessa",
            "function" => "sequenceNumber",
        ],
        [
            "length" => 9,
            "type" => "text",
            "name" => "padding",
            "default" => "",
        ],
        [
            "length" => 4,
            "type" => "int",
            "name" => "agenciaDebito",
            "default" => 0, // preenchimento SECULT
        ],
        [
            "length" => 9,
            "type" => "int",
            "name" => "contaDebito",
            "default" => 0, // preenchimento SECULT
        ],
        [
            "length" => 120,
            "type" => "text",
            "name" => "padding",
            "default" => "",
        ],
        [
            "length" => 9,
            "type" => "int",
            "name" => "numeroRegistro",
            "default" => 1,
        ],
    ],
    "details" => [
        [
            "fields" => [
                [
                    "length" => 1,
                    "type" => "int",
                    "name" => "tipoRegistro",
                    "default" => 1,
                ],
                [
                    "length" => 15,
                    "type" => "meta",
                    "name" => "numeroProtocolo", // enviar CSV no extraData
                    "fields" => [
                        [ // apenas no novo formato
                            "length" => 6,
                            "type" => "int",
                            "name" => "padding",
                            "default" => 0,
                        ],
                        [
                            "length" => 3, // 3 no novo formato, 4 no antigo
                            "type" => "int",
                            "name" => "idBB",
                            "default" => 0, // preenchimento SECULT
                        ],
                        [
                            "length" => 6, // 6 no novo formato, 10 no antigo
                            "type" => "int",
                            "name" => "idCliente",
                        ],
                        // [ // apenas no antigo formato
                        //     "length" => 1,
                        //     "type" => "int",
                        //     "name" => "dv",
                        // ],
                    ],
                    //"function" => "ppg100ProtocolNumberPA",
                ],
                [
                    "length" => 6,
                    "type" => "int",
                    "name" => "senhaSaque",
                    "function" => "ppg100PIN",
                ],
                [
                    "length" => 10,
                    "type" => "text",
                    "name" => "padding",
                    "default" => "",
                ],
                [
                    "length" => 11,
                    "type" => "int",
                    "name" => "cpf",
                ],
                [
                    "length" => 11,
                    "type" => "int",
                    "name" => "valorCarga",
                ],
                [
                    "length" => 1,
                    "type" => "int",
                    "name" => "indicadorAcao",
                    "function" => "ppg100ActionPA",
                ],
                [
                    "length" => 136,
                    "type" => "text",
                    "name" => "padding",
                    "default" => "",
                ],
                [
                    "length" => 9,
                    "type" => "int",
                    "name" => "numeroRegistro",
                ],
            ],
        ],
    ],
    "trailer" => [
        [
            "length" => 1,
            "type" => "int",
            "name" => "tipoRegistro",
            "default" => 9,
        ],
        [
            "length" => 9,
            "type" => "int",
            "name" => "totalDetalhes",
        ],
        [
            "length" => 11,
            "type" => "int", // em desacordo com a documentação
            "name" => "totalCarga",
        ],
        [
            "length" => 170,
            "type" => "text",
            "name" => "padding",
            "default" => "",
        ],
        [
            "length" => 9,
            "type" => "int",
            "name" => "numeroRegistro",
        ],
    ],
    "condition" => [
        "operator" => "and",
        "operands" => [
            [
                "operator" => "exists",
                "operands" => ["wantsPaymentOrder"],
            ],
            [
                "operator" => "prefix",
                "operands" => [
                    "wantsPaymentOrder",
                    ["const" => "Ordem de pagamento"],
                ],
            ],
            // [
            //     "operator" => "equals",
            //     "operands" => ["singleParent", ["const" => "NÃO"]]
            // ]
        ],
    ],
];
