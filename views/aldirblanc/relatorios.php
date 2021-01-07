<?php 
namespace Relatorios;

function print_table($title, $data, $max_values_to_chart, $print_total = false) {
    $statuses = array_keys($data);
    $values = [];
    $total = [];

    $id = uniqid();
    ?>
    <section class="data">
        <h2><?=$title?></h2>
        <div id="container-<?=$id?>"></div>
        <table id="table-<?=$id?>" class="datatable">
            <tr>
                <td>&nbsp;</td>
                <?php foreach($statuses as $col): $values = array_unique(array_merge($values, array_keys($data[$col])))?>
                    <td><?= $col ?></td>
                <?php endforeach ?>
            </tr>

            <?php foreach($values as $val): ?>
                <tr>
                    <td><?= $val ? $val : '<em>Não Informado</em>' ?></td>
                    <?php foreach($statuses as $status): $total[$status] = @$total[$status] += $data[$status][$val] ?? 0?>
                        <td> <?= $data[$status][$val] ?? 0 ?> </td>
                    <?php endforeach ?>
                </tr>
            <?php endforeach; ?>

            <?php if($print_total): ?>
            <tr>
                <td><strong>TOTAL</strong></td>
                <?php foreach($statuses as $status): ?>
                    <td><?= $total[$status] ?? 0 ?></td>
                <?php endforeach ?>
            </tr>
            <?php endif; ?>
        </table>
        
        
        <?php if(count($values) <= $max_values_to_chart): ?>
        <script>
        Highcharts.chart('container-<?=$id?>', {
            data: {
                table: 'table-<?=$id?>',
                switchRowsAndColumns: true,
            },
            chart: {
                type: 'column'
            },
            title: {
                text: ''
            },
            yAxis: {
                allowDecimals: false,
                title: {
                    text: 'Units'
                }
            },
            tooltip: {
                formatter: function () {
                    return '<b>' + this.series.name + '</b><br/>' +
                        this.point.y + ' ' + this.point.name.toLowerCase();
                }
            }
        });
        </script>
        <?php endif; ?>
    </section>
    <?php
}
?>

<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/data.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
<style>
    h1 {
        text-align: center;
        margin: 1em;
    }
    h2 {
        margin-top: 2em;
        text-align: center;
    }

    section.data {
        margin:2em;
    }

    #container {
        height: 400px;
    }

    .highcharts-figure, .highcharts-data-table table {
        min-width: 310px;
        max-width: 800px;
        margin: 1em auto;
    }

    .datatable {
        font-family: Verdana, sans-serif;
        border-collapse: collapse;
        border: 1px solid #EBEBEB;
        margin: 10px auto;
        text-align: center;
        width: 100%;
    }
    .datatable caption {
        padding: 1em 0;
        font-size: 1.2em;
        color: #555;
    }
    .datatable th {
        font-weight: 600;
        padding: 0.5em;
    }
    .datatable td, .datatable th, .datatable caption {
        padding: 0.5em;
    }
    .datatable thead tr, .datatable tr:nth-child(even) {
        background: #f8f8f8;
    }
    .datatable tr:hover {
        background: #f1f7ff;
    }
</style>

<h1><?=$title?></h1>

<figure class="highcharts-figure">
    <?php foreach($rel_data as $data): ?>
        <?php print_table($data['name'], $data['data'], $data['max_chart'] ?? 15) ?>
    <?php endforeach; ?>
</figure>