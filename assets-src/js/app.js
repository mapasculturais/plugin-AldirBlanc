$(document).ready(function() {



    var params = {
        opportunity: null,
        category: null
    };
    var formalizado = null;
    var coletivo = null;
    var returning = false;
    /**
     * Se houver cidade/oportunidade defualt definida na configuração do plugin para o Inciso II, o id é setado no paramentro.
     */
    if (MapasCulturais.opportunityId != null) {
        params.opportunity = MapasCulturais.opportunityId
    }
    if (MapasCulturais.opportunitiesInciso2 != null) {
        params.opportunitiesInciso2 = MapasCulturais.opportunitiesInciso2
    }
    /**
     * Redireciona o usuário para próxima tela conforme paramentros selecionados.
     */
    function goToNextPage() {
        params.category = coletivo + '-' + formalizado;
        document.location = MapasCulturais.createUrl('aldirblanc', 'coletivo', params);
    }

    function showModal() {
        var modalTitle = "";
        var msg = "";
        var modal = $('#modalAlertCadastro');
        var coletivo = $('input[name=coletivo]:checked').siblings().find('.js-text').text();
        var fomalizado = $('input[name=formalizado]:checked').siblings().find('.js-text').text();

        coletivo = coletivo.replace(".", "");
        fomalizado = fomalizado.replace(".", "");

        var nomeCidade = $('.select2-chosen').text();
        

        modal.css("display", "flex").hide().fadeIn(900);

        $('#modalAlertCadastro .modal-content').find('.js-confirmar').show();

        //$('#modalAlertCadastro .modal-content').find('.js-title').text('Confirmação');

        modalTitle = "Confirmação";

        $('#modalAlertCadastro .modal-content').find('.btn').val('next');
        $('#modalAlertCadastro .modal-content').find('.btn').text('Confirmar');
        
        if (params.opportunity != null) {
            msg = `Você está solicitando o benefício para <strong>_fomalizado_</strong> para espaço do tipo  <strong>_coletivo_</strong>_cidade_ <br><br><p>Você confirma essas informações?</p>`;
            msg = msg.replace(/_fomalizado_/g, fomalizado);
            msg = msg.replace(/_coletivo_/g, coletivo);

            if (nomeCidade) {
                msg = msg.replace(/_cidade_/g, " na cidade de <strong>" + nomeCidade + "</strong>.");
            } else {
                msg = msg.replace(/_cidade_/g, ".");
            }

        } else {
            var cidade = $('.js-select-cidade option:selected').val();
            if (cidade > 0) {
                msg = `Você está solicitando o benefício para <strong>_fomalizado_</strong> para espaço do tipo  <strong>_coletivo_</strong>_cidade_ <br><br><p>Você confirma essas informações?</p>`;
                msg = msg.replace(/_fomalizado_/g, fomalizado);
                msg = msg.replace(/_coletivo_/g, coletivo);
                if (nomeCidade) {
                    msg = msg.replace(/_cidade_/g, " na cidade de <strong>" + nomeCidade + "</strong>.");
                } else {
                    msg = msg.replace(/_cidade_/g, ".");
                }
            } else {
                msg =  'Você precisa selecionar a cidade.';
                modalTitle = "Atenção";
            }
        }
        if ($("#input-cidade").length > 0 ){
            selectedCityId = $('#input-cidade').val();
        }
        else{
            selectedCityId = $('.js-select-cidade option:selected').val();
        }
        
        let cityObj = MapasCulturais.opportunitiesInciso2.filter(city => city.id == selectedCityId)[0]
        if (!(MapasCulturais.serverDate.date >= cityObj.registrationFrom.date && MapasCulturais.serverDate.date <= cityObj.registrationTo.date) && !MapasCulturais.ignoreDates) {
            modalTitle = cityObj.name;

            msg = `Infelizmente não será possivel realizar sua inscrição:
            <br>
            <br>
            
            > Data de inicio das inscri ções: <strong> ${new Date(cityObj.registrationFrom.date).toLocaleDateString("pt-BR")} </strong>
            <br>
            <br>
            > Data de fim das inscrições: <strong> ${new Date(cityObj.registrationTo.date).toLocaleDateString("pt-BR")} </strong>`
            
            $('.js-confirmar').hide();
        } 
        
        showModalMsg( modalTitle, msg);

        //$('#modalAlertCadastro .modal-content').find('.modal-content-text').html(msg);

        $('.close, .btn-ok').on('click', function() {
            modal.fadeOut('slow');
        });
    }

    function showModalMsg(title, message) {
        let modal = $('#modalAlertCadastro');
        let text = document.getElementById("modal-content-text");

        $('#modalAlertCadastro .modal-content').find('.js-title').text(title);

        if(title != "Confirmação") {
            $('#modalAlertCadastro .modal-content').find('.btn').val('close');
            $('#modalAlertCadastro .modal-content').find('.btn').text('OK');
        } 


        text.innerHTML = message;
        modal.fadeIn('fast');

        $('.close, .btn-ok').on('click', function() {
            modal.fadeOut('fast');
        });
    }
    

    /**
     * Ao clicar em uma das opções do local de atividade do beneficiário , o usuário é encaminhado para tela de opções de personalidades jurídica do beneficiário.
     */
    function goToQuestionPersonality() {
        $('.js-questions-tab').hide();
        $('#personalidade-juridica').fadeIn('fast');
        returning = false;
    }

    /**
     * Ao clicar em uma das opções de opções de personalidades jurídica do beneficiário, o usuário é encaminhado para tela de seleção da oportunidade/cidade,
     * senão é redirecionado conforme os parametros selecionados.
     */
    function goToQuestionCounty(hide = true) {
        if(hide) {
            $('.js-questions-tab').hide();
        }

        if (returning) {
            $('.js-questions-tab').hide();
            $('#select-cidade').fadeIn('fast');
            return;
        }

        let hasCities = $('.js-questions').find('#select-cidade');
        /**
         * Se a oportunidade for null e o campo de seleção da cidades/oportunidades for encontrado, significa que há mais uma cerragada na configuração do plugin.
         * O usuário deverá ser encaminhado para tela de seleção da cidade/oportunidade.
         */
        if (params.opportunity == null && hasCities.length > 0) {
            $('.js-questions-tab').hide();
            $('#select-cidade').fadeIn('fast');
            returning = false;
        } else {
            // $('.js-questions-tab').hide();
            showModal()
        }
    }

    $('.coletivo').click(function() {
        coletivo = this.value;
        $('.coletivo').parent().removeClass('selected')
        $(this).parent().addClass('selected');
    });

    $('.formalizado').click(function() {
        formalizado = this.value
        $('.formalizado').parent().removeClass('selected')
        $(this).parent().addClass('selected');
    });

    /**
     * Ao selecionar a cidade/opotunidade o usuário é redirecionado conforme os parametros selecionados.
     */
    $('.js-select-cidade').change(function() {
        params.opportunity = this.value;
    });


    $('.js-back').click(function() {
        var parentId = $(this).closest('.js-questions-tab').attr('id');
        returning = true;
        switch (parentId) {
            case 'personalidade-juridica':
                $('#personalidade-juridica').hide();
                $('#local-atividade').fadeIn('fast');
                break;
            case 'local-atividade':
                $('.js-questions').hide();
                $('#personalidade-juridica').hide();
                $('.js-lab-item').fadeIn('fast');
                break;
            case 'select-cidade':
                $('#select-cidade').hide();
                $('#personalidade-juridica').fadeIn('fast');
                params.opportunity = null;
                $(".js-select-cidade").select2("val", "-1");
                break;
        }
    });

    $('.js-next').click(function() {
        var parentId = $(this).closest('.js-questions-tab').attr('id');

        if (parentId == 'local-atividade') {
            var hasSeletedColetivo = $('input[name=coletivo]:checked');
            if (hasSeletedColetivo.length > 0) {
                goToQuestionPersonality()
            } else {
                showModalMsg('Atenção!', 'Você precisa selecionar uma opção para avançar')
            }
        } else if (parentId == 'select-cidade') {
            showModal()
        } else {
            var hasSeletedFormalizado = $('input[name=formalizado]:checked');
            if (hasSeletedFormalizado.length > 0) {
                if($('#select-cidade').lenght) {
                    goToQuestionCounty()
                } else {
                    goToQuestionCounty(false)
                }
            } else {
                showModalMsg('Atenção!', 'Você precisa selecionar uma opção para avançar')
            }
        }
    });

    $('button.js-confirmar').click(function() {
        if (this.value == 'next') {
            $('.js-questions-tab').hide();
            $('.js-questions').html('<h4>Enviando informações ...</h4>');
            $('#modalAlertCadastro').fadeOut('slow')
            goToNextPage();
        } else {
            $('#modalAlertCadastro').fadeOut('slow')
        }
    });

    //Fechar modal ao clicar fora dela.
    $(window).click(function(event) {
        var modal = $('#modalAlertCadastro');
        if (event.target.value != 'next') {
            if ($(event.target).css('display') == 'flex') {
                modal.fadeOut('slow')
            }
        }
    });

    /**
     * Ao clicar nos cards do Inciso II, o usuário é encaminhado para tela de opções do local de atividade do beneficiário.
     */
    let selectedInciso = '';

    $('.js-lab-option').click(function() {
        // selectedInciso = $(this).attr('id');
        // $('.lab-option').removeClass('active');
        // $(this).toggleClass('active');

        $('.js-lab-item').fadeOut(1);
        $('.js-questions').fadeIn(11);
        $('#local-atividade').fadeIn('fast');
        returning = false;
    });

    $('select#opportunity-type').select2({
        placeholder: 'Selecione uma opção',
        width: '100%',
        height: '100px'
    });

    /**
     * Devolve o formulario dos exportadores ao estado inicial
     */
    $('.form-export-clear').on('click', function() {                      
        $('.form-export-dataprev').trigger("reset");
     });
     
     
    var cpfInput = document.getElementById("RegraValida");
    if(cpfInput) {
        cpfInput.addEventListener('keydown', function() {
        
            function fMasc(objeto,mascara) {
                obj=objeto
                masc=mascara
                setTimeout(() => {
                    obj.value=masc(obj.value)
                },1)
            }
    
            function mCPF(cpf){
                cpf=cpf.replace(/\D/g,"")
                cpf=cpf.replace(/(\d{3})(\d)/,"$1.$2")
                cpf=cpf.replace(/(\d{3})(\d)/,"$1.$2")
                cpf=cpf.replace(/(\d{3})(\d{1,2})$/,"$1-$2")
                return cpf
            }
    
            fMasc( this, mCPF )

        });
    }
    

    /**
     * Formulario de inscrição por mediadores
     */

    if ($(".action-formulario .registration-fieldset-moderator").length > 0){

        // mediacao_contato_tipo
        $field_validate = false;
        $checked_mediacao_contato_tipo = $('.field-mediacao-contato-tipo label input:checked');
        if($checked_mediacao_contato_tipo.length > 0) {
            $field_validate = true;
        }

        if($field_validate == false) {
            $('.ng-scope .registration-fieldset .errors-header').before('<div class="errors erro-mediacao-contato-tipo"><a href="">Forma de contato: <span class="errors-field ng-binding ng-scope"> O campo é obrigatório.</span></a></div>');
        }

        $fields_mediacao_contato_tipo = $('.field-mediacao-contato-tipo label input');
        $fields_mediacao_contato_tipo.each(function(){
            $(this).change(function(){
                $field_validate = true;
                $('.erro-mediacao-contato-tipo').remove();
            });
        });

        // mediacao_contato
        $field_mediacao_contato = $('#field-mediacao-contato');
        $normalize_field_mediacao_contato = $field_mediacao_contato.val().replace(/[^0-9]/gi, '');

        if ($normalize_field_mediacao_contato.length < 10) {
            $('.ng-scope .registration-fieldset .errors-header').after('<div class="errors erro-mediacao-contato"><a href="">Número de telefone: <span class="errors-field ng-binding ng-scope"> O campo é obrigatório.</span></a></div>');
        } else {
            $('.erro-mediacao-contato').remove();
        }

        $field_mediacao_contato.change(function() {
            $normalize_field_mediacao_contato = $field_mediacao_contato.val().replace(/[^0-9]/gi, '');
            if ($normalize_field_mediacao_contato.length < 10) {
                $('.erro-mediacao-contato').remove();
                $('.ng-scope .registration-fieldset .errors-header').after('<div class="errors erro-mediacao-contato"><a href="">Número de telefone: <span class="errors-field ng-binding ng-scope"> O campo é obrigatório.</span></a></div>');
            } else {
                $('.erro-mediacao-contato').remove();
            }
        });

        // mediacao_senha
        $field_mediacao_senha = $('#field-mediacao-senha');

        if ($field_mediacao_senha.val().length < 6 || $field_mediacao_senha.val() == '""') {
            $('.ng-scope .registration-fieldset .errors-header').after('<div class="errors erro-mediacao-senha"><a href="">Senha: <span class="errors-field ng-binding ng-scope"> O campo é obrigatório.</span></a></div>');
        } else {
            $('.erro-mediacao-senha').remove();
        }

        $field_mediacao_senha.change(function() {
            if ($field_mediacao_senha.val().length < 6 || $field_mediacao_senha.val() == '""') {
                $('.erro-mediacao-senha').remove();
                $('.ng-scope .registration-fieldset .errors-header').after('<div class="errors erro-mediacao-senha"><a href="">Senha: <span class="errors-field ng-binding ng-scope"> O campo é obrigatório.</span></a></div>');
            } else {
                $('.erro-mediacao-senha').remove();
            }
        });
        setInterval(function(){

            // mediacao_autorizacao
            if ($('#mediacao-autorizacao').children().length < 1) {
                $('.erro-mediacao-autorizacao').remove();
                $('.ng-scope .registration-fieldset .errors-header').after('<div class="errors erro-mediacao-autorizacao"><a href="">Autorização: <span class="errors-field ng-binding ng-scope"> O campo é obrigatório.</span></a></div>');
            } else {
                $('.erro-mediacao-autorizacao').remove();
            }

            // mediacao_documento
            if ($('#mediacao-documento').children().length < 1) {
                $('.erro-mediacao-documento').remove();
                $('.ng-scope .registration-fieldset .errors-header').after('<div class="errors erro-mediacao-documento"><a href="">Documento: <span class="errors-field ng-binding ng-scope"> O campo é obrigatório.</span></a></div>');
            } else {
                $('.erro-mediacao-documento').remove();
            }

            // mediacao_senha
            if ($field_mediacao_senha.val().length < 6 || $field_mediacao_senha.val() == '""') {
                $('.erro-mediacao-senha').remove();
                $('.ng-scope .registration-fieldset .errors-header').after('<div class="errors erro-mediacao-senha"><a href="">Senha: <span class="errors-field ng-binding ng-scope"> O campo é obrigatório.</span></a></div>');
            } else {
                $('.erro-mediacao-senha').remove();
            }

            // mediacao_contato
            if ($normalize_field_mediacao_contato.length < 10) {
                $('.erro-mediacao-contato').remove();
                $('.ng-scope .registration-fieldset .errors-header').after('<div class="errors erro-mediacao-contato"><a href="">Número de telefone: <span class="errors-field ng-binding ng-scope"> O campo é obrigatório.</span></a></div>');
            } else {
                $('.erro-mediacao-contato').remove();
            }
            
            $errors = $('.registration-fieldset .errors');
            if($errors.length == 0) {
                $('.btn-validate').css('display', 'inline-block');
            } else {
                $('.btn-validate').css('display', 'none');
            }
        }, 1000);

    }
});