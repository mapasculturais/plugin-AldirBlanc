<?php
use MapasCulturais\i;

if ($inciso == 1) {
    $route = MapasCulturais\App::i()->createUrl("remessas", "exportBankless");
    $title = "Exportações Desbancarizados";
}
?>

<a class="btn btn-default download btn-export-cancel" ng-click="editbox.open('form-parameters-bankless', $event)" rel="noopener noreferrer">Desbancarizados</a>

<!-- Formulários -->
<edit-box id="form-parameters-bankless" position="top" title="<?php i::esc_attr_e($title) ?>" cancel-label="Cancelar" close-on-cancel="true">
    <form class="form-export-dataprev" action="<?=$route?>" method="POST">
        <label for="paymentDate">Data de pagamento</label>
        <input type="date" name="paymentDate" id="paymentDate">
        <label for="type">Tipo de exportação</label>
        <select name="type" id="type"><?php
        foreach ($exports as $value => $label) { ?>
            <option value="<?=$value?>"><?=$label?></option><?php
        } ?>
        </select>
        <div>
            <b>Escolha quais inscrições quer exportar</b> <br>
            <input type="radio" name="statusPayment" value="0" checked title="Exporta as inscrições com pagamentos cadastrados na data selecionada. Após exportar um arquivo de remessa nessa modalidade, há mudança de status."> Para pagamento<br>
            <input type="radio" name="statusPayment" value="3" title="Exporta asinscrições com pagamentos cadastrados na data selecionada, que já foram exportadas anteriormente para pagamento."> Em processo de pagamento<br>
            <input type="radio" name="statusPayment" value="all" title="Exporta todas as inscrições com pagamentos cadastrados na data selecionada."> Todas
        </div>
        <input type="hidden" name="opportunity" value="<?=$opportunity?>">
        <p><span style="color: red;">*</span> Obrigatório</p>
        <button class="btn btn-primary download" type="submit">Exportar</button>
    </form>
</edit-box>
