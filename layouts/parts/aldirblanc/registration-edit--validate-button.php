<div class="registration-fieldset">
    <a ng-click="validateRegistration()" class="btn btn-danger">Validar</a>
    <div ng-repeat="field in data.fields" ng-if="entityErrors[field.fieldName]"><a ng-click="scrollTo('wrapper-' + field.fieldName, 130)">{{field.title}}: <span ng-repeat="error in entityErrors[field.fieldName]">{{error}} </span></a></div>
</div>
<div ng-if="entityValidated" style="display:block" id="modalAlert" class="">
    <!-- Modal content -->
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Preenchimento Finalizado</h2>
        <p class="text">lorem</p>
        <button ng-click="confirma()" class="btn js-confirmar"><?php \MapasCulturais\i::_e("Confirmar"); ?></button>
    </div>
</div>
<script>
    // $(document).ready(function(){

    // });
    function confirma(){
        document.location = MapasCulturais.createUrl('aldirblanc', 'registration-confirmacao',[$scope.data.entity.id])
    }
</script>
