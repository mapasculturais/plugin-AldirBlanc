<?php
/**
 * Exibe o logo no cabeçalho
 */
$app = \MapasCulturais\App::i();
$aldirBlancController = $app->controller('aldirblanc');
$logotipo_instituicao = isset($aldirBlancController->config['logotipo_instituicao']) ? $aldirBlancController->config['logotipo_instituicao'] : '';
$logotipo_central = isset($aldirBlancController->config['logotipo_central']) ? $aldirBlancController->config['logotipo_central'] : '';
?>

<?php if ($logotipo_instituicao){
    ?>
   <div class="logo-state">
        <img src="<?= $logotipo_instituicao ?>">
    </div>
    <?php
}?>

<?php if ($logotipo_central){
    ?>
    <div class="logo">
      <a href="<?= $app->createUrl('aldirblanc', 'cadastro') ?>"> <img src="<?= $logotipo_central ?>"></a>
    </div>
    <?php
}?>

