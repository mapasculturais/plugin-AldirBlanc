<?php

$this->bodyProperties['ng-app'] = "entity.app";
$this->bodyProperties['ng-controller'] = "EntityController";
$this->jsObject['angularAppDependencies'][] = 'entity.module.opportunity';

$this->addEntityToJs($registration);
$this->addOpportunityToJs($registration->opportunity);
$this->addOpportunitySelectFieldsToJs($registration->opportunity);
$this->addRegistrationToJs($registration);
$this->includeAngularEntityAssets($registration);
$this->includeEditableEntityAssets();

$_params = [
    'entity'      => $registration,
    'opportunity' => $registration->opportunity
]; ?>

<section id="lab-status" class="lab-main-content">

    <article class="main-content registration" ng-controller="OpportunityController">

        <div class="status-card status-<?= $registration->status ?>">
            <h2 class="status-card--title"><?= $registrationStatusMessage['title'] ?? ''; ?></h2>

            <?php if (!empty($justificativaAvaliacao) && sizeof($justificativaAvaliacao) != 0) : ?>
                <?php foreach ($justificativaAvaliacao as $message) : ?>
                    <?php if (is_array($message) && !empty($this->controller->config['exibir_resultado_padrao'])) : ?>
                        <p class="status-card--content"><?= $message['message']; ?></p>
                        <hr>
                    <?php else : ?>
                        <p class="status-card--content"><?= $message; ?></p>
                        <hr>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else : ?>
                <hr>
            <?php endif; ?>

            <?php if (($registration->status == 3 || $registration->status == 2) && !empty($this->controller->config['msg_recurso'])) : ?>
                <hr>
                <h2 class="status-card--title">Você pode entrar com recurso</h2>
                <p class="status-card--content"><?= $this->controller->config['msg_recurso']; ?></p>

                <?php if (!empty($this->controller->config['email_recurso'])) : ?>
                    <br>
                    <p class="status-card--content">Caso queira solicitar recurso envie um email para <a href="mailto:<?php echo $this->controller->config['email_recurso']; ?>"><?php echo $this->controller->config['email_recurso']; ?></a></p>
                <?php endif; ?>
            <?php endif; ?>
        </div><!-- /.status-card -->
        <div class="wrap-button">
            <a href="<?php echo $app->createUrl('aldirblanc', 'cadastro'); ?>" class="btn secondary"><?php \MapasCulturais\i::_e("Voltar para os Cadastros"); ?></a>
        </div><!-- /.wrap-button -->

        <h4 class="title-subsection">Edital <span class="underline">Inciso <?= $registration->inciso ?></span></h4>

        <h1> Cadastro de pessoa física </h1>

        <?php $this->part('aldirblanc/registration-single--header', $_params) ?>
        <?php $this->part('singles/registration-single--fields', $_params) ?>

        <?php if ($app->user->is('mediador') || $app->user->is('admin')) {
            $this->part('aldirblanc/registration-mediacao', $_params);
        } ?>

        <div class="wrap-button">
            <a href="<?php echo $app->createUrl('aldirblanc', 'cadastro'); ?>" class="btn secondary"><?php \MapasCulturais\i::_e("Voltar para os Cadastros"); ?></a>
        </div><!-- /.wrap-button -->

    </article>

</section>