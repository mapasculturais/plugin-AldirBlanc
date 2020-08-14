<?php
use MapasCulturais\Entities\Registration;

$action = preg_replace("#^(\w+/)#", "", $this->template);

$this->bodyProperties['ng-app'] = "entity.app";
$this->bodyProperties['ng-controller'] = "EntityController";

$this->jsObject['angularAppDependencies'][] = 'entity.module.opportunity.aldirblanc';
$this->jsObject['angularAppDependencies'][] = 'ui.sortable';

$this->addEntityToJs($entity);

$this->addOpportunityToJs($entity);

$this->addOpportunitySelectFieldsToJs($entity);


$this->addEntityTypesToJs($entity);
$this->addTaxonoyTermsToJs('tag');


$this->includeAngularEntityAssets($entity);

$child_entity_request = isset($child_entity_request) ? $child_entity_request : null;

?>

<article class="main-content opportunity" ng-controller="OpportunityController">
    
        <?php $this->part('singles/opportunity-about', ['entity' => $entity]) ?>

        <button class="btn btn-primary" onclick="goToNextPage()" > Cadastrar agente </button>

</article>

<script>
    
function goToNextPage() {
    document.location = `${MapasCulturais.baseURL}agentes/create/`;
    //salva agente e emcaminha pra tela de inscricao, 
    //ao chegar la ou vai ta com agente criad ou selecionado
    //criar nova oportunidade
}
</script>
        

        

