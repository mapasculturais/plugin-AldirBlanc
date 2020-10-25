<section class="lab-main-content cadastro ">
    <header>
        <div class="intro-message">
        </div>
    </header>

    <div class="js-lab-item lab-item cadastro-options">
        <!-- <p class="lab-form-question">Para quem você está solicitando o benefício? <a class="js-help icon icon-help" href="#" title=""></a></p> -->
        <h2 class="featured-title">
            Entre para acessar seu cadastro
        </h2>

        <div class="lab-form-filter opcoes-inciso">
           <form class="" action="" method="POST">
                
                <label for="cpf">CPF</label>
                <input type="text" name="cpf" id="documento" value="<?=$data['cpf'] ?? '';?>">
                <br/>
                <label for="password">Senha</label>  
                <input type="password" name="password" id="password" value="<?=$data['password'] ?? '';?>">
                <br/>

                <button class="btn btn-primary" type="submit">Enviar</button>
            </form>
            <?php 
                foreach ($errors as $error => $message) {?>
                    <h3>
                        <?=$message?>
                    </h3>
                <?php
                }
               ?>
        </div>

    </div><!-- End .lab-item -->


</section>

