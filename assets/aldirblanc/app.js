/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./js/app.js":
/*!*******************!*\
  !*** ./js/app.js ***!
  \*******************/
/*! no static exports found */
/***/ (function(module, exports) {

$(document).ready(function () {
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
    params.opportunity = MapasCulturais.opportunityId;
  }

  if (MapasCulturais.opportunitiesInciso2 != null) {
    params.opportunitiesInciso2 = MapasCulturais.opportunitiesInciso2;
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
    var nomeCidade = $('.js-select-cidade option:selected').text();
    modal.css("display", "flex").hide().fadeIn(900);
    $('#modalAlertCadastro .modal-content').find('.js-confirmar').show(); //$('#modalAlertCadastro .modal-content').find('.js-title').text('Confirmação');

    modalTitle = "Confirmação";
    $('#modalAlertCadastro .modal-content').find('.btn').val('next');
    $('#modalAlertCadastro .modal-content').find('.btn').text('Confirmar');

    if (params.opportunity != null) {
      msg = "Voc\xEA est\xE1 solicitando o benef\xEDcio para <strong>_fomalizado_</strong> para espa\xE7o do tipo  <strong>_coletivo_</strong>_cidade_ <br><br><p>Voc\xEA confirma essas informa\xE7\xF5es?</p>";
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
        msg = "Voc\xEA est\xE1 solicitando o benef\xEDcio para <strong>_fomalizado_</strong> para espa\xE7o do tipo  <strong>_coletivo_</strong>_cidade_ <br><br><p>Voc\xEA confirma essas informa\xE7\xF5es?</p>";
        msg = msg.replace(/_fomalizado_/g, fomalizado);
        msg = msg.replace(/_coletivo_/g, coletivo);

        if (nomeCidade) {
          msg = msg.replace(/_cidade_/g, " na cidade de <strong>" + nomeCidade + "</strong>.");
        } else {
          msg = msg.replace(/_cidade_/g, ".");
        }
      } else {
        msg = 'Você precisa selecionar a cidade.';
        modalTitle = "Atenção";
      }
    }

    if ($("#select-cidade").length > 0) {
      selectedCityId = $('#select-cidade').val();
    } else {
      selectedCityId = $('.js-select-cidade option:selected').val();
    }

    var cityObj = MapasCulturais.opportunitiesInciso2.filter(function (city) {
      return city.id == selectedCityId;
    })[0];

    if (!(MapasCulturais.serverDate.date >= cityObj.registrationFrom.date && MapasCulturais.serverDate.date <= cityObj.registrationTo.date)) {
      modalTitle = cityObj.name;
      msg = "Infelizmente n\xE3o ser\xE1 possivel realizar sua inscri\xE7\xE3o:\n            <br>\n            <br>\n            > Data de inicio das inscri\xE7\xF5es: <strong> ".concat(new Date(cityObj.registrationFrom.date).toLocaleDateString("pt-BR"), " </strong>\n            <br>\n            <br>\n            > Data de fim das inscri\xE7\xF5es: <strong> ").concat(new Date(cityObj.registrationTo.date).toLocaleDateString("pt-BR"), " </strong>");
      $('.js-confirmar').hide();
    }

    showModalMsg(modalTitle, msg); //$('#modalAlertCadastro .modal-content').find('.modal-content-text').html(msg);

    $('.close, .btn-ok').on('click', function () {
      modal.fadeOut('slow');
    });
  }

  function showModalMsg(title, message) {
    var modal = $('#modalAlertCadastro');
    var text = document.getElementById("modal-content-text");
    $('#modalAlertCadastro .modal-content').find('.js-title').text(title);

    if (title != "Confirmação") {
      $('#modalAlertCadastro .modal-content').find('.btn').val('close');
      $('#modalAlertCadastro .modal-content').find('.btn').text('OK');
    }

    text.innerHTML = message;
    modal.fadeIn('fast');
    $('.close, .btn-ok').on('click', function () {
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


  function goToQuestionCounty() {
    var hide = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;

    if (hide) {
      $('.js-questions-tab').hide();
    }

    if (returning) {
      $('.js-questions-tab').hide();
      $('#select-cidade').fadeIn('fast');
      return;
    }

    var hasCities = $('.js-questions').find('#select-cidade');
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
      showModal();
    }
  }

  $('.coletivo').click(function () {
    coletivo = this.value;
    $('.coletivo').parent().removeClass('selected');
    $(this).parent().addClass('selected');
  });
  $('.formalizado').click(function () {
    formalizado = this.value;
    $('.formalizado').parent().removeClass('selected');
    $(this).parent().addClass('selected');
  });
  /**
   * Ao selecionar a cidade/opotunidade o usuário é redirecionado conforme os parametros selecionados.
   */

  $('.js-select-cidade').change(function () {
    params.opportunity = this.value;
  });
  $('.js-back').click(function () {
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
  $('.js-next').click(function () {
    var parentId = $(this).closest('.js-questions-tab').attr('id');

    if (parentId == 'local-atividade') {
      var hasSeletedColetivo = $('input[name=coletivo]:checked');

      if (hasSeletedColetivo.length > 0) {
        goToQuestionPersonality();
      } else {
        showModalMsg('Atenção!', 'Você precisa selecionar uma opção para avançar');
      }
    } else if (parentId == 'select-cidade') {
      showModal();
    } else {
      var hasSeletedFormalizado = $('input[name=formalizado]:checked');

      if (hasSeletedFormalizado.length > 0) {
        if ($('#select-cidade').lenght) {
          goToQuestionCounty();
        } else {
          goToQuestionCounty(false);
        }
      } else {
        showModalMsg('Atenção!', 'Você precisa selecionar uma opção para avançar');
      }
    }
  });
  $('button.js-confirmar').click(function () {
    if (this.value == 'next') {
      $('.js-questions-tab').hide();
      $('.js-questions').html('<h4>Enviando informações ...</h4>');
      $('#modalAlertCadastro').fadeOut('slow');
      goToNextPage();
    } else {
      $('#modalAlertCadastro').fadeOut('slow');
    }
  }); //Fechar modal ao clicar fora dela.

  $(window).click(function (event) {
    var modal = $('#modalAlertCadastro');

    if (event.target.value != 'next') {
      if ($(event.target).css('display') == 'flex') {
        modal.fadeOut('slow');
      }
    }
  });
  /**
   * Ao clicar nos cards do Inciso II, o usuário é encaminhado para tela de opções do local de atividade do beneficiário.
   */

  var selectedInciso = '';
  $('.js-lab-option').click(function () {
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

  $('.form-export-clear').on('click', function () {
    $('.form-export-dataprev').trigger("reset");
  });
});

/***/ }),

/***/ "./sass/app.scss":
/*!***********************!*\
  !*** ./sass/app.scss ***!
  \***********************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 0:
/*!*****************************************!*\
  !*** multi ./js/app.js ./sass/app.scss ***!
  \*****************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! /app/assets-src/js/app.js */"./js/app.js");
module.exports = __webpack_require__(/*! /app/assets-src/sass/app.scss */"./sass/app.scss");


/***/ })

/******/ });