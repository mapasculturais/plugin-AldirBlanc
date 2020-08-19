<?php
use MapasCulturais\i;
use MapasCulturais\Entities\Registration;
$inciso1Limite = $this->controller->config['inciso1_limite'];
$inciso2Limite = $this->controller->config['inciso2_limite'];
$inciso2_enabled = $this->controller->config['inciso2_enabled'];
$inciso1_enabled = $this->controller->config['inciso1_enabled'];

$this->jsObject['opportunityId'] = null;
if(count($cidades) <= 1){
    /**
     * Pega oportunidade/cidade default para cadastro do inciso II.
     */
    $this->jsObject['opportunityId'] = array_values($cidades)[0];
}

?>
<script>
    $(document).ready(function(){

        let params      = {opportunity: null, category:null};
        let formalizado = null;
        let coletivo    = null;
        /**
         * Se houver cidade/oportunidade defualt definida na configuração do plugin para o Inciso II, o id é setado no paramentro.
         */
        if(MapasCulturais.opportunityId != null){
            params.opportunity = MapasCulturais.opportunityId
        }

        /**
         * Redireciona o usuário para próxima tela conforme paramentros selecionados.
         */
        function goToNextPage(){
            params.category = formalizado +'-'+ coletivo;
            document.location = MapasCulturais.createUrl('aldirblanc', 'coletivo', params)
        }

        /**
         * Ao clicar nos cards do Inciso II, o usuário é encaminhado para tela de opções de personalidade jurídica do beneficiário.
         */
        $('.js-lab-option').click(function(){
            $('.js-lab-item').fadeOut(1);
            $('.js-questions').fadeIn(11);
            $('#local-atividade').fadeIn(1100);
        });

        /**
         * Ao clicar em uma das opções de personalidade jurídica do beneficiário, o usuário é encaminhado para tela de seleção da local de atividade do beneficiário.
         */
        $('.coletivo').click(function(){
            coletivo = this.value;
            $('.js-questions-tab').hide();
            $('#personalidade-juridica').fadeIn(1100);
        });

        /**
         * Ao clicar em uma das opções do local de atividade do beneficiário, o usuário é encaminhado para tela de seleção da oportunidade/cidade,
         * senão é redirecionado conforme os parametros selecionados.
         */
        $('.formalizado').click(function(){
            formalizado = this.value;
            $('.js-questions-tab').hide();

            let hasCities = $('.js-questions').find('#select-cidade');
            /**
             * Se a oportunidade for null e o campo de seleção da cidades/oportunidades for encontrado, significa que há mais uma cerragada na configuração do plugin.
             * O usuário deverá ser encaminhado para tela de seleção da cidade/oportunidade.
             */
            if(params.opportunity == null && hasCities.length > 0){
                $('.js-questions-tab').hide();
                $('#select-cidade').fadeIn(1100);
            }else{
                $('.js-questions-tab').hide();
                goToNextPage();
            }
        });

        /**
         * Ao selecionar a cidade/opotunidade o usuário é redirecionado conforme os parametros selecionados.
         */
        $('.js-select-cidade').change(function(){
            params.opportunity = this.value;
        });

        $('.coletivo').click(function() {
            $('.coletivo').parent().removeClass('selected')
            $(this).parent().addClass('selected');
        });

        $('.formalizado').click(function(){
            $('.formalizado').parent().removeClass('selected')
            $(this).parent().addClass('selected');
        });

        $('.js-back').click(function(){
            let parentId = $(this).parent().attr('id');
            switch (parentId) {
                case 'personalidade-juridica':
                    $('#personalidade-juridica').hide();
                    $('#local-atividade').fadeIn(1100);
                    break;
                case 'local-atividade':
                    $('.js-questions').hide();
                    $('#personalidade-juridica').hide();
                    $('.js-lab-item').fadeIn(1100);
                    break;
                case 'select-cidade':
                    $('#select-cidade').hide();
                    $('#personalidade-juridica').fadeIn(1100);
                    break;
            }
        });

        $('.js-next').click(function(){
            let modal      = $('#modalAlert');
            let nomeCidade = $('.js-select-cidade option:selected').text();

            modal.fadeIn(1200);

            let msg = `<?php \MapasCulturais\i::_e("Confirmar inscrição do Auxílio Emergencial da Cultura no município de <strong>_cidade_</strong>.");?>`;
            msg = msg.replace(/_cidade_/g, nomeCidade);
            $('.modal-content').find('.text').html(msg);

            $('.close').on('click', function () {
                modal.fadeOut('slow');
            });

            $('.js-confirmar').click(function(){
                $('.js-questions-tab').hide();
                $('.js-questions').html('<h4>Enviando informações ...</h4>');
                goToNextPage();
            });
        });
        // Exibe/esconde texto explicativo das opções de cadastro em celulares
        $('.js-help').click(function(){
            $('.js-detail').toggle('1000');
        });
    });
