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
        <a href="http://www.secult.pa.gov.br/" target="_blank">
            <img src="<?= $logotipo_instituicao ?>">
        </a>
    </div>
    <?php
}?>

<?php if ($logotipo_central){
    ?>
    <div class="logo">
        <a href="https://leialdirblanc.pa.gov.br/" target="_blank">
            <img src="<?= $logotipo_central ?>">
        </a>
    </div>
    <?php
}?>

