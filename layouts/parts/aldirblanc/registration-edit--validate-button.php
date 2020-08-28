<div class="registration-fieldset">
    <a ng-click="validateRegistration()" class="btn btn-secondary">Validar</a>
    <div ng-repeat="field in data.fields" ng-if="entityErrors[field.fieldName]">
        <a ng-click="scrollTo('wrapper-' + field.fieldName, 130)">
            {{field.title}}:
            <span ng-repeat="error in entityErrors[field.fieldName]">{{error}} </span>
        </a>
    </div>
</div>
<div ng-if="entityValidated" style="display:block" id="modalAlert" class="">
    <!-- Modal content -->
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Preenchimento Finalizado</h2>
        <p class="text">lorem</p>
        <a href="<?= $this->controller->createUrl('confirmacao', [$entity->id]) ?>" ng-click="" class="btn btn-primary js-confirmar"><?php \MapasCulturais\i::_e("Confirmar"); ?></a>
    </div>
</div>