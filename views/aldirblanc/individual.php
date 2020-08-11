





IGNORE ESSE ( verificar se tem que ignorar mesmo )







































<?php 
    use MapasCulturais\App;
    
    $app = App::i();
?>

<?php
$action = preg_replace("#^(\w+/)#", "", $this->template);

$this->bodyProperties['ng-app'] = "entity.app";
$this->bodyProperties['ng-controller'] = "EntityController";

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


<?php //$this->part('editable-entity', array('entity'=>$entity, 'action'=>$action));  ?>

<?php $this->part('aldirblanc/editable-entity', array('entity'=>$entity, 'action'=>$action));  ?>

<article class="main-content registration" ng-controller="OpportunityController">

    <article>
        
        <?php $this->part('singles/registration-edit--categories', $_params) ?>
        
        <?php $this->part('aldirblanc/registration-edit--agents', $_params) ?>
        
        <?php // Desabilitando este template por enquanto, pois não é a melhor forma de apresentar para o usuário que está se inscrevendo ?>
        <?php //$this->part('singles/registration-edit--seals', $_params) ?>
        
        <?php $this->part('singles/registration-edit--fields', $_params) ?>

        <?php if(!$entity->preview): ?>
            <?php $this->part('singles/registration-edit--send-button', $_params) ?>
        <?php endif; ?>

        
    </article>

</article>
<?php $this->part('singles/registration--sidebar--left', $_params) ?>
<?php $this->part('singles/registration--sidebar--right', $_params) ?>
