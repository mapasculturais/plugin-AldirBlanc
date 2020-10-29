<?php

use AldirBlanc\Controllers\AldirBlanc;

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


$plugin = \MapasCulturais\App::i()->plugins['AldirBlanc'];
if(false) $plugin = new \AldirBlanc\Plugin;

$inciso2_categories = $plugin->config['inciso2_categories'];
?>

<div id="editable-entity" class="clearfix sombra" >
</div>
<article class="main-content registration" ng-controller="OpportunityController">
        <?php if($entity->inciso == 1): ?> 
            <h1> Solicitação de trabalhadora ou trabalhador da cultura </h1>
        <?php else: ?> 
            <h1> Espaços e organizações culturais </h1>
            <h2 class="category"><?php echo $entity->category ?></h2>
            <p>Solicitação de benefício para
                <?php 
                if ($entity->category == $inciso2_categories['espaco-formalizado']):
                    echo $plugin->config['texto_categoria_espaco-formalizado'];
                elseif ($entity->category == $inciso2_categories['espaco-nao-formalizado']): 
                    echo $plugin->config['texto_categoria_espaco-nao-formalizado'];
                elseif ($entity->category == $inciso2_categories['coletivo-formalizado']): 
                    echo $plugin->config['texto_categoria_coletivo-formalizado'];
                 elseif ($entity->category == $inciso2_categories['coletivo-nao-formalizado']): 
                    echo $plugin->config['texto_categoria_coletivo-nao-formalizado'];
                endif; ?>
            </p>
        <?php endif; ?> 
        <?php $this->applyTemplateHook('form','begin'); ?>
        
        <?php $this->part('singles/registration-edit--header', $_params) ?>

        <?php if($app->user->is('mediador')): ?>
            <?php $this->part('aldirblanc/mediacao-fields', $_params) ?>
        <?php endif; ?>
        
        <?php $this->part('singles/registration-edit--fields', $_params) ?>

        <?php $this->part('aldirblanc/registration-edit--validate-button', $_params) ?>
        
        <?php $this->applyTemplateHook('form','end'); ?>

</article>
