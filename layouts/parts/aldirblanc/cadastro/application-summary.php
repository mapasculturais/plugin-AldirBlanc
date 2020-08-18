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
    
        <span class="label">Data do envio:</span> <?php \MapasCulturais\i::_e("Enviada em");?> <?php echo $registration->sentTimestamp ? $registration->sentTimestamp->format(\MapasCulturais\i::__('d/m/Y à\s H:i')): ''; ?>.  </br>
    
        <span class="label">Responsável:</span>  <?php echo $registration->owner->name; ?> </br>
    
        <span class="label">CPF:</span> <?php echo $registration->owner->documento; ?>
</p>
