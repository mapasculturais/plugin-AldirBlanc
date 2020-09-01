<div class="registration-fieldset">
    <a ng-click="validateRegistration()" class="btn btn-secondary">Validar</a>
    <div class="errors-header" ng-if="entityErrors">
        <p class="errors-header-title">O cadastro não foi enviado!</p>
        <p>Corrija os campos e envie novamente</p>
    </div>
    <div class="errors" ng-repeat="field in data.fields" ng-if="entityErrors[field.fieldName]">
        <a ng-click="scrollTo('wrapper-' + field.fieldName, 130)">
            {{field.title.replace(':', '')}}: <span class="errors-field" ng-repeat="error in entityErrors[field.fieldName]">{{error}} </span>
        </a>
    </div>
</div>
<div ng-if="entityValidated" style="display:flex" id="modalAlert" class="modal">
    <!-- Modal content -->
    <div class="modal-content">
        <!-- <span class="close">&times;</span> -->
        <h2 class="modal-content--title">Preenchimento Finalizado</h2>
        <p class="text">Agradecemos sua participação!</p>
        <p class="text">Antes de enviar a inscrição, releia atentamente os dados preenchidos e certifique-se que estão todos corretos. Você pode editar o formulário caso encontre alguma informação incorreta.</p>
        <a href="<?= $this->controller->createUrl('confirmacao', [$entity->id]) ?>" ng-click="" class="btn btn-primary js-confirmar"><?php \MapasCulturais\i::_e("Revisar formulário"); ?></a>
    </div>
</div>