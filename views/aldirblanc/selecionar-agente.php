<?php
    $agents = [];

    foreach($user->enabledAgents as $agent){
        $enabledAgent         = new stdClass();
        $enabledAgent->id     = $agent->id;
        $enabledAgent->name   = $agent->name;
        $enabledAgent->avatar = $agent->getAvatar() ? $agent->getAvatar()->transform('avatarSmall')->url : null;
        $enabledAgent->type   = $agent->getType()->name;
        $enabledAgent->areas  = $agent->getTerms()['area'];
        array_push($agents, $enabledAgent);
    }

    foreach($user->hasControlAgents as $agent){
        $controlAgent         = new stdClass();
        $controlAgent->id     = $agent->id;
        $controlAgent->name   = $agent->name;
        $controlAgent->avatar = $agent->getAvatar() ? $agent->getAvatar()->transform('avatarSmall')->url : null;
        $controlAgent->type   = $agent->getType()->name;
        $controlAgent->areas  = $agent->getTerms()['area'];
        array_push($agents, $controlAgent);
    }
    //Ordena o array de agents pelo name
    usort($agents, function($a, $b) {return strcmp($a->name, $b->name);});
?>

<article class="main-content" >
    <section id="selecionar-agentes">
        <h3 class="title-section"><?php \MapasCulturais\i::_e("Selecione um agente");?></h3>
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
                        <button type="button" class="btn btn-selecionar" name="<?php echo $agent->name; ?>" value="<?php echo $agent->id; ?>"> <?php \MapasCulturais\i::_e("Selecionar");?></button>
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
            let modal     = $('#modalAlert');
            let agentId   = this.value;
            let agentName = this.name;
            let inciso    = 1;

            modal.fadeIn(1500);
            let msg = `<?php \MapasCulturais\i::_e("Realizar inscrição para <strong>_name_</strong> no Auxílio Emergencial da Cultura.");?>`;
            msg = msg.replace(/_name_/g, agentName);
            $('.modal-content').find('.text').html(msg);

            $('.close').on('click', function () {
                modal.fadeOut('slow');
            });

            $('#confirmar').on('click', function () {
                document.location =  MapasCulturais.createUrl('aldirblanc', 'nova_inscricao', {agent: agentId, inciso: inciso})
                // $.ajax({
                //     type: "GET",
                //     url: MapasCulturais.createUrl('aldirblanc', 'nova_inscricao', {agent: agentId, inciso: inciso}),
                //     success: function(msg){
                //         if(msg.error) {
                //             alert("você so pode ter uma inscrição")
                //             return false;
                //             //quando chegar la, verifica qual inscrição a pessoa já esta em andamento
                //             // document.location = `${MapasCulturais.baseURL}aldirblanc/individual/`;
                //             // return false;
                //         }
                //         document.location = `${MapasCulturais.baseURL}aldirblanc/individual/${msg.id}`;
                //     },
                //     error: function(XMLHttpRequest, textStatus, errorThrown) {
                //         console.log("some error***");
                //     }
                // });
            });
        });

        function handleSelectionEffect(card) {
            $('.wrapper').removeClass('selected-card');
            $(card).addClass('selected-card');
        }
    });
</script>
