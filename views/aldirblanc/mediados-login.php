<article class="main-content moderator">

    <h1 class="featured-title">Entre para acessar seu inscrição</h1>
    <h4 class="title-subsection">Apenas para inscrições feitas por um mediador</h4>

    <div class="registration-fieldset registration-fieldset-moderator">

        <form action="" method="POST">
            <div class="each-field">
                <label for="cpf">CPF</label>
                <input type="text" name="cpf" id="documento" value="<?= $data['cpf'] ?? ''; ?>">
            </div>

            <div class="each-field">
                <label for="password">Senha</label>
                <input type="password" name="password" id="password" value="<?= $data['password'] ?? ''; ?>">
            </div>

            <button class="btn btn-secondary" type="submit">Enviar</button>
        </form>

        <div class="display-error">
            <?php foreach ($errors as $error => $message) : ?>
                <span class="error-login"><?= $message ?></span>
            <?php endforeach; ?>
        </div>

    </div><!-- /.registration-fieldset -->

</article>