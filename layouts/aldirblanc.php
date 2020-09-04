<?php 
$app = MapasCulturais\App::i();
$plugin = $app->plugins['AldirBlanc'];
$plugin->registerAssets();
?>
<?php $this->part('aldirblanc/header'); ?>
<?php echo $TEMPLATE_CONTENT; ?>
<?php $this->part('aldirblanc/footer'); ?>