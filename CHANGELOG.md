## [Unreleased]
- Adiciona um novo hook na página de status
- Realiza correções no importador do MCI470 [#162](https://github.com/mapasculturais/plugin-AldirBlanc/issues/162)
- Padroniza exibição da área de upload de desbancarizados [#162](https://github.com/mapasculturais/plugin-AldirBlanc/issues/162)
- Adiciona suporte a múltiplos pagamentos no mesmo arquivo PPG100 [#169](https://github.com/mapasculturais/plugin-AldirBlanc/issues/163)
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

## [v2.3.1] - 2020-12-01
- Implementa os importadores para o MCI470 e PPG101 [#162](https://github.com/mapasculturais/plugin-AldirBlanc/issues/162)
- Reordena as avaliações antes da reconsolidação do resultado das inscrições, para colocar as avaliações que foram importadas (as que têm id igual ao id da inscrição) para serem processadas primeiro;
- Redireciona usuários com controle sobre alguma oportunidade ou avaliadores de alguma oportunidade para o painel ao acessar a tela de cadastro. [#161]https://github.com/mapasculturais/plugin-AldirBlanc/issues/161 

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
