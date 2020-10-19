<div class="registration-fieldset card registration-fieldset-mediacao">

    <h4>Informações por Mediação</h4>

    <p>
        <strong>Autorização: </strong>
        <?php if (isset($entity->files['mediacao-autorizacao'][0])) : ?>
            <a class="attachment-title" href="<?php echo $entity->files['mediacao-autorizacao'][0]->getUrl(); ?>" target="_blank" rel="noopener noreferrer"><?php echo basename($entity->files['mediacao-autorizacao'][0]->getPath()); ?></a>
        <?php else : ?>
            <span>Arquivo não enviado</span>
        <?php endif; ?>
    </p>
    <p>
        <strong>Documento (RG): </strong>
        <?php if (isset($entity->files['mediacao-documento'][0])) : ?>
            <a class="attachment-title" href="<?php echo $entity->files['mediacao-documento'][0]->getUrl(); ?>" target="_blank" rel="noopener noreferrer"><?php echo basename($entity->files['mediacao-documento'][0]->getPath()); ?></a>
        <?php else : ?>
            <span>Arquivo não enviado</span>
        <?php endif; ?>
    </p>
    <p>
        <strong>O contato será por: </strong>
        <?php echo (isset($entity->mediacao_contato_tipo)) ? '--' : $entity->mediacao_contato_tipo; ?>
    </p>

    <p>
        <strong>Telefone: </strong>
        <?php echo (isset($entity->mediacao_contato)) ? '--' : $entity->mediacao_contato; ?>
    </p>

</div>