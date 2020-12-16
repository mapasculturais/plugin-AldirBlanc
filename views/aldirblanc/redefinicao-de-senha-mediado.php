
<section id="main-section" class="clearfix" style="text-align: center;">
    <section class="lab-main-content cadastro ">
    <header>
        <div class="intro-message">
            </div>
        </header>
        <strong>redefinição de senha de mediados</strong>
        <h2 class="featured-title"><?= $registration->number ?></h2>
        <h3><?=$registration->owner->name ?></h3>
        <h5><?=$registration->owner->documento ?></h5>

        <form class="js-mediados-change-password">
            <p>
                <label>
                    nova senha:<br>
                    <input type="password" id="pass1"/>
                </label>
            </p>
            <p>
                <label>
                    repita a nova senha:<br>
                    <input type="password" id="pass2"/>
                </label>
            </p>
            
            <button>Alterar</button>
        </form>
    </section>
</section>
<script>
    $(()=> {
        var $form = $('.js-mediados-change-password');
        var $button = $('.js-mediados-change-password button');

        var $field1 = $('#pass1');
        var $field2 = $('#pass2');

        $form.on('submit', function(e) {
            e.preventDefault();
            if ($field1.val() != $field2.val()) {
                alert('As senhas informadas não são iguais');
                return;
            }
            
            if ($field1.val().trim().length < 5) {
                alert('A senha deve ter no mínimo 5 caracteres');
                return;
            }

            $.post(document.location, {senha: $field1.val().trim()}, function(response) {
                $form.replaceWith('<h1>SENHA ALTERADA COM SUCESSO</h1>');
            }).fail(function(r){
                console.log(r);
                alert(r.responseJSON.data);
            });
        });
    });
</script>