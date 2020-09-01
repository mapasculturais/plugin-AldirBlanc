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
    <meta charset="UTF-8" />
    <title><?php echo $title == $site_name ? $title : "{$site_name} - {$title}"; ?></title>
    <link rel="profile" href="http://gmpg.org/xfn/11" />
    <link rel="shortcut icon" href="<?php $this->asset('img/favicon.ico') ?>" />
    <?php $this->head(); ?>
    <!--[if lt IE 9]>
        <script src="<?php $this->asset('js/html5.js'); ?>" type="text/javascript"></script>
    <![endif]-->
<<<<<<< HEAD

    <!-- Primary Meta Tags -->
    <meta name="title" content="<?php echo $title == $site_name ? $title : "{$site_name} - {$title}"; ?>">
    <meta name="description" content=" A Lei de Emergência Cultural Aldir Blanc surgiu com o objetivo de ajudar trabalhadoras e trabalhadores da Cultura bem como espaços culturais brasileiros no período de isolamento social,
    ocasionado pela pandemia da COVID-19. Solicite seu benefício!">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo $title == $site_name ? $title : "{$site_name} - {$title}"; ?>">
    <meta property="og:description" content="A Lei de Emergência Cultural Aldir Blanc surgiu com o objetivo de ajudar trabalhadoras e trabalhadores da Cultura bem como espaços culturais brasileiros no período de isolamento social,
    ocasionado pela pandemia da COVID-19. Solicite seu benefício!">
    <meta property="og:image" content="https://metatags.io/assets/meta-tags-16a33a6a8531e519cc0936fbba0ad904e52d35f34a46c97a2c9f6f7dd7d336f2.png">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:title" content="<?php echo $title == $site_name ? $title : "{$site_name} - {$title}"; ?>">
    <meta property="twitter:description" content=" A Lei de Emergência Cultural Aldir Blanc surgiu com o objetivo de ajudar trabalhadoras e trabalhadores da Cultura bem como espaços culturais brasileiros no período de isolamento social,
    ocasionado pela pandemia da COVID-19. Solicite seu benefício!">
    <meta property="twitter:image" content="https://metatags.io/assets/meta-tags-16a33a6a8531e519cc0936fbba0ad904e52d35f34a46c97a2c9f6f7dd7d336f2.png">
=======
>>>>>>> c30507eff157cb24379f8ac01183b4f82c3ee158
</head>

<body <?php $this->bodyProperties() ?>>
    <?php $this->bodyBegin(); ?>
    <header id="main-header" class="clearfix" ng-class="{'sombra':data.global.viewMode !== 'list'}">
        <?php $this->part('aldirblanc/header-logos') ?>
        <?php $this->part('aldirblanc/header-logout') ?>
    </header>
    <section id="main-section" class="clearfix">