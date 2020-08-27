<?php
/**
 * Contém as informações resumidas do cadastro
 * Exibida na tela inicial e na tela de status (acompanhamento) 
 * 
 * 
 */
?>

<h3>Situação da inscrição para <?php echo $registration->owner->name; ?>:</h3>
<h2> <?php echo $registrationStatusName; ?> </h2>
<p class="lab-form-detail">
        <span class="label">Número:</span> <?php echo $registration->number; ?> </br>
    
        <span class="label">Data do envio:</span> <?php echo $registration->sentTimestamp ? $registration->sentTimestamp->format(\MapasCulturais\i::__('d/m/Y à\s H:i')): ''; ?>.  </br>
    
        <span class="label">Responsável:</span>  <?php echo $registration->owner->name; ?> </br>
    
        <span class="label">CPF:</span> <?php echo $registration->owner->documento; ?>
</p>

<button class="informative-box lab-option">
    <div class="informative-box--status">
        <?php echo $registrationStatusName; ?> 
    </div>
    <div class="informative-box--icon">
        <?php if($incisoInfo['inciso'] == 1): ?>
            <i class="fas fa-users"></i>
        <?php else: ?>
            <i class="fas fa-university"></i>
        <?php endif; ?>
    </div>

    <div class="informative-box--title">
        <h2><?= $incisoInfo['incisoTitle'] ?></h2>
        <i class="far fa-check-circle"></i>
    </div>

    <div class="informative-box--content" data-content="">
        <span class="more"> Mais informações </span>
        <div class="content">
            <div class="item">  
                <span class="label">Número:</span> <?php echo $registration->number; ?> </br>
            </div>

            <div class="item">
                <span class="label">Data do envio:</span> <?php echo $registration->sentTimestamp ? $registration->sentTimestamp->format(\MapasCulturais\i::__('d/m/Y à\s H:i')): ''; ?>.  </br>
            </div>

            <div class="item">
                <span class="label">Responsável:</span>  <?php echo $registration->owner->name; ?> </br>
            </div>
            
            <div class="item">
                <span class="label">CPF:</span> <?php echo $registration->owner->documento; ?>
            </div>
        </div>
    </div>
</button>
