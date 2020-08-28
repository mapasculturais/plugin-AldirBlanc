<?php
/**
 * Carregada na tela inicial
 * Contém as informações resumidas do cadastro e link para tela de status (acompanhamento)
 * Classe .lab-status-waiting deve ser trocada por .lab-status-approved ou .lab-status-denied de acordo com o status do cadastro
 */
?>

<div class="lab-option lab-status-waiting">
    <a href="<?= $this->controller->createUrl( 'status', [$registration->id]) ?>">
        <?php $this->part('aldirblanc/cadastro/application-summary', ['registration' => $registration,'registrationStatusName' => $registrationStatusName]) ?>
    </a>
</div>
