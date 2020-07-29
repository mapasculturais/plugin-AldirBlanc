<?php 
use MapasCulturais\i; 

?>
<section class="lab-form-intro">
    <div class="box">
        <h1>Cadastro</h1>
        <p>Olá, <?php echo $app->user->profile->name ?>!</p>
        <p>Por favor, responda às perguntas abaixo para iniciar seu cadastro.</p>
        <div class="lab-form-item">
            <p class="lab-form-question">Você está solicitando o auxílio para:</p>           
            <ul class="lab-form-filter">
                <li><a href="#home-questions">Espaço Cultural</a></li>
                <li><a href="#home-questions">Pequena empresa ou coletivo</a></li>
                <li><a href="<?= $this->controller->createUrl( 'individual' ) ?>"><?php i::_e('Trabalhador da Cultura') ?></a></li>
            </ul>
        </div>
        <div class="lab-form-item hidden">
            <p class="lab-form-question">Seu espaço é formalizado?</p>
            <div class="lab-form-boolean">
                <span>
                    <a class="btn btn-boolean btn-large" href="<?= $this->controller->createUrl( 'coletivo' ) ?>"><?php i::_e('Sim') ?></a>
                    <a class="btn btn-boolean btn-large" href="<?= $this->controller->createUrl( 'coletivo' ) ?>"><?php i::_e('Não') ?></a>
                </span>
                <a href="#"><span class="icon icon-go-back"></span><span class="screen-reader-text">Voltar</span></a>
            </div>
        </div>
        <div class="lab-form-item hidden">
            <p class="lab-form-question">Sua pequena empresa ou coletivo é formalizado?</p>
            <div class="lab-form-boolean">
                <span>
                    <a class="btn btn-boolean btn-large" href="<?= $this->controller->createUrl( 'coletivo' ) ?>"><?php i::_e('Sim') ?></a>
                    <a class="btn btn-boolean btn-large" href="<?= $this->controller->createUrl( 'coletivo' ) ?>"><?php i::_e('Não') ?></a>
                </span>
                <a href="#"><span class="icon icon-go-back"></span><span class="screen-reader-text">Voltar</span></a>
            </div>
        </div>
    </div>
</section>