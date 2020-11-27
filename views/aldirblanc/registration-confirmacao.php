<?php
$app = \MapasCulturais\App::i();
$aldirBlancController = $app->controller('aldirblanc');
$PreventSend      = $aldirBlancController->config['oportunidades_desabilitar_envio'];
$PreventSendMessages      = $aldirBlancController->config['mensagens_envio_desabilitado'];

$action = preg_replace("#^(\w+/)#", "", $this->template);

$this->bodyProperties['ng-app'] = "entity.app";
$this->bodyProperties['ng-controller'] = "EntityController";

$this->jsObject['angularAppDependencies'][] = 'entity.module.opportunity';

$this->addEntityToJs($entity);

$this->addOpportunityToJs($entity->opportunity);

$this->addOpportunitySelectFieldsToJs($entity->opportunity);

$this->addRegistrationToJs($entity);

$this->includeAngularEntityAssets($entity);
$this->includeEditableEntityAssets();

$_params = [
    'entity' => $entity,
    'action' => $action,
    'opportunity' => $entity->opportunity
];
$opportunityId = $entity->opportunity->id;
?>
<article class="main-content registration" ng-controller="OpportunityController">

    <article>
        <?php $this->applyTemplateHook('form', 'begin'); ?>

        <?php $this->part('aldirblanc/registration-single--header', $_params) ?>

        <?php $this->part('singles/registration-single--fields', $_params) ?>

        <?php if ($app->user->is('mediador') || $app->user->is('admin')) {
            $this->part('aldirblanc/registration-mediacao', $_params);
        } ?>

        <?php $this->applyTemplateHook('form', 'end'); ?>

        <?php if (in_array($opportunityId,$PreventSend)) {?>
            <h2 class="registration-help">
                <strong>
                    <?= $PreventSendMessages[$opportunityId] ?? ''?>
                </strong>
            </h2>
            <?php
        }  else {?>
            <p class="registration-help"><?php \MapasCulturais\i::_e("Certifique-se que você preencheu as informações corretamente antes de enviar sua inscrição."); ?> <strong><?php \MapasCulturais\i::_e("Depois de enviada, não será mais possível editá-la."); ?></strong></p>
            <a class="btn btn-confirmar" ng-click="sendRegistration(false)" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Confirmar envio"); ?></a>
            <?php
        } ?>
        <a href="<?= $this->controller->createUrl('formulario', [$entity->id]) ?>" class="btn secondary"><?php \MapasCulturais\i::_e("Editar formulário"); ?></a>

    </article>
    <div  ng-show="data.sent" style="display:none" id="modalAlert" class="modal">
        <!-- Modal content -->
        <div class="modal-content">
            <!-- <span class="close">&times;</span> -->
            <h2>Cadastro enviado com sucesso!</h2>
            <p class="text">Sua inscrição será analisada pelo comitê de curadoria e o resultado será informado por email. <br/>Você também pode acompanhar o andamento da análise através desse site.</p>
            <a href="<?= $this->controller->createUrl('status', [$entity->id]) ?>" class="btn js-confirmar"><?php \MapasCulturais\i::_e("Acompanhar solicitação"); ?></a>
        </div>
    </div>

</article>

<script>
    $(window).ready(function () {
        $('.btn-confirmar').click(function () {
            $('#modalAlert').css('display', 'flex')
        });
    });
</script>
