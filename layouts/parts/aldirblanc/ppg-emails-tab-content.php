<?php
$userID = $entity->user->id;
$pkPath = PRIVATE_FILES_PATH . "/aldirblanc/{$app->user->id}.pem";
$pkIsOld = file_exists($pkPath) && ((time() - filemtime($pkPath)) > 600);
$urlClean = $app->createUrl("aldirblanc", "cleanPPGEmailFiles");
$urlKeys = $app->createUrl("aldirblanc", "genKeys");
$urlProcess = $app->createUrl("aldirblanc", "processPPGEmails");
?>
<div id="ppg-emails" class="aba-content"><?php
    if ($pkIsOld) { ?>
        <span style="color: red;">Limpe os arquivos antes de continuar.</span>
        <br><?php
    } ?>
    <a class="btn btn-default btn-export-cancel" href="<?=$urlClean?>" rel="noopener">Limpar arquivos</a><?php
    if (!$pkIsOld) { ?>
        <a class="btn btn-primary download" href="<?=$urlKeys?>" rel="noopener noreferrer">Gerar chaves</a>
        <br><br>
        <div>
            <h3>Instruções:</h3>
            <ol>
                <li>clique no botão "Gerar chaves" e salve o arquivo pub-<?=$userID?>.pem</li>
                <li>você tem 10 minutos para usar a chave salva</li>
                <li>crie uma chave aleatória localmente
                    <pre>openssl rand -hex -out otk.bin 64</pre>
                </li>
                <li>criptografe o arquivo remessa a ser enviado com a chave aleatória e tome nota do IV retornado
                    <pre>openssl enc -aes-256-cbc -salt -in txt.txt -out txt.enc -K $(cat otk.bin) -iv $(openssl rand -hex 64) -p</pre>
                </li>
                <li>criptografe a chave aleatória com a chave salva
                    <pre>openssl rsautl -encrypt -inkey pub-<?=$userID?>.pem -pubin -in otk.bin -out otk.bin.enc</pre>
                </li>
                <li>envie a
                    <a class="js-open-editbox hltip" data-target="#editbox-key-file"
                       href="#" title="Clique para enviar a chave">chave aleatória criptografada</a></li>
                <li>envie o
                    <a class="js-open-editbox hltip" data-target="#editbox-remittance-file"
                       href="#" title="Clique para enviar o arquivo remessa">arquivo remessa criptografado</a></li><?php
                    if ((sizeof($uploadedFiles) > 1) && !empty($files)) { ?>
                        <li>escolha os arquivos nos drop-down abaixo e envie o formulário</li>
                        <li>os arquivos são automaticamente apagados se o processamento chegar ao final e não estiver em modo de simulação</li><?php
                    } else { ?>
                        <li>recarregue a página para continuar ou clique no botão "Limpar arquivos" acima se for interromper o processo</li><?php
                    } ?>
            </ol>
        </div><?php
        if ((sizeof($uploadedFiles) > 1) && !empty($files)) { ?>
            <hr>
            <form action="<?=$urlProcess?>" method="POST">
                <label for="key">Escolha o arquivo com a chave:</label>
                <select name="key"><?php
                    foreach ($uploadedFiles as $file) {
                        ?><option value="<?=$file->id?>">[<?=$file->id?>] <?=$file->name?></option><?php
                    } ?>
                </select>
                <br>
                <label for="source">Escolha o arquivo remessa:</label>
                <select name="source"><?php
                    foreach ($uploadedFiles as $file) {
                        ?><option value="<?=$file->id?>">[<?=$file->id?>] <?=$file->name?></option><?php
                    } ?>
                </select>
                <br>
                <label for="response">Escolha o arquivo retorno:</label>
                <select name="response"><?php
                    foreach ($files as $file) { ?>
                        <option value="<?=$file->id?>">[<?=$file->id?>] <?=$file->name?></option><?php
                    } ?>
                </select>
                <br>
                <label for="ivhex">Informe o IV utilizado:</label>
                <input type="text" name="ivhex" pattern="[A-Fa-f0-9]{32}" minlength="32" maxlength="32">
                <br>
                <label for="dryrun">Simular?</label>
                <input type="checkbox" name="dryrun">
                <br><br>
                <button class="btn btn-primary" type="submit">Enviar</button>
            </form><?php
        } ?>
        <div id="editbox-key-file" class="js-editbox mc-right" title="Enviar chave criptografada" data-submit-label="Enviar">
            <?php $this->ajaxUploader($entity, "email-files", "append", "ul.js-keyfile", "", "", false, false, false)?>
        </div>
        <div id="editbox-remittance-file" class="js-editbox mc-right" title="Enviar arquivo remessa de desbancarizados" data-submit-label="Enviar">
            <?php $this->ajaxUploader($entity, "email-files", "append", "ul.js-remittance", "", "", false, false, false)?>
        </div><?php
    } ?>
</div>
