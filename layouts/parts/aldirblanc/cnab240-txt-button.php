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

<a class="btn btn-default download btn-export-cancel"  ng-click="editbox.open('form-parameters-Cnab240', $event)" rel="noopener noreferrer">TXT CNAB240 BB</a>

<!-- Formulário -->
<edit-box id="form-parameters-Cnab240" position="top" title="<?php i::esc_attr_e($title) ?>" cancel-label="Cancelar" close-on-cancel="true">
    <form class="form-export-dataprev" action="<?=$route?>" method="POST">
  
        <label for="from">Data de pagamento</label>
        <input type="date" name="paymentDateCnab240" id="paymentDateCnab240">
        
        <div>
            <b>Escolha quais inscrições quer exportar</b> <br>      
            <input type="radio" name="statusPaymentCnab240" value="0" checked title="Exporta CNAB240 das inscrições com pagamentos cadastrados na data selecionada. Após exportar nessa modalidade, a inscrição fica com status de (EM PROCESSO DE PAGAMENTO)"> Exportar para pagamento <span style="color: red;">*</span><br>
            <input type="radio" name="statusPaymentCnab240" value="3" title="Exporta CNAB240 com pagamentos cadastrados na data selecionada, que já foram exportadas anteriormente para pagamento (SEM MUDANÇA DE STATUS)"> Visualizar em processo de pagamento<br>
            <input type="radio" name="statusPaymentCnab240" value="all" title="Exporta CNAB240 com todas as inscrições com pagamentos cadastrados na data selecionada (SEM MUDANÇA DE STATUS)"> Exportar todas<br><br>       
            
            <?php if($selectList){?>
                <b>Selecionar uma lista de inscrições que</b> <br>  
                <input type="radio" name="cnabSelect" value="only" title=""> Devem ser exportadas</br>
                <input type="radio" name="cnabSelect" value="ignore" title=""> Devem ser ignoradas </br>
                <input type="radio" name="cnabSelect" title="" checked> Não usar essa função</br>
                <textarea name="listCnab" id="listCnab" cols="30" rows="2" placeholder="Separe por virgula e sem prefixo Ex.: 1256584,6941216854"></textarea> 
            <?php } ?>       
        </div>

        <input type="hidden" name="opportunity" value="<?=$opportunity?>">
        <input type="radio" name="typeFile" value="TS" title=""> Gerar arquivo para teste TS</br>
        <input type="radio" name="typeFile" title="" checked> Gerar arquivo para pagamento</br>
        <p><span style="color: red;">*</span> Obrigatório informar data de pagamento</p>
        <button class="btn btn-primary download" name = "cnab240" value="cnab240" type="submit">Exportar</button>
    </form>
</edit-box>