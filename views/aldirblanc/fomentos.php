<section class="lab-main-content cadastro">
    <header>
        <div class="intro-message">
            <div class="name"> Olá <?= $niceName ? ", " . $niceName : "" ?>! </div>
        </div>
    </header>

    <!-- Begin .js-questions -->
    <div class="opportunity-listing questions">
        <?php if (count($cidades) > 1){ ?>   
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
        <?php } ?>
        <?php foreach ($opportunities as $opportunity) { ?>
            <div class="opportunities">
            <div class="opportunities--cards">
                <div class="card">
                    <a href="<?= $opportunity->singleUrl ?>" class="card--title">
                        <?= $opportunity->name?>
                    </a>

                    <div class="card--content">
                        <?= nl2Br($opportunity->shortDescription) ?>
                    </div>

                    <hr>
                    
                    <div class="card--adicional-info">
                        <?php if (isset($opportunity->registrationFrom) && isset($opportunity->registrationTo) ){
                            ?>
                        <div class="time">
                            <strong>Prazo:</strong> de <?=$opportunity->registrationFrom->format('d/m/Y')?> até <?=$opportunity->registrationTo->format('d/m/Y')?>
                        </div>
                            
                        <?php } ?>

                       
                        
                        <div class="taxonomies">
                            <div class="type">
                                <strong>Tipo: </strong> <?= $opportunity->type->name?>
                            </div>
                            <?php 
                                $tags = implode(', ', $opportunity->terms['tag']);
                                if ($tags != ''){ 
                            ?>
                            <div class="tags">
                                <strong>Tags: </strong><?= $tags?>
                            </div>
                            <?php } ?>
                        </div>

                        
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
        

       
    </div>
    <!-- End .js-questions -->

</section>
