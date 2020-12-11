<section id="lab-status" class="lab-main-content">
    <article class="main-content registration" ng-controller="OpportunityController">
        <table class ="js-registration-list registrations-table no-options">
            <tr>
                <td>ID OPORTUNIDADE</td>
                <td>NOME DA OPORTUNIDADE</td>
                <td>QUANTIDADE</td>
                <td>CPF</td>
                <td>CNPJ</td>
            </tr>

            <?php foreach ($opportunities as $key => $value) { ?>
                <tr>
                    <td><?=$value['id']?></td>
                    <td><?=$value['name']?></td>
                    <td><?=$value['num']?></td>
                    <td><a href="/dataprev/export_inciso2/opportunity:<?=$value['id']?>/type:cpf">CPF</a></td>
                    <td><a href="/dataprev/export_inciso2/opportunity:<?=$value['id']?>/type:cnpj">CNPJ</a></td>
                </tr>
            <?php  }?>
        </table>
    </article>
</section>
