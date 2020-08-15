<?php 
use MapasCulturais\i;
?>
<script>
    $(document).ready(function(){

        // Esse trecho de codigo "esconde" a sessão "Primeiro acesso ao plugin" ao escolher algumas das opções
         $('.lab-form-option').addClass('inactive');
         $('.lab-form-item').hide();
         $('.lab-form-item:first').show();
                
         $('.lab-form-option').click(function(){
             var t = $(this).attr('id');
             if($(this).hasClass('inactive')){
                 $('.lab-form-option').addClass('inactive');           
                 $(this).removeClass('inactive');
                 $('.lab-form-item').hide();
                 $('#'+ t + 'C').fadeIn('slow');
             }
         });
         $('.lab-form-option').change(function(){
             var t = $(this).attr('id');
             if($(this).hasClass('inactive')){
                 $('.lab-form-option').addClass('inactive');           
                 $(this).removeClass('inactive');
                 $('.lab-form-item').hide();
                 $('#'+ t + 'C').fadeIn('slow');
             }
         });
         $('.back').click(function(){
             $('.lab-form-option').addClass('inactive');
             $('.lab-form-item').hide();
             $('.lab-form-item:first').show();
         });
        $('.icon-help').click(function(){
            $('.lab-form-detail').toggle('1000');
        });
    });
