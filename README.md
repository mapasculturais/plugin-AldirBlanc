# plugin-AldirBlanc #
Plugin que implementa no Mapas Culturais os formulários do inciso I e II da Lei Aldir Blanc 

# Configuração do exportador de arquivs CSV inciso-1 #

Antes de tudo certifique-se de estar com a biblioteca CSV instalada em seu ambiente.
Para ver a documentação da lib, acessar https://csv.thephpleague.com/

**Configuração do arquivo config-csv-inciso1.php**

Para configurar o exportador, você deve copiar o arquivo <b>config-csv-inciso1-exemplo.php</b> que está no caminho .

#### mapasculturais-aldirblanc/plugins/AldirBlanc #### 

e colar no arquivo arquivo <b>config-csv-inciso1.php</b>  que esta no mesmo diretório.


#### Abaixo esta alguns exeplos de como recuperar os dados dentro do controlador DataPrev.php ####
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
