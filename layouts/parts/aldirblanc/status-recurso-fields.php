<?php if ($this->isEditable()) : ?>

    <div id="registration-recourse-configuration" class="registration-fieldset project-edit-mode">
        <h4>Mensagem de Recurso para o Status</h4>
        <p class="registration-help" ng-if="data.isEditable"><?php \MapasCulturais\i::_e("Adicione uma mensagem indicando a forma com que o inscrito deve enviar os recursos, quando necessário."); ?></p>
        <p>
            <span class="label">Mensagem:</span>
            <span class="js-editable" data-edit="aldirblanc_status_recurso" data-original-title="Mensagem de Recurso" data-emptytext="Adicione aqui a mensagem">
                <?php echo $opportunity->aldirblanc_status_recurso; ?>
            </span>
        </p>
    </div>
    
<?php endif; ?>