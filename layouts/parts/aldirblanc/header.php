<?php
$site_name = $this->dict('site: name', false);
if ($title = $this->getTitle()) {
    $title = "{$site_name} - {$title}";
} else {
    $title = $site_name;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $app->getCurrentLCode(); ?>" dir="ltr">

<head>
    <?php if ($env = env('GTM_TAG', '')): ?>
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?= $env?>"></script>

        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '<?= $env?>');
        </script>
    <?php endif;?>
    <meta charset="UTF-8" />
    <title><?php echo $title == $site_name ? $title : "{$site_name} - {$title}"; ?></title>
    <link rel="profile" href="http://gmpg.org/xfn/11" />
    <link rel="shortcut icon" href="<?php $this->asset('img/favicon.ico') ?>" />

    <?php 
    $meta_description = "A Lei de Emergência Cultural Aldir Blanc surgiu com o objetivo de ajudar trabalhadoras e trabalhadores da Cultura bem como espaços culturais brasileiros no período de isolamento social,
    ocasionado pela pandemia da COVID-19. Solicite seu benefício!";

    foreach($this->documentMeta as $i => &$meta) {
        // Redefine description metas
        // Using name key
        if(@$meta['name'] && strpos($meta['name'], 'description') !== false){
            $meta['content'] = $meta_description;
        }

        // Using property key (this is used for facebook)
        if(@$meta['property'] && strpos($meta['property'], 'description') !== false ) {
            $meta['content'] = $meta_description;
        }

        // Image metas
        // Using name key
        if(@$meta['name'] && strpos($meta['name'], 'image') !== false){
            $meta['content'] = $this->asset('aldirblanc/img/thumb.png', false);;
        }

        // Using property key
        if(@$meta['property'] && strpos($meta['property'], 'image') !== false ) {
            $meta['content'] = $this->asset('aldirblanc/img/thumb.png', false);;
        }

        // Using itemprop key
        if(@$meta['itemprop'] && strpos($meta['itemprop'], 'image') !== false ) {
            $meta['content'] = $this->asset('aldirblanc/img/thumb.png', false);;
        }
    }    
    ?>

    <?php $this->head(); ?>
    <!--[if lt IE 9]>
        <script src="<?php $this->asset('js/html5.js'); ?>" type="text/javascript"></script>
    <![endif]-->
    

</head>

<body <?php $this->bodyProperties() ?>>
    <?php $this->bodyBegin(); ?>
    <header id="main-header" class="clearfix" ng-class="{'sombra':data.global.viewMode !== 'list'}">
        <?php $this->part('aldirblanc/header-logos') ?>
        <?php $this->part('aldirblanc/header-logout') ?>
    </header>
    <section id="main-section" class="clearfix">