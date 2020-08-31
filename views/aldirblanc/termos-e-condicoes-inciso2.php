<?php
    $this->jsObject['registrationId'] = $registration_id;
?>
<section class="termos" >
    <p class="termos--summay"><?php \MapasCulturais\i::_e("De acordo com os critérios da LEI Nº 14.017 com sanção presidencial em 29 DE JUNHO DE 2020. Para ter acesso ao Auxílio Emergencial Cultural, você deve cumprir os seguintes requisitos:");?> </p>

    <h2><?php \MapasCulturais\i::_e("Termos e Condições Inciso II");?></h2>

    <div class="termos--list">
        <div class="term">
            <span class="term--box"></span>
            <label for="two" class="term--label">
                <input type="checkbox" class="term--input" id="two" />
                <span class="termos--text">
                <?php \MapasCulturais\i::_e("Declaro que o(a) espaço artístico e cultural, 
                micro ou pequena empresa cultural, organização cultural comunitária, 
                cooperativa cultural ou instituição cultural ao qual represento 
                tem finalidade cultural e teve suas atividades interrompidas em 
                decorrência da pandemia da covid 19, conforme dispõe o inciso II 
                do artigo 2º da Lei 14.017/2020.");?>
            </span>
            </label>
        </div>
        <div class="term">
            <span class="term--box"></span>
            <label for="three" class="term--label">
                <input type="checkbox" class="term--input" id="three" />
                <span class="termos--text">
                <?php \MapasCulturais\i::_e("Declaro que o espaço artístico e 
                cultural NÃO foi criado ou está vinculado à administração pública 
                de qualquer esfera, conforme vedação prevista no § Único do 
                Art. 8º da Lei 14.017/2020;");?>
            </span>
            </label>
        </div>
        <div class="term">
            <span class="term--box"></span>
            <label for="four" class="term--label">
                <input type="checkbox" class="term--input" id="four" />
                <span class="termos--text">
                <?php \MapasCulturais\i::_e("Declaro que o espaço artístico 
                e cultural NÃO está vinculado às fundações,  
                institutos ou instituições criados ou mantidos por grupos 
                de empresas, conforme vedação prevista no § Único do Art. 8º 
                da Lei 14.017/2020;");?>
            </span>
            </label>
        </div>
        <div class="term">
            <span class="term--box"></span>
            <label for="five" class="term--label">
                <input type="checkbox" class="term--input" id="five" />
                <span class="termos--text">
                <?php \MapasCulturais\i::_e("Declaro que o espaço artístico e cultural 
                NÃO é gerido pelos serviços sociais do Sistema S 
                (Sescoop, Sesi, Senai, Sesc, Senac, Sest, Senat e Senar), 
                conforme vedação prevista no § Único do Art. 8º da Lei 14.017/2020;");?>
            </span>
            </label>
        </div>
        <div class="term">
            <span class="term--box"></span>
            <label for="six" class="term--label">
                <input type="checkbox" class="term--input" id="six" />
                <span class="termos--text">
                <?php \MapasCulturais\i::_e("Declaro que estou solicitando apenas 
                este subsídio mensal, em todo território nacional, e que não irei 
                requerer esse mesmo benefício para outro espaço artístico e cultural 
                ao qual sou responsável pela gestão, pois estou ciente da vedação de 
                recebimento cumulativo prevista no § 3º do artigo 7º da Lei 14.017/2020.");?>
            </span>
            </label>
        </div>
        <div class="term">
            <span class="term--box"></span>
            <label for="ten" class="term--label">
                <input type="checkbox" class="term--input" id="ten" />
                <span class="termos--text">
                <?php \MapasCulturais\i::_e("Declaro que li e que concordo 
                com os termos previstos em edital e no decreto de 
                regulamentação publicado pelo ente repassador dos recursos 
                do subsídio mensal.");?>
            </span>
            </label>
        </div>
    </div>

    <nav class="termos--nav-terms">
        <button class="btn btn-large btn-lab js-btn">  <?php \MapasCulturais\i::_e("Continuar");?></button>
    </nav>

    <div id="modalAlert" class="modal">
        <!-- Modal content -->
        <div class="modal-content">
            <h2 class="modal-content--title title-modal">Atenção!</h2>
            <span class="close">&times;</span>
            <p>
                <?php \MapasCulturais\i::_e("Você precisa aceitar todos os termos para proseguir com a inscrição no auxílio emergencial da cultura.");?>
            </p>

            <button id="btn-close" class="btn secondary "> OK </button>
        </div>
    </div>

</section>

<script>
    var span          = document.getElementsByClassName("close")[0];
    var modal         = document.getElementById("modalAlert");
    var btnClose      = document.getElementById("btn-close");
    var btnProsseguir = document.querySelector(".js-btn");

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if(event.target == btnProsseguir){
            goToNextPage();
        }else{
            if(modal.style.display == 'flex'){
                modal.style.display = "none";
            }
        }

    }
    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        modal.style.display = "none";
    }

    btnClose.onclick = function() {
        modal.style.display = "none";
    }

    function goToNextPage() {
        var checkboxes        = document.querySelectorAll('input[type="checkbox"]');
        var checkboxesChecked = document.querySelectorAll('input[type="checkbox"]:checked');

        if(checkboxes.length === checkboxesChecked.length){
            //redirect to next page
            document.location = MapasCulturais.createUrl('aldirblanc', 'aceitar_termos', [MapasCulturais.registrationId])
        }else{
            modal.style.display = "flex";
        }
    }

</script>