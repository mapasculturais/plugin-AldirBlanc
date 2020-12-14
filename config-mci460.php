<?php

/**
 * Configuração do exportador do MCI460
 *
 * serial: temporariamente usado para trocar o número seqüencial do arquivo
 *         MCI460
 * branchMap: um CSV usado para mapear CEPs para agências
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
 * condition: estrutura de verificações para decidir se o registro entra na
 *            remessa
 *
 * Tudo o que for "condition" (interna, não top-level) ou "function" precisa ser
 * o nome de um método no controller.
 * ToDo: documentar assinaturas para esses métodos.
 */
return [
    "serial" => 0,
    "branchMap" => "CSV/branchMap.csv",
    "defaults" => [
        "bankNumber" => "001",
        "accountType" => "Conta corrente",
    ],
    "fieldMap" => [
        "hasAccount" => "field_1",
        "wantsAccount" => "field_18",
        "singleParent" => "field_10",
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
            "function" => "genericDateDDMMYYYY",
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
            "default" => 0, // preenchimento SECULT
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
        [ // detail01, informações básicas do cliente
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
        [ // detail02, informações extras do cliente
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
                    "function" => "mci460NationalityES",
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
            "condition" => "genericFalse",
        ],
        [ // detail03, filiação
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
        [ // detail04, informações do cônjuge
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
            "condition" => "genericFalse",
        ],
        [ // detail05, ocupação
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
                    "length" => 1,
                    "type" => "int",
                    "name" => "tipoContratoTrabalho",
                    "default" => 3, // sempre sem vínculo
                ],
                [
                    "length" => 1,
                    "type" => "int",
                    "name" => "tipoPessoaTrabalho",
                    "default" => 1, // sempre PF
                ],
                [
                    "length" => 14,
                    "type" => "int",
                    "name" => "cpfcnpj", // mapeia para o do beneficiário
                ],
                [
                    "length" => 6,
                    "type" => "int",
                    "name" => "inicioEmprego",
                    "default" => 0, // indisponível
                ],
                [
                    "length" => 60,
                    "type" => "text",
                    "name" => "nomeCliente", // mapeia para o do beneficiário
                ],
                [
                    "length" => 60,
                    "type" => "text",
                    "name" => "cargo",
                    "default" => "", // indisponível
                ],
                [
                    "length" => 1,
                    "type" => "int",
                    "name" => "nivelCargo",
                    "default" => 6, // sempre sem nível
                ],
			],
			"condition" => "genericFalse",
		],
        [ // detail06, endereço
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
                            "filter" => "/[^a-z0-9 ,\.]/i",
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
                            "filter" => "/[^0-9\(\)]/",
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
            "condition" => "genericFalse",
        ],
        // [], // detail07, endereço de trabalho, sem uso
        [ // detail08, e-mail
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
                    "filter" => "/[^a-z0-9_\.@\-\+]/i",
                ],
                [
                    "length" => 83,
                    "type" => "text",
                    "name" => "padding",
                    "default" => "",
                ],
            ],
            "condition" => "mci460ConditionDetail08ES",
        ],
        [ // detail09, primeira referência
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
                    "default" => 9,
                ],
                [
                    "length" => 60,
                    "type" => "text",
                    "name" => "nomeReferencia",
                    "default" => "", // preenchimento SECULT
                ],
                [
                    "length" => 4,
                    "type" => "text",
                    "name" => "dddReferencia",
                    "default" => "", // preenchimento SECULT
                ],
                [
                    "length" => 9,
                    "type" => "text",
                    "name" => "telefoneReferencia",
                    "default" => "", // preenchimento SECULT
                ],
                [
                    "length" => 70,
                    "type" => "text",
                    "name" => "padding",
                    "default" => "",
                ],
            ],
        ],
        [ // detail10, segunda referência
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
                    "default" => 10,
                ],
                [
                    "length" => 60,
                    "type" => "text",
                    "name" => "nomeReferencia",
                    "default" => "", // preenchimento SECULT
                ],
                [
                    "length" => 4,
                    "type" => "text",
                    "name" => "dddReferencia",
                    "default" => "", // preenchimento SECULT
                ],
                [
                    "length" => 9,
                    "type" => "text",
                    "name" => "telefoneReferencia",
                    "default" => "", // preenchimento SECULT
                ],
                [
                    "length" => 70,
                    "type" => "text",
                    "name" => "padding",
                    "default" => "",
                ],
            ],
        ],
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
    "condition" => [
        // "operator" => "and",
        // "operands" => [
        //     [
                "operator" => "not",
                "operands" => [
                    [
                        "operator" => "or",
                        "operands" => [
                            [
                                "operator" => "equals",
                                "operands" => ["hasAccount", ["const" => "SIM"]],
                            ],
                            [
                                "operator" => "not",
                                "operands" => [
                                    [
                                        "operator" => "exists",
                                        "operands" => ["wantsAccount"]
                                    ],
                                ],
                            ],
                            [
                                "operator" => "not",
                                "operands" => [
                                    [
                                        "operator" => "prefix",
                                        "operands" => [
                                            "wantsAccount",
                                            ["const" => "CONTA"]
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
        //     ],
        //     [
        //         "operator" => "equals",
        //         "operands" => ["singleParent", ["const" => "NÃO"]]
        //     ],
        // ],
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
                "length" => 145,
                "type" => "text",
                "name" => "data",
            ],
        ],
        "records" => [
            "00000" => [ // header
                [
                    "length" => 5,
                    "type" => "int",
                    "name" => "numeroRegistro",
                    "match" => 0,
                ],
                [
                    "length" => 8,
                    "type" => "int",
                    "name" => "dataRemessa",
                ],
                [
                    "length" => 8,
                    "type" => "text",
                    "name" => "nomeArquivo",
                    "match" => "MCIF470",
                ],
                [
                    "length" => 5,
                    "type" => "int",
                    "name" => "numeroProcesso",
                    "match" => 0, // preenchimento SECULT
                ],
                [
                    "length" => 5,
                    "type" => "int",
                    "name" => "sequencialRemessa",
                ],
                [
                    "length" => 2,
                    "type" => "int",
                    "name" => "versaoLayout",
                    "match" => 1,
                ],
                [
                    "length" => 117,
                    "type" => "text",
                    "name" => "padding",
                ],
            ],
            "default" => [ // detail
                [
                    "length" => 5,
                    "type" => "int",
                    "name" => "numeroRegistro",
                ],
                [
                    "length" => 14,
                    "type" => "text",
                    "name" => "cpfcnpj",
                ],
                [
                    "length" => 8,
                    "type" => "int",
                    "name" => "dataNascimento",
                ],
                [
                    "length" => 60,
                    "type" => "text",
                    "name" => "nomeCliente",
                ],
                [
                    "length" => 17,
                    "type" => "text",
                    "name" => "usoEmpresa",
                    "capture" => "registrationID",
                ],
                [
                    "length" => 4,
                    "type" => "text",
                    "name" => "agencia",
                    "capture" => "branch",
                ],
                [
                    "length" => 1,
                    "type" => "text",
                    "name" => "dvAgencia",
                    "capture" => "branchVC",
                ],
                [
                    "length" => 2,
                    "type" => "text",
                    "name" => "grupoSetex",
                    "capture" => "setex",
                ],
                [
                    "length" => 1,
                    "type" => "text",
                    "name" => "dvGrupoSetex",
                    "capture" => "setexVC",
                ],
                [
                    "length" => 11,
                    "type" => "int",
                    "name" => "conta",
                    "capture" => "account",
                ],
                [
                    "length" => 1,
                    "type" => "text",
                    "name" => "dvConta",
                    "capture" => "accountVC",
                ],
                [
                    "length" => 3,
                    "type" => "int",
                    "name" => "ocorrenciaCliente",
                    "capture" => "errorClient",
                ],
                [
                    "length" => 3,
                    "type" => "int",
                    "name" => "ocorrenciaConta",
                    "capture" => "errorAccount",
                ],
                [
                    "length" => 3,
                    "type" => "int",
                    "name" => "ocorrenciaCredito",
                    "capture" => "errorCredit",
                ],
                [
                    "length" => 9,
                    "type" => "int",
                    "name" => "mciCliente",
                ],
                [
                    "length" => 8,
                    "type" => "text",
                    "name" => "padding",
                ],
            ],
            "99999" => [ // trailer
                [
                    "length" => 5,
                    "type" => "int",
                    "name" => "numeroRegistro",
                    "match" => 99999,
                ],
                [
                    "length" => 9,
                    "type" => "int",
                    "name" => "totalRegistros",
                    "capture" => "countEntries",
                ],
                [
                    "length" => 136,
                    "type" => "text",
                    "name" => "padding",
                ],
            ],
        ],
    ],
];
