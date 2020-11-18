<?php

use Doctrine\Common\Util\Debug;
use MapasCulturais\i;
use MapasCulturais\Entities\Registration;


$this->jsObject['opportunityId'] = null;
$this->jsObject['opportunitiesInciso2'] = $opportunitiesInciso2;
$this->jsObject['ignoreDates'] = $ignoreDates;
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
            <div class="name"> Olá <?= $niceName ? ", " . $niceName : "" ?>! 
            <br>
            Clique <a href="<?=$registrationUrl = $app->createUrl('site');?>"> aqui</a> para retornar à página inicial
            </div>
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
                        <span class="content"><i>
                        Renda emergencial destinada aos trabalhadores e trabalhadoras da cultura que tiveram suas atividades interrompidas e se enquadram ao disposto no Art. 6º - Lei 14.017/2020. Prevê o pagamento de três parcelas de R$ 600,00 (seiscentos reais), podendo ser prorrogado conforme Art 5º - Lei 14.017/2020.</i></span>
                    </div>
                </button>
            <?php
            } else if ( !$inciso1_enabled && $this->controller->config['msg_inciso1_disabled'] != '' ) {
                $mensagemInciso1Disabled = $this->controller->config['msg_inciso1_disabled'];
                $this->part('aldirblanc/cadastro/inciso-disabled',  ['mensagem' => $mensagemInciso1Disabled, 'title' => $inciso1Title]);
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
                        <span class="more js">Mais informações</span>
                        <span class="content"><i>    
                        Benefício destinado a espaços, organizações da sociedade civil, empresas, cooperativas e instituições com finalidade cultural, conforme Arts. 7º e 8º - Lei 14.017/2020. Prevê subsídio mensal entre R$ 3.000,00 (três mil reais) e R$ 10.000,00 (dez mil reais), conforme definição da gestão local.</i></span>
                    </div>
                </button>

                <!-- End #option1 -->
            <?php
            } else if (!$inciso2_enabled && isset($this->controller->config['msg_inciso2_disabled'])) {
                $mensagemInciso2Disabled = $this->controller->config['msg_inciso2_disabled'];
                $this->part('aldirblanc/cadastro/inciso-disabled',  ['mensagem' => $mensagemInciso2Disabled, 'title' => $inciso2Title]);
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
            if ($inciso3_enabled && count($opportunitiesInciso3) > 0 ) {
            ?>
            <button onclick="location.href='<?= $this->controller->createUrl('fomentos') ?>'" class="informative-box lab-option">
                <div class="informative-box--icon">
                    <i class="fas fa-file-alt"></i>
                </div>

                <div class="informative-box--title">
                    <h2>Editais, fomentos e oportunidades</h2>
                    <i class="fas fa-minus"></i>
                    <!-- <i class="far fa-check-circle"></i> -->
                </div>

                <div class="informative-box--content active" data-content="">
                    <span class="more"> Mais informações </span>
                    <span class="content">
                        <i>
                            Donec facilisis tortor ut augue lacinia, at viverra est semper. Sed sapien metus, scelerisque nec pharetra id, tempor a tortor. Pellentesque non dignissim neque. Ut porta viverra est, ut dignissim elit elementum ut. Nunc vel rhoncus nibh, ut tincidunt turpis. Integer ac enim pellent.
                        </i>
                    </span>
                </div>
            </button>
            <?php 
            }?>
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
                        <h2 class="js-text"><?php echo $this->controller->config['texto_cadastro_espaco'] ?></h2>
                        <i class="fas fa-minus"></i>
                        <!-- <i class="far fa-check-circle"></i> -->
                    </div>
                    <input type="radio" class="coletivo" name="coletivo" value="espaco" />
                </label>
                <label class="informative-box">
                    <div class="informative-box--title">
                        <h2 class="js-text"><?php echo $this->controller->config['texto_cadastro_coletivo'] ?></h2>
                        <i class="fas fa-minus"></i>
                        <!-- <i class="far fa-check-circle"></i> -->
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
                        <h2 class="js-text"><?php echo $this->controller->config['texto_cadastro_cnpj'] ?></h2>
                        <i class="fas fa-minus"></i>
                        <!-- <i class="far fa-check-circle"></i> -->
                    </div>
                    <input type="radio" class="formalizado" name="formalizado" value="formalizado" />
                </label>
                <label class="informative-box">
                    <div class="informative-box--title">
                        <h2 class="js-text"><?php echo $this->controller->config['texto_cadastro_cpf'] ?></h2>
                        <i class="fas fa-minus"></i>
                        <!-- <i class="far fa-check-circle"></i> -->
                    </div>
                    <input type="radio" class="formalizado" name="formalizado" value="nao-formalizado" />
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
            
        <?php 
        if (count($cidades) > 1) : ?>
            <div id="select-cidade" class="js-questions-tab questions--tab lab-form-answer inactive">
                <i class="questions--icon fas fa-university"></i>
                <h4 class="questions--title"><?php i::_e('Selecione a cidade') ?></h4>
                <p class="questions--summary"><?php i::_e('Indique a cidade onde o beneficiário do subsídio está instalado ou tem desenvolvido, atualmente, suas atividades culturais. Se sua cidade não constar na lista, é porque ela está operando a Lei Aldir Blanc por outro sistema. ') ?></p>
                <?php $this->part('aldirblanc/cadastro/select-cidade', ['cidades' => $cidades]) ?>
                <div class="questions--nav">
                    <button class="btn secondary btn-back js-back"><?php i::_e('Voltar') ?></button>
                    <button class="btn btn-next js-next"><?php i::_e("Avançar"); ?></button>
                </div>
            </div>
        <?php elseif (count($cidades) == 1) : ?>
            
            <?php foreach($cidades as $nome => $oportunidade): ?>
                <input type="hidden" id="input-cidade" value="<?=$oportunidade?>">
            <?php endforeach; ?>
         
        <?php endif; ?>
    </div>
    <!-- End .js-questions -->

    </div><!-- End .box -->

    <div id="modalAlertCadastro" class="modal">
        <!-- Modal content -->
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 class="modal-content--title js-title"></h2>
            <strong>Atenção, você não poderá alterar essas opções após clicar em confirmar!</strong>
            <br><br>
            <p id="modal-content-text" class="modal-content-text"></p>
            <button class="btn js-confirmar"><?php \MapasCulturais\i::_e("Confirmar"); ?></button>        
        </div>
    </div>

</section>

