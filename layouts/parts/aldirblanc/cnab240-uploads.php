<?php 
$app = MapasCulturais\App::i();

$files = $entity->getFiles('cnab240'); 

$url = $app->createUrl('remessas', 'importFileCnab240', ['opportunity' => $entity->id]);
$template = '
<li id="file-{{id}}" class="widget-list-item">
    <a href="{{url}}" rel="noopener noreferrer">{{description}}</a> 
    <div class="botoes">
        <a href="'.$url.'?file={{id}}" class="btn btn-primary hltip js-dataprev-process" data-hltip-classes="hltip-ajuda" title="Clique para processar o arquivo enviado">processar</a>
        <a data-href="{{deleteUrl}}" data-target="#file-{{id}}" data-configm-message="Remover este arquivo?" class="icon icon-close hltip js-remove-item" data-hltip-classes="hltip-ajuda" title="Excluir arquivo" rel="noopener noreferrer"></a>
    </div>
</li>';
?>
<?= $entity->inciso ?>
<div class="widget">
    <h3 class="editando">Arquivos CNAB240</h3>
    
    <div>
        <a class="add js-open-editbox hltip" data-target="#editbox-cnab240-file" href="#" title="Clique para adicionar subir novo arquivo de retorno do CNAB240"> subir arquivo</a>
    </div>
    <div id="editbox-cnab240-file" class="js-editbox mc-left" title="Subir arquivo de retorno do CNAB240" data-submit-label="Enviar">
        <?php $this->ajaxUploader($entity, 'cnab240', 'append', 'ul.js-dataprev', $template, '', false, false, false)?>
    </div>
    <ul class="widget-list js-dataprev js-slimScroll">
        <?php if(is_array($files)): foreach($files as $file): ?>
            <li id="file-<?php echo $file->id ?>" class="widget-list-item<?php if($this->isEditable()) echo \MapasCulturais\i::_e(' is-editable'); ?>" >
                <a href="<?php echo $file->url;?>"><span><?php echo $file->description ? $file->description : $file->name;?></span></a>
                <?php if($processed_at = $entity->cnab240_processed_files->{$file->name} ?? null): ?>
                    - processado em <?= $processed_at ?>
                <?php else: ?>
                <div class="botoes">
                    <a href="<?=$url?>?file=<?=$file->id?>" class="btn btn-primary hltip js-dataprev-process" data-hltip-classes="hltip-ajuda" title="Clique para processar o arquivo enviado">processar</a>
                    <a data-href="<?php echo $file->deleteUrl?>" data-target="#file-<?php echo $file->id ?>" data-configm-message="Remover este arquivo?" class="delete hltip js-remove-item" data-hltip-classes="hltip-ajuda" title="Excluir arquivo. Só é possível fazer esta ação antes do processamento."></a>
                </div>
                <?php endif; ?>
            
            </li>
        <?php endforeach; endif;?>
    </ul>
</div>
