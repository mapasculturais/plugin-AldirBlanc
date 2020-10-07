# plugin-AldirBlanc #
Plugin que implementa no Mapas Culturais os formulários do inciso I e II da Lei Aldir Blanc 

# Configuração dos exportadores CSV #

Antes de tudo, certifique-se de estar com a biblioteca CSV instalada em seu ambiente.
Para ver a documentação da lib, acesse https://csv.thephpleague.com/
s
# Exportador inciso 1 #

Para configurar o exportador, você deve copiar o arquivo <b>config-csv-inciso1-exemplo.php</b> que está no caminho.

#### mapasculturais-aldirblanc/plugins/AldirBlanc #### 

e colar no arquivo arquivo <b>config-csv-inciso1.php</b>  que esta no mesmo diretório.

### Informações do arquivo de configuração ###

<b>Campos que a busca é feita pelo field_id</b>

No Arquivo de configuração, especificamente para os campos listados no exemplo abaixo, deve-se informar o <b>field_id</b> do campo para que o sistema possa fazer a busca altomática dos registros.

```
return [
    "CPF" => 'field_30',
    "SEXO" => "field_17",
    "FLAG_ATUACAO_ARTES_CENICAS" => "field_10",
    "FLAG_ATUACAO_AUDIOVISUAL" => "field_10",
    "FLAG_ATUACAO_MUSICA" =>"field_10",
    "FLAG_ATUACAO_ARTES_VISUAIS" => "field_10",
    "FLAG_ATUACAO_PATRIMONIO_CULTURAL" => "field_10",
    "FLAG_ATUACAO_MUSEUS_MEMORIA" => "field_10",
    "FLAG_ATUACAO_HUMANIDADES" => "field_10",
    "FAMILIARCPF" => 'field_5',
    "GRAUPARENTESCO" => 'field_5',
]
```

A forma mais fácil de conseguir o field_id é olhando a configuração do formulário dentro da oportunidade. Nessa tela ao lado do nome do campo, tem algo como <b>#650</b> que se trata do id do campo. basta pegar esse id e informar na configuração. No nosso exemplo ficaria algo como <b>"CPF" => 'field_650'.</b>

<b>Campos que utilizam o conteúdo do arquivo de configuração</b>

No Arquivo de configuração, especificamente para os campos listados no exemplo abaixo, deve-se informar exatamente o registro que seja inserido no arquivo.

```
return [
    "FLAG_CAD_ESTADUAL" => 1,
    "FLAG_CAD_MUNICIPAL" => 0,
    "FLAG_CAD_DISTRITAL" => 0,
    "FLAG_CAD_SNIIC" => 0,
    "FLAG_CAD_SALIC" => 0,
    "FLAG_CAD_SICAB" => 0,
    "FLAG_CAD_OUTROS" => 0,
    "SISTEMA_CAD_OUTROS" => null,
    "IDENTIFICADOR_CAD_OUTROS" => null,    
]
``` 

Nesse caso, o sistema ira inserir 1 na colona FLAG_CAD_ESTADUAL do CSV e nas colunas SISTEMA_CAD_OUTROS e IDENTIFICADOR_CAD_OUTROS ficaram sem registro.

<b>Configuração de área de atuação cultural</b>

Para a área de atuação cultural, deve-se informar exatamente os textos cadastrados no campo durante a elaboração do formulário. Abaixo esta um exemplo.

```
return[
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
]
```

# Exportador inciso 2 #

Para configurar o exportador, você deve copiar o arquivo <b>config-csv-inciso2-exemplo.php</b> que está no caminho.

#### mapasculturais-aldirblanc/plugins/AldirBlanc #### 

e colar no arquivo arquivo <b>config-csv-inciso2.php</b>  que esta no mesmo diretório.

## Observação importante ##

Na configuração do exportador para o inciso 2, <b>Não</b> se deve usar o field_id do campo, mas sim o nome do campo utilizado no formulário de cadastro.

<b>lembre - se que a comparação e feita entre textos ou seja, o texto deve estar exatamente igual ao que esta configurado no formulário de cadastro, não deixe qual quer caracter para traz.</b>

caso no formulário de cadastro exista mais de 1 campo com o mesmo nome ou até mesmo com nomes diferentes, o exportador irá verificar em qual dos campos existe o registro e pegar os dados que estão preenchidos. 

Por exemplo o campo NOME_ESPACO_CULTURAL.

veja que no arquivo de configuração <b>exemplo</b>, O campo NOME_ESPACO_CULTURAL em um array com 2 campos diferentes para verificar.

```
return [
    'NOME_ESPACO_CULTURAL' => [
        'NOME DO COLETIVO',
        'NOME DO ESPAÇO CULTURAL VINCULADO OU MANTIDO PELO BENEFICIÁRIO DO SUBSÍDIO:',
    ],
]
```

Nesse caso, o sistema ira buscar o registro nos 2 campos e onde ele encontrar registro ele vai retornar.

Logo então, não encontrando registro no NOME DO COLETIVO ele fará a busca no campo NOME DO ESPAÇO CULTURAL VINCULADO OU MANTIDO PELO BENEFICIÁRIO DO SUBSÍDIO:

<b>Se ele encontrar registros nos 2 campos ele irá trazer o ultimo registro da consulta</b> que no nosso exemplo, retornaria os registros do campo
NOME DO ESPAÇO CULTURAL VINCULADO OU MANTIDO PELO BENEFICIÁRIO DO SUBSÍDIO:

