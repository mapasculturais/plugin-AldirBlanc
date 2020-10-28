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

<?php if($existsSelected){?>
<a class="btn btn-default download btn-export-cancel"  ng-click="editbox.open('form-parameters', $event)" rel="noopener noreferrer">Exportador genérico</a>
<?php } ?>

<!-- Formulário -->
<edit-box id="form-parameters" position="top" title="<?php i::esc_attr_e($title) ?>" cancel-label="Cancelar" close-on-cancel="true">
    <form class="form-export-dataprev" action="<?=$route?>" method="POST">
  
        <label for="from">Data inícial</label>
        <input type="date" name="from" id="from">
        
        <label for="from">Data final</label>  
        <input type="date" name="to" id="to">

        <input type="hidden" name="opportunity" value="<?=$opportunity?>">
        # Caso não queira filtrar entre datas, deixe os campos vazios.
        <button class="btn btn-primary download" type="submit">Exportar</button>
    </form>
</edit-box>