<?php

$mediacao_contato_tipo = '';

switch ($entity->mediacao_contato_tipo) {
    case 'telefone-fixo':
        $mediacao_contato_tipo = 'Telefone fixo';
        break;

    case 'whatsapp':
        $mediacao_contato_tipo = 'WhatsApp';
        break;

    case 'SMS':
        $mediacao_contato_tipo = 'SMS';
        break;

    default:
        $mediacao_contato_tipo = '--';
        break;
}

// Retorna todos pagamentos dessa inscrição
$payments = $app->repo('RegistrationPayments\Payment')->findBy(['registration' => $entity->id]);
$isPPG = false;

// Verifica se existe pagamento do PPG101
foreach ($payments as $p) {
    if ($p->metadata['ppg101'] ?? '') {
        $isPPG = true;
    }
}

if ($isPPG) :

    $ppg_data = $app->plugins['AldirBlanc']->getSenhasPPG($entity->id) ?? false;

    if ($ppg_data) : ?>

        <div class="registration-fieldset registration-fieldset-moderator registration-fieldset-moderator-payments">

            <div class="each-line">
                <span class="label">Ordens de pagamento</span>
            </div>

            <span><em>
            O pagamento das três parcelas de R$ 1.000,00 do seu benefício está disponível para saque em qualquer caixa eletrônico do Banco do Brasil ou 24 horas. Para realizar o saque, você precisa dos números de protocolo e senhas abaixo.<br><br>
            No caixa eletrônico, selecione no menu “opções sem cartão”, na sequência escolha “Saques” e depois “Benefícios Emergenciais”. Para encerrar, informe o número do protocolo e a senha da primeira parcela e faça o saque do valor. Finalize o processo e comece novamente para realizar o saque da segunda parcela, seguindo as mesmas orientações e alterando o número de protocolo e senha. Por fim, repita o procedimento para realizar o saque da terceira parcela.<br><br>
            Se você foi aprovada como mulher chefe de família (monoparental). Serão seis parcelas e o procedimento será repetido por seis vezes.
            </em></span>

            <hr>

            <?php foreach ($ppg_data as $payment) : ?>

                <div class="each-line">
                    <span class="label">Protocolo</span>
                    <span><?php echo $payment->protocolo; ?></span>
                </div>

                <div class="each-line">
                    <span class="label">Senha</span>
                    <span><?php echo $payment->senha; ?></span>
                </div>

                <div class="each-line">
                    <span class="label">Valor</span>
                    <span><?php echo 'R$ ' . number_format($payment->payment->amount, 2, ',', '.'); ?></span>
                </div>

                <hr>

            <?php endforeach; ?>

        </div><!-- /.registration-fieldset-moderator-payments -->

    <?php endif;

endif; // $isPPG  ?>

<div class="registration-fieldset registration-fieldset-moderator">

    <div class="each-line">
        <span class="label">Documentação para a mediação</span>
    </div>

    <hr>

    <div class="each-line">
        <span class="label">Autorização</span>
        <?php if (isset($entity->files['mediacao-autorizacao'][0])) : ?>
            <a class="attachment-title" href="<?php echo $entity->files['mediacao-autorizacao'][0]->getUrl(); ?>" target="_blank" rel="noopener noreferrer"><?php echo basename($entity->files['mediacao-autorizacao'][0]->getPath()); ?></a>
        <?php else : ?>
            <span><em>Arquivo não enviado</em></span>
        <?php endif; ?>
    </div>
    <div class="each-line">
        <span class="label">Documento (RG)</span>
        <?php if (isset($entity->files['mediacao-documento'][0])) : ?>
            <a class="attachment-title" href="<?php echo $entity->files['mediacao-documento'][0]->getUrl(); ?>" target="_blank" rel="noopener noreferrer"><?php echo basename($entity->files['mediacao-documento'][0]->getPath()); ?></a>
        <?php else : ?>
            <span><em>Arquivo não enviado</em></span>
        <?php endif; ?>
    </div>
    <div class="each-line">
        <span class="label">O contato será por</span>
        <span><?php echo $mediacao_contato_tipo; ?></span>
    </div>

    <div class="each-line">
        <span class="label">Telefone</span>
        <span><?php echo $entity->mediacao_contato; ?></span>
    </div>

</div>