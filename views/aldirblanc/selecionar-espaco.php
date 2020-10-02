<?php
    
?>

<section class="espaco">
    <header class="espaco--head">
        <i class="fas fa-building"></i>
        <h3 class="espaco--title"><?php \MapasCulturais\i::_e("Selecione um espaço");?></h3>
        <p class="espaco--summary"></p>
    </header>
    <div class="espaco--wrapper">
        <?php foreach($spaces as $space): ?>
            <div class="informative-box espaco--item btn-selecionar" inciso='<?= $inciso?>' category="<?= $category?>" opportunity="<?= $opportunity?>" type="button" class="btn btn-selecionar" agent="<?php echo $agent; ?>" name="<?php echo $space->name; ?>" value="<?php echo $space->id; ?>">
                <div class="informative-box--icon">
                    <?php if(isset($space->avatar)): ?>
                        <img src="<?php echo $space->avatar;?>" class="thumbnail" alt="<?php echo $space->name ?>">
                    <?php else:?>
                        <img src="<?php $app->view->asset('img/avatar--space.png');?>" class="thumbnail" alt="<?php echo $space->name ?>">
                    <?php endif; ?>
                </div>

                <div class="informative-box--title">
                    <h2><?php echo $space->name ?></h2>
                    <i class="fas fa-minus"></i>
                </div>

                <div class="informative-box--content espaco-item" data-content="">
                    <span class="espaco--titulo">
                        <?php sizeof($space->areas) > 1? \MapasCulturais\i::_e("Áreas de Atuação") : \MapasCulturais\i::_e("Área de Atuação");?>
                    </span>

                    <span class="espaco--descricao">
                        <?php foreach($space->areas as $area): ?>
                            <span><?php echo $area ?></span><br>
                        <?php endforeach; ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div id="modalAlert" class="modal">
        <!-- Modal content -->
        <div class="modal-content">
            <span class="close">&times;</span>
            <p class="text"></p>
            <button class="btn" id="confirmar"><?php \MapasCulturais\i::_e("Confirmar");?></button>
        </div>
    </div>
</section>





<script>
    $(document).ready(function(){
        $('.wrapper').on('click', function (event) {
            handleSelectionEffect(this)
        });

        $('.btn-selecionar').on('click', function (event) {
            $('.espaco--item').removeClass('active');
            $(this).toggleClass('active');

            let modal       = $('#modalAlert');
            let space       = $(this).attr('value');
            let spaceName   = $(this).attr('name');
            let agent       = $(this).attr('agent');
            let inciso      = $(this).attr('inciso');
            let category    = $(this).attr('category');
            let opportunity = $(this).attr('opportunity');

            modal.css("display", "flex").hide().fadeIn(900);

            let msg = `<?php \MapasCulturais\i::_e("Realizar inscrição para <strong>_name_</strong> no Auxílio Emergencial da Cultura.");?>`;
            msg = msg.replace(/_name_/g, spaceName);
            $('.modal-content').find('.text').html(msg);

            $('.close').on('click', function () {
                $('.espaco--item').removeClass('active');
                modal.fadeOut('slow');
            });

            $('#confirmar').on('click', function () {
                document.location =  MapasCulturais.createUrl('aldirblanc', 'nova_inscricao', {category: category, opportunity: opportunity, agent: agent, inciso: inciso, space: space})
            });
        });

        function handleSelectionEffect(card) {
            $('.wrapper').removeClass('selected-card');
            $(card).addClass('selected-card');
        }
    });
</script>
