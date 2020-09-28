<?php

use Doctrine\Common\Util\Debug;
use MapasCulturais\i;
use MapasCulturais\Entities\Registration;

$inciso1Limite = $this->controller->config['inciso1_limite'];
$inciso2Limite = $this->controller->config['inciso2_limite'];
$inciso2_enabled = $this->controller->config['inciso2_enabled'];
$inciso1_enabled = $this->controller->config['inciso1_enabled'];

$this->jsObject['opportunityId'] = null;
$this->jsObject['opportunitiesInciso2'] = $opportunitiesInciso2;
$this->jsObject['serverDate'] = new DateTime();

if (count($cidades) === 0) {
    $inciso2_enabled = false;
} else if (count($cidades) === 1) {
    /**
     * Pega oportunidade/cidade default para cadastro do inciso II.
     */
    $this->jsObject['opportunityId'] = reset($cidades);
}

?>
<section class="lab-main-content cadastro <?= $app->user->is('mediador') ? "mediador" : '' ?>">
    <header>
        <div class="intro-message">
            <div class="name"> Olá, <?= $niceName ?>! </div>
        </div>
    </header>

    <!-- Begin .js-questions -->
    <div class="opportunity-listing questions">
        <div id="select-opportunity" class="js-questions-tab questions--tab lab-form-answer">
            <i class="fas fa-file-alt"></i>
            <h4 class="questions--title"><?php i::_e('Oportunidades disponíveis') ?></h4>
            <p class="questions--summary"><?php i::_e('Selecione abaixo a oportunidade desejada e, caso necessário, filtre as opções por tipo de oportunidade') ?></p>
            <form>
                <select id="opportunity-type" name="opportunity-type" class="opportunity-type">
                    <option></option>
                    <?php foreach ($cidades as $nome => $oportunidade) : ?>
                        <option value="<?= $oportunidade ?>"><?= $nome ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <div class="opportunities">
            <div class="opportunities--cards">
                <div class="card">
                    <a href="#" class="card--title">
                        Nome da oportunidade
                    </a>

                    <div class="card--content">
                        <p>
                            Premiar roteiros criativos de audiovisual, teatro, dança, circo, artes visuais e literatura já desenvolvidos ou em desenvolvimento na cidade de São Paulo.
                        </p>
                        <p>
                            Quem pode participar deste módulo? Poderão se inscrever no MÓDULO VI coletivos organizados e pessoas fśicas que possuem histórico em elaboração de roteiros e/ou roteiro criativos e inéditos de audiovisual, teatro, dança, circo, artes visuais e demais linguagens.
                        </p>
                        <p>
                            A inscrição para este módulo poderá acontecer através de inscrição única via pessoa física ou coletivos formados por, no mínimo, 3 (três) pessoas
                        </p>
                    </div>

                    <hr>

                    <div class="card--adicional-info">
                        <div class="time">
                            <strong>Prazo:</strong> de 28/09/20 até 30/10/20
                        </div>
                        
                        <div class="taxonomies">
                            <div class="type">
                                <strong>Tipo: </strong> Edital
                            </div>
                            <div class="tags">
                                <strong>Tags: </strong>Circo
                                
                            </div>
                        </div>

                        
                    </div>
                </div>


                <div class="card">
                    <a href="#" class="card--title">
                        Nome da oportunidade
                    </a>

                    <div class="card--content">
                        <p>
                            Premiar roteiros criativos de audiovisual, teatro, dança, circo, artes visuais e literatura já desenvolvidos ou em desenvolvimento na cidade de São Paulo.
                        </p>
                        <p>
                            Quem pode participar deste módulo? Poderão se inscrever no MÓDULO VI coletivos organizados e pessoas fśicas que possuem histórico em elaboração de roteiros e/ou roteiro criativos e inéditos de audiovisual, teatro, dança, circo, artes visuais e demais linguagens.
                        </p>
                        <p>
                            A inscrição para este módulo poderá acontecer através de inscrição única via pessoa física ou coletivos formados por, no mínimo, 3 (três) pessoas
                        </p>
                    </div>

                    <hr>

                    <div class="card--adicional-info">
                        <div class="time">
                            <strong>Prazo:</strong> de 28/09/20 até 30/10/20
                        </div>
                        
                        <div class="taxonomies">
                            <div class="type">
                                <strong>Tipo: </strong> Edital
                            </div>
                            <div class="tags">
                                <strong>Tags: </strong>Circo
                                
                            </div>
                        </div>

                        
                    </div>
                </div>
            </div>

            <div class="pagination">
                <a href="#"> 
                    <i class="fas fa-angle-left"></i>
                </a>
                <a href="#">1</a>
                <a href="#" class="current-page">2</a>
                <a href="#">3</a>

                <a href="#"> 
                    <i class="fas fa-angle-right"></i>
                </a>
            </div>
        </div>

       
    </div>
    <!-- End .js-questions -->

</section>
