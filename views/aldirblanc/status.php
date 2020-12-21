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
                        <?= nl2br(str_replace(array('\r\n', '\r', '\n'), "<br />", $message['message'])); ?>
                        <hr>
                    <?php else : ?>
                        <p><?= nl2br(str_replace(array('\r\n', '\r', '\n'), "<br />", $message)); ?></p>
                        <hr>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else : ?>
                <hr>
            <?php endif; ?>
            <?php foreach ($mensagem_ppg as $msg): ?>
                <?= $msg ?>
                <hr>
            <?php endforeach; ?>


            <?php

            /**
             * 
             * Exibe mensagem com informações sobre solicitação de recurso nas inscrições com status 2 (inválida) e 3 (não selecionada)
             * 
             * Verifica se existe uma mensagem no campo `Mensagem de Recurso para o Status` da oportunidade.
             * Se não tiver, verifica na configuração `msg_recurso`.
             * 
             */
            if (!$recursos && ($registration->status == 3 || $registration->status == 2)) {
                $statusRecurso = '';
                
                $data_recurso = $registration->lab_data_limite_recurso ?? false;

                if (!empty($avaliacoesRecusadas) && $data_recurso >= date ('Y-m-d')){
                    $statusRecurso = $avaliacoesRecusadas; 
                }elseif ($registration->opportunity->getMetadata('aldirblanc_status_recurso')) {
                    $statusRecurso = $registration->opportunity->getMetadata('aldirblanc_status_recurso');
                } elseif (!empty($this->controller->config['msg_recurso'])) {
                    $statusRecurso = $this->controller->config['msg_recurso'];
                }
                
                if ($statusRecurso) { 
                    if ($data_recurso >= date ('Y-m-d')) {
                        $date = (new DateTime($data_recurso))->format('d/m/Y');                        
                        ?>
                            <hr>
                            <h2 class="status-card--title"><b>Você pode entrar com recurso até o dia <?= $date ?></b></h2>
                            <?php if (is_array($statusRecurso)){
                                foreach($statusRecurso as $s){
                                    echo '<p class="status-card--content"> '. $s .'</p>';
                                }                             
                            } else {
                                echo '<p class="status-card--content"> '. $statusRecurso .'</p>';
                            }
                        
                    } else {
                        
                        $date = (new DateTime($data_recurso))->format('d/m/Y');                        
                        ?>
                            <hr>
                            <h2 class="status-card--title">Verifique o período para solicitação de recurso</h2>
                            <p class="status-card--content"><?= $statusRecurso; ?></p>
                        <?php
                    }
                
                
                }
            } ?>

        </div><!-- /.status-card -->
        <?php
        if ($recursos) {
            foreach ($recursos as $recurso) {
                if (is_numeric($recurso->result)) {
                    $status = $recurso->result ;
                } else if ($recurso->result == 'homogada por recurso') {
                    $status = 10;
                } else {
                    $status = 0;
                }
                ?>
                <div class="status-card status-<?= $status ?>">
                    <h2 class="status-card--title">Recurso</h2>
                    <p class="status-card--content"><?= $recurso->evaluationData->obs; ?></p>
                </div>
                <?php
            } 
        }
        ?>

        <?php $this->applyTemplateHook('reason-failure','begin', [ $_params ]); ?>
        <?php $this->applyTemplateHook('reason-failure','end'); ?>

        <div class="wrap-button">
            <a href="<?php echo $app->createUrl('aldirblanc', 'cadastro'); ?>" class="btn secondary"><?php \MapasCulturais\i::_e("Voltar para os Cadastros"); ?></a>
        </div><!-- /.wrap-button -->

        <h4 class="title-subsection">Edital <span class="underline">Inciso <?= $registration->inciso ?></span></h4>

        <h1> Cadastro de pessoa física </h1>

        <?php $this->part('aldirblanc/registration-single--header', $_params) ?>
        
        <?php if ($app->user->is('mediador') || $app->user->is('admin')) {
            $this->part('aldirblanc/registration-mediacao', $_params);
        } ?>

        <?php $this->part('singles/registration-single--fields', $_params) ?>

        <div class="wrap-button">
            <a href="<?php echo $app->createUrl('aldirblanc', 'cadastro'); ?>" class="btn secondary"><?php \MapasCulturais\i::_e("Voltar para os Cadastros"); ?></a>
        </div><!-- /.wrap-button -->

    </article>

</section>