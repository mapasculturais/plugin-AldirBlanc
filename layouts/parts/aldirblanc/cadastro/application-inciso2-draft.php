<?php

/**
 * Carregada na tela inicial
 * Contém as informações resumidas do cadastro não enviado e link para tela de edição 
 */
?>

<div id="" class="lab-option draf-option">
    <a href="<?= $registrationUrl; ?>">
    <button class="informative-box lab-option has-status status-<?= $registration->status ?>">
        <div class="informative-box--status">
            <?php echo $registrationStatusName; ?>
        </div>
        <div class="informative-box--icon">
            <?php if ($registration->inciso == 1) : ?>
                <i class="fas fa-users"></i>
            <?php else : ?>
                <i class="fas fa-university"></i>
            <?php endif; ?>
        </div>

        <div class="informative-box--title">
            <h2>
                <?php
                $inciso1Title = 'Trabalhadoras e trabalhadores da Cultura';
                $inciso2Title = 'Espaços e organizações culturais';
                ?>

                <?php if ($registration->inciso == 1) : ?>
                    <?= $inciso1Title ?>
                <?php else : ?>
                    <?= $inciso2Title ?>
                <?php endif; ?>
            </h2>
            <i class="far fa-check-circle"></i>
        </div>

        <div class="informative-box--content" data-content="">
            <span class="more"> Mais informações </span>
            <div class="content">
                <div class="item">
                    <span class="label">Número:</span> <?php echo $registration->number; ?> </br>
                </div>
                
                <?php if(!empty($registration->sentTimestamp)): ?>
                    <div class="item">
                        <span class="label">Data do envio:</span> <?php echo $registration->sentTimestamp ? $registration->sentTimestamp->format(\MapasCulturais\i::__('d/m/Y à\s H:i')) : ''; ?>. </br>
                    </div>
                <?php endif; ?>

                <div class="item">
                    <span class="label">Responsável:</span> <?php echo $registration->owner->name; ?> </br>
                </div>

                <div class="item">
                    <span class="label">CPF:</span> <?php echo $registration->owner->documento; ?>
                </div>
            </div>
        </div>
    </button>
        <div class="btn">
            Continuar inscrição incompleta
        </div>
    </a>


</div>