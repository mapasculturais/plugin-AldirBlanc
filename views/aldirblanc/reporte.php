<?php if ($inciso1) : ?>
    <section class="lab-main-content cadastro">
        <header>
            <div class="intro-message">
                <div class="name"> Inciso I - Trabalhadoras e trabalhadores da Cultura </div>
            </div>
        </header>
        <div class="js-lab-item lab-item cadastro-options">
            <!-- <p class="lab-form-question">Para quem você está solicitando o benefício? <a class="js-help icon icon-help" href="#" title=""></a></p> -->
            <h2 class="featured-title">
                <?= $inciso1->total ?> benefícios solicitados.
            </h2>
    </section>
<?php endif ?>

<?php 
if ($inciso2) : 
    $controller = $app->controller('Opportunity');
?>
<style>
    .oportunidades .report-item {
        display: inline-block;
    }
    .oportunidades th {
        text-align: left;
    }
    .oportunidades td {
        text-align: left;
        padding: 0 1em;
    }

    .oportunidades td.number {
        text-align: center;
    }

    .oportunidades tr:hover {
        background-color: #fff;
    }
    
    
</style>
    <section class="lab-main-content cadastro">
        <header>
            <div class="intro-message">
                <div class="name"> Inciso II - Espaços e organizações culturais </div>
            </div>
        </header>
        <div class="js-lab-item lab-item cadastro-options">
            <!-- <p class="lab-form-question">Para quem você está solicitando o benefício? <a class="js-help icon icon-help" href="#" title=""></a></p> -->
            <h2 class="featured-title">
                <?= $inciso2->total ?> benefícios solicitados.
            </h2>

            <section class="oportunidades">
                <div class="report-item">
                    <h3>Oportunidades com inscrições enviadas</h3>
                    <table>
                        <tr>
                            <th>Oportunidade</th>
                            <th>Inscrições</th>
                        </tr>
                        <?php foreach($inciso2->enviadas as $opp): $opp = (object) $opp; ?>
                            <tr>
                                <td>
                                    <a href="<?= $controller->createUrl('single', [$opp->id]) ?>"><?= $opp->name ?></a>
                                </td>
                                <td class='number'><?= $opp->num_inscricoes ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <div class="report-item">
                    <h3>Oportunidades somente com inscrições rascunho</h3>
                    <table style="width: 80%;">
                        <tr>
                            <th>Oportunidade</th>
                            <th>Inscrições</th>
                        </tr>
                        <?php foreach($inciso2->soh_rascunhos as $opp): $opp = (object) $opp; ?>
                            <tr>
                                <td>
                                    <a href="<?= $controller->createUrl('single', [$opp->id]) ?>"><?= $opp->name ?></a>
                                </td>
                                <td class='number'><?= $opp->num_inscricoes ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>

                <div class="report-item">
                    <h3>Oportunidades sem nenhuma inscrição</h3>
                    <table style="width: 80%;">
                        <tr>
                            <th>Oportunidade</th>
                        </tr>
                        <?php foreach($inciso2->sem_inscricao as $opp): $opp = (object) $opp; ?>
                            <tr>
                                <td>
                                    <a href="<?= $controller->createUrl('single', [$opp->id]) ?>"><?= $opp->name ?></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </section>
    </section>
<?php endif ?>