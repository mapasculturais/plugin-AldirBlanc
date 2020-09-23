# plugin-AldirBlanc #
Plugin que implementa no Mapas Culturais os formulários do inciso I e II da Lei Aldir Blanc 

# Configuração do exportador de arquivs CSV inciso-1 #

Antes de tudo certifique-se de estar com a biblioteca CSV instalada em seu ambiente.
Para ver a documentação da lib, acessar https://csv.thephpleague.com/

**Pach para arquivo de configuração**

Para configurar o exportador, vocẽ deve fazer a configuração no arquivo que esta no caminho abaixo.

#### mapasculturais-aldirblanc/plugins/AldirBlanc Acessar o arquivo arquivo config-csv-inciso1.php ####

**Exemplo para recuperar um CPF**

$fields = [
    "CPF" => function ($registrations) {
        return str_replace(['.', '-'], '', $registrations->field_30);
    },
]

**Exemplo para recuperar o sexo**

$fields = [
   'SEXO' => function ($registrations) {
        if ($registrations->field_17 == 'Masculino') {
            return 1;
        } else if ($registrations->field_17 == 'Feminino') {
            return 2;
        } else {
            return 0;
        }
    ]

**Exemplo para um campo vazio**

$fields = [
    "SISTEMA_CAD_MUNICIPAL" => null,
    ]

**Exemplo para um campo boolean ou inteiro**

$fields = [
    "FLAG_CAD_ESTADUAL" => 1,
    "FLAG_CAD_DISTRITAL" => 0,
    ]

**Exemplo para retornar o campo direto ou seja, diretamente pelo fild_id**
 
$fields = [
   "FAMILIARCPF" => 'field_5',
    ]
