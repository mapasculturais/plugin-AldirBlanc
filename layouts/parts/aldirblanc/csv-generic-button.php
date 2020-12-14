<?php 
use MapasCulturais\i;

if($inciso == 1){
    $route = MapasCulturais\App::i()->createUrl('remessas', 'genericExportInciso1'); 
    $title = "Exportador remessa genérico Inciso I";

}elseif($inciso == 2){
    $route = MapasCulturais\App::i()->createUrl('remessas', 'genericExportInciso2'); 
    $title = "Exportador remessa genérico II";

}elseif($inciso == 3){    
    $route = MapasCulturais\App::i()->createUrl('remessas', 'genericExportInciso3');
    $title = "Exportador remessa genérico III";  
}
?>

<a class="btn btn-default download btn-export-cancel"  ng-click="editbox.open('form-parameters-generic', $event)" rel="noopener noreferrer">Exportador genérico</a>

<!-- Formulário -->
<edit-box id="form-parameters-generic" position="top" title="<?php i::esc_attr_e($title) ?>" cancel-label="Cancelar" close-on-cancel="true">
    <form class="form-export-dataprev" action="<?=$route?>" method="POST">
  
        <label for="from">Data de pagamento</label>
        <input type="date" name="paymentDateGeneric" id="paymentDateGeneric">
        
        <div>  
        <b>Escolha quais inscrições quer exportar</b> <br>      
        <input type="radio" name="statusPaymentGeneric" value="0" checked title="Exporta CSV das inscrições com pagamentos cadastrados na data selecionada. Após exportar nessa modalidade, a inscrição fica com status de (EM PROCESSO DE PAGAMENTO)"> Exportar para pagamento<span style="color: red;"> *</span> <br>
        <input type="radio" name="statusPaymentGeneric" value="3" title="Exporta CSV com pagamentos cadastrados na data selecionada, que já foram exportadas anteriormente para pagamento (SEM MUDANÇA DE STATUS)"> Visualizar em processo de pagamento<br>
        <input type="radio" name="statusPaymentGeneric" value="all" title="Exporta CSV com todas as inscrições com pagamentos cadastrados na data selecionada (SEM MUDANÇA DE STATUS)"> Exportar todas  <br><br>       
        
        <?php if($selectList){?>
                <b>Selecionar uma lista de inscrições que</b> <br>  
               
                <input type="radio" name="genericSelect" value="only" title=""> Devem ser exportadas</br>
                <input type="radio" name="genericSelect" value="ignore" title=""> Devem ser ignoradas </br>
                <input type="radio" name="genericSelect" title="" checked> Não usar essa função</br>
                <textarea name="listGeneric" id="listGeneric" cols="30" rows="2" placeholder="Separe por virgula e sem prefixo Ex.: 1256584,6941216854"></textarea> 
            <?php } ?> 
        </div>

        <input type="hidden" name="opportunity" value="<?=$opportunity?>">        
        <p><span style="color: red;">*</span> Obrigatório informar data de pagamento</p>
        <button class="btn btn-primary download" name = "generic" value="generic" type="submit">Exportar</button>
    </form>
</edit-box>