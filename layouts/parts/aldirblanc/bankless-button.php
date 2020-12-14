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
            <label for="statusPayment"><b>Escolha quais inscrições quer exportar</b></label><br>
            <input type="radio" name="statusPayment" value="0" checked title="Exporta as inscrições com pagamentos cadastrados na data selecionada. Após exportar um arquivo de remessa nessa modalidade, há mudança de status."> Para pagamento <span style="color: red;">*</span><br>
            <input type="radio" name="statusPayment" value="3" title="Exporta as inscrições com pagamentos cadastrados na data selecionada, que já foram exportadas anteriormente para pagamento."> Em processo de pagamento<br>
            <input type="radio" name="statusPayment" value="all" title="Exporta todas as inscrições com pagamentos cadastrados na data selecionada."> Todas
            <br>
            <label for="serial"><b>Número da remessa</b></label>
            <input style="display: inline;" type="number" name="serial" id="serial" min="0" value="0">
            <br>
            <?php if ($selectList) { ?>
                <label for="select"><b>Selecionar uma lista de inscrições que</b></label><br>
                <input type="radio" name="select" value="only" title=""> Devem ser exportadas<br>
                <input type="radio" name="select" value="ignore" title=""> Devem ser ignoradas<br>
                <input type="radio" name="select" title="" checked> Não usar essa função<br>
                <textarea name="list" id="list" cols="30" rows="2" placeholder="Separe por virgula e sem prefixo Ex.: 1256584,6941216854"></textarea>
            <?php } ?>
        </div>
        <input type="hidden" name="opportunity" value="<?=$opportunity?>">
        <p><span style="color: red;">*</span> Obrigatório informar data de pagamento</p>
        <button class="btn btn-primary download" type="submit">Exportar</button>
    </form>
</edit-box>