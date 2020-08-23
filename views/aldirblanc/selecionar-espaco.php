<?php
    
?>

<article class="main-content" >
    <section id="selecionar-agentes">
        <h3 class="title-section"><?php \MapasCulturais\i::_e("Selecione um espaço");?></h3>
        <div id="agentes">
            <?php foreach($spaces as $space): ?>
                <div class="wrapper" >
                    <div class="profile">
                        <?php if(isset($space->avatar)): ?>
                            <img src="<?php echo $space->avatar;?>" class="thumbnail" alt="<?php echo $space->name ?>">
                        <?php else:?>
                            <img src="<?php $app->view->asset('img/avatar--space.png');?>" class="thumbnail" alt="<?php echo $space->name ?>">
                        <?php endif; ?>

                        <h3 class="name"><?php echo $space->name ?></h3>
                        <p class="title"><?php \MapasCulturais\i::_e("Áreas de Atuação");?></p>
                        <p class="description" >
                            <?php foreach($space->areas as $area): ?>
                                <span><?php echo $area ?></span><br>
                            <?php endforeach; ?>
                        </p>
                        <button inciso='<?= $inciso?>' category="<?= $category?>" opportunity="<?= $opportunity?>" type="button" class="btn btn-selecionar" agent="<?php echo $agent; ?>" name="<?php echo $space->name; ?>" value="<?php echo $space->id; ?>"> <?php \MapasCulturais\i::_e("Selecionar");?></button>
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


</article>

<script>
    $(document).ready(function(){
        $('.wrapper').on('click', function (event) {
            handleSelectionEffect(this)
        });

        $('.btn-selecionar').on('click', function (event) {
            let modal       = $('#modalAlert');
            let space     = this.value;
            let spaceName   = this.name;
            let agent       = $(this).attr('agent');
            let inciso      = $(this).attr('inciso');
            let category    = $(this).attr('category');
            let opportunity = $(this).attr('opportunity');
            opportunity
            modal.fadeIn(1500);
            let msg = `<?php \MapasCulturais\i::_e("Realizar inscrição para <strong>_name_</strong> no Auxílio Emergencial da Cultura.");?>`;
            msg = msg.replace(/_name_/g, spaceName);
            $('.modal-content').find('.text').html(msg);

            $('.close').on('click', function () {
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
