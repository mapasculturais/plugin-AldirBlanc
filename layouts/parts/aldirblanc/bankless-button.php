<?php
use MapasCulturais\i;

if ($inciso == 1) {
    $route = MapasCulturais\App::i()->createUrl('remessas', 'exportBankless');
    $title = "Exportações Desbancarizados";
}
?>

<a class="btn btn-default download btn-export-cancel" ng-click="editbox.open('form-parameters-bankless', $event)" rel="noopener noreferrer">Desbancarizados</a>

<!-- Formulários -->
<edit-box id="form-parameters-bankless" position="top" title="<?php i::esc_attr_e($title) ?>" cancel-label="Cancelar" close-on-cancel="true">
    <form class="form-export-dataprev" action="<?=$route?>" method="POST">

        <label for="from">Data inicial</label>
        <input type="date" name="from" id="from">

        <label for="from">Data final</label>
        <input type="date" name="to" id="to">

        <label for="type">Tipo de exportação</label>
            <select name="type" id="type"><?php
            foreach ($exports as $value => $label) { ?>
                <option value="<?=$value?>"><?=$label?></option><?php
            } ?>
            </select>

            <input type="hidden" name="opportunity" value="<?=$opportunity?>">
        # Caso não queira filtrar entre datas, deixe os campos vazios.
        <button class="btn btn-primary download" type="submit">Exportar</button>
    </form>
</edit-box>
