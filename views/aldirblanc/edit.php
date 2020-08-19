<?php
$action = preg_replace("#^(\w+/)#", "", $this->template);

$this->bodyProperties['ng-app'] = "entity.app";
$this->bodyProperties['ng-controller'] = "EntityController";
$this->jsObject['request']['controller'] = 'registration';
$this->jsObject['angularAppDependencies'][] = 'entity.module.opportunity';

$this->addEntityToJs($entity);

$this->addOpportunityToJs($entity->opportunity);

$this->addOpportunitySelectFieldsToJs($entity->opportunity);

$this->addRegistrationToJs($entity);

$this->includeAngularEntityAssets($entity);


$_params = [
    'entity' => $entity,
    'action' => $action,
    'opportunity' => $entity->opportunity
];

?>


<?php $this->part('aldirblanc/editable-entity', array('entity'=>$entity, 'action'=>$action));  ?>

<style>
.editable.editable-click {
    font-size: 18px;
    color: blue;
    cursor: pointer;
}
</style>

<section class="lab-main-content" ng-controller="OpportunityController">

    <?php // $this->part('singles/registration--header', $_params); ?>
    
        <?php $this->applyTemplateHook('form','begin'); ?>
        
        <?php $this->part('aldirblanc/registration-edit--header', $_params) ?>
        <nav class="lab-form-tabs">
            <ul>
                <li class="lab-form-tab lab-form-tab-active"><span class="screen-reader-text">Passo 1</span></li>
                <li class="lab-form-tab lab-form-tab-complete"><span class="screen-reader-text">Passo 2</span></li>
                <li class="lab-form-tab"><span class="screen-reader-text">Passo 3</span></li>
                <li class="lab-form-tab"><span class="screen-reader-text">Passo 4</span></li>
                <li class="lab-form-tab"><span class="screen-reader-text">Passo 5</span></li>
            </ul>
        </nav>

        <div>
            <p class="lab-form-step-count">Passo 1 de 5</p>
        </div>
        <?php // Desabilitando este template por enquanto, pois não é a melhor forma de apresentar para o usuário que está se inscrevendo ?>
        <?php //$this->part('singles/registration-edit--seals', $_params) ?>
        
        <?php $this->part('singles/registration-edit--fields', $_params) ?>

        <?php if(!$entity->preview): ?>
            <?php //$this->part('singles/registration-edit--send-button', $_params) ?>
        <?php endif; ?>

        <?php $this->applyTemplateHook('form','end'); ?>
        
        <nav class="lab-form-nav">
            <span>
                <a class="btn btn-boolean btn-large" href="#">Anterior</a>
                <a class="btn btn-boolean btn-large" href="#">Próximo</a>
            </span>
        </nav>
</section>
<?php $this->part('singles/registration--sidebar--left', $_params) ?>
<?php $this->part('singles/registration--sidebar--right', $_params) ?>
