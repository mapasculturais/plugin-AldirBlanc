<?php

$this->bodyProperties['ng-app'] = "entity.app";
$this->bodyProperties['ng-controller'] = "EntityController";
$this->jsObject['angularAppDependencies'][] = 'entity.module.opportunity';

$this->addEntityToJs($registration);
$this->addOpportunityToJs($registration->opportunity);
$this->addOpportunitySelectFieldsToJs($registration->opportunity);
$this->addRegistrationToJs($registration);
$this->includeAngularEntityAssets($registration);
$this->includeEditableEntityAssets();

$_params = [
    'entity'      => $registration,
    'opportunity' => $registration->opportunity
];

$_messages = [
    //STATUS_SENT = 1
    '1' => [
        'title'   => 'Sua solicitação segue em análise.',
        'message' => 'Consulte novamente em outro momento. Você também receberá o resultado da sua solicitação por e-mail.',
    ],
    //STATUS_APPROVED = 10;
    '10' => [
        'title'   => 'Sua solicitação foi aprovada.',
        'message' => '<p>Caso tenha optado por transação bancária, brevemente seu benefício será disponibilizado na conta informada.</p>
        <p>Caso tenha optado por ordem de pagamento, quando disponibilizado o recurso, você poderá realizar o saque diretamente em qualquer agência do Banco do Brasil pessoalmente - apresentando RG e CPF, sem nenhum custo.</p>
        <p>Em virtude da pandemia da covid-19, algumas agências do Banco do Brasil podem estar operando com restrições e horários diferenciados de funcionamento, conforme determinação do poder público.</p>',
    ],
    //STATUS_INVALID = 2;
    '2' => [
        'title'   => 'Sua solicitação foi negada.',
        'message' => 'Não atendeu aos requisitos necessários. Caso não concorde com o resultado, você poderá fazer enviar novo formulário de solicitação ao benefício - fique atento aos preenchimento dos campos.',
    ],
    //STATUS_NOTAPPROVED = 3;
    '3' => [
        'title'   => 'Sua solicitação foi negada.',
        'message' => 'Não atendeu aos requisitos necessários. Caso não concorde com o resultado, você poderá fazer enviar novo formulário de solicitação ao benefício - fique atento aos preenchimento dos campos.',
    ],
    //STATUS_WAITLIST = 8;
    '8' => [
        'title'   => 'Sua solicitação foi validada.',
        'message' => 'Os recursos disponibilizados já foram destinados. Para sua solicitação ser aprovada será necessário aguardar possível liberação de recursos. Em caso de aprovação, você também será notificado por e-mail. Consulte novamente em outro momento.',
    ],
    '0' => [
        'title'   => 'Sua solicitação segue em análise.',
        'message' => 'Consulte novamente em outro momento. Você também receberá o resultado da sua solicitação por e-mail.',
    ]
];

?>

<section id="lab-status" class="lab-main-content">

    <article class="main-content registration" ng-controller="OpportunityController">
        
        <?php $status = $registration->status; ?>
        <?php if(isset($status)) : ?>
            <div class="status-card status-<?= $registration->status ?>">
                <h2 class="status-card--title"><?= $_messages[$registration->status]['title'] ?></h2>
                <p class="status-card--content"><?= $_messages[$registration->status]['message'] ?></p>
            </div><!-- /.status-card -->

        <?php endif; ?>
        <div class="wrap-button">
            <a href="<?php echo $app->createUrl('aldirblanc', 'cadastro'); ?>" class="btn secondary"><?php \MapasCulturais\i::_e("Voltar para os Cadastros"); ?></a>
        </div><!-- /.wrap-button -->

        <h4 class="title-subsection">Edital <span class="underline">Inciso <?= $registration->inciso ?></span></h4>

        <h1> Cadastro de pessoa física </h1>

        <?php $this->part('aldirblanc/registration-single--header', $_params) ?>
        <?php $this->part('singles/registration-single--fields', $_params) ?>

        <div class="wrap-button">
            <a href="<?php echo $app->createUrl('aldirblanc', 'cadastro'); ?>" class="btn secondary"><?php \MapasCulturais\i::_e("Voltar para os Cadastros"); ?></a>
        </div><!-- /.wrap-button -->

    </article>

</section>