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
  
        <label for="from"><span style="color: red;">*</span> Data de pagamento</label>
        <input type="date" name="paymentDate" id="paymentDate">        
       
        
        <div>  
        <b>Escolha quais inscrições quer exportar</b> <br>      
        <input type="radio" name="statusPayment" value="0" checked> Exportar pendentes de pagamento<br>
        <input type="radio" name="statusPayment" value="3"> Exportar em processo de pagamento<br>
        <input type="radio" name="statusPayment" value="all"> Exportar todas        
        </div>
        <input type="hidden" name="opportunity" value="<?=$opportunity?>">
        <p><span style="color: red;">*</span> Obrigatório</p>
        <button class="btn btn-primary download" type="submit">Exportar</button>
    </form>
</edit-box>