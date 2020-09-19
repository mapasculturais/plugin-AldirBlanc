<?php

namespace AldirBlanc\Controllers;

use League\Csv\Writer;
use MapasCulturais\App;
use MapasCulturais\Entities\Registration;
use MapasCulturais\i;

/**
 * Registration Controller
 *
 * By default this controller is registered with the id 'registration'.
 *
 *  @property-read \MapasCulturais\Entities\Registration $requestedEntity The Requested Entity
 */
// class AldirBlanc extends \MapasCulturais\Controllers\EntityController {
class DataPrev extends \MapasCulturais\Controllers\Registration
{
    protected $config = [];

    public function __construct()
    {
        parent::__construct();

        $app = App::i();

        $this->config = $app->plugins['AldirBlanc']->config;
        $this->entityClassName = '\MapasCulturais\Entities\Registration';
        $this->layout = 'aldirblanc';
    }

    public function GET_export()
    {
        $this->requireAuthentication();
        $app = App::i();
        $app->user->is("admin");

        $opportunity_id = 1;
        $startDate = "2020-09-14 00:00";
        $finishDate = "2020-09-16 00:00";
        $dql = "SELECT
        e
        FROM MapasCulturais\Entities\Registration e
        WHERE e.sentTimestamp >= :startDate AND
        e.sentTimestamp <= :finishDate AND
        e.status = 1 AND
        e.opportunity = :opportunityId";

        $paramters = [
            'opportunityId' => 1,
            'startDate' => $startDate,
            'finishDate' => $finishDate,
        ];

        $opportunity = $app->repo('Opportunity')->find($opportunity_id);
        $this->registerRegistrationMetadata($opportunity);

        $query = $app->em->createQuery($dql)->setParameters($paramters);
        $registrations = $query->getResult();

        $registrations = $app->repo("Registration")->findBy([
            "opportunity" => $opportunity_id,
            "status" => 1,
        ]);

        $csv = Writer::createFromString("");

        /**
         * Prepara o Header do documento
         */
        $csv->insertOne([
            "CPF",
            "SEXO",
            "FLAG_CAD_ESTADUAL",
            "SISTEMA_CAD_ESTADUAL",
            "IDENTIFICADOR_CAD_ESTADUAL",
            "FLAG_CAD_MUNICIPAL",
            "SISTEMA_CAD_MUNICIPAL",
            "IDENTIFICADOR_CAD_MUNICIPAL",
            "FLAG_CAD_DISTRITAL",
            "SISTEMA_CAD_DISTRITAL",
            "IDENTIFICADOR_CAD_DISTRITAL",
            "FLAG_CAD_SNIIC",
            "SISTEMA_CAD_SNIIC",
            "IDENTIFICADOR_CAD_SNIIC",
            "FLAG_CAD_SALIC",
            "FLAG_CAD_SICAB",
            "FLAG_CAD_OUTROS",
            "SISTEMA_CAD_OUTROS",
            "IDENTIFICADOR_CAD_OUTROS",
            "FLAG_ATUACAO_ARTES_CENICAS",
            "FLAG_ATUACAO_AUDIOVISUAL",
            "FLAG_ATUACAO_MUSICA",
            "FLAG_ATUACAO_ARTES_VISUAIS",
            "FLAG_ATUACAO_PATRIMONIO_CULTURAL",
            "FLAG_ATUACAO_MUSEUS_MEMORIA",
            "FLAG_ATUACAO_HUMANIDADES",
            "FAMILIARCPF",
            "GRAUPARENTESCO",
        ]);

        /**
         * Mapeamento de campos
         */
        $fields = [
            "CPF" => function ($registrations) {
                return str_replace(['.', '-'], '', $registrations->field_30);
            },
            'SEXO' => function ($registrations) {
                if ($registrations->field_17 == 'Masculino') {
                    return 1;
                } else if ($registrations->field_17 == 'Feminino') {
                    return 2;
                } else {
                    return 0;
                }
            },
            "FLAG_CAD_ESTADUAL" => 1,
            "SISTEMA_CAD_ESTADUAL" => function ($registration) {
                $app = \MapasCulturais\App::i();
                return $app->view->dict('site: name', false);
            },
            "IDENTIFICADOR_CAD_ESTADUAL" => 'number',
            "FLAG_CAD_MUNICIPAL" => 0,
            "SISTEMA_CAD_MUNICIPAL" => null,
            "IDENTIFICADOR_CAD_MUNICIPAL" => null,
            "FLAG_CAD_DISTRITAL" => 0,
            "SISTEMA_CAD_DISTRITAL" => null,
            "IDENTIFICADOR_CAD_DISTRITAL" => null,
            "FLAG_CAD_SNIIC" => null,
            "SISTEMA_CAD_SNIIC" => null,
            "IDENTIFICADOR_CAD_SNIIC" => null,
            "FLAG_CAD_SALIC" => 0,
            "FLAG_CAD_SICAB" => 0,
            "FLAG_CAD_OUTROS" => 0,
            "SISTEMA_CAD_OUTROS" => null,
            "IDENTIFICADOR_CAD_OUTROS" => null,
            "FLAG_ATUACAO_ARTES_CENICAS" => 0,
            "FLAG_ATUACAO_AUDIOVISUAL" => 0,
            "FLAG_ATUACAO_MUSICA" => 0,
            "FLAG_ATUACAO_ARTES_VISUAIS" => 0,
            "FLAG_ATUACAO_PATRIMONIO_CULTURAL" => 0,
            "FLAG_ATUACAO_MUSEUS_MEMORIA" => 0,
            "FLAG_ATUACAO_HUMANIDADES" => 0,
            "FAMILIARCPF" => 1,
            "GRAUPARENTESCO" => 1,
        ];

        //Itera sobre os registros mapeados
        $data = [];
        foreach ($registrations as $key => $registration) {

            foreach ($fields as $csv_column => $column) {
                if (is_callable($column)) {
                    $data[$key][$csv_column] = $column($registration);
                } else if (is_string($column) && strlen($column) > 0) {
                    $data[$key][$csv_column] = $registration->$column;
                } else {
                    $data[$key][$csv_column] = $column;
                }
            }

            $csv->insertOne($data[$key]);
        }
        
        //Faz o output do CSV
        $csv->output("user.csv");
    }
}