</script>
<section class="lab-main-content">
    <div class="box">
        <h1>Cadastro</h1>
        <p>Olá, <?=$niceName?>!</p>
        <p>Por favor, responda às perguntas abaixo para iniciar seu cadastro.</p>

        <div class="js-lab-item lab-item">
            <p class="lab-form-question">Para quem você está solicitando o auxílio? <a class="js-help icon icon-help" href="#" title=""></a></p>

            <div class="lab-form-filter">
                <?php
    
                if (count($registrationsInciso2) < $inciso2Limite && $inciso2_enabled) {
                    ?>
                    <div id="option1" class="js-lab-option lab-option">
                        <h3>Espaços e organizações culturais</h3>
                            <p class="js-detail lab-option-detail">Farão jus ao benefício espaços, organizações da sociedade civil, empresas, cooperativas e instituições com finalidade cultural, como previsto nos Arts. 7º e 8º - Lei 14.017/2020. Prevê subsídio de R$3.000,00 (três mil reais) a R$10.000,00 (dez mil reais), prescrito pela gestão local.</p>
                    </div><!-- End #option1 -->
                    <?php
                }
                foreach ($registrationsInciso2 as $registration){
                    $registrationUrl = $this->controller->createUrl('formulario',[$registration->id]);
                    switch ($registration->status) {
                        //caso seja do Inciso 2 e nao enviada (Rascunho)
                        case $statusCodes[0]:
                            $this->part('aldirblanc/cadastro/application-inciso2-draft',  ['registration' => $registration,'registrationUrl' => $registrationUrl,'niceName' => $niceName]);
                            break;
                        //caso seja do Inciso 2 e tenha sido enviada
                        default:
                        $registrationStatusName = $summaryStatusName[$registration->status];
                        $this->part('aldirblanc/cadastro/application-status',  ['registration' => $registration,'registrationStatusName' => $registrationStatusName]);
                            break;
                    }
                }
                //se em menos inscriçoes que a configuração do pugin permite para o inciso 1 mosra a opçao de cadasrtrar
                if (count($registrationsInciso1) < $inciso1Limite && $inciso1_enabled) {
                    ?>
                    <div id="option3" class="lab-option">
                        <a href="<?= $this->controller->createUrl( 'individual') ?>">
                            <h3><?php i::_e('Trabalhadoras e trabalhadores da Cultura') ?></h3>
                            <p class="js-detail lab-option-detail">Farão jus à renda emergencial os(as) trabalhadores(as) da cultura com atividades interrompidas e que se enquadrem, comprovadamente, ao disposto no Art. 6º - Lei 14.017/2020. Prevê o pagamento de cinco parcelas de R$ 600 (seiscentos reais), podendo ser prorrogado conforme Art 5º - Lei 14.017/2020.</p>
                        </a>
                    </div><!-- End #option3 -->
                <?php
                }
                foreach ($registrationsInciso1 as $registration){
                    $registrationUrl = $this->controller->createUrl('formulario',[$registration->id]);
                    switch ($registration->status) {
                        //caso seja do Inciso 1 e nao enviada (Rascunho)
                        case Registration::STATUS_DRAFT:
                            $this->part('aldirblanc/cadastro/application-inciso1-draft',  ['registration' => $registration,'registrationUrl' => $registrationUrl,'niceName' => $niceName]);
                            break;
                        //caso seja do Inciso 1 e tenha sido enviada
                        default:
                            $registrationStatusName = $summaryStatusName[$registration->status];
                            $this->part('aldirblanc/cadastro/application-status',  ['registration' => $registration,'registrationStatusName' => $registrationStatusName]);
                            break;
                    }
                }
                ?>
            </div>

        </div><!-- End .lab-item -->

        <!-- Begin .js-questions -->
        <div class="js-questions questions inactive">

            <div id="local-atividade" class="js-questions-tab questions-tab inactive">
                <h4 class="questions-tab-title"><?php i::_e('Onde o beneficiário desenvolve suas atividades?') ?></h4>
                <p class="questions-tab-summary"><?php i::_e('Escolha a opção que melhor identifica a situação do local onde o beneficiário do subsídio desenvolve a atividade cultural.') ?></p>
                <div class="options-questions">
                    <label>
                        <input type="radio" class="coletivo" name="coletivo" value="espaco"/><?php i::_e('Espaço físico próprio, alugado, itinerante, público cedido em comodato, emprestado ou de uso compartilhado;') ?>
                    </label>
                    <label>
                        <input type="radio" class="coletivo" name="coletivo" value="coletivo" /><?php i::_e('Espaço público (praça, rua, escola, quadra ou prédio custeado pelo poder público) ou espaço virtual de cultura digital.') ?>
                    </label>
                </div>
                <button class="btn js-back">Voltar</button>
            </div>

            <div id="personalidade-juridica" class="js-questions-tab questions-tab inactive">
                <h4 class="questions-tab-title"><?php i::_e('Qual a personalidade jurídica do beneficiário?') ?></h4>
                <p class="questions-tab-summary"><?php i::_e('Escolha a opção que melhor identifica o beneficiário do subsídio previsto no inciso II do art. 2º da lei federal nº 14.017/2020.') ?></p>
                <div class="options-questions">
                    <label>
                        <input type="radio" class="formalizado" name="formalizado" value="formalizado" /><?php i::_e('Entidade, empresa ou cooperativa do setor cultural com inscrição em CNPJ.') ?>
                    </label>
                    <label>
                        <input type="radio" class="formalizado" name="formalizado" value="nao-formalizado" /><?php i::_e('Espaço artístico e cultural mantido por coletivo ou grupo cultural (sem CNPJ) ou por pessoa física (CPF).') ?>
                    </label>
                </div>
                <button class="btn js-back">Voltar</button>
            </div>

            <?php if(count($cidades) > 1): ?>
                <div id="select-cidade" class="js-questions-tab questions-tab lab-form-answer inactive">
                    <p class="lab-form-question"><?php \MapasCulturais\i::_e("Em qual cidade?");?></p>
                        <?php $this->part('aldirblanc/cadastro/select-cidade', ['cidades' => $cidades]) ?>
                    <button class="btn btn-back js-back"><?php i::_e('Voltar') ?></button>
                    <button class="btn btn-next js-next"><?php i::_e("Avançar");?></button>
                </div>
            <?php endif;?>
        </div>
        <!-- End .js-questions -->

    </div><!-- End .box -->

    <div id="modalAlert" class="modal">
        <!-- Modal content -->
        <div class="modal-content">
            <span class="close">&times;</span>
            <p class="text"></p>
            <button class="btn js-confirmar" ><?php \MapasCulturais\i::_e("Confirmar");?></button>
        </div>
    </div>

</section>
