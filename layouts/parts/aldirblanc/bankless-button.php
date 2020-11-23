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

        <input type="date" name="paymentDateMc1460" id="paymentDateMc1460">
        
        <div>  
            <b>Escolha quais inscrições quer exportar</b> <br>      
            <input type="radio" name="statusPaymentMci460" value="0" checked title="Exporta MCI460 das inscrições com pagamentos cadastrados na data selecionada. Após exportar nessa modalidade, a inscrição fica com status de (EM PROCESSO DE PAGAMENTO)"> Exportar para pagamento<br>
            <input type="radio" name="statusPaymentMci460" value="3" title="Exporta MCI460 com pagamentos cadastrados na data selecionada, que já foram exportadas anteriormente para pagamento (SEM MUDANÇA DE STATUS)"> Visualizar em processo de pagamento<br>
            <input type="radio" name="statusPaymentMci460" value="all" title="Exporta MCI460 com todas as inscrições com pagamentos cadastrados na data selecionada (SEM MUDANÇA DE STATUS)"> Exportar todas        
        </div>
        <input type="hidden" name="opportunity" value="<?=$opportunity?>">        
        <p><span style="color: red;">*</span> Obrigatório</p>
        <button class="btn btn-primary download" name = "mci460" value="mci460" type="submit">Exportar</button>
    </form>
</edit-box>
