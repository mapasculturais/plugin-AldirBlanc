<?php 
if ($inciso == 1){
    $route = MapasCulturais\App::i()->createUrl('dataprev', 'export_inciso1');    
    ?>
    <a class="btn btn-primary" href="<?= $route ?>" target="_blank">
        Exportar csv
    </a>
    <?php
}
else if ($inciso ==2){
    $routeCpf = MapasCulturais\App::i()->createUrl('dataprev', 'export_inciso2', ['status' => 1, 'type' =>'cpf', 'opportunity' =>$opportunity]);
    $routeCnpj = MapasCulturais\App::i()->createUrl('dataprev', 'export_inciso2', ['status' => 1, 'type' =>'cnpj', 'opportunity' =>$opportunity]);
    ?>
    <a class="btn btn-primary" href="<?= $routeCpf ?>" target="_blank">
        Exportar csv CPF
    </a>
    <a class="btn btn-primary" href="<?= $routeCnpj ?>" target="_blank">
        Exportar csv CNPJ
    </a>
    <?php
}
?>
