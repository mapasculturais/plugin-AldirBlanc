
<div class="box">
    <h1><?= $titulo ?></h1>
    <div style="text-align:center">
        <img src="<?=$this->asset('aldirblanc/img/preamar1.png', false)?>" alt="Preamar de cultura e arte" style="width: 15%; margin-right: 10px;">
        <img src="<?=$this->asset('aldirblanc/img/preamar2.png', false)?>" alt="Preamar da leitura" style="width: 15%;">
    </div >  
    <br><br>
   <div>
        <p style="text-align:center; line-height:27px;">
            <?=$texto?>
        </p>
   </div>
    
    <!-- <a class="btn btn-accent btn-large" href="<?php echo $app->createUrl('aldirblanc') ?>"><?= $botao ?></a> -->
</div>