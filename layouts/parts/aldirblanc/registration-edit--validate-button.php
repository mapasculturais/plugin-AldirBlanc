
<div ng-controller="RegistrationFieldsController">
<div class="registration-fieldset">
    <a ng-init="validateRegistration()" ng-click="validateRegistration()" class="btn btn-secondary btn-validate">Validar</a>
    <div class="errors-header" ng-if="numFieldErrors() > 0">
        <p class="errors-header-title">O cadastro não foi enviado!</p>
        <p>Corrija os campos listados abaixo e valide seu formulário utilizando o botão Validar.</p>
    </div>
    <div class="errors-header" ng-if="numFieldErrors() == 0">
        <p class="errors-header-title">O cadastro ainda não foi enviado! Use o botão Validar para finalizar seu cadastro.</p>
    </div>
    <div class="errors" ng-repeat="field in data.fields" ng-if="entityErrors[field.fieldName]">
        <a ng-click="scrollTo('wrapper-' + field.fieldName, 130)">
            {{field.title.replace(':', '')}}: <span class="errors-field" ng-repeat="error in entityErrors[field.fieldName]">{{error}} </span>
        </a>
    </div>
</div>

<div show="{{entityValidated}}" ng-show="entityValidated" id="modalAlert" class="modal">
    <!-- Modal content -->
    <div class="modal-content">
        <!-- <span class="close">&times;</span> -->
        <h2 class="modal-content--title">Preenchimento Finalizado</h2>
        <p class="text">Agradecemos sua participação!</p>
        <p class="text">Antes de enviar a inscrição, releia atentamente os dados preenchidos e certifique-se que estão todos corretos. Você pode editar o formulário caso encontre alguma informação incorreta.</p>
        <a href="<?= $this->controller->createUrl('confirmacao', [$entity->id]) ?>" ng-click="" class="btn btn-primary js-confirmar"><?php \MapasCulturais\i::_e("Revisar formulário"); ?></a>
    </div>
</div>

<script>
    $(window).ready(function () {
        $('.btn-validate').click(function () {
            $('#modalAlert').css('display', 'flex')
        });
    });
</script>
</div>