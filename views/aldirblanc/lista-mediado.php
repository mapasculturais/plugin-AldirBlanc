<section class="lab-main-content cadastro ">
    <header>
        <div class="intro-message">
        </div>
    </header>

    <div class="js-lab-item lab-item cadastro-options">
        <!-- <p class="lab-form-question">Para quem você está solicitando o benefício? <a class="js-help icon icon-help" href="#" title=""></a></p> -->
        <h2 class="featured-title">
            Clique no cadastro que deseja visualizar
        </h2>

        <div class="lab-form-filter opcoes-inciso">
            <?php foreach ($registrations as $registration) {
               ?>
            <a href="<?= $this->controller->createUrl( 'status', [$registration->id]) ?>">

                <button id="" role="button" class="informative-box js-lab-option lab-option">
                  
                    <div class="informative-box--title">
                        <h2>
                            <?=$registration->opportunity->name;?>
                        </h2>
                    </div>

                </button>  
            </a>
               <?php
            }
            ?>
             
        </div>

    </div><!-- End .lab-item -->


</section>

