<div class="registration-fieldset">
    <a ng-click="validateRegistration()" class="btn btn-danger hltip" data-hltip-classes="hltip-danger" >Validar</a>
    
    <div ng-repeat="field in data.fields" ng-if="entityErrors[field.fieldName]"><a ng-click="scrollTo('wrapper-' + field.fieldName)">{{field.title}}: {{entityErrors[field.fieldName]}}</a></div>
    
</div>
