<?php

use MapasCulturais\i;

$route = MapasCulturais\App::i()->createUrl("aldirblanc", "email_ppg");

?>

<a class="btn btn-default download btn-export-cancel" ng-click="editbox.open('form-ppg', $event)" rel="noopener noreferrer">E-mails PPG</a>

<!-- Formulários -->
<edit-box id="form-ppg" position="top" title="Enviar e-mails PPG" cancel-label="Cancelar" close-on-cancel="true">
    <form class="form-export-dataprev" action="<?= $route ?>" method="POST">

        <div class="buttons">
           <div id="editbox-upload-document" class="js-editbox mc-left" title="Enviar documento" data-submit-label="Enviar">
                <?php $this->ajaxUploader($opportunity, 'ppg-txt', 'set-content', '#ppg-txt', '
                    <div id="file-{{id}}">
                        <a href="{{url}}" rel="noopener noreferrer">{{name}}</a> 
                        <div class="botoes">
                            <a data-href="{{deleteUrl}}" data-target="#file-{{id}}" data-configm-message="Remover este arquivo?" class="icon icon-close hltip js-remove-item" data-hltip-classes="hltip-ajuda" title="Excluir arquivo" rel="noopener noreferrer"></a>
                        </div>
                    </div>', '', true, false, false) ?>
            </div>
        </div>
        <div class="buttons">
            <div id="editbox-upload-document" class="js-editbox mc-left" title="Enviar documento" data-submit-label="Enviar">
                <?php $this->ajaxUploader($opportunity, 'ppg.ret', 'set-content', '#ppg.ret', '
                    <div id="file-{{id}}">
                        <a href="{{url}}" rel="noopener noreferrer">{{name}}</a> 
                        <div class="botoes">
                            <a data-href="{{deleteUrl}}" data-target="#file-{{id}}" data-configm-message="Remover este arquivo?" class="icon icon-close hltip js-remove-item" data-hltip-classes="hltip-ajuda" title="Excluir arquivo" rel="noopener noreferrer"></a>
                        </div>
                    </div>', '', true, false, false) ?>
            </div>
        </div>
        <div class="buttons">
            <div id="editbox-upload-document" class="js-editbox mc-left" title="Enviar documento" data-submit-label="Enviar">
                <?php $this->ajaxUploader($opportunity, 'ppg-map', 'set-content', '#ppg-map', '
                        <div id="file-{{id}}">
                            <a href="{{url}}" rel="noopener noreferrer">{{name}}</a> 
                            <div class="botoes">
                                <a data-href="{{deleteUrl}}" data-target="#file-{{id}}" data-configm-message="Remover este arquivo?" class="icon icon-close hltip js-remove-item" data-hltip-classes="hltip-ajuda" title="Excluir arquivo" rel="noopener noreferrer"></a>
                            </div>
                        </div>', '', true, false, false) ?>
            </div>

        </div>


        <input type="hidden" name="opportunity" value="<?= $opportunity ?>">
        <button class="btn btn-primary download" type="submit">Enviar E-mails</button>
    </form>
</edit-box>