</script>
<section class="lab-form-intro">
    <details open>
        <summary>Primeiro acesso ao plugin</summary>
        <div class="box">
                <h1>Cadastro</h1>
                <p>Olá, <?php echo $app->user->profile->name ?>!</p>
                <p>Por favor, responda às perguntas abaixo para iniciar seu cadastro.</p>

            <div class="lab-form-item">
                <p class="lab-form-question">Para quem você está solicitando o auxílio? <a class="icon icon-help" href="#" title=""></a></p>           
                <ul class="lab-form-filter">
                    <li>
                        <a id="option1" class="lab-form-option" href="#">
                            <h3>Espaço Cultural</h3>
                            <p class="lab-form-detail">Mussum Ipsum, cacilds vidis litro abertis. Admodum accumsan disputationi eu sit. Vide electram sadipscing et per. Per aumento de cachacis, eu reclamis. Paisis, filhis, espiritis santis. Cevadis im ampola pa arma uma pindureta.</p>
                        </a>
                    </li>
                    <li>
                        <a id="option2" class="lab-form-option" href="#">
                            <h3>Pequena empresa ou coletivo</h3>
                            <p class="lab-form-detail">Mussum Ipsum, cacilds vidis litro abertis. Admodum accumsan disputationi eu sit. Vide electram sadipscing et per. Per aumento de cachacis, eu reclamis. Paisis, filhis, espiritis santis. Cevadis im ampola pa arma uma pindureta.</p>
                        </a>
                    </li>
                    <li>
                        <a id="option3" class="lab-form-option" href="<?= $this->controller->createUrl( 'individual') ?>">
                            <h3><?php i::_e('Trabalhador da Cultura') ?></h3>
                            <p class="lab-form-detail">Mussum Ipsum, cacilds vidis litro abertis. Admodum accumsan disputationi eu sit. Vide electram sadipscing et per. Per aumento de cachacis, eu reclamis. Paisis, filhis, espiritis santis. Cevadis im ampola pa arma uma pindureta.</p>
                        </a>
                    </li>
                </ul>
            </div><!-- End .lab-form-item -->
            
            <div id="option1C" class="lab-form-item">
                <?php $this->part('aldirblanc/cadastro/select-cidade') ?>
            </div><!-- End .lab-form-item -->
            <div id="option4C" class="lab-form-item">
                <p class="lab-form-question">Seu espaço é formalizado?</p>
                <div class="lab-form-answer">
                    <span>
                        <a class="btn btn-boolean btn-large" href="<?= $this->controller->createUrl( 'coletivo', ['espaco-formalizado'] ) ?>"><?php i::_e('Sim') ?></a>
                        <a class="btn btn-boolean btn-large" href="<?= $this->controller->createUrl( 'coletivo', ['espaco-nao-formalizado'] ) ?>"><?php i::_e('Não') ?></a>
                    </span>
                    <a class="back" href="#"><span class="icon icon-go-back"></span><span class="screen-reader-text">Voltar</span></a>
                </div><!-- End .lab-form-answer -->
            </div><!-- End .lab-form-item -->

            <div id="option2C" class="lab-form-item">
                <?php $this->part('aldirblanc/cadastro/select-cidade') ?>
            </div><!-- End .lab-form-item -->
            <div id="option5C" class="lab-form-item">
                <p class="lab-form-question">Sua pequena empresa ou coletivo é formalizado?</p>
                <div class="lab-form-answer">
                    <span>
                        <a class="btn btn-boolean btn-large" href="<?= $this->controller->createUrl( 'coletivo', ['coletivo-formalizado'] ) ?>"><?php i::_e('Sim') ?></a>
                        <a class="btn btn-boolean btn-large" href="<?= $this->controller->createUrl( 'coletivo', ['coletivo-nao-formalizado'] ) ?>"><?php i::_e('Não') ?></a>
                    </span>
                    <a class="back" href="#"><span class="icon icon-go-back"></span><span class="screen-reader-text">Voltar</span></a>
                </div><!-- End .lab-form-answer -->
            </div><!-- End .lab-form-item -->
        </div><!-- End .box -->
    </details>
    <details>
        <summary>Cadastro trabalhador incompleto</summary>
        <div class="box">
            <h1>Cadastro</h1>
            <p>Olá, <?php echo $app->user->profile->name ?>!</p>
            <div class="">
                <p class="lab-form-question">O que você deseja fazer?<a class="icon icon-help" href="#" title=""></a></p>           
                <ul class="lab-form-filter">
                    <li>
                        <a id="option2" class="lab-form-option" href="#">
                            <h3>Cadastrar Espaço Cultural</h3>
                            <p class="lab-form-detail">Mussum Ipsum, cacilds vidis litro abertis. Admodum accumsan disputationi eu sit. Vide electram sadipscing et per. Per aumento de cachacis, eu reclamis. Paisis, filhis, espiritis santis. Cevadis im ampola pa arma uma pindureta.</p>
                        </a>
                    </li>
                    <li>
                        <a id="option2" class="lab-form-option" href="#">
                            <h3>Cadastrar Pequena empresa ou coletivo</h3>
                            <p class="lab-form-detail">Mussum Ipsum, cacilds vidis litro abertis. Admodum accumsan disputationi eu sit. Vide electram sadipscing et per. Per aumento de cachacis, eu reclamis. Paisis, filhis, espiritis santis. Cevadis im ampola pa arma uma pindureta.</p>
                        </a>
                    </li>
                    <li>
                        <a id="option1" class="lab-form-option" href="#">
                            <h3>Continuar cadastro iniciado para <?php echo $app->user->profile->name ?></h3>
                            <p class="lab-form-detail">Mussum Ipsum, cacilds vidis litro abertis. Admodum accumsan disputationi eu sit. Vide electram sadipscing et per. Per aumento de cachacis, eu reclamis. Paisis, filhis, espiritis santis. Cevadis im ampola pa arma uma pindureta.</p>
                        </a>
                    </li>
                </ul>
            </div>

        </div><!-- End .box -->
    </details>
    <details>
        <summary>Cadastro trabalhador completo</summary>
        <div class="box">
            <h1>Cadastro</h1>
            <p>Olá, <?php echo $app->user->profile->name ?>!</p>
            <div class="">
            <p class="lab-form-question">O que você deseja fazer?<a class="icon icon-help" href="#" title=""></a></p>           
                <ul class="lab-form-filter">
                    <li>
                        <a id="option2" class="lab-form-option" href="#">
                            <h3>Cadastrar Espaço Cultural</h3>
                            <p class="lab-form-detail">Mussum Ipsum, cacilds vidis litro abertis. Admodum accumsan disputationi eu sit. Vide electram sadipscing et per. Per aumento de cachacis, eu reclamis. Paisis, filhis, espiritis santis. Cevadis im ampola pa arma uma pindureta.</p>
                        </a>
                    </li>
                    <li>
                        <a id="option2" class="lab-form-option" href="#">
                            <h3>Cadastrar Pequena empresa ou coletivo</h3>
                            <p class="lab-form-detail">Mussum Ipsum, cacilds vidis litro abertis. Admodum accumsan disputationi eu sit. Vide electram sadipscing et per. Per aumento de cachacis, eu reclamis. Paisis, filhis, espiritis santis. Cevadis im ampola pa arma uma pindureta.</p>
                        </a>
                    </li>
                    <?php $this->part('aldirblanc/cadastro/application-status') ?>
                </ul>
            </div>

        </div><!-- End .box -->
    </details>
    <details>
        <summary>Cadastro Espaço/Pequena empresa ou coletivo incompleto</summary>
        <div class="box">
            <h1>Cadastro</h1>
            <p>Olá, <?php echo $app->user->profile->name ?>!</p>
            <div class="">
            <p class="lab-form-question">O que você deseja fazer?<a class="icon icon-help" href="#" title=""></a></p>           
                <ul class="lab-form-filter">
                    <li>
                        <a id="option2" class="lab-form-option" href="#">
                            <h3>Continuar cadastro iniciado para [NOME DO ESPAÇO]</h3>
                            <p class="lab-form-detail">Mussum Ipsum, cacilds vidis litro abertis. Admodum accumsan disputationi eu sit. Vide electram sadipscing et per. Per aumento de cachacis, eu reclamis. Paisis, filhis, espiritis santis. Cevadis im ampola pa arma uma pindureta.</p>
                        </a>
                    </li>
                    <li>
                        <a id="option1" class="lab-form-option" href="<?= $this->controller->createUrl( 'individual') ?>">
                            <h3>Cadastrar Trabalhador da Cultura</h3>
                            <p class="lab-form-detail">Mussum Ipsum, cacilds vidis litro abertis. Admodum accumsan disputationi eu sit. Vide electram sadipscing et per. Per aumento de cachacis, eu reclamis. Paisis, filhis, espiritis santis. Cevadis im ampola pa arma uma pindureta.</p>
                        </a>
                    </li>
                </ul>
            </div>

        </div><!-- End .box -->
    </details>
    <details open>
        <summary>Cadastro Espaço/Pequena empresa ou coletivo completo</summary>
        <div class="box">
            <h1>Cadastro</h1>
            <p>Olá, <?php echo $app->user->profile->name ?>!</p>
            <div class="">
            <p class="lab-form-question">O que você deseja fazer?<a class="icon icon-help" href="#" title=""></a></p>           
                <ul class="lab-form-filter">
                    <?php $this->part('aldirblanc/cadastro/application-status') ?>
                    <li>
                        <a id="option1" class="lab-form-option" href="<?= $this->controller->createUrl( 'individual') ?>">
                            <h3>Cadastrar Trabalhador da Cultura</h3>
                            <p class="lab-form-detail">Mussum Ipsum, cacilds vidis litro abertis. Admodum accumsan disputationi eu sit. Vide electram sadipscing et per. Per aumento de cachacis, eu reclamis. Paisis, filhis, espiritis santis. Cevadis im ampola pa arma uma pindureta.</p>
                        </a>
                    </li>
                </ul>
            </div>

        </div><!-- End .box -->
    </details>
    <details open>
        <summary>Cadastro Trabalhador / Espaço/Pequena empresa ou coletivo completos</summary>
        <div class="box">
            <h1>Cadastro</h1>
            <p>Olá, <?php echo $app->user->profile->name ?>!</p>
            <div class="">
            <p class="lab-form-question">O que você deseja fazer?<a class="icon icon-help" href="#" title=""></a></p>           
                <ul class="lab-form-filter">
                    <li class="lab-form-approved">
                        <a id="option2" class="lab-form-option" href="#">
                            <h3>Status do cadastro para [NOME DO TRABALHADOR]</h3>
                            <h2>Aprovado</h2>
                            <p class="lab-form-detail">
                                    <span class="label">Número:</span> on-321123123123</br>
                                
                                    <span class="label">Data do envio:</span> 00/00/00</br>
                                
                                    <span class="label">Responsável:</span> Nome Sobrenome</br>
                                
                                    <span class="label">CPF:</span> 000.000.000-00
                            </p>
                        </a>
                    </li>
                    <li class="lab-form-disapproved">
                        <a id="option2" class="lab-form-option" href="#">
                            <h3>Status do cadastro para [NOME DO ESPAÇO]</h3>
                            <h2>Negado</h2>
                            <p class="lab-form-detail">
                                    <span class="label">Número:</span> on-321123123123</br>
                                
                                    <span class="label">Data do envio:</span> 00/00/00</br>
                                
                                    <span class="label">Responsável:</span> Nome Sobrenome</br>
                                
                                    <span class="label">CPF:</span> 000.000.000-00
                            </p>
                        </a>
                    </li>
                </ul>
            </div>

        </div><!-- End .box -->
    </details>
</section>