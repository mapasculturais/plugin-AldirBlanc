<?php

namespace RegistrationPayments;

use MapasCulturais\i;

?>

<label><span class="label"> <?php i::_e("Observações"); ?></span>
    <textarea ng-if="data.multiplePayments == false" ng-model="data.editPayment.metadata.csv_line.OBSERVACOES" cols="90" rows="5"></textarea>
    <textarea ng-if="data.multiplePayments == true" ng-model="data.editMultiplePayments.metadata.csv_line.OBSERVACOES" cols="90" rows="5"></textarea>
</label>
