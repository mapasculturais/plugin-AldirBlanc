<?php 
use MapasCulturais\i; 

?>

<a href="<?= $this->controller->createUrl( 'tipo', ['individual'] ) ?>" class="btn btn-primary"> <?php i::_e('Auxílio para trabalhador da Cultura') ?> </a>
<a href="<?= $this->controller->createUrl( 'tipo', ['coletivo'] ) ?>" class="btn btn-primary"> <?php i::_e('Auxílio para Grupos ou Espaços Culturais') ?> </a>