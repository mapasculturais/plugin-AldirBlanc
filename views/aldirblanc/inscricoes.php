<section id="lab-status" class="lab-main-content">
    <article class="main-content registration" ng-controller="OpportunityController">
        <table>
            <tr>
                <td>ID OPORTUNIDADE</td>
                <td>NOME DA OPORTUNIDADE</td>
                <td>QUANTIDADE</td>
            </tr>

            <?php foreach ($opportunities as $key => $value) { ?>
                <tr>
                    <td><?=$value['id']?></td>
                    <td><?=$value['name']?></td>
                    <td><?=$value['num']?></td>
                </tr>
            <?php  }?>
        </table>
    </article>
</section>
