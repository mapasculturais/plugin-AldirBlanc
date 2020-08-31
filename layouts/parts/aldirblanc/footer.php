<?php $linkSuporte = isset($this->controller->config['link_suporte']) ? $this->controller->config['link_suportesuporte'] : '';?>
</section>
<footer id="main-footer">

    <div class="logo-state">
        <img src="<?php $this->asset('aldirblanc/img/logo-sergipe.png') ?>">
    </div>

    <div class="credits">
        <img alt="Mapas Culturais e hacklab/" src="<?php $this->asset('aldirblanc/img/mapas-culturais-hacklab.png') ?>">
    </div>

</footer>

<?php if ($linkSuporte){
?>
<div class="support">
    <a target="_blank" href="<?= $linkSuporte; ?> ">
        Suporte
    </a>
</div>
<?php
}
 $this->bodyEnd(); ?>
</body>

</html>