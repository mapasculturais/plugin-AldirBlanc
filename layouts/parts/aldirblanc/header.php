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
    <?php $this->head(); ?>
    <!--[if lt IE 9]>
        <script src="<?php $this->asset('js/html5.js'); ?>" type="text/javascript"></script>
    <![endif]-->
    

</head>

<body <?php $this->bodyProperties() ?>>
    <?php $this->bodyBegin(); ?>
    <header id="main-header" class="clearfix" ng-class="{'sombra':data.global.viewMode !== 'list'}">
        <?php $this->part('aldirblanc/header-logos') ?>
        <?php if($app->auth->isUserAuthenticated()): ?>
            <?php $this->part('aldirblanc/header-logout') ?>
        <?php endif; ?>
    </header>
    <section id="main-section" class="clearfix">