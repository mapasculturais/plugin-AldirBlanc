<?php
    
?>

<article class="main-content" >
    <section id="selecionar-agentes">
        <?php $typeName = ($tipo == 1 ? 'individual' : 'coletivo');?>
        <h3 class="title-section"><?php \MapasCulturais\i::_e("Selecione um agente " . $typeName);?></h3>
        <div id="agentes">
            <?php foreach($agents as $agent): ?>
                <div class="wrapper" >
                    <div class="profile">
                        <?php if($agent->avatar): ?>
                            <img src="<?php echo $agent->avatar;?>" class="thumbnail" alt="<?php echo $agent->name ?>">
                        <?php else:?>
                            <img src="<?php $app->view->asset('img/avatar--agent.png');?>" class="thumbnail" alt="<?php echo $agent->name ?>">
                        <?php endif; ?>

                        <h3 class="name"><?php echo $agent->name ?></h3>
                        <p class="title"><?php \MapasCulturais\i::_e("Tipo");?></p>
                        <p class="description"><?php echo $agent->type ?></p>
                        <p class="title"><?php \MapasCulturais\i::_e("Áreas de Atuação");?></p>
                        <p class="description" >
                            <?php foreach($agent->areas as $area): ?>
                                <span><?php echo $area ?></span><br>
                            <?php endforeach; ?>
                        </p>
                        <button type="button" agentOwner="<?php echo isset($agentOwner) ? $agentOwner : ''; ?>" opportunity="<?php echo isset($opportunity) ? $opportunity : ''; ?>" category="<?php echo isset($category) ? $category : ''; ?>" inciso="<?php echo isset($inciso) ? $inciso : ''; ?>" class="btn btn-selecionar" name="<?php echo $agent->name; ?>" value="<?php echo $agent->id; ?>"> <?php \MapasCulturais\i::_e("Selecionar");?></button>
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
            let agentRelated = '';
            let modal       = $('#modalAlert');
            let agentId     = $(this).attr('value');
            let agentName   = $(this).attr('name');
            let inciso      = $(this).attr('inciso');
            let category    = $(this).attr('category');
            let opportunity = $(this).attr('opportunity');
            let agentOwner  = $(this).attr('agentOwner');
            if (agentOwner !== '') {
                agentId = agentOwner;
                agentRelated = this.value;
            }
            modal.fadeIn(1500);
            let msg = `<?php \MapasCulturais\i::_e("Realizar inscrição para <strong>_name_</strong> no Auxílio Emergencial da Cultura.");?>`;
            msg = msg.replace(/_name_/g, agentName);
            $('.modal-content').find('.text').html(msg);

            $('.close').on('click', function () {
                modal.fadeOut('slow');
            });

            $('#confirmar').on('click', function () {
                document.location =  MapasCulturais.createUrl('aldirblanc', 'nova_inscricao', {agent: agentId, agentRelated:agentRelated, inciso: inciso, category: category, opportunity: opportunity})
            });
        });

        function handleSelectionEffect(card) {
            $('.wrapper').removeClass('selected-card');
            $(card).addClass('selected-card');
        }
    });
</script>
