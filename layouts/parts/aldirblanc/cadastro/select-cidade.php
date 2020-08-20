<?php
/**
 * Carrega as cidades para cadastro no Inciso II
 * Contém o nome da cidade e o ID da oportunidade vinculada.
 */

ksort($cidades)

?>
<form>
    <select id="option4" class="js-select-cidade">
        <option value="-1"><?php \MapasCulturais\i::_e("Selecione sua cidade");?></option>
        <?php foreach($cidades as $nome => $oportunidade): ?>
            <option value="<?=$oportunidade?>"><?=$nome?></option>
        <?php endforeach; ?>
    </select>
</form>
<!--    <a class="js-back lab-back" href="#"><span class="icon icon-go-back"></span><span class="screen-reader-text">--><?php //\MapasCulturais\i::_e("Voltar");?><!--</span></a>-->
<script>
    $(document).ready(function() {
        $('select.js-select-cidade').select2({
            placeholder: `<?php \MapasCulturais\i::_e("Selecione sua cidade");?>`,
            allowClear: true
        });
    });
</script>