Caso não encontre registros em nehum dos campos, ele retorna null e a coluna do CSV fica sem registro.


### Informações do arquivo de configuração ###

<b>Configuração de categoria</b>

Você deve informar o texto das categorias que estão configuradas em seu formulário como mostra o exemplo abaixo

<b>Inportante, separe as configurações exatamente como mostra o exemplo. COM ESPAÇO FÍSICO e SEM ESPAÇO FÍSICO</b>

```
return [
    "category" => [
        'com_espaco_fisico' => [
            'BENEFICIÁRIO COM CNPJ E ESPAÇO FÍSICO',
            'BENEFICIÁRIO COM CPF E ESPAÇO FÍSICO',
        ],
        'sem_espaco_fisico' => [
            'BENEFICIÁRIO COM CNPJ E SEM ESPAÇO FÍSICO',
            'BENEFICIÁRIO COM CPF E SEM ESPAÇO FÍSICO',
        ],
    ],
]
```

<b>Configuração de inscrições culturais</b>

No campo inscricoes_culturais, deve-se informar os tipos de inscrições culturais disponíveis em seu formulário de cadastro, conforme exemplo abaixo

```
return [
    "inscricoes_culturais" => [
        'mapa-cultural' => 'Cadastro Estadual de Cultura (Mapa Cultural)',
        'cadastro-municipal' => 'Cadastros Municipais de Cultura',
        'sniic' => 'Sistema Nacional de Informações e Indicadores Culturais',
        'salic' => 'Sistema de Apoio às Leis de incentivo à Cultura (Salic)',
        'sicab' => 'Sistema de Informações Cadastrais do Artesanato Brasileiro',
        'outros' => 'Outros cadastros referentes a atividades culturais',
    ],
]
```
No inciso 2 essa busca e feita de forma automática no banco de dados, diferente do inciso 1 que devemos informar qual queremos utilizar no arquivo de configuração. na configuração do inciso 2, basta informar o nome do campo onde e capturado esse dado no formulario e ele fará a busca do registro.

<b>Configuração de área atuação cultural</b>

Para a área de atuação cultural, deve-se informar exatamente os textos cadastrados no campo, durante a elaboração do formulário. Abaixo esta um exemplo.

```
return[
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
]
```

### Abaixo esta alguns exeplos de como exportador esta recuperando os dados dentro do arquico DataPrev.php ###

**Exemplo para recuperar um CPF**

```
$fields = [
    "CPF" => function ($registrations) use ($csv_conf) {
        $field_id = $csv_conf["CPF"];
        return str_replace(['.', '-'], '', $registrations->$field_id);
    },
]
```

**Exemplo para recuperar o sexo**
```
$fields = [
    'SEXO' => function ($registrations) use ($csv_conf) {
        $field_id = $csv_conf["SEXO"];

        if ($registrations->$field_id == 'Masculino') {
            return 1;

        } else if ($registrations->$field_id == 'Feminino') {
            return 2;

        } else {
            return 0;
        }
    }
]
```

**Examplo de como se recupera os dados de inscrições culturais no inciso 1**

#### Arquivo de configuração ####

```
return [
    "FLAG_CAD_MUNICIPAL" => 0,
    "SEXO" => null,
    "FLAG_CAD_SALIC" => false,
]

```
#### DataPrev.php ####

```
$fields = [
    "FLAG_CAD_ESTADUAL" => function ($registrations) use ($csv_conf) {
        $field_id = $csv_conf["FLAG_CAD_MUNICIPAL"];
        return $field_id;
    }
]
```

**Exemplo de como se recuperar os dados de incrição cultural no inciso 2**


```
return [
    'FLAG_CAD_MUNICIPAL' => 'INSCRIÇÃO EM CADASTRO CULTURAL:',
    'SISTEMA_CAD_MUNICIPAL' => null,
    'IDENTIFICADOR_CAD_MUNICIPAL' => null,
]

```
#### DataPrev.php ####

```
$fields = [
    'FLAG_CAD_MUNICIPAL' => function ($registrations) use ($fields_cpf, $inscricoes) {
        $field_id = $fields_cpf["FLAG_CAD_MUNICIPAL"];

        $option = $inscricoes['mapa-cultural'];

        $result = 0;

        if (is_array($registrations->$field_id)) {
            if ($field_id && in_array($option, $registrations->$field_id)) {
                $result = 1;
            }

        } else {
            if ($field_id && $registrations->$field_id == $option) {
                $result = 1;
            }

        }

        return $result;

    }
]
```


#### Exeplo de como recuperar  as áreas de atuações culturais ####

```
$fields = [
    'FLAG_ATUACAO_ARTES_CENICAS' => function ($registrations) use ($csv_conf, $fields_cnpj, $atuacoes, $category) {
        $field_temp = $fields_cnpj['FLAG_ATUACAO_ARTES_CENICAS'];

        if (is_array($field_temp)) {
            foreach (array_filter($field_temp) as $key => $value) {
                if ($registrations->$value) {
                    $field_id = $registrations->$value;

                } else {
                    $field_id = "";

                }
            }
        } else {
            $field_id = $registrations->$field_temp;
        }

        $options = $atuacoes['artes-cenicas'];

        $result = 0;
        foreach ($options as $value) {

            if (in_array($value, $options)) {
                $result = 1;
            }
        }

        return $result;
    }
];
```