<!-- referencia para esse css: https://www.w3schools.com/howto/tryit.asp?filename=tryhow_js_form_steps -->
<style>
    * {
        box-sizing: border-box;
    }

    body {
        background-color: #f1f1f1;
    }

    #regForm {
        background-color: #ffffff;
        margin: 100px auto;
        font-family: Raleway;
        padding: 40px;
        width: 70%;
        min-width: 300px;
    }

    h1 {
        text-align: center;
    }

    input {
        padding: 10px;
        width: 100%;
        font-size: 17px;
        font-family: Raleway;
        border: 1px solid #aaaaaa;
    }

    /* Mark input boxes that gets an error on validation: */
    input.invalid {
        background-color: #ffdddd;
    }

    /* Hide all steps by default: */
    .tab {
        display: none;
    }

    button {
        background-color: #4CAF50;
        color: #ffffff;
        border: none;
        padding: 10px 20px;
        font-size: 17px;
        font-family: Raleway;
        cursor: pointer;
    }

    button:hover {
        opacity: 0.8;
    }

    #prevBtn {
        background-color: #bbbbbb;
    }

    /* Make circles that indicate the steps of the form: */
    .step {
        height: 15px;
        width: 15px;
        margin: 0 2px;
        background-color: #bbbbbb;
        border: none;
        border-radius: 50%;
        display: inline-block;
        opacity: 0.5;
    }

    .step.active {
        opacity: 1;
    }

    /* Mark the steps that are finished and valid: */
    .step.finish {
        background-color: #4CAF50;
    }
</style>

<div ng-if="data.fields.length > 0" id="registration-attachments" class="registration-fieldset registration-edit-mode">

    <div id="regForm" action="#" ng-controller="RegistrationFieldsController">
        <h1>Registro:</h1>

        <!-- One "tab" for each step in the form: -->
        <div class="tab" ng-repeat="groupFields in data.groupFields" >:
            
            <div ng-repeat="field in groupFields">

                <ul class="attachment-list">

                    <li on-repeat-done="registration-fields" class="attachment-list-item registration-edit-mode attachment-list-item-type-{{field.fieldType}}">

                        <?php 
                        $definitions = \MapasCulturais\App::i()->getRegisteredRegistrationFieldTypes();

                            foreach($definitions as $def) {
                                $this->part($def->viewTemplate);
                            }
                        ?>

                        <div ng-if="field.fieldType === 'file'" id="registration-file-{{field.id}}">
                            <div class="label"> {{field.title}} {{field.required ? '*' : ''}}</div>
                            <div class="attachment-description">
                                <span ng-if="field.description">{{field.description}}</span>
                                <span ng-if="field.template">
                                    (<a class="attachment-template" target="_blank" href="{{field.template.url}}" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("baixar modelo"); ?></a>)
                                </span>
                            </div>
                            <a ng-if="field.file" class="attachment-title" href="{{field.file.url}}" target="_blank" rel='noopener noreferrer'>{{field.file.name}}</a>

                            <div class="btn-group">
                                <!-- se já subiu o arquivo-->
                                <!-- se não subiu ainda -->
                                <a class="btn btn-default hltip" ng-class="{'send':!field.file,'edit':field.file}" ng-click="openFileEditBox(field.id, $index, $event)" title="{{!field.file ? 'enviar' : 'editar'}} <?php \MapasCulturais\i::_e("anexo"); ?>">{{!field.file ? 'Enviar' : 'Editar'}}</a>
                                <a class="btn btn-default delete hltip" ng-if="!field.required && field.file" ng-click="removeFile(field.id, $index)" title="<?php \MapasCulturais\i::esc_attr_e("excluir anexo"); ?>"><?php \MapasCulturais\i::_e("Excluir"); ?></a>
                            </div>

                            <edit-box id="editbox-file-{{field.id}}" position="bottom" title="{{field.title}} {{field.required ? '*' : ''}}" cancel-label="<?php \MapasCulturais\i::esc_attr_e("Cancelar"); ?>" submit-label="<?php \MapasCulturais\i::esc_attr_e("Enviar anexo"); ?>" loading-label="<?php \MapasCulturais\i::esc_attr_e("Carregando ..."); ?>" on-submit="sendFile" close-on-cancel='true' index="{{$index}}" spinner-condition="data.uploadSpinner">

                                <form class="js-ajax-upload" method="post" action="{{uploadUrl}}" data-group="{{field.groupName}}" enctype="multipart/form-data">
                                    <div class="alert danger hidden"></div>
                                    <p class="form-help"><?php \MapasCulturais\i::_e("Tamanho máximo do arquivo:"); ?> {{maxUploadSizeFormatted}}</p>
                                    <input type="file" name="{{field.groupName}}" />

                                    <div class="js-ajax-upload-progress">
                                        <div class="progress">
                                            <div class="bar"></div>
                                            <div class="percent">0%</div>
                                        </div>
                                    </div>
                                </form>
                            </edit-box>
                        </div>
                    </li>
                </ul>

            </div>
            
        </div>

        <div style="overflow:auto;">
            <div style="float:right;">
                <button type="button" id="prevBtn" onclick="nextPrev(-1)">Anterior</button>
                <button type="button" id="nextBtn" onclick="nextPrev(1)">Proximo</button>

                <div id="showButtonFinishRegistration">
                    <?php $this->part('singles/registration-edit--send-button', ['entity' => $entity,'action' => $action,'opportunity' => $entity->opportunity]) ?>
                </div>
            </div>
        </div>

        <!-- Circles which indicates the steps of the form: -->
        <div style="text-align:center;margin-top:40px;">
            <span class="step" ng-repeat="groupFields in data.groupFields"></span>
        </div>
    </div>
    
