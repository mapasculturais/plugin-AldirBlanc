# plugin-AldirBlanc #
Plugin que implementa no Mapas Culturais os formulários do inciso I e II da Lei Aldir Blanc 

## Configuração do fluxo de homologação e validações das inscrições
Para o fluxo de homologação e validações, são utilizados 3 plugins:

- [plugin-AldirBlanc](https://github.com/mapasculturais/plugin-AldirBlanc) - base da solução Aldir Blanc, implementa o cadastro e a base dos plugins validadores
- [plugin-AldirBlancDataprev](https://github.com/mapasculturais/plugin-AldirBlancDataprev) - implementa as exportações dos CSVs para o Dataprev e as importações dos arquivos de retorno. 
- [plugin-AldirBlancValidador](https://github.com/mapasculturais/plugin-AldirBlancValidador) - validador genérico no qual é possível configurar o layout do arquivo exportado. O arquivo exportado contém duas colunas para serem preenchidas com o status e as observações das avaliações, para que seja enviado para o sistema novamente. Este plugin pode ser instanciado várias vezes para diferentes validações.

É possível configurar no sistema a ordem em que ocorrem as validações e a homologação das inscrições, além de quando que as avaliações/validações são consolidadas nas inscrições para que possam ser publicadas para o requerente. Por padrão, primeiro ocorre a homologação e depois as validações.

Abaixo seguem 2 exemplos de configuração, com os parâmetros comentados. 

### Exemplo 1 
Neste exemplo de configuração, primeiro ocorre a homologação e depois ocorrem em paralelo as validações do Dataprev e SCGE (plugin-AldirBlancValidador). A consolidação do resultado só acontece quando Dataprev e SCGE tiverem validado a inscrição.

- homologação
    - validação Dataprev
    - validação SCGE (Validador Genérico)

```PHP

    'plugins' => [
        
        'AldirBlanc' => [
            'namespace' => 'AldirBlanc',
            'config' => [ 
                ... outras configurações 

                // primeiro ocorre a homologação, só depois as validações
                'homologacao_requer_validacao' => [],

                // indica que a consolidação do status "Selecionada" só deve acontecer 
                // depois das validações
                'consolidacao_requer_validacao' => ['dataprev', 'scge']
            ]
        ],

        'AldirBlancDataprev' => [
            'namespace' => 'AldirBlancDataprev',
            'config' => [
                // indica que só deve exportar as inscrições já homologadas
                'exportador_requer_homologacao' => true,

                // indica que só deve consolidar o resultado se a inscrição
                // já tiver sido processada pelo SCGE
                'consolidacao_requer_validacoes' => ['scge']
            ],
        ],

        'SCGE' => [
            'namespace' => 'AldirBlancValidador',
            'config' => [
                // slug utilizado como id do controller e identificador do validador
                'slug' => 'scge',

                // nome apresentado na interface
                'name' => 'SCGE',

                // indica que só deve exportar as inscrições já homologadas
                'exportador_requer_homologacao' => true,

                // indica que a exportação não requer nenhuma validação
                'exportador_requer_validacao' => [],

                // indica que só deve consolidar o resultado se a inscrição
                // já tiver sido processada pelo Dataprev
                'consolidacao_requer_validacoes' => ['dataprev'],

                'inciso1' => [
                    // id do field do formulário 
                    'CPF' => 33,

                    // para campos compostos, ou que não estão no formulário, 
                    // é possível utilizar uma função que recebe a inscrição 
                    // e o nome da coluna, no caso $key = 'campo_personalizado'
                    'campo_personalizado' => function ($registration, $key) {
                        return $registration->owner->
                    }
                ],
            ]
        ]
```


### Exemplo 2 
Neste exemplo de configuração, primeiro ocorre a validação pelo Dataprev, depois a homologação e só depois a validação do SCGE (plugin-AldirBlancValidador). A consolidação do resultado só acontece quando o SCGE validar a inscrição. Somente as inscrições validadas pelo dataprev aparecerão para os avaliadores avaliarem.

- validação Dataprev
    - homologação
        - validação SCGE (Validador Genérico)

```PHP

    'plugins' => [
        
        'AldirBlanc' => [
            'namespace' => 'AldirBlanc',
            'config' => [ 
                ... outras configurações 

                // primeiro ocorre a validação pelo Dataprev, só depois a homologação
                'homologacao_requer_validacao' => ['dataprev'],

                // indica que a consolidação do status "Selecionada" só deve acontecer 
                // depois das validações
                'consolidacao_requer_validacao' => ['dataprev', 'scge']
            ]
        ],

        'AldirBlancDataprev' => [
            'namespace' => 'AldirBlancDataprev',
            'config' => [
                // indica que deve exportar TODAS as inscrições enviadas (status pendente)
                'exportador_requer_homologacao' => false,

                // indica que só deve consolidar o resultado se a inscrição
                // já tiver sido processada pelo SCGE
                'consolidacao_requer_validacoes' => ['scge']
            ],
        ],

        'SCGE' => [
            'namespace' => 'AldirBlancValidador',
            'config' => [
                // slug utilizado como id do controller e identificador do validador
                'slug' => 'scge',

                // nome apresentado na interface
                'name' => 'SCGE',

                // indica que só deve exportar as inscrições já homologadas
                'exportador_requer_homologacao' => true,

                // indica que a exportação requer que as inscrições tenham sido validadas
                // pelo Dataprev
                'exportador_requer_validacao' => ['dataprev'],

                // indica que deve consolidar o resultado na importação do arquivo
                // de retorno.
                'consolidacao_requer_validacoes' => [],

                'inciso1' => [
                    // id do field do formulário 
                    'CPF' => 33,

                    // para campos compostos, ou que não estão no formulário, 
                    // é possível utilizar uma função que recebe a inscrição 
                    // e o nome da coluna, no caso $key = 'campo_personalizado'
                    'campo_personalizado' => function ($registration, $key) {
                        return $registration->owner->
                    }
                ],
            ]
        ]
```
