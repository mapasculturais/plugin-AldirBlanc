<?php

/**
 * Configuração do exportador do MCI460
 *
 * fieldMap: usado para localizar os diversos campos na oportunidade
 * header: configuração do header; lista seqüencial de campos com length, type e
 *         name, mais default se for um campo constante ou function para um
 *         campo dinâmico
 * details: configuração dos detalhes; lista seqüencial de especificações de
 *          registros de detalhes
 * detail: fields com lista seqüencial de campos com length, type, e name, mais
 *         default se for um campo constante; se o tipo do campo for "meta", o
 *         campo deve conter a lista de subcampos fields e uma function para
 *         preenchimento
 * trailer: configuração do trailer; lista seqüencial de campos com length, type
 *          e name, mais default se for um campo constante; os nomes dos campos
 *          dinâmicos são utilizados como chaves do último parâmetro passado
 *          para o método que gera o trailer
 * condition: function para determinar se o registro entra na remessa
 *
 * Tudo o que for "condition" ou "function" precisa ser o nome de um método no
 * controller.
 * ToDo: documentar assinaturas para esses métodos.
 */
return [
    "fieldMap" => [
        "hasAccount" => "field_1",
        "wantsAccount" => "field_18",
        "cpfcnpj" => "field_21",
        "dataNascimento" => "field_24",
        "nomeCliente" => "field_22",
        "usoEmpresa" => "number",
        "sexo" => "field_7",
        "nacionalidade" => "field_28",
        "nomeMae" => "field_19",
        "conjuge" => "field_36",
        "endereco" => "field_8",
        "dddTelefone" => "field_25",
        "email" => "field_27",
    ],
    "header" => [
        [
            "length" => 7,
            "type" => "int",
            "name" => "zeros",
            "default" => 0,
        ],
        [
            "length" => 8,
            "type" => "int",
            "name" => "dataRemessa",
            "function" => "mci460DateDDMMYYYY",
        ],
        [
            "length" => 8,
            "type" => "text",
            "name" => "nomeArquivo",
            "default" => "MCIF460",
        ],
        [
            "length" => 9,
            "type" => "int",
            "name" => "mciEmpresa",
            "default" => "", // preenchimento SECULT
        ],
        [
            "length" => 5,
            "type" => "int",
            "name" => "numeroProcesso",
            "default" => 0, // preenchimento SECULT
        ],
        [
            "length" => 5,
            "type" => "int",
            "name" => "sequencialRemessa",
            "function" => "sequenceNumber",
        ],
        [
            "length" => 2,
            "type" => "int",
            "name" => "versaoLayout",
            "default" => 3,
        ],
        [
            "length" => 4,
            "type" => "int",
            "name" => "agenciaEmpresa",
            "default" => 0, // preenchimento SECULT
        ],
        [
            "length" => 1,
            "type" => "text",
            "name" => "dvAgenciaEmpresa",
            "default" => "", // preenchimento SECULT
        ],
        [
            "length" => 11,
            "type" => "int",
            "name" => "contaEmpresa",
            "default" => 0, // preenchimento SECULT
        ],
        [
            "length" => 1,
            "type" => "text",
            "name" => "dvContaEmpresa",
            "default" => "", // preenchimento SECULT
        ],
        [
            "length" => 1,
            "type" => "int",
            "name" => "indicadorKit",
            "default" => 0, // preenchimento SECULT
        ],
        [
            "length" => 88,
            "type" => "text",
            "name" => "padding",
            "default" => "",
        ],
    ],
    "details" => [
        [
            "fields" => [
                [
                    "length" => 5,
                    "type" => "int",
                    "name" => "sequencialCliente",
                ],
                [
                    "length" => 2,
                    "type" => "int",
                    "name" => "tipoDetalhe",
                    "default" => 1,
                ],
                [
                    "length" => 1,
                    "type" => "int",
                    "name" => "tipoPessoa",
                    "default" => 1, // sempre PF
                ],
                [
                    "length" => 1,
                    "type" => "int",
                    "name" => "tipoCPFCNPJ",
                    "default" => 1, // sempre CPF próprio
                ],
                [
                    "length" => 14,
                    "type" => "int",
                    "name" => "cpfcnpj",
                ],
                [
                    "length" => 8,
                    "type" => "int",
                    "name" => "dataNascimento",
                    "function" => "mci460DateFormatDDMMYYYY",
                ],
                [
                    "length" => 60,
                    "type" => "text",
                    "name" => "nomeCliente",
                ],
                [
                    "length" => 25,
                    "type" => "text",
                    "name" => "nomePersonalizadoPJ",
                    "default" => "", // registros são PF
                ],
                [
                    "length" => 1,
                    "type" => "text",
                    "name" => "emBranco",
                    "default" => "",
                ],
                [
                    "length" => 17,
                    "type" => "text",
                    "name" => "usoEmpresa",
                ],
                [
                    "length" => 4,
                    "type" => "int",
                    "name" => "agencia",
                ],
                [
                    "length" => 1,
                    "type" => "text",
                    "name" => "dvAgencia",
                ],
                [
                    "length" => 2,
                    "type" => "int",
                    "name" => "grupoSetex",
                ],
                [
                    "length" => 1,
                    "type" => "text",
                    "name" => "dvGrupoSetex",
                ],
                [
                    "length" => 8,
                    "type" => "text",
                    "name" => "padding",
                    "default" => "",
                ],
            ],
        ],
        [
            "fields" => [
                [
                    "length" => 5,
                    "type" => "int",
                    "name" => "sequencialCliente",
                ],
                [
                    "length" => 2,
                    "type" => "int",
                    "name" => "tipoDetalhe",
                    "default" => 2,
                ],
                [
                    "length" => 1,
                    "type" => "text",
                    "name" => "sexo",
                ],
                [
                    "length" => 2,
                    "type" => "int",
                    "name" => "nacionalidade",
                ],
                [
                    "length" => 25,
                    "type" => "text",
                    "name" => "naturalidade",
                    "default" => "", // indisponível
                ],
                [
                    "length" => 2,
                    "type" => "int",
                    "name" => "tipoDocumento",
                    "default" => 0, // indisponível
                ],
                [
                    "length" => 20,
                    "type" => "text",
                    "name" => "numeroDocumento",
                    "default" => "", // indisponível
                ],
                [
                    "length" => 15,
                    "type" => "text",
                    "name" => "emissor",
                    "default" => "", // indisponível
                ],
                [
                    "length" => 8,
                    "type" => "int",
                    "name" => "dataEmissao",
                    "default" => 0, // indisponível
                ],
                [
                    "length" => 2,
                    "type" => "int",
                    "name" => "estadoCivil",
                    "default" => 0, // indisponível
                ],
                [
                    "length" => 2,
                    "type" => "int",
                    "name" => "capacidadeCivil",
                    "default" => 1, // sempre capaz
                ],
                [
                    "length" => 3,
                    "type" => "int",
                    "name" => "formacao",
                    "default" => 0, // indisponível
                ],
                [
                    "length" => 3,
                    "type" => "int",
                    "name" => "grauInstrucao",
                    "default" => 0, // indisponível
                ],
                [
                    "length" => 3,
                    "type" => "int",
                    "name" => "naturezaOcupacao",
                    "default" => 0, // indisponível
                ],
                [
                    "length" => 3,
                    "type" => "int",
                    "name" => "ocupacao",
                    "default" => 0, // indisponível
                ],
                [
                    "length" => 15,
                    "type" => "int",
                    "name" => "rendimentoFixP",
                    "default" => 0, // indisponível
                ],
                [
                    "length" => 6,
                    "type" => "int",
                    "name" => "mesAnoRendimento",
                    "default" => 0, // indisponível
                ],
                [
                    "length" => 33,
                    "type" => "text",
                    "name" => "padding",
                    "default" => "",
                ],
            ],
        ],
        [
            "fields" => [
                [
                    "length" => 5,
                    "type" => "int",
                    "name" => "sequencialCliente",
                ],
                [
                    "length" => 2,
                    "type" => "int",
                    "name" => "tipoDetalhe",
                    "default" => 3,
                ],
                [
                    "length" => 60,
                    "type" => "text",
                    "name" => "nomeMae",
                ],
                [
                    "length" => 60,
                    "type" => "text",
                    "name" => "nomePai",
                    "default" => "", // indisponível
                ],
                [
                    "length" => 23,
                    "type" => "text",
                    "name" => "padding",
                    "default" => "",
                ],
            ],
        ],
        [
            "fields" => [
                [
                    "length" => 5,
                    "type" => "int",
                    "name" => "sequencialCliente",
                ],
                [
                    "length" => 2,
                    "type" => "int",
                    "name" => "tipoDetalhe",
                    "default" => 4,
                ],
                [
                    "length" => 1,
                    "type" => "int",
                    "name" => "tipoCPFConjuge",
                    "default" => 0, // indisponível
                ],
                [
                    "length" => 79,
                    "type" => "meta",
                    "name" => "conjuge",
                    "fields" => [
                        [
                            "length" => 11,
                            "type" => "int",
                            "name" => "cpf",
                        ],
                        [
                            "length" => 8,
                            "type" => "int",
                            "name" => "nascimentoConjuge",
                            "default" => 0, // indisponivel
                        ],
                        [
                            "length" => 60,
                            "type" => "text",
                            "name" => "name",
                        ],
                    ],
                    "function" => "mci460SpouseES",
                ],
                [
                    "length" => 63,
                    "type" => "text",
                    "name" => "padding",
                "default" => "",
                ],
            ],
            "condition" => "mci460ConditionDetail04ES",
        ],
        // [], // detail05, ocupação, sem uso
        [
            "fields" => [
                [
                    "length" => 5,
                    "type" => "int",
                    "name" => "sequencialCliente",
                ],
                [
                    "length" => 2,
                    "type" => "int",
                    "name" => "tipoDetalhe",
                    "default" => 6,
                ],
                [
                    "length" => 98,
                    "type" => "meta",
                    "name" => "endereco",
                    "fields" => [
                        [
                            "length" => 60,
                            "type" => "text",
                            "name" => "logradouro",
                        ],
                        [
                            "length" => 30,
                            "type" => "text",
                            "name" => "distritoBairro",
                        ],
                        [
                            "length" => 8,
                            "type" => "int",
                            "name" => "cep",
                        ],
                    ],
                    "function" => "mci460AddressES"
                ],
                [
                    "length" => 13,
                    "type" => "meta",
                    "name" => "dddTelefone",
                    "fields" => [
                        [
                            "length" => 4,
                            "type" => "text",
                            "name" => "ddd",
                        ],
                        [
                            "length" => 9,
                            "type" => "text",
                            "name" => "telefone",
                        ],
                    ],
                    "function" => "mci460PhoneES",
                ],
                [
                    "length" => 9,
                    "type" => "int",
                    "name" => "caixaPostal",
                    "default" => 0, // indisponível
                ],
                [
                    "length" => 2,
                    "type" => "int",
                    "name" => "situacaoImovel",
                    "default" => 0, // indisponível
                ],
                [
                    "length" => 6,
                    "type" => "int",
                    "name" => "inicioResidencia",
                    "default" => 0, // indisponível
                ],
                [
                    "length" => 15,
                    "type" => "text",
                    "name" => "padding",
                    "default" => "",
                ],
            ],
        ],
        // [], // detail07, endereço de trabalho, sem uso
        [
            "fields" => [
                [
                    "length" => 5,
                    "type" => "int",
                    "name" => "sequencialCliente",
                ],
                [
                    "length" => 2,
                    "type" => "int",
                    "name" => "tipoDetalhe",
                    "default" => 8,
                ],
                [
                    "length" => 60,
                    "type" => "text",
                    "name" => "email",
                ],
                [
                    "length" => 83,
                    "type" => "text",
                    "name" => "padding",
                    "default" => "",
                ],
            ],
            "condition" => "mci460ConditionDetail09ES",
        ],
        // [], // detail09, primeira referência, sem uso
        // [], // detail10, segunda referência, sem uso
    ],
    "trailer" => [
        [
            "length" => 7,
            "type" => "int",
            "name" => "noves",
            "default" => 9999999,
        ],
        [
            "length" => 5,
            "type" => "int",
            "name" => "totalClientes",
        ],
        [
            "length" => 9,
            "type" => "int",
            "name" => "totalRegistros",
        ],
        [
            "length" => 129,
            "type" => "text",
            "name" => "padding",
            "default" => "",
        ],
    ],
    "condition" => "mci460ConditionES",
];
