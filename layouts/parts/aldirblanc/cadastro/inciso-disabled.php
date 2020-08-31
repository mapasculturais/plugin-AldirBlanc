<?php

/**
 * Carregada na tela inicial
 * Contém as informações resumidas do cadastro não enviado e link para tela de edição 
 */
?>
<div>
<button id="" role="button" class="informative-box ">
    <div class="informative-box--icon">
        <i class="fas fa-university"></i>
    </div>

    <div class="informative-box--title">
        <h2><?= $title ?></h2>
        <i class="far fa-check-circle"></i>
    </div>

    <div class="informative-box--content active" data-content="">
        
        <span class="content">
            <?= $mensagem;?>
        </span>
    </div>
</button>
</div>
