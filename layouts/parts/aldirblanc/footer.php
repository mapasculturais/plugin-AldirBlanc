<?php
$app = \MapasCulturais\App::i();
$aldirBlancController = $app->controller('aldirblanc');
$linkSuporte      = isset($aldirBlancController->config['link_suporte']) ? $aldirBlancController->config['link_suporte'] : '';
$termosECondicoes = isset($aldirBlancController->config['privacidade_termos_condicoes']) ? $aldirBlancController->config['privacidade_termos_condicoes'] : '';
$logotipo = isset($aldirBlancController->config['logotipo_instituicao']) ? $aldirBlancController->config['logotipo_instituicao'] : '';?>

</section>

<?php if ($linkSuporte){
    ?>
    <div class="support">
        Precisa de ajuda? <a target="_blank" class="link" href="<?= $linkSuporte; ?> ">Clique aqui</a>
    </div>
    <?php
}?>

<footer id="main-footer">

    <?php if ($logotipo){
        ?>
       <div class="logo-state">
            <img src="<?= $logotipo ?>">
        </div>
        <?php
    }?>

    <?php if ($termosECondicoes){
        ?>
        <a target="_blank" class="terms-conditions" href="<?= $termosECondicoes; ?> ">
            Politica de Privacidade e termos de condições de uso
        </a>
        <?php
    }?>

    <div class="credits">
        <a href="https://github.com/mapasculturais/mapasculturais" target="_blank">
            Mapas Culturais
        </a> 
        <span> e </span> 

        <a href="https://hacklab.com.br/" class="hacklab" target="_blank">
            hacklab <span>/</span>
        </a>
        <!-- <img alt="Mapas Culturais e hacklab/" src="<?php $this->asset('aldirblanc/img/mapas-culturais-hacklab.png') ?>"> -->
    </div>

</footer>

<?php $this->bodyEnd(); ?>
</body>

</html>