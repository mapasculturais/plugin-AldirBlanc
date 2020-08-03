<?php 
use MapasCulturais\i; 

?>
<script>
    $(document).ready(function(){
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
                <p class="lab-form-question">Para quem você está solicitando o auxílio?</p>           
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
        <summary>Cadastro incompleto</summary>
        <div class="box">
            <h1>Cadastro</h1>
            <p>Olá, <?php echo $app->user->profile->name ?>!</p>
            <div class="">
                <p class="lab-form-question">O que você deseja fazer?</p>           
                <ul class="lab-form-filter">
                    <li>
                        <a id="option1" class="lab-form-option" href="#">
                            <h3>Continuar o cadastro para [Nome do trabalhador ou espaço]</h3>
                        </a>
                    </li>
                    <li>
                        <a id="option2" class="lab-form-option" href="#">
                            <h3>Fazer novo cadastro para trabalhador ou espaço</h3>
                        </a>
                    </li>
                </ul>
            </div>

        </div><!-- End .box -->
    </details>
    <details>
        <summary>Cadastro completo</summary>
        <div class="box">
            <h1>Cadastro</h1>
            <p>Olá, <?php echo $app->user->profile->name ?>!</p>
            <div class="">
                <p class="lab-form-question">O que você deseja fazer?</p>           
                <ul class="lab-form-filter">
                    <li>
                        <a id="option1" class="lab-form-option" href="#">
                            <h3>Conferir o status do cadastro para [Nome do trabalhador ou espaço]</h3>
                        </a>
                    </li>
                    <li>
                        <a id="option2" class="lab-form-option" href="#">
                            <h3>Fazer novo cadastro</h3>
                        </a>
                    </li>
                </ul>
            </div>

        </div><!-- End .box -->
    </details>
</section>