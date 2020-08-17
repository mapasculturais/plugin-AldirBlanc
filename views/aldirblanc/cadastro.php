<?php 
use MapasCulturais\i;
?>
<script>
    $(document).ready(function(){

        // Esse trecho de codigo "esconde" a sessão "Primeiro acesso ao plugin" ao escolher algumas das opções
         $('.js-lab-option').addClass('inactive');
         $('.js-lab-item').hide();
         $('.js-lab-item:first').show();
                
         $('.js-lab-option').click(function(){
             var t = $(this).attr('id');
             if($(this).hasClass('inactive')){
                 $('.js-lab-option').addClass('inactive');           
                 $(this).removeClass('inactive');
                 $('.js-lab-item').hide();
                 $('#'+ t + 'C').fadeIn('slow');
             }
         });
         $('.js-lab-option').change(function(){
             var t = $(this).attr('id');
             if($(this).hasClass('inactive')){
                 $('.js-lab-option').addClass('inactive');           
                 $(this).removeClass('inactive');
                 $('.js-lab-item').hide();
                 $('#'+ t + 'C').fadeIn('slow');
             }
         });
         $('.back').click(function(){
             $('.js-lab-option').addClass('inactive');
             $('.js-lab-item').hide();
             $('.js-lab-item:first').show();
         });
        $('.icon-help').click(function(){
            $('.lab-form-detail').toggle('1000');
        });
    });
