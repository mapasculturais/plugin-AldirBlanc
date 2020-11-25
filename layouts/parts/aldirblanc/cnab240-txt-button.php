<?php 
use MapasCulturais\i;

if($inciso == 1){
    $route = MapasCulturais\App::i()->createUrl('remessas', 'exportCnab240Inciso1'); 
    $title = "Exportador CNAB240 BB inciso I";

}elseif($inciso == 2){
    $route = MapasCulturais\App::i()->createUrl('remessas', 'exportCnab240Inciso2'); 
    $title = "Exportador CNAB240 BB inciso II";

}elseif($inciso == 3){    
    $route = MapasCulturais\App::i()->createUrl('remessas', 'exportCnab240Inciso3');
    $title = "Exportador CNAB240 BB inciso III";  
}
?>

<a class="btn btn-default download btn-export-cancel"  ng-click="editbox.open('form-parameters-cnab240', $event)" rel="noopener noreferrer">TXT CNAB240 BB</a>

<!-- Formulário -->
<edit-box id="form-parameters-cnab240" position="top" title="<?php i::esc_attr_e($title) ?>" cancel-label="Cancelar" close-on-cancel="true">
    <form class="form-export-dataprev" action="<?=$route?>" method="POST">
  
    <label for="from"><span style="color: red;">*</span> Data de pagamento</label>
        <input type="date" name="paymentDateCanb240" id="paymentDateCanb240">
        
        <div>  
        <b>Escolha quais inscrições quer exportar</b> <br>      
        <input type="radio" name="statusPaymentCanb240" value="0" checked title="Exporta CNAB240 das inscrições com pagamentos cadastrados na data selecionada. Após exportar nessa modalidade, a inscrição fica com status de (EM PROCESSO DE PAGAMENTO)"> Exportar para pagamento<br>
        <input type="radio" name="statusPaymentCanb240" value="3" title="Exporta CNAB240 com pagamentos cadastrados na data selecionada, que já foram exportadas anteriormente para pagamento (SEM MUDANÇA DE STATUS)"> Visualizar em processo de pagamento<br>
        <input type="radio" name="statusPaymentCanb240" value="all" title="Exporta CNAB240 com todas as inscrições com pagamentos cadastrados na data selecionada (SEM MUDANÇA DE STATUS)"> Exportar todas        
        </div>
        <input type="hidden" name="opportunity" value="<?=$opportunity?>">
        <p><span style="color: red;">*</span> Obrigatório</p>
        <button class="btn btn-primary download" name = "canb240" value="canb240" type="submit">Exportar</button>
    </form>
</edit-box>