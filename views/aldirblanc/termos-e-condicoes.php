<?php
    $this->jsObject['registrationId'] = $registration_id;
?>
<section class="termos" >
    <p class="termos--summay"><?php \MapasCulturais\i::_e("De acordo com os critérios da LEI Nº 14.017 com sanção presidencial em 29 DE JUNHO DE 2020. Para ter acesso ao Auxílio Emergencial Cultural, você deve cumprir os seguintes requisitos:");?> </p>

    <h2><?php \MapasCulturais\i::_e("Termos e Condições");?></h2>

    <div class="termos--list">
        <div class="term">
            <span class="term--box"></span>
            <label for="one" class="term--label">
                <input type="checkbox" class="term--input" id="one" />
                <span class="termos--text">
                <?php \MapasCulturais\i::_e("Declaro atuação no setor cultural e fonte de renda, conforme lei nº 14.017, de
                    29 de junho de 2020, que dispõe sobre ações emergenciais destinadas ao setor
                    cultural a serem adotadas durante o estado de calamidade pública.");?>
            </span>
            </label>
        </div>
        <div class="term">
            <span class="term--box"></span>
            <label for="two" class="term--label">
                <input type="checkbox" class="term--input" id="two" />
                <span class="termos--text">
                <?php \MapasCulturais\i::_e("Declaro que não sou titular de benefício previdenciário ou assistencial do
                    governo federal, exceto do programa bolsa família, conforme inciso iii do art.
                    6º da lei nº 14.017.");?>
            </span>
            </label>
        </div>
        <div class="term">
            <span class="term--box"></span>
            <label for="three" class="term--label">
                <input type="checkbox" class="term--input" id="three" />
                <span class="termos--text">
                <?php \MapasCulturais\i::_e("Declaro que não sou titular de benefício previdenciário ou assistencial do
                    governo federal, exceto do programa bolsa família, conforme inciso iii do art.
                    6º da lei nº 14.017.");?>
            </span>
            </label>
        </div>
        <div class="term">
            <span class="term--box"></span>
            <label for="four" class="term--label">
                <input type="checkbox" class="term--input" id="four" />
                <span class="termos--text">
                <?php \MapasCulturais\i::_e("Declaro que não estou recebendo benefício do seguro desemprego ou
                    programa de transferência de renda federal, exceto do programa bolsa família,
                    conforme inciso iii do art. 6º da lei nº 14.017.");?>
            </span>
            </label>
        </div>
        <div class="term">
            <span class="term--box"></span>
            <label for="five" class="term--label">
                <input type="checkbox" class="term--input" id="five" />
                <span class="termos--text">
                <?php \MapasCulturais\i::_e("Declaro renda familiar per capita de até meio salário mínimo ou renda familiar
                    total de até três salários mínimos, conforme inciso iv do art. 6º da lei nº 14.017.");?>
            </span>
            </label>
        </div>
        <div class="term">
            <span class="term--box"></span>
            <label for="six" class="term--label">
                <input type="checkbox" class="term--input" id="six" />
                <span class="termos--text">
                <?php \MapasCulturais\i::_e("Declaro que no ano de 2018, não recebi rendimentos acima de R$ 28.559,70 (vinte
                    e oito mil, quinhentos e cinquenta e nove reais e setenta centavos), conforme
                    inciso v do art. 6º da lei nº 14.017.");?>
            </span>
            </label>
        </div>
        <div class="term">
            <span class="term--box"></span>
            <label for="eight" class="term--label">
                <input type="checkbox" class="term--input" id="eight" />
                <span class="termos--text">
                <?php \MapasCulturais\i::_e("Declaro não ser beneficiário(a) do auxílio emergencial previsto na lei nº 13.982,
                    de 2 de abril de 2020, e em conformidade com o inciso vii do art. 6º da lei nº 14.017.");?>
            </span>
            </label>
        </div>
        <div class="term">
            <span class="term--box"></span>
            <label for="nine" class="term--label">
                <input type="checkbox" class="term--input" id="nine" />
                <span class="termos--text">
                <?php \MapasCulturais\i::_e("Declaro que estou ciente de que, em caso de utilização de qualquer meio ilícito,
                    imoral ou declaração falsa para a participação deste credenciamento, incorro
                    nas penalidades previstas nos artigos 171 e 299 do decreto lei nº 2.848, de 07 de
                    dezembro de 1940 (código penal).");?>
            </span>
            </label>
        </div>
        <div class="term">
            <span class="term--box"></span>
            <label for="ten" class="term--label">
                <input type="checkbox" class="term--input" id="ten" />
                <span class="termos--text">
                <?php \MapasCulturais\i::_e("Declaro que estou ciente da concessão das informações por mim declaradas
                    neste formulário para validação em outras bases de dados oficiais.");?>
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
            <span class="close">&times;</span>
                <?php \MapasCulturais\i::_e("Você precisa aceitar todos os termos para proseguir com a inscrição no auxílio emergencial da cultura.");?>
            <p></p>
        </div>
    </div>

</section>

<script>
    var span          = document.getElementsByClassName("close")[0];
    var modal         = document.getElementById("modalAlert");
    var btnProsseguir = document.querySelector(".js-btn");

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if(event.target == btnProsseguir){
            goToNextPage();
        }else{
            if(modal.style.display == 'block'){
                modal.style.display = "none";
            }
        }

    }
    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        modal.style.display = "none";
    }

    function goToNextPage() {
        var checkboxes        = document.querySelectorAll('input[type="checkbox"]');
        var checkboxesChecked = document.querySelectorAll('input[type="checkbox"]:checked');

        if(checkboxes.length === checkboxesChecked.length){
            //redirect to next page
            document.location = MapasCulturais.createUrl('aldirblanc', 'aceitar_termos', [MapasCulturais.registrationId])
        }else{
            modal.style.display = "block";
        }
    }

</script>