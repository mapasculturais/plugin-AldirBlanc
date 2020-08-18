<?php 
use MapasCulturais\i;
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
    <!--Primeiro acesso ao plugin (não pediu nenhum auxílio ainda)-->
        <div class="box">
            <h1>Cadastro</h1>
            <p>Olá, <?=$niceName?>!</p>
            <p>Por favor, responda às perguntas abaixo para iniciar seu cadastro.</p>

            <div class="js-lab-item lab-item">
                <p class="lab-form-question">Para quem você está solicitando o auxílio? <a class="js-help icon icon-help" href="#" title=""></a></p>

                <div class="lab-form-filter">
                    <div id="option1" class="js-lab-option lab-option">
                            <h3>Espaço Cultural</h3>
                            <p class="js-detail lab-option-detail">Mussum Ipsum, cacilds vidis litro abertis. Admodum accumsan disputationi eu sit. Vide electram sadipscing et per. Per aumento de cachacis, eu reclamis. Paisis, filhis, espiritis santis. Cevadis im ampola pa arma uma pindureta.</p>
                    </div><!-- End #option1 -->
                    <div id="option2" class="js-lab-option lab-option">
                            <h3>Pequena empresa ou coletivo</h3>
                            <p class="js-detail lab-option-detail">Mussum Ipsum, cacilds vidis litro abertis. Admodum accumsan disputationi eu sit. Vide electram sadipscing et per. Per aumento de cachacis, eu reclamis. Paisis, filhis, espiritis santis. Cevadis im ampola pa arma uma pindureta.</p>
                    </div><!-- End #option2 -->
                    <div id="option3" class="lab-option">
                        <a href="<?= $this->controller->createUrl( 'individual') ?>">
                            <h3><?php i::_e('Trabalhador da Cultura') ?></h3>
                            <p class="js-detail lab-option-detail">Mussum Ipsum, cacilds vidis litro abertis. Admodum accumsan disputationi eu sit. Vide electram sadipscing et per. Per aumento de cachacis, eu reclamis. Paisis, filhis, espiritis santis. Cevadis im ampola pa arma uma pindureta.</p>
                        </a>
                    </div><!-- End #option3 -->
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
    <!-- Cadastro somente para inciso 1 incompleto) -->
        <div class="box">
            <h1>Cadastro</h1>
            <p>Olá, <?=$niceName?>!</p>
            <div class="lab-item">
                <p class="lab-form-question">O que você deseja fazer? <a class="js-help icon icon-help" href="#" title=""></a></p>           
                <div class="lab-form-filter">
                    <div id="option2" class="js-lab-option lab-option">
                            <h3>Cadastrar Espaço Cultural</h3>
                            <p class="js-detail lab-option-detail">Mussum Ipsum, cacilds vidis litro abertis. Admodum accumsan disputationi eu sit. Vide electram sadipscing et per. Per aumento de cachacis, eu reclamis. Paisis, filhis, espiritis santis. Cevadis im ampola pa arma uma pindureta.</p>
                    </div>
                    <div id="option2" class="js-lab-option lab-option">
                            <h3>Cadastrar Pequena empresa ou coletivo</h3>
                            <p class="js-detail lab-option-detail">Mussum Ipsum, cacilds vidis litro abertis. Admodum accumsan disputationi eu sit. Vide electram sadipscing et per. Per aumento de cachacis, eu reclamis. Paisis, filhis, espiritis santis. Cevadis im ampola pa arma uma pindureta.</p>
                    </div>
                    <div id="option1" class="lab-option">
                        <a href="#">
                            <h3>Continuar cadastro iniciado para [nome]</h3>
                            <p class="js-detail lab-option-detail">Mussum Ipsum, cacilds vidis litro abertis. Admodum accumsan disputationi eu sit. Vide electram sadipscing et per. Per aumento de cachacis, eu reclamis. Paisis, filhis, espiritis santis. Cevadis im ampola pa arma uma pindureta.</p>
                        </a>
                    </div>
                </div>
            </div>

        </div><!-- End .box -->
    <!-- Cadastro somente para inciso 1 completo, exibe status -->
        <div class="box">
            <h1>Cadastro</h1>
            <p>Olá, <?=$niceName?>!</p>
            <div class="lab-item">
            <p class="lab-form-question">O que você deseja fazer? <a class="js-help icon icon-help" href="#" title=""></a></p>           
                <div class="lab-form-filter">
                    <div id="option2" class="js-lab-option lab-option">
                            <h3>Cadastrar Espaço Cultural</h3>
                            <p class="js-detail lab-option-detail">Mussum Ipsum, cacilds vidis litro abertis. Admodum accumsan disputationi eu sit. Vide electram sadipscing et per. Per aumento de cachacis, eu reclamis. Paisis, filhis, espiritis santis. Cevadis im ampola pa arma uma pindureta.</p>
                    </div>
                    <div id="option2" class="js-lab-option lab-option">
                            <h3>Cadastrar Pequena empresa ou coletivo</h3>
                            <p class="js-detail lab-option-detail">Mussum Ipsum, cacilds vidis litro abertis. Admodum accumsan disputationi eu sit. Vide electram sadipscing et per. Per aumento de cachacis, eu reclamis. Paisis, filhis, espiritis santis. Cevadis im ampola pa arma uma pindureta.</p>
                    </div>
                    <?php $this->part('aldirblanc/cadastro/application-status') ?>
                </div>
            </div>

        </div><!-- End .box -->
    <!-- Cadastro somente para inciso 2 incompleto -->
        <div class="box">
            <h1>Cadastro</h1>
            <p>Olá, <?=$niceName?>!</p>
            <div class="lab-item">
            <p class="lab-form-question">O que você deseja fazer? <a class="js-help icon icon-help" href="#" title=""></a></p>           
                <div class="lab-form-filter">
                    <div id="option2" class="lab-option">
                        <a href="#">
                            <h3>Continuar cadastro iniciado para [NOME DO ESPAÇO]</h3>
                            <p class="js-detail lab-option-detail">Mussum Ipsum, cacilds vidis litro abertis. Admodum accumsan disputationi eu sit. Vide electram sadipscing et per. Per aumento de cachacis, eu reclamis. Paisis, filhis, espiritis santis. Cevadis im ampola pa arma uma pindureta.</p>
                        </a>
                    </div>
                    <div id="option1" class="lab-option">
                        <a href="<?= $this->controller->createUrl( 'individual') ?>">
                            <h3>Cadastrar Trabalhador da Cultura</h3>
                            <p class="js-detail lab-option-detail">Mussum Ipsum, cacilds vidis litro abertis. Admodum accumsan disputationi eu sit. Vide electram sadipscing et per. Per aumento de cachacis, eu reclamis. Paisis, filhis, espiritis santis. Cevadis im ampola pa arma uma pindureta.</p>
                        </a>
                    </div>
                </div>
            </div>

        </div><!-- End .box -->
    <!-- Cadastro somente para inciso 2 completo, exibe status  -->
        <div class="box">
            <h1>Cadastro</h1>
            <p>Olá, <?=$niceName?>!</p>
            <div class="lab-item">
            <p class="lab-form-question">O que você deseja fazer? <a class="js-help icon icon-help" href="#" title=""></a></p>           
                <div class="lab-form-filter">
                    <?php $this->part('aldirblanc/cadastro/application-status') ?>
                    <div id="option1" class="lab-option">
                        <a href="<?= $this->controller->createUrl( 'individual') ?>">
                            <h3>Cadastrar Trabalhador da Cultura</h3>
                            <p class="js-detail lab-option-detail">Mussum Ipsum, cacilds vidis litro abertis. Admodum accumsan disputationi eu sit. Vide electram sadipscing et per. Per aumento de cachacis, eu reclamis. Paisis, filhis, espiritis santis. Cevadis im ampola pa arma uma pindureta.</p>
                        </a>
                    </div>
                </div>
            </div>
        </div><!-- End .box -->
    <!-- Cadastro para inciso 2 e inciso 1 completos, exibe status -->
        <div class="box">
            <h1>Cadastro</h1>
            <p>Olá, <?=$niceName?>!</p>
            <div class="lab-item">
                <p class="lab-form-question">O que você deseja fazer? <a class="js-help icon icon-help" href="#" title=""></a></p>           
                <div class="lab-form-filter">
                    <?php $this->part('aldirblanc/cadastro/application-status') ?>
                    <?php $this->part('aldirblanc/cadastro/application-status') ?>
                </div>
            </div>

        </div><!-- End .box -->
</section>