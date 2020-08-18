<?php

$can_edit_roles = $this->controller->id == 'agent' && $entity->user->id != $app->user->id && $entity->id == $entity->user->profile->id && $entity->user->canUser('addRole');
if(true){
    $classes = 'editable-entity-edit';
    if($can_edit_roles)
        $classes .= ' can-edit-roles';
}else{
    $classes = 'editable-entity-single';
}

$class = $entity->getClassName();
$params = [
    'entity' => $entity,
    'status_draft' => $class::STATUS_DRAFT,
    'status_enabled' => $class::STATUS_ENABLED,
    'status_trash' => $class::STATUS_TRASH,
    'status_archived' => $class::STATUS_ARCHIVED
];
?>

<div id="editable-entity" class="clearfix sombra <?php echo $classes ?>" data-action="edit" data-entity="registration" data-id="<?php echo $entity->id ?>">
    <?php $this->part('editable-entity-logo') ?>
    <div class="controles">

    <?php $this->part('aldirblanc/control--edit-buttons', $params) ?>

    </div>
</div>