</script>
<section class="lab-main-content">
    <!--Primeiro acesso ao plugin-->
        <div class="box">
            <h1>Cadastro</h1>
            <p>Olá, [nome]!</p>
            <p>Por favor, responda às perguntas abaixo para iniciar seu cadastro.</p>

            <div class="js-lab-item lab-item">
                <p class="lab-form-question">Para quem você está solicitando o auxílio? <a class="icon icon-help" href="#" title=""></a></p>

                <div class="lab-form-filter">
                    <div id="option1" class="js-lab-option lab-form-option">
                            <h3>Espaço Cultural</h3>
                            <p class="lab-form-detail">Mussum Ipsum, cacilds vidis litro abertis. Admodum accumsan disputationi eu sit. Vide electram sadipscing et per. Per aumento de cachacis, eu reclamis. Paisis, filhis, espiritis santis. Cevadis im ampola pa arma uma pindureta.</p>
                        
                    </div><!-- End #option1 -->
                    <div id="option2" class="js-lab-option lab-form-option">
                            <h3>Pequena empresa ou coletivo</h3>
                            <p class="lab-form-detail">Mussum Ipsum, cacilds vidis litro abertis. Admodum accumsan disputationi eu sit. Vide electram sadipscing et per. Per aumento de cachacis, eu reclamis. Paisis, filhis, espiritis santis. Cevadis im ampola pa arma uma pindureta.</p>
                    </div><!-- End #option2 -->
                    <div id="option3" class="lab-form-option">
                            <h3><?php i::_e('Trabalhador da Cultura') ?></h3>
                            <p class="lab-form-detail">Mussum Ipsum, cacilds vidis litro abertis. Admodum accumsan disputationi eu sit. Vide electram sadipscing et per. Per aumento de cachacis, eu reclamis. Paisis, filhis, espiritis santis. Cevadis im ampola pa arma uma pindureta.</p>
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
                    <a class="back" href="#"><span class="icon icon-go-back"></span><span class="screen-reader-text">Voltar</span></a>
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
                    <a class="back" href="#"><span class="icon icon-go-back"></span><span class="screen-reader-text">Voltar</span></a>
                </div><!-- End .lab-form-answer -->
            </div><!-- End .lab-item -->
        </div><!-- End .box -->
    </details>
    <details>
        <summary>Cadastro trabalhador incompleto</summary>
        <div class="box">
            <h1>Cadastro</h1>
            <p>Olá, [nome]!</p>
            <div class="lab-item">
                <p class="lab-form-question">O que você deseja fazer? <a class="icon icon-help" href="#" title=""></a></p>           
                <div class="lab-form-filter">
                    <div id="option2" class="js-lab-option lab-form-option">
                            <h3>Cadastrar Espaço Cultural</h3>
                            <p class="lab-form-detail">Mussum Ipsum, cacilds vidis litro abertis. Admodum accumsan disputationi eu sit. Vide electram sadipscing et per. Per aumento de cachacis, eu reclamis. Paisis, filhis, espiritis santis. Cevadis im ampola pa arma uma pindureta.</p>
                    </div>
                    <div id="option2" class="js-lab-option lab-form-option">
                            <h3>Cadastrar Pequena empresa ou coletivo</h3>
                            <p class="lab-form-detail">Mussum Ipsum, cacilds vidis litro abertis. Admodum accumsan disputationi eu sit. Vide electram sadipscing et per. Per aumento de cachacis, eu reclamis. Paisis, filhis, espiritis santis. Cevadis im ampola pa arma uma pindureta.</p>
                    </div>
                    <div id="option1" class="lab-form-option">
                            <h3>Continuar cadastro iniciado para [nome]</h3>
                            <p class="lab-form-detail">Mussum Ipsum, cacilds vidis litro abertis. Admodum accumsan disputationi eu sit. Vide electram sadipscing et per. Per aumento de cachacis, eu reclamis. Paisis, filhis, espiritis santis. Cevadis im ampola pa arma uma pindureta.</p>
                    </div>
                </div>
            </div>

        </div><!-- End .box -->
    </details>
    <details>
        <summary>Cadastro trabalhador completo</summary>
        <div class="box">
            <h1>Cadastro</h1>
            <p>Olá, [nome]!</p>
            <div class="lab-item">
            <p class="lab-form-question">O que você deseja fazer? <a class="icon icon-help" href="#" title=""></a></p>           
                <div class="lab-form-filter">
                    <div id="option2" class="js-lab-option lab-form-option">
                            <h3>Cadastrar Espaço Cultural</h3>
                            <p class="lab-form-detail">Mussum Ipsum, cacilds vidis litro abertis. Admodum accumsan disputationi eu sit. Vide electram sadipscing et per. Per aumento de cachacis, eu reclamis. Paisis, filhis, espiritis santis. Cevadis im ampola pa arma uma pindureta.</p>
                    </div>
                    <div id="option2" class="js-lab-option lab-form-option">
                            <h3>Cadastrar Pequena empresa ou coletivo</h3>
                            <p class="lab-form-detail">Mussum Ipsum, cacilds vidis litro abertis. Admodum accumsan disputationi eu sit. Vide electram sadipscing et per. Per aumento de cachacis, eu reclamis. Paisis, filhis, espiritis santis. Cevadis im ampola pa arma uma pindureta.</p>
                    </div>
                    <div class="lab-form-option lab-form-waiting">
                        <?php $this->part('aldirblanc/cadastro/application-status') ?>
                    </div>
                </div>
            </div>

        </div><!-- End .box -->
    </details>
    <details>
        <summary>Cadastro Espaço/Pequena empresa ou coletivo incompleto</summary>
        <div class="box">
            <h1>Cadastro</h1>
            <p>Olá, [nome]!</p>
            <div class="lab-item">
            <p class="lab-form-question">O que você deseja fazer? <a class="icon icon-help" href="#" title=""></a></p>           
                <div class="lab-form-filter">
                    <div id="option2" class="lab-form-option">
                            <h3>Continuar cadastro iniciado para [NOME DO ESPAÇO]</h3>
                            <p class="lab-form-detail">Mussum Ipsum, cacilds vidis litro abertis. Admodum accumsan disputationi eu sit. Vide electram sadipscing et per. Per aumento de cachacis, eu reclamis. Paisis, filhis, espiritis santis. Cevadis im ampola pa arma uma pindureta.</p>
                    </div>
                    <div id="option1" class="lab-form-option">
                            <h3>Cadastrar Trabalhador da Cultura</h3>
                            <p class="lab-form-detail">Mussum Ipsum, cacilds vidis litro abertis. Admodum accumsan disputationi eu sit. Vide electram sadipscing et per. Per aumento de cachacis, eu reclamis. Paisis, filhis, espiritis santis. Cevadis im ampola pa arma uma pindureta.</p>
                    </div>
                </div>
            </div>

        </div><!-- End .box -->
    </details>
    <details open>
        <summary>Cadastro Espaço/Pequena empresa ou coletivo completo</summary>
        <div class="box">
            <h1>Cadastro</h1>
            <p>Olá, [nome]!</p>
            <div class="lab-item">
            <p class="lab-form-question">O que você deseja fazer? <a class="icon icon-help" href="#" title=""></a></p>           
                <div class="lab-form-filter">
                    <div class="lab-form-option lab-form-waiting">
                        <?php $this->part('aldirblanc/cadastro/application-status') ?>
                    </div>
                    <div id="option1" class="lab-form-option">
                            <h3>Cadastrar Trabalhador da Cultura</h3>
                            <p class="lab-form-detail">Mussum Ipsum, cacilds vidis litro abertis. Admodum accumsan disputationi eu sit. Vide electram sadipscing et per. Per aumento de cachacis, eu reclamis. Paisis, filhis, espiritis santis. Cevadis im ampola pa arma uma pindureta.</p>
                    </div>
                </div>
            </div>

        </div><!-- End .box -->
    </details>
    <details open>
        <summary>Cadastro Trabalhador / Espaço/Pequena empresa ou coletivo completos</summary>
        <div class="box">
            <h1>Cadastro</h1>
            <p>Olá, [nome]!</p>
            <div class="lab-item">
                <p class="lab-form-question">O que você deseja fazer? <a class="icon icon-help" href="#" title=""></a></p>           
                <div class="lab-form-filter">
                    <div id="option2" class="lab-form-approved lab-form-option">
                            <h3>Status do cadastro para [NOME DO TRABALHADOR]</h3>
                            <h2>Aprovado</h2>
                            <p class="lab-form-detail">
                                    <span class="label">Número:</span> on-321123123123</br>
                                
                                    <span class="label">Data do envio:</span> 00/00/00</br>
                                
                                    <span class="label">Responsável:</span> Nome Sobrenome</br>
                                
                                    <span class="label">CPF:</span> 000.000.000-00
                            </p>
                    </div>
                    <div id="option2" class="lab-form-disapproved lab-form-option">
                            <h3>Status do cadastro para [NOME DO ESPAÇO]</h3>
                            <h2>Negado</h2>
                            <p class="lab-form-detail">
                                    <span class="label">Número:</span> on-321123123123</br>
                                
                                    <span class="label">Data do envio:</span> 00/00/00</br>
                                
                                    <span class="label">Responsável:</span> Nome Sobrenome</br>
                                
                                    <span class="label">CPF:</span> 000.000.000-00
                            </p>
                    </div>
                </div>
            </div>

        </div><!-- End .box -->
    </details>
</section>