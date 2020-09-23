<?php
function __mediacao_file($file) {
    if(!$file) {
        return;
    }
    ?>
    <div id="file-<?= $file->id ?>">
        <a href="<?= $file->url ?>" rel="attachment-title noopener noreferrer" target="_blank"><?= $file->name ?></a> 
        <a data-href="<?= $file->deleteUrl ?>" data-target="#file-<?= $file->id ?>" data-configm-message="Remover este arquivo?" class="icon icon-delete hltip js-remove-item delete" data-hltip-classes="hltip-ajuda" title="Excluir arquivo" rel="noopener noreferrer"></a>
    </div>
    <?php
}
?>
<div class="registration-fieldset" ng-controller="RegistrationFieldsController">
    <p>
        <strong>Documentação para a mediação </strong>
    </p>

    <div style="display: inline-block; width: 49%;" >
        <div id="mediacao-autorizacao">
            <?php __mediacao_file($entity->getFile('mediacao-autorizacao')) ?>
        </div>
        <a class="btn btn-default send js-open-editbox" data-target="#editbox-upload-autorizacao" href="#" >Enviar autorização</a>

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

    <div style="display: inline-block; width: 49%;" >
        <div id="mediacao-documento">
            <?php __mediacao_file($entity->getFile('mediacao-documento')) ?>
        </div>
        <a class="btn btn-default send js-open-editbox" data-target="#editbox-upload-document" href="#" >Enviar foto do RG</a>

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

    
    {{::(entity.mediacao_contato_tipo = "<?= $entity->mediacao_contato_tipo ?>") ? '' : null}}
    {{::(entity.mediacao_contato = "<?= $entity->mediacao_contato ?>") ? '' : null}}
    
    <p class="mt-30">
        <strong>O contato será por</strong><br>
        <label class="mr-10">
            <input type=radio ng-model="entity.mediacao_contato_tipo" ng-change="saveField({fieldName: 'mediacao_contato_tipo'}, entity.mediacao_contato_tipo)" value="telefone-fixo" />
            Telefone Fixo
        </label>
        <label class="mr-10">
            <input type=radio ng-model="entity.mediacao_contato_tipo" ng-change="saveField({fieldName: 'mediacao_contato_tipo'}, entity.mediacao_contato_tipo)" value="whatsapp" />
            Whatsapp
        </label>
        <label class="mr-10">
            <input type=radio ng-model="entity.mediacao_contato_tipo" ng-change="saveField({fieldName: 'mediacao_contato_tipo'}, entity.mediacao_contato_tipo)" value="SMS" />
            SMS
        </label>
    </p>

    <label> Número de telefone <br>
        <input ng-model="entity.mediacao_contato" ng-blur="saveField({fieldName: 'mediacao_contato'}, entity.mediacao_contato)" js-mask="(99) 999999999" placeholder="(__) _________" />
    </label>
</div>