<?php
function __mediacao_file($file) {
    if(!$file) {
        return;
    }
    ?>
    <div id="file-<?= $file->id ?>">
        <a href="<?= $file->url ?>" rel="noopener noreferrer"><?= $file->name ?></a> 
        <div class="botoes">
            <a data-href="<?= $file->deleteUrl ?>" data-target="#file-<?= $file->id ?>" data-configm-message="Remover este arquivo?" class="icon icon-close hltip js-remove-item" data-hltip-classes="hltip-ajuda" title="Excluir arquivo" rel="noopener noreferrer"></a>
        </div>
    </div>
    <?php
}
?>
<div class="registration-fieldset">
    <div class="label"> Mediação </div>

    <div style="display: inline-block; width: 45%;">
        <a class="btn btn-default send js-open-editbox" data-target="#editbox-upload-autorizacao" href="#" >Enviar autorização</a>
        <div id="mediacao-autorizacao">
            <?php __mediacao_file($entity->getFile('mediacao-autorizacao')) ?>
        </div>

        <div id="editbox-upload-autorizacao" class="js-editbox mc-left" title="Enviar autorização" data-submit-label="Enviar">
            <?php $this->ajaxUploader($entity, 'mediacao-autorizacao', 'set-content', '#mediacao-autorizacao', '
                <div id="file-{{id}}">
                    <a href="{{url}}" rel="noopener noreferrer">{{name}}</a> 
                    <div class="botoes">
                        <a data-href="{{deleteUrl}}" data-target="#file-{{id}}" data-configm-message="Remover este arquivo?" class="icon icon-close hltip js-remove-item" data-hltip-classes="hltip-ajuda" title="Excluir arquivo" rel="noopener noreferrer"></a>
                    </div>
                </div>', '', true, false, false) ?>
        </div>
    </div>

    <div style="display: inline-block; width: 45%;">
        <a class="btn btn-default send js-open-editbox" data-target="#editbox-upload-document" href="#" >Enviar documento</a>
        <div id="mediacao-documento">
            <?php __mediacao_file($entity->getFile('mediacao-documento')) ?>
        </div>

        <div id="editbox-upload-document" class="js-editbox mc-left" title="Enviar documento" data-submit-label="Enviar">
            <?php $this->ajaxUploader($entity, 'mediacao-documento', 'set-content', '#mediacao-documento', '
                <div id="file-{{id}}">
                    <a href="{{url}}" rel="noopener noreferrer">{{name}}</a> 
                    <div class="botoes">
                        <a data-href="{{deleteUrl}}" data-target="#file-{{id}}" data-configm-message="Remover este arquivo?" class="icon icon-close hltip js-remove-item" data-hltip-classes="hltip-ajuda" title="Excluir arquivo" rel="noopener noreferrer"></a>
                    </div>
                </div>', '', true, false, false) ?>
        </div>
    </div>
</div>