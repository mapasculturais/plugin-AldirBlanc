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
} ?>

<div class="registration-fieldset registration-fieldset-moderator">

    <div class="each-line">
        <span class="label">Documentação para a mediação</span>
    </div>

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