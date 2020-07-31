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
                $('lab-form-option').addClass('inactive');           
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
    <div class="box">
        <h1>Cadastro</h1>
        <p>Olá, <?php echo $app->user->profile->name ?>!</p>
        <p>Por favor, responda às perguntas abaixo para iniciar seu cadastro.</p>
        <div class="lab-form-item">
            <p class="lab-form-question">Você está solicitando o auxílio para:</p>           
            <ul class="lab-form-filter">
                <li><a id="option1" class="lab-form-option" href="#">Espaço Cultural</a></li>
                <li><a id="option2" class="lab-form-option" href="#">Pequena empresa ou coletivo</a></li>
                <li><a id="option3" class="lab-form-option" href="<?= $this->controller->createUrl( 'individual') ?>"><?php i::_e('Trabalhador da Cultura') ?></a></li>
            </ul>
        </div>
        <div id="option1C" class="lab-form-item">
            <p class="lab-form-question">Seu espaço é formalizado?</p>
            <div class="lab-form-boolean">
                <span>
                    <a class="btn btn-boolean btn-large" href="<?= $this->controller->createUrl( 'coletivo' ) ?>"><?php i::_e('Sim') ?></a>
                    <a class="btn btn-boolean btn-large" href="<?= $this->controller->createUrl( 'coletivo' ) ?>"><?php i::_e('Não') ?></a>
                </span>
                <a class="back" href="#"><span class="icon icon-go-back"></span><span class="screen-reader-text">Voltar</span></a>
            </div>
        </div>
        <div id="option2C" class="lab-form-item">
            <p class="lab-form-question">Sua pequena empresa ou coletivo é formalizado?</p>
            <div class="lab-form-boolean">
                <span>
                    <a class="btn btn-boolean btn-large" href="<?= $this->controller->createUrl( 'coletivo' ) ?>"><?php i::_e('Sim') ?></a>
                    <a class="btn btn-boolean btn-large" href="<?= $this->controller->createUrl( 'coletivo' ) ?>"><?php i::_e('Não') ?></a>
                </span>
                <a class="back" href="#"><span class="icon icon-go-back"></span><span class="screen-reader-text">Voltar</span></a>
            </div>
        </div>
    </div>
</section>