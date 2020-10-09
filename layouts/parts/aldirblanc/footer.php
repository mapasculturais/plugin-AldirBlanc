<?php
$app = \MapasCulturais\App::i();
$aldirBlancController = $app->controller('aldirblanc');
$linkSuporte      = isset($aldirBlancController->config['link_suporte']) ? $aldirBlancController->config['link_suporte'] : '';
$termosECondicoes = isset($aldirBlancController->config['privacidade_termos_condicoes']) ? $aldirBlancController->config['privacidade_termos_condicoes'] : $app->createUrl('auth', '', array('termos-e-condicoes'));
$logotipo = isset($aldirBlancController->config['logotipo_instituicao']) ? $aldirBlancController->config['logotipo_instituicao'] : '';?>

</section>

<?php if ($linkSuporte){
    ?>
    <div class="support">
        Precisa de ajuda? Envie um e-mail para: <a target="_blank" class="link" href="mailto:<?= $linkSuporte; ?> ">$linkSuporte</a>
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
        Software livre Mapas Culturais
        </a> 
        <span> por </span> 

        <a href="https://hacklab.com.br/" class="hacklab" target="_blank" style="white-space: nowrap;">
            hacklab <span>/</span>
        </a>

        <span> e comunidade </span>
    </div>

</footer>

<?php $this->bodyEnd(); ?>
</body>

</html>