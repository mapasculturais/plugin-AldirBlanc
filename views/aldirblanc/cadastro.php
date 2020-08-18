<?php 
use MapasCulturais\i;
use MapasCulturais\Entities\Registration;
$inciso1Limite = $this->controller->config['inciso1_limite'];
$inciso2Limite = $this->controller->config['inciso2_limite'];
$inciso2_enabled = $this->controller->config['inciso2_enabled'];
$inciso1_enabled = $this->controller->config['inciso1_enabled'];


?>
<script>
    $(document).ready(function(){

        // Esconde os filtros de cidade e categorias para espaços ou coletivos no início
        $('.js-lab-option').addClass('inactive');
        $('.js-lab-item').hide();
        // Exibe as opções de cadastro no início
        $('.js-lab-item:first').show();
        // Exibe os filtros de cidade    
        $('.js-lab-option').click(function(){
            var t = $(this).attr('id');
            if($(this).hasClass('inactive')){
                $('.js-lab-option').addClass('inactive');           
                $(this).removeClass('inactive');
                $('.js-lab-item').hide();
                $('#'+ t + 'C').fadeIn('slow');
            }
        });
        // Exibe os filtros de categorias para espaços ou coletivos
        $('.js-lab-option').change(function(){
            var t = $(this).attr('id');
            if($(this).hasClass('inactive')){
                $('.js-lab-option').addClass('inactive');           
                $(this).removeClass('inactive');
                $('.js-lab-item').hide();
                $('#'+ t + 'C').fadeIn('slow');
            }
        });
        // Botão voltar a pergunta anterior
        $('.js-back').click(function(){
            $('.js-lab-option').addClass('inactive');
            $('.js-lab-item').hide();
            $('.js-lab-item:first').show();
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
                        <div id="option2" class="js-lab-option lab-option">
                                <h3>Pequena empresa ou coletivo</h3>
                                <p class="js-detail lab-option-detail">Mussum Ipsum, cacilds vidis litro abertis. Admodum accumsan disputationi eu sit. Vide electram sadipscing et per. Per aumento de cachacis, eu reclamis. Paisis, filhis, espiritis santis. Cevadis im ampola pa arma uma pindureta.</p>
                        </div><!-- End #option2 -->
                        <?php
                    }
                    foreach ($registrationsInciso2 as $registration){
                        $registrationUrl = $this->controller->createUrl('formulario',[$registration->id]);
                        switch ($registration->status) {
                            //caso seja do Inciso 2 e nao enviada (Rascunho)
                            case $statusCodes[0]:
                                $this->part('aldirblanc/cadastro/application-inciso2-draft',  ['registrationUrl' => $registrationUrl,'niceName' => $niceName]);
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
                                $this->part('aldirblanc/cadastro/application-inciso1-draft',  ['registrationUrl' => $registrationUrl,'niceName' => $niceName]);
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
            
            <div id="option1C" class="js-lab-item lab-item">
                <?php $this->part('aldirblanc/cadastro/select-cidade') ?>
            </div><!-- End .lab-item -->

            <div id="option4C" class="js-lab-item lab-item">
                <p class="lab-form-question">Seu espaço é formalizado?</p>
                <div class="lab-form-answer">
                    <span>
                        <a class="btn btn-boolean btn-large" href="<?= $this->controller->createUrl( 'coletivo', ['espaco-formalizado'] ) ?>"><?php i::_e('Sim') ?></a>
                        <a class="btn btn-boolean btn-large" href="<?= $this->controller->createUrl( 'coletivo', ['espaco-nao-formalizado'] ) ?>"><?php i::_e('Não') ?></a>
                    </span>
                    <a class="js-back lab-back" href="#"><span class="icon icon-go-back"></span><span class="screen-reader-text">Voltar</span></a>
                </div><!-- End .lab-form-answer -->
            </div><!-- End .lab-item -->

            <div id="option2C" class="js-lab-item lab-item">
                <?php $this->part('aldirblanc/cadastro/select-cidade') ?>
            </div><!-- End .lab-item -->
            <div id="option5C" class="js-lab-item lab-item">
                <p class="lab-form-question">Sua pequena empresa ou coletivo é formalizado?</p>
                <div class="lab-form-answer">
                    <span>
                        <a class="btn btn-boolean btn-large" href="<?= $this->controller->createUrl( 'coletivo', ['coletivo-formalizado'] ) ?>"><?php i::_e('Sim') ?></a>
                        <a class="btn btn-boolean btn-large" href="<?= $this->controller->createUrl( 'coletivo', ['coletivo-nao-formalizado'] ) ?>"><?php i::_e('Não') ?></a>
                    </span>
                    <a class="js-back lab-back" href="#"><span class="icon icon-go-back"></span><span class="screen-reader-text">Voltar</span></a>
                </div><!-- End .lab-form-answer -->
            </div><!-- End .lab-item -->
        </div><!-- End .box -->
    
</section>