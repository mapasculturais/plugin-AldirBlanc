<?php

use MapasCulturais\i;
use MapasCulturais\Entities\Registration;

$inciso1Limite = $this->controller->config['inciso1_limite'];
$inciso2Limite = $this->controller->config['inciso2_limite'];
$inciso2_enabled = $this->controller->config['inciso2_enabled'];
$inciso1_enabled = $this->controller->config['inciso1_enabled'];

$this->jsObject['opportunityId'] = null;

if (count($cidades) === 0) {
    $inciso2_enabled = false;
    
} else if(count($cidades) === 1) {
    /**
     * Pega oportunidade/cidade default para cadastro do inciso II.
     */
    $this->jsObject['opportunityId'] = reset($cidades);
}

?>
<script>
    $(document).ready(function() {

        var params = {
            opportunity: null,
            category: null
        };
        var formalizado = null;
        var coletivo = null;
        var returning = false;
        /**
         * Se houver cidade/oportunidade defualt definida na configuração do plugin para o Inciso II, o id é setado no paramentro.
         */
        if (MapasCulturais.opportunityId != null) {
            params.opportunity = MapasCulturais.opportunityId
        }

        /**
         * Redireciona o usuário para próxima tela conforme paramentros selecionados.
         */
        function goToNextPage() {
            params.category = coletivo + '-' + formalizado;
            document.location = MapasCulturais.createUrl('aldirblanc', 'coletivo', params)
        }

        function showModal(){
            var msg   = "";
            var modal = $('#modalAlertCadastro');
            var coletivo   =  $('input[name=coletivo]:checked').siblings().find('.js-text').text();
            var fomalizado =  $('input[name=formalizado]:checked').siblings().find('.js-text').text();

            coletivo = coletivo.replace(".", "");
            fomalizado = fomalizado.replace(".", "");

            var nomeCidade =  $('.js-select-cidade option:selected').text();

            modal.css("display", "flex").hide().fadeIn(900);

            $('#modalAlertCadastro .modal-content').find('.js-confirmar').show();
            $('#modalAlertCadastro .modal-content').find('.js-title').text('Confirmação');


            $('#modalAlertCadastro .modal-content').find('.btn').val('next');
            $('#modalAlertCadastro .modal-content').find('.btn').text('<?php \MapasCulturais\i::_e("Confirmar"); ?>');

            if(params.opportunity != null){

                msg = `<?php \MapasCulturais\i::_e("Você está solicitando o benefício para <strong>_fomalizado_</strong> para espaço do tipo  <strong>_coletivo_</strong>_cidade_ <br><br><p>Você confirma essas informações?</p>"); ?>`;
                msg = msg.replace(/_fomalizado_/g, fomalizado);
                msg = msg.replace(/_coletivo_/g, coletivo);

                if(nomeCidade){
                    msg = msg.replace(/_cidade_/g, " na cidade de <strong>" + nomeCidade + "</strong>.");
                }else{
                    msg = msg.replace(/_cidade_/g, ".");
                }

            }else{
                var cidade = $('.js-select-cidade option:selected').val();
                if( cidade > 0 ){
                    msg = `<?php \MapasCulturais\i::_e("Você está solicitando o benefício para <strong>_fomalizado_</strong> para espaço do tipo  <strong>_coletivo_</strong>_cidade_ <br><br><p>Você confirma essas informações?</p>"); ?>`;
                    msg = msg.replace(/_fomalizado_/g, fomalizado);
                    msg = msg.replace(/_coletivo_/g, coletivo);
                    if(nomeCidade){
                        msg = msg.replace(/_cidade_/g, " na cidade de <strong>" + nomeCidade + "</strong>.");
                    }else{
                        msg = msg.replace(/_cidade_/g, ".");
                    }
                }else{
                    showModalMsg('Atenção!', 'Você precisa selecionar a cidade.');
                }
            }

            $('#modalAlertCadastro .modal-content').find('.modal-content-text').html(msg);

            $('.close, .btn-ok').on('click', function() {
                modal.fadeOut('slow');
            });

        }

        function showModalMsg(title, message){
            let modal   = $('#modalAlertCadastro');
            let text = document.getElementById("modal-content-text");

            $('#modalAlertCadastro .modal-content').find('.js-title').text(title);

            $('#modalAlertCadastro .modal-content').find('.btn').val('close');
            $('#modalAlertCadastro .modal-content').find('.btn').text('<?php \MapasCulturais\i::_e("OK"); ?>');

            text.textContent = message ;

            modal.fadeIn('fast');

            $('.close, .btn-ok').on('click', function() {
                modal.fadeOut('fast');
            });
        }

        /**
         * Ao clicar em uma das opções do local de atividade do beneficiário , o usuário é encaminhado para tela de opções de personalidades jurídica do beneficiário.
         */
        function goToQuestionPersonality(){
            $('.js-questions-tab').hide();
            $('#personalidade-juridica').fadeIn('fast');
            returning = false;
        }

        /**
         * Ao clicar em uma das opções de opções de personalidades jurídica do beneficiário, o usuário é encaminhado para tela de seleção da oportunidade/cidade,
         * senão é redirecionado conforme os parametros selecionados.
         */
        function goToQuestionCounty(){
            $('.js-questions-tab').hide();

            if (returning) {
                $('.js-questions-tab').hide();
                $('#select-cidade').fadeIn('fast');
                return;
            }

            let hasCities = $('.js-questions').find('#select-cidade');
            /**
             * Se a oportunidade for null e o campo de seleção da cidades/oportunidades for encontrado, significa que há mais uma cerragada na configuração do plugin.
             * O usuário deverá ser encaminhado para tela de seleção da cidade/oportunidade.
             */
            if (params.opportunity == null && hasCities.length > 0) {
                $('.js-questions-tab').hide();
                $('#select-cidade').fadeIn('fast');
                returning = false;
            } else {
                // $('.js-questions-tab').hide();
                showModal()
            }
        }

        $('.coletivo').click(function() {
            coletivo = this.value;
            $('.coletivo').parent().removeClass('selected')
            $(this).parent().addClass('selected');
        });

        $('.formalizado').click(function() {
            formalizado = this.value
            $('.formalizado').parent().removeClass('selected')
            $(this).parent().addClass('selected');
        });

        /**
         * Ao selecionar a cidade/opotunidade o usuário é redirecionado conforme os parametros selecionados.
         */
        $('.js-select-cidade').change(function() {
            params.opportunity = this.value;
        });


        $('.js-back').click(function() {
            var parentId = $(this).closest('.js-questions-tab').attr('id');
            returning = true;
            switch (parentId) {
                case 'personalidade-juridica':
                    $('#personalidade-juridica').hide();
                    $('#local-atividade').fadeIn('fast');
                    break;
                case 'local-atividade':
                    $('.js-questions').hide();
                    $('#personalidade-juridica').hide();
                    $('.js-lab-item').fadeIn('fast');
                    break;
                case 'select-cidade':
                    $('#select-cidade').hide();
                    $('#personalidade-juridica').fadeIn('fast');
                    params.opportunity = null;
                    $(".js-select-cidade").select2("val", "-1");
                    break;
            }
        });

        $('.js-next').click(function() {
            var parentId = $(this).closest('.js-questions-tab').attr('id');

            if(parentId == 'local-atividade'){
                var hasSeletedColetivo =  $('input[name=coletivo]:checked');
                if(hasSeletedColetivo.length > 0){
                    goToQuestionPersonality()
                }else{
                    showModalMsg('Atenção!', 'Você precisa selecionar uma opção para avançar')
                }
            }else if(parentId == 'select-cidade'){
                showModal()
            }else{
                var hasSeletedFormalizado =  $('input[name=formalizado]:checked');
                if(hasSeletedFormalizado.length > 0){
                    goToQuestionCounty()
                }else{
                    showModalMsg('Atenção!','Você precisa selecionar uma opção para avançar')
                }
            }
        });

        $('button.js-confirmar').click(function() {
            if(this.value == 'next'){
                $('.js-questions-tab').hide();
                $('.js-questions').html('<h4>Enviando informações ...</h4>');
                $('#modalAlertCadastro').fadeOut('slow')
                goToNextPage();
            }else{
                $('#modalAlertCadastro').fadeOut('slow')
            }
        });

        //Fechar modal ao clicar fora dela.
        $(window).click(function (event) {
            var modal =  $('#modalAlertCadastro');
            if( event.target.value != 'next'){
                if($(event.target).css('display') == 'flex'){
                    modal.fadeOut('slow')
                }
            }
        });

        /**
         * Ao clicar nos cards do Inciso II, o usuário é encaminhado para tela de opções do local de atividade do beneficiário.
         */
        let selectedInciso = '';

        $('.js-lab-option').click(function() {
            // selectedInciso = $(this).attr('id');
            // $('.lab-option').removeClass('active');
            // $(this).toggleClass('active');

            $('.js-lab-item').fadeOut(1);
            $('.js-questions').fadeIn(11);
            $('#local-atividade').fadeIn('fast');
            returning = false;
        });
    });
