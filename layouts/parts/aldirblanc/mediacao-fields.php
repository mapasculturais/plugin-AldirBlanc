<?php
function __mediacao_file($file)
{
    if (!$file) {
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
<div class="registration-fieldset registration-fieldset-moderator" ng-controller="RegistrationFieldsController">

    <div class="each-line">
        <span class="label">Documentação para a mediação <span class="required">*todos campos são obrigatórios</span></span>
    </div>

    <div class="each-line">
        <div class="buttons">
            <a class="btn btn-default send js-open-editbox" data-target="#editbox-upload-autorizacao" href="#">Enviar autorização</a>
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

        <div class="buttons">
            <a class="btn btn-default send js-open-editbox" data-target="#editbox-upload-document" href="#">Enviar foto do RG</a>
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

    {{::(entity.mediacao_contato_tipo = "<?= $entity->mediacao_contato_tipo ?>") ? '' : null}}
    {{::(entity.mediacao_contato = "<?= $entity->mediacao_contato ?>") ? '' : null}}
    {{::(entity.mediacao_senha = "<?= $entity->mediacao_senha ?>") ? '' : null}}

    <div class="each-line field-mediacao-contato-tipo">
        <span class="label">O contato será por <span class="required">*</span></span>
        <label class="mr-10">
            <input type=radio ng-model="entity.mediacao_contato_tipo" ng-change="saveField({fieldName: 'mediacao_contato_tipo'}, entity.mediacao_contato_tipo)" value="telefone-fixo" />
            Telefone fixo
        </label>
        <label class="mr-10">
            <input type=radio ng-model="entity.mediacao_contato_tipo" ng-change="saveField({fieldName: 'mediacao_contato_tipo'}, entity.mediacao_contato_tipo)" value="whatsapp" />
            WhatsApp
        </label>
        <label class="mr-10">
            <input type=radio ng-model="entity.mediacao_contato_tipo" ng-change="saveField({fieldName: 'mediacao_contato_tipo'}, entity.mediacao_contato_tipo)" value="SMS" />
            SMS
        </label>
    </div>

    <div class="each-line">
        <span class="label">Número de telefone <span class="required">*</span></span>
        <label>
            <input id="field-mediacao-contato" class="input-text" ng-model="entity.mediacao_contato" ng-blur="saveField({fieldName: 'mediacao_contato'}, entity.mediacao_contato)" js-mask="(99) 9999 99999" placeholder="(__) _________" ng-required=true />
        </label>
    </div>

    <div class="each-line">
        <span class="label">Senha <span class="required">*</span></span>
        <span class="description">Essa senha será utilizada pelo inscrito, junto com o CPF, para acompanhar a sua inscrição.</span>
        <label>
            <input id="field-mediacao-senha" class="input-text" ng-model="entity.mediacao_senha" ng-blur="saveField({fieldName: 'mediacao_senha'}, entity.mediacao_senha)" ng-required=true />
        </label>
    </div>

</div>