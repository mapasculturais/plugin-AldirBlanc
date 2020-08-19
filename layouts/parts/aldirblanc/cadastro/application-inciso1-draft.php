<?php
/**
 * Carregada na tela inicial
 * Contém as informações resumidas do cadastro não enviado e link para tela de edição 
 */
?>

<div id="   " class="lab-option">
    <a href="<?=$registrationUrl;?>">
        <h3>Continuar inscrição iniciada para  <?=$registration->owner->name;?></h3>
        <p class="js-detail lab-option-detail">Sua solicitação do benefício da Renda Emergencial está incompleta. Continue o preenchimento dos campos para finalizá-la.</p>
    </a>
</div>
