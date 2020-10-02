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

<?php if ($inciso2) : ?>
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
    </section>
<?php endif ?>