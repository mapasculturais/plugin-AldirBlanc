# plugin-AldirBlanc #
Plugin que implementa no Mapas Culturais os formulários do inciso I e II da Lei Aldir Blanc 

# Configuração do exportador de arquivs CSV inciso-1 #

Antes de tudo certifique-se de estar com a biblioteca CSV instalada em seu ambiente.
Para ver a documentação da lib, acessar https://csv.thephpleague.com/

**Configuração do arquivo config-csv-inciso1.php**

Para configurar o exportador, você deve copiar o arquivo <b>config-csv-inciso1-exemplo.php</b> que está no caminho .

#### mapasculturais-aldirblanc/plugins/AldirBlanc #### 

e colar no arquivo arquivo <b>config-csv-inciso1.php</b>  que esta no mesmo diretório.


#### Abaixo temos alguns exeplos de como recuperar os dados dentro do controlador DataPrev.php ####

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

**Para colocar um valor especifico no campo, basta informar o valor desejado no arquivo de configuração e recuperar como o exemplo abaixo no DataPrev.php**

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
#### Exeplo de como recuperar dados que são retornados como array ####

```
$fields = [
    "FLAG_ATUACAO_ARTES_CENICAS" => function ($registrations) use ($csv_conf) {
        $field_id = $csv_conf["FLAG_ATUACAO_ARTES_CENICAS"];
        $options = [
            'Artes Circenses',
            'Dança',
            'Teatro',
            'Artes Visuais',
            'Artesanato',
            'Ópera',
        ];
        foreach ($options as $key => $value) {
            if (in_array($value, $registrations->$field_id)) {
                return 1;
            } else {
                return 0;
            }
        }
    }
]
```

# Configuração do exportador de arquivs CSV inciso-2 #

Antes de tudo certifique-se de estar com a biblioteca CSV instalada em seu ambiente.
Para ver a documentação da lib, acessar https://csv.thephpleague.com/

**Configuração do arquivo config-csv-inciso2.php**

Para configurar o exportador, você deve copiar o arquivo <b>config-csv-inciso2-exemplo.php</b> que está no caminho.

#### mapasculturais-aldirblanc/plugins/AldirBlanc #### 

e colar no arquivo arquivo <b>config-csv-inciso2.php</b>  que esta no mesmo diretório.

# Observação importante #

No exportador do inciso 2, é utilizado o nome  do campo para fazer a busca dos registros. 
Então no arquivo de configuração não deve ser inserido diretamente o fild_id do campo e sim o seu label.

#### ATENÇÃO ####
<b>O texto deve estar identico ao label, caso contrario não será retornado valor algum.</b>

# Arquivo de configuração #

Como em alguns casos irá existir mais de um campo com o memo texto label ou eté mesmo, em alguns casos os campos vão ter label diferentes porem cada um em uma categoria.
Por exemplo, um campo tem um nome XX em um adeterminada categoria e em outra gategoria tem um nome YY, como mostra no exemplo 1, em alguns casos o sistema já esta esperando um array. segue abaixo campos que já estão implementado desse forma.

- NOME DO ESPAÇO CULTURAL VINCULADO OU MANTIDO PELO BENEFICIÁRIO DO SUBSÍDIO
- NOME_ESPACO_CULTURAL
- CNPJ

e no exemplo abaixo

- NOME_DO_ESPACO_CULTURAL 
- NOME DO ESPAÇO CULTURAL VINCULADO OU MANTIDO PELO BENEFICIÁRIO DO SUBSÍDIO 

representam o mesmo registro "Mesmo campo no DataPrev" porem, cada campo se encontra em uma categoria diferente.

O mesmo acontece com CNPJ, porem nesse caso os campos em ambas as categorias tem exatamente o mesmo nome (label) como mostra no Exemplo 2.

#### Exeplo 1 ####
```

fields_cpf" => [            
        'NOME_ESPACO_CULTURAL' => [
        'NOME DO COLETIVO',
        'NOME DO ESPAÇO CULTURAL VINCULADO OU MANTIDO PELO BENEFICIÁRIO DO SUBSÍDIO:'
    ],
]
```
#### Exeplo 2 ####
```

"fields_cnpj" => [
    'CNPJ' => [
        'NÚMERO DE INSCRIÇÃO EM CADASTRO NACIONAL DE PESSOA JURÍDICA – CNPJ:', 
        'NÚMERO DE INSCRIÇÃO EM CADASTRO NACIONAL DE PESSOA JURÍDICA – CNPJ:'
    ],
]
```

Odnde cada posição desse array, deve conter o "label"  dos campos.

#### Campo que os dados são fornecidos diretamente pelo arquivo de configuração ####

Em alguns casos, deverá ser inserido o valor desejado diretamente no arquivo de configuração como mostra no exemplo abaixo.

```
"fields_cpf" =>[
    'FLAG_CAD_DISTRITAL' => string 'dados vem da config',
    'SISTEMA_CAD_DISTRITAL' => string 'dados vem da config',
    'IDENTIFICADOR_CAD_DISTRITAL' => string 'dados vem da config'
]
```

Nesse caso, será inserido exatamente o que esta no arquivo de configuração.
Caso precise que insrir um valor oriundo da base de dados, basta colocar o nome do "label" do campo.
