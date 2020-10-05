<?php
$app                  = \MapasCulturais\App::i();
$aldirBlancController = $app->controller('aldirblanc');
$linkSuporte          = isset($aldirBlancController->config['link_suporte']) ? $aldirBlancController->config['link_suporte'] : '';

if (!empty($linkSuporte)) : ?>
    <div class="support" style="clear:left; text-align:center;">
        Precisa de ajuda? <a target="_blank" class="link" href="<?= $linkSuporte; ?> ">Clique aqui</a>
    </div>
<?php endif; ?>