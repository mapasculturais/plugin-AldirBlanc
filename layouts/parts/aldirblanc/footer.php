<?php
$linkSuporte      = isset($this->controller->config['link_suporte']) ? $this->controller->config['link_suporte'] : '';
$termosECondicoes = isset($this->controller->config['privacidade_termos_condicoes']) ? $this->controller->config['privacidade_termos_condicoes'] : '';
;?>
</section>

<?php if ($linkSuporte){
    ?>
    <div class="support">
        Precisa de ajuda? <a target="_blank" class="link" href="<?= $linkSuporte; ?> ">Clique aqui</a>
    </div>
    <?php
}?>

<footer id="main-footer">

    <div class="logo-state">
        <img src="<?php $this->asset('aldirblanc/img/secult-para.png') ?>">
    </div>


    <?php if ($termosECondicoes){
        ?>
        <a target="_blank" class="terms-conditions" href="<?= $termosECondicoes; ?> ">
            Politica de Privacidade e termos de condições de uso
        </a>
        <?php
    }?>

    <div class="credits">
        <a href="https://github.com/mapasculturais/mapasculturais" target="_blank">
        Software livre Mapas Culturais
        </a> 
        <span> por </span> 

        <a href="https://hacklab.com.br/" class="hacklab" target="_blank">
            hacklab <span>/</span>
        </a>

        <span> e comunidade </span>
        <!-- <img alt="Mapas Culturais e hacklab/" src="<?php $this->asset('aldirblanc/img/mapas-culturais-hacklab.png') ?>"> -->
    </div>

</footer>

<?php $this->bodyEnd(); ?>
</body>

</html>