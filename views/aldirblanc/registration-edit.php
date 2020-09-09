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
                <?php if ($entity->category == $inciso2_categories['espaco-formalizado']): ?>
                    <strong>Entidade, empresa ou cooperativa do setor cultural com inscrição em CNPJ</strong> para espaço do tipo 
                    <strong>Espaço físico próprio, alugado, itinerante, público cedido em comodato, emprestado ou de uso compartilhado</strong>.

                <?php elseif ($entity->category == $inciso2_categories['espaco-nao-formalizado']): ?>
                    <strong>Espaço artístico e cultural mantido por coletivo ou grupo cultural (sem CNPJ) ou por pessoa física (CPF)</strong> para espaço do tipo 
                    <strong>Espaço físico próprio, alugado, itinerante, público cedido em comodato, emprestado ou de uso compartilhado</strong>.

                <?php elseif ($entity->category == $inciso2_categories['coletivo-formalizado']): ?>
                    <strong>Entidade, empresa ou cooperativa do setor cultural com inscrição em CNPJ</strong> para espaço do tipo 
                    <strong>Espaço público (praça, rua, escola, quadra ou prédio custeado pelo poder público) ou espaço virtual de cultura digital</strong>.
                    
                <?php elseif ($entity->category == $inciso2_categories['coletivo-nao-formalizado']): ?>
                    <strong>Espaço artístico e cultural mantido por coletivo ou grupo cultural (sem CNPJ) ou por pessoa física (CPF)</strong> para espaço do tipo 
                    <strong>Espaço público (praça, rua, escola, quadra ou prédio custeado pelo poder público) ou espaço virtual de cultura digital</strong>.
                <?php endif; ?>
            </p>
        <?php endif; ?> 
        <?php $this->applyTemplateHook('form','begin'); ?>
        
        <?php $this->part('singles/registration-edit--header', $_params) ?>
        
        <?php $this->part('singles/registration-edit--fields', $_params) ?>

        <?php $this->part('aldirblanc/registration-edit--validate-button', $_params) ?>
        
        <?php $this->applyTemplateHook('form','end'); ?>

</article>
<?php $this->part('singles/registration--sidebar--left', $_params) ?>
<?php $this->part('singles/registration--sidebar--right', $_params) ?>
