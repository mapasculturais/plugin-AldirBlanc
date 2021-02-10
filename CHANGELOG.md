## [Unreleased]
- endpoint para corrigir espaços e agentes de inscrições mediadas

## [v2.4.0]
- Corrige bug no processamento de múltiplos pagamentos do exportador PPG100 [#169](https://github.com/mapasculturais/plugin-AldirBlanc/issues/169)
- Corrige exibição de listagem de arquivos que apareciam em oportunidades que não são da AldirBlanc
- Recuperação de senha de mediados
- Endpoint para validação dos dados Bancários [#174](https://github.com/mapasculturais/plugin-AldirBlanc/issues/174)
- Altera referência padrão dos PPG10x para ID do pagamento [#162](https://github.com/mapasculturais/plugin-AldirBlanc/issues/162)
- Corrige bug de armazenamento dos metadados no importador do MCI470 [#171](https://github.com/mapasculturais/plugin-AldirBlanc/issues/171)
- Realiza correções no importador do MCI470 [#162](https://github.com/mapasculturais/plugin-AldirBlanc/issues/162)
- Padroniza exibição da área de upload de desbancarizados [#162](https://github.com/mapasculturais/plugin-AldirBlanc/issues/162)
- Adiciona suporte a múltiplos pagamentos no mesmo arquivo PPG100 [#169](https://github.com/mapasculturais/plugin-AldirBlanc/issues/169)
- Adiciona ao formulário de avaliação dos incisos I e II a informação da consolidação atual das avaliações da inscrição [#163](https://github.com/mapasculturais/plugin-AldirBlanc/issues/163)
- Realiza correções no importador do MCI470 [#162](https://github.com/mapasculturais/plugin-AldirBlanc/issues/162)
- Refatorar CNAB240 para exportar valores quebrados EX.: 2.904,80 (Ref. [#167](https://github.com/mapasculturais/plugin-AldirBlanc/issues/167))
- Refatorar calculo da data de pagamento para 5 dias após o envio (Ref. [#167](https://github.com/mapasculturais/plugin-AldirBlanc/issues/167))
- Inserir Número da inscrição no logradouro para que a mesma retorne nos arquivos de retorno BB (Ref. [#167](https://github.com/mapasculturais/plugin-AldirBlanc/issues/167))
- Corrige código de Câmara centralizadora para outros banco para 018 no CNAB240 (Ref. [#170](https://github.com/mapasculturais/plugin-AldirBlanc/issues/170))
- Corrige Forma de lançamento para lote de outros bancos para 041 no CNAB240(Ref. [#170](https://github.com/mapasculturais/plugin-AldirBlanc/issues/170))
- Corrige prefixo para casos de conta corrente de 510 para 51 no CNAB240(Ref. [#170](https://github.com/mapasculturais/plugin-AldirBlanc/issues/170))
- Inserire opção de fazer exportação de arquivos teste (TS) no CNAB240(Ref. [#170](https://github.com/mapasculturais/plugin-AldirBlanc/issues/170))
- Inserire configurações para habilitar e desabilitar botão do CNAB240 nos incisos no CNAB240(Ref. [#170](https://github.com/mapasculturais/plugin-AldirBlanc/issues/170))
- Inserire posições 178 a 179 campo 24.3A No lote 2 Poupança BB inserir o valor default 11 no CNAB240(Ref. [#172](https://github.com/mapasculturais/plugin-AldirBlanc/issues/172))
- Corrige complemento de conta poupança para 9 caracteres prefixo 51 + 7 números1 no CNAB240(Ref. [#172](https://github.com/mapasculturais/plugin-AldirBlanc/issues/172))
- Insere quebra de linha no treiller do lote no CNAB240 (Ref. [#177](https://github.com/mapasculturais/plugin-AldirBlanc/issues/177)
- Refatorar código para que o busque cnab240 os dados bancários nos metadados inseridos na tabela agent_meta apos a validação de contas bancárias. (Ref. [#175](https://github.com/mapasculturais/plugin-AldirBlanc/issues/175))
- Refatora Importador CNAB240 para que busque as inscrições pelo CPF. (Ref. [#185](https://github.com/mapasculturais/plugin-AldirBlanc/issues/185))
- Refatora Importador CNAB240 para permitir baixar arquivos de consolidação. (Ref. [#185](https://github.com/mapasculturais/plugin-AldirBlanc/issues/185))
- Refatora Importador CNAB240 para qpermitir processar mais de um retorno para mesma inscrição, fazendo a consolidação do resultado. (Ref. [#185](https://github.com/mapasculturais/plugin-AldirBlanc/issues/185))

## [v2.3.1] - 2020-12-01
- Implementa os importadores para o MCI470 e PPG101 [#162](https://github.com/mapasculturais/plugin-AldirBlanc/issues/162)
- Reordena as avaliações antes da reconsolidação do resultado das inscrições, para colocar as avaliações que foram importadas (as que têm id igual ao id da inscrição) para serem processadas primeiro;
- Implementa função para processar os retornos de pagamento do Banco do Brasil para o CNAB240 [#173]https://github.com/mapasculturais/plugin-AldirBlanc/issues/173 
- Refatora CNAB240 para ser possivel separar a exportação por lotes [#176]https://github.com/mapasculturais/plugin-AldirBlanc/issues/176 

## [v2.3.0] - 2020-10-27

- Corrige bug na exportação da planilha de endereços (Ref. [#156](https://github.com/mapasculturais/plugin-AldirBlanc/issues/156))
- Corrige bug na exportação do PPG100 e implementa novo número de protocolo (Ref. [#150](https://github.com/mapasculturais/plugin-AldirBlanc/issues/150))
- Insere função nos exportadores CNAB240 e genérico, para exportar uma lista de inscrições passadas pelo usuário (Ref. [#157](https://github.com/mapasculturais/plugin-AldirBlanc/issues/157))
- Corrige forma de capturar DV da conta em casos de contas digital BB (Ref. [#158](https://github.com/mapasculturais/plugin-AldirBlanc/issues/158))
- Refatora exportador CNAB240 para o retorno do DV da conta nao ignorar strings EX: DV = X #158 (Ref. [#158](https://github.com/mapasculturais/plugin-AldirBlanc/issues/158))
- Corrige exportador CNAB240 para sempre pegar o ultimo caracter no DV caso ele tenha 2 EX. 57 irá retornar 7 (Ref. [#158](https://github.com/mapasculturais/plugin-AldirBlanc/issues/158))
- Corrige nome do input no formulário do CNAB240 (Ref. [#160](https://github.com/mapasculturais/plugin-AldirBlanc/issues/160))
- Adiciona possibilidade mensagem no lugar do botão de enviar inscrição quando desabilitado através da configuração 'mensagens_envio_desabilitado'  (Ref. [#159](https://github.com/mapasculturais/plugin-AldirBlanc/issues/159))
- Adiciona possibilidade de impedir envios de inscrição de um array de oportunidades através da configuração 'oportunidades_desabilitar_envio' (Ref. [#159](https://github.com/mapasculturais/plugin-AldirBlanc/issues/159))
- Refatora CNAB240 para ser possivel separar a exportação por lotes [#176]https://github.com/mapasculturais/plugin-AldirBlanc/issues/176 

## [v2.2.0] - 2020-10-25

- Padronização dos exportadores para uso dos sistemas (de-para) via CSV (Ref. [#150](https://github.com/mapasculturais/plugin-AldirBlanc/issues/150))
- Opção de exportar por Data de pagamento nos exportadores genéricos, CNAB240 e PPG100 (Ref. [#150](https://github.com/mapasculturais/plugin-AldirBlanc/issues/150))
- Opção para exportar inscrições que já tenha pagamento cadastrados e ainda não foram enviadas para pagamento nos exportadores genéricos, CNAB240 e PPG100 (Ref. [#150](https://github.com/mapasculturais/plugin-AldirBlanc/issues/150))
- Opção para exportar todas as inscrições que tenham pagamento cadastrados independentemente do status nos exportadores genéricos, CNAB240 e PPG100 (Ref. [#150](https://github.com/mapasculturais/plugin-AldirBlanc/issues/150))
- Fazer com que o status do pagamento mude para 3 ao exportar pagamentos ainda não exportados nos exportadores genéricos, CNAB240 e PPG100 (Ref. [#150](https://github.com/mapasculturais/plugin-AldirBlanc/issues/150))
- Adiciona coluna na exportação das inscrições informando se foi feita por mediador (Ref. [#35](https://git.hacklab.com.br/mapas/MapasBR/-/issues/35))
- Corrige exportador CNAB240 para não ignorar casas decimais no somatório dos treillers do lote (Ref. [#151](https://github.com/mapasculturais/plugin-AldirBlanc/issues/151))

## [v2.1.0] - 2020-10-23

- Adiciona quebra de linha nas avaliações nas mensagens de status (Ref. [#12](https://git.hacklab.com.br/mapas/mapas-es/-/issues/12))
- corrige fonte do número de seqüência dos arquivos
- Adiciona configuracao para definir mensagem de reprocessamento do Dataprev
- prepara plugin validador para o inciso 3

## [v2.0.0] - 2020-10-23

## [v1.0.0] - 2020-10-02
