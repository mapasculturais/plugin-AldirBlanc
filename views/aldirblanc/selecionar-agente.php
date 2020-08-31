<?php
    
?>

<section class="agentes">
    <header class="agentes--head">
        <i class="fas fa-users"></i>
        <h3 class="agentes--title"><?php \MapasCulturais\i::_e("Selecione um agente para acompanhar a solicitação");?></h3>
        <p class="agentes--summary"><?php \MapasCulturais\i::_e("O benefício é destinado aos trabalhadores e trabalhadoras da cultura que tiveram suas atividades interrompidas e se enquadram ao disposto no Art. 6º - Lei 14.017/2020.");?></p>
    </header>
    <div class="agentes--wrapper">
        <?php foreach($agents as $agent): ?>
            <div class="informative-box agentes--item" agentOwner="<?php echo isset($agentOwner) ? $agentOwner : ''; ?>" opportunity="<?php echo isset($opportunity) ? $opportunity : ''; ?>" category="<?php echo isset($category) ? $category : ''; ?>" inciso="<?php echo isset($inciso) ? $inciso : ''; ?>" name="<?php echo $agent->name; ?>" value="<?php echo $agent->id; ?>">
                <div class="informative-box--icon">
                    <?php if($agent->avatar): ?>
                        <img src="<?php echo $agent->avatar;?>" class="agentes--thumbnail" alt="<?php echo $agent->name ?>">
                    <?php else:?>
                        <img src="<?php $app->view->asset('img/avatar--agent.png');?>" class="agentes--thumbnail" alt="<?php echo $agent->name ?>">
                    <?php endif; ?>
                </div>

                <div class="informative-box--title">
                    <h2><?=$agent->name?></h2>
                    <i class="far fa-check-circle"></i>
                </div>

                <div class="informative-box--content agentes-item" data-content="">

                    <span class="agentes--titulo">
                        <?php \MapasCulturais\i::_e("Tipo");?>
                    </span>

                    <span class="agentes--descricao">
                        <?=$agent->type?>
                    </span>

                    <span class="agentes--titulo">
                        <?php \MapasCulturais\i::_e("Áreas de Atuação");?>
                    </span>

                    <span class="agentes--descricao">
                         <?php if(count($agent->areas) > 0): ?>
                              <?php foreach($agent->areas as $area): ?>
                                  <span><?php echo $area ?></span><br>
                              <?php endforeach; ?>
                         <?php else:?>
                             <span><?php \MapasCulturais\i::_e("Agente sem áreas de atuação definidas"); ?></span><br>
                         <?php endif; ?>
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

        $('.agentes--item').on('click', function (event) {
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

            modal.css("display", "flex").hide().fadeIn(900);
            
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
