<?php 
use MapasCulturais\i;

if ($inciso == 1){
    $route = MapasCulturais\App::i()->createUrl('dataprev', 'export_inciso1');    
    ?>
    
    <a class="btn btn-primary btn-export-cancel"  ng-click="editbox.open('export-inciso1', $event)" rel="noopener noreferrer">CSV DataPrev</a>

    <!-- Formulário -->
    <edit-box id="export-inciso1" position="top" title="<?php i::esc_attr_e('Exportar csv Inciso 1') ?>" cancel-label="Cancelar" close-on-cancel="true">
        <form class="form-export-dataprev" action="<?=$route?>" method="POST">
      
            <label for="from">Data inícial</label>
            <input type="date" name="from" id="from">
            
            <label for="from">Data final</label>  
            <input type="date" name="to" id="to">

            # Caso não queira filtrar entre datas, deixe os campos vazios.
            <button class="btn btn-primary download" type="submit">Exportar</button>
        </form>
    </edit-box>

    <?php
}
else if ($inciso ==2){
    $route = MapasCulturais\App::i()->createUrl('dataprev', 'export_inciso2');   
    ?>
    <a class="btn btn-primary form-export-clear" ng-click="editbox.open('export-inciso2', $event)" rel="noopener noreferrer">CSV DataPrev</a>
    
    <!-- Formulario para cpf -->
    <edit-box id="export-inciso2" position="top" title="<?php i::esc_attr_e('Exportar csv Inciso 2') ?>" cancel-label="Cancelar" close-on-cancel="true">
        <form class="form-export-dataprev" action="<?=$route?>" method="POST">
      
            <label for="from">Data inícial</label>
            <input type="date" name="from" id="from">
            
            <label for="from">Data final</label>  
            <input type="date" name="to" id="to">            

            <label for="type">Tipo de exportação (CPF ou CNPJ)</label>
            <select name="type" id="type">
                <option value="cpf">Pessoa física (CPF)</option>
                <option value="cnpj">Pessoa jurídica (CNPJ)</option>
            </select>

            <input type="hidden" name="opportunity" value="<?=$opportunity?>">

            # Caso não queira filtrar entre datas, deixe os campos vazios.
            <button class="btn btn-primary download" type="submit">Exportar</button>            
        </form>
    </edit-box>

    
    <?php
}
?>