</div>


<!-- referencia para esse JS : https://www.w3schools.com/howto/tryit.asp?filename=tryhow_js_form_steps -->
<script>
    $(document).ready(function() {
        showTab(currentTab); // Display the current tab
    });

    function showTab(n) {
            // This function will display the specified tab of the form...
            var x = document.getElementsByClassName("tab");
            x[n].style.display = "block";
            //... and fix the Previous/Next buttons:
            if (n == 0) {
                document.getElementById("prevBtn").style.display = "none";
            } else {
                document.getElementById("prevBtn").style.display = "inline";
            }
            if (n == (x.length - 1)) {
                // document.getElementById("nextBtn").innerHTML = "Submit";
                document.getElementById("showButtonFinishRegistration").style.display = "inline";
                document.getElementById("nextBtn").style.display = "none";
            } else {
                document.getElementById("showButtonFinishRegistration").style.display = "none";
                document.getElementById("nextBtn").style.display = "inline";
                document.getElementById("nextBtn").innerHTML = "Proximo";
            }
            //... and run a function that will display the correct step indicator:
            fixStepIndicator(n)
        }

        function nextPrev(n) {
            // This function will figure out which tab to display
            var x = document.getElementsByClassName("tab");
            // Exit the function if any field in the current tab is invalid:
            if (n == 1 && !validateForm()) return false;
            // Hide the current tab:
            x[currentTab].style.display = "none";
            // Increase or decrease the current tab by 1:
            currentTab = currentTab + n;
            // if you have reached the end of the form...
            if (currentTab >= x.length) {
                // ... the form gets submitted:
                document.getElementById("regForm").submit();
                return false;
            }
            // Otherwise, display the correct tab:
            showTab(currentTab);
        }

        function validateForm() {
            // This function deals with validation of the form fields
            var x, y, i, valid = true;
            x = document.getElementsByClassName("tab");
            y = x[currentTab].getElementsByTagName("input");
            // A loop that checks every input field in the current tab:
            for (i = 0; i < y.length; i++) {
                // If a field is empty...
                if (y[i].value == "") {
                    // add an "invalid" class to the field:
                    y[i].className += " invalid";
                    // and set the current valid status to false
                    valid = false;
                }
            }
            // If the valid status is true, mark the step as finished and valid:
            if (valid) {
                document.getElementsByClassName("step")[currentTab].className += " finish";
            }
            return valid; // return the valid status
        }

        function fixStepIndicator(n) {
            // This function removes the "active" class of all steps...
            var i, x = document.getElementsByClassName("step");
            for (i = 0; i < x.length; i++) {
                x[i].className = x[i].className.replace(" active", "");
            }
            //... and adds the "active" class on the current step:
            x[n].className += " active";
        }


    var currentTab = 0; // Current tab is set to be the first tab (0)


</script>