</script>
<section class="lab-main-content cadastro">
    <header>
        <div class="intro-message">
            <div class="name"> Olá, <?= $niceName ?>! </div>
        </div>
    </header>

    <div class="js-lab-item lab-item cadastro-options">
        <!-- <p class="lab-form-question">Para quem você está solicitando o benefício? <a class="js-help icon icon-help" href="#" title=""></a></p> -->
        <h2 class="featured-title"> 
            Selecione abaixo o benefício desejado
        </h2>

        <div class="lab-form-filter opcoes-inciso">
            <?php
            $inciso1Title = 'Trabalhadoras e trabalhadores da Cultura';
            $inciso2Title = 'Espaços e organizações culturais';

            if (count($registrationsInciso1) < $inciso1Limite && $inciso1_enabled) {
            ?>
                <button onclick="location.href='<?= $this->controller->createUrl('individual') ?>'" clickable id="option3" class="informative-box lab-option">
                <!-- <button id="option3" class="informative-box lab-option"> -->
                    <div class="informative-box--icon">
                        <i class="fas fa-user"></i>
                    </div>

                    <div class="informative-box--title">
                        <h2><?= $inciso1Title ?></h2>
                        <i class="fas fa-minus"></i>
                        <!-- <i class="far fa-check-circle"></i> -->
                    </div>

                    <div class="informative-box--content active" data-content="">
                        <span class="more"> Mais informações </span>
                        <span class="content">
                            Farão jus à renda emergencial os(as) trabalhadores(as) da cultura com atividades interrompidas e que se enquadrem, comprovadamente, ao disposto no Art. 6º - Lei 14.017/2020. Prevê o pagamento de cinco parcelas de R$ 600 (seiscentos reais), podendo ser prorrogado conforme Art 5º - Lei 14.017/2020.
                        </span>
                    </div>
                </button>
            <?php
            }
            else if(!$inciso1_enabled && isset($this->controller->config['msg_inciso1_disabled'])){    
                $mensagemInciso1Disabled = $this->controller->config['msg_inciso1_disabled'];
                $this->part('aldirblanc/cadastro/inciso-disabled',  ['mensagem' => $mensagemInciso1Disabled, 'title' => $inciso1Title ]);  
            }
            foreach ($registrationsInciso1 as $registration) {
                $registrationUrl = $this->controller->createUrl('formulario', [$registration->id]);
                switch ($registration->status) {
                        //caso seja do Inciso 1 e nao enviada (Rascunho)
                    case Registration::STATUS_DRAFT:
                        $this->part('aldirblanc/cadastro/application-inciso1-draft',  ['registration' => $registration, 'registrationUrl' => $registrationUrl, 'niceName' => $niceName, 'registrationStatusName' => 'Cadastro iniciado']);
                        break;
                        //caso seja do Inciso 1 e tenha sido enviada
                    default:
                        $registrationStatusName = $summaryStatusName[$registration->status];
                        $this->part('aldirblanc/cadastro/application-status',  ['registration' => $registration, 'registrationStatusName' => $registrationStatusName]);
                        break;
                }
            }
            if (count($registrationsInciso2) < $inciso2Limite && $inciso2_enabled) {
                ?>
    
                    <button id="option1" role="button" class="informative-box js-lab-option lab-option">
                        <div class="informative-box--icon">
                            <i class="fas fa-university"></i>
                        </div>
    
                        <div class="informative-box--title">
                            <h2><?= $inciso2Title ?></h2>
                            <!-- <i class="far fa-check-circle"></i> -->
                            <i class="fas fa-minus"></i>
                        </div>
    
                        <div class="informative-box--content active" data-content="">
                            <span class="more js"> Mais informações </span>
                            <span class="content">
                                Farão jus ao benefício espaços, organizações da sociedade civil, empresas, cooperativas e instituições com finalidade cultural, como previsto nos Arts. 7º e 8º - Lei 14.017/2020. Prevê subsídio de R$3.000,00 (três mil reais) a R$10.000,00 (dez mil reais), prescrito pela gestão local.
                            </span>
                        </div>
                    </button>
    
                    <!-- End #option1 -->
                <?php
                }
                else if(!$inciso2_enabled && isset($this->controller->config['msg_inciso2_disabled'])){    
                    $mensagemInciso2Disabled = $this->controller->config['msg_inciso2_disabled'];
                    $this->part('aldirblanc/cadastro/inciso-disabled',  ['mensagem' => $mensagemInciso2Disabled, 'title' => $inciso2Title ]);  
                }
                foreach ($registrationsInciso2 as $registration) {
                    $registrationUrl = $this->controller->createUrl('formulario', [$registration->id]);
                    switch ($registration->status) {
                            //caso seja do Inciso 2 e nao enviada (Rascunho)
                        case Registration::STATUS_DRAFT:
                            //todo pegar nome do coletivo ou do espaço
                            $this->part('aldirblanc/cadastro/application-inciso2-draft',  ['registration' => $registration, 'registrationUrl' => $registrationUrl, 'niceName' => $niceName, 'registrationStatusName' => 'Cadastro iniciado']);
                            break;
                            //caso seja do Inciso 2 e tenha sido enviada
                        default:                        
                            $registrationStatusName = $summaryStatusName[$registration->status];
                            $this->part('aldirblanc/cadastro/application-status',  ['registration' => $registration, 'registrationStatusName' => $registrationStatusName]);
                            break;
                    }
                }
            ?>
        </div>

    </div><!-- End .lab-item -->

    <!-- Begin .js-questions -->
    <div class="js-questions questions inactive">

        <div id="local-atividade" class="js-questions-tab questions--tab inactive">
            <i class="questions--icon fas fa-university"></i>
            <h4 class="questions--title"><?php i::_e('Onde o beneficiário desenvolve suas atividades?') ?></h4>
            <p class="questions--summary"><?php i::_e('Escolha a opção que melhor identifica a situação do local onde o beneficiário do subsídio desenvolve a atividade cultural.') ?></p>

            <div class="questions--options ">
                <label class="informative-box">
                    <div class="informative-box--title">
                        <h2 class="js-text"><?php i::_e('Espaço físico próprio, alugado, itinerante, público cedido em comodato, emprestado ou de uso compartilhado;') ?></h2>
                        <i class="far fa-check-circle"></i>
                    </div>
                    <input type="radio" class="coletivo" name="coletivo" value="espaco" />
                </label>
                <label class="informative-box">
                    <div class="informative-box--title">
                        <h2 class="js-text"><?php i::_e('Espaço público (praça, rua, escola, quadra ou prédio custeado pelo poder público) ou espaço virtual de cultura digital.') ?></h2>
                        <i class="far fa-check-circle"></i>
                    </div>
                    <input type="radio" class="coletivo" name="coletivo" value="coletivo" />
                </label>
            </div>
            <div class="questions--nav">
                <button class="btn secondary btn-back js-back"><?php i::_e('Voltar') ?></button>
                <button class="btn btn-next js-next"><?php i::_e("Avançar"); ?></button>
            </div>
        </div>

        <div id="personalidade-juridica" class="js-questions-tab questions--tab inactive">
            <h4 class="questions--title"><?php i::_e('Qual a personalidade jurídica do beneficiário?') ?></h4>
            <p class="questions--summary"><?php i::_e('Escolha a opção que melhor identifica o beneficiário do subsídio previsto no inciso II do art. 2º da lei federal nº 14.017/2020.*') ?></p>
            <div class="questions--options">
                <label class="informative-box">
                    <div class="informative-box--title">
                        <h2 class="js-text"><?php i::_e('Entidade, empresa ou cooperativa do setor cultural com inscrição em CNPJ.') ?></h2>
                        <i class="far fa-check-circle"></i>
                    </div>
                    <input type="radio" class="formalizado" name="formalizado" value="formalizado" />
                </label>
                <label class="informative-box">
                    <div class="informative-box--title">
                        <h2 class="js-text"><?php i::_e('Espaço artístico e cultural mantido por coletivo ou grupo cultural (sem CNPJ) ou por pessoa física (CPF).') ?></h2>
                        <i class="far fa-check-circle"></i>
                    </div>
                    <input type="radio" class="formalizado" name="formalizado" value="nao-formalizado"/>
                </label>
            </div>
            <p class="questions--note">
                <?php i::_e('* Subsídio mensal para manutenção de espaços artísticos e culturais, microempresas e pequenas empresas culturais, cooperativas, instituições e organizações culturais comunitárias que tiveram as suas atividades interrompidas por força das medidas de isolamento social.') ?>
            </p>
            <div class="questions--nav">
                <button class="btn secondary btn-back js-back"><?php i::_e('Voltar') ?></button>
                <button class="btn btn-next js-next"><?php i::_e("Avançar"); ?></button>
            </div>
        </div>

        <?php if (count($cidades) > 1) : ?>
            <div id="select-cidade" class="js-questions-tab questions--tab lab-form-answer inactive">
                <i class="questions--icon fas fa-university"></i>
                <h4 class="questions--title"><?php i::_e('Selecione a cidade') ?></h4>
                <p class="questions--summary"><?php i::_e('Indique a cidade onde o beneficiário do subsídio está instalado ou tem desenvolvido, atualmente, suas atividades culturais.') ?></p>
                <?php $this->part('aldirblanc/cadastro/select-cidade', ['cidades' => $cidades]) ?>
                <div class="questions--nav">
                    <button class="btn secondary btn-back js-back"><?php i::_e('Voltar') ?></button>
                    <button class="btn btn-next js-next"><?php i::_e("Avançar"); ?></button>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <!-- End .js-questions -->

    </div><!-- End .box -->

    <div id="modalAlertCadastro" class="modal">
        <!-- Modal content -->
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 class="modal-content--title js-title"></h2>
            <p id="modal-content-text" class="modal-content-text"></p>
            <button class="btn js-confirmar"><?php \MapasCulturais\i::_e("Confirmar"); ?></button>
        </div>
    </div>

</section>