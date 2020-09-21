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
        $headers = [
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
        ];
        $csv->insertOne($headers);

        //Itera sobre os registros mapeados
        $data_candidate = [];
        $data_familyGroup = [];
        foreach ($registrations as $key => $registration) {

            /**
             * Mapeamento de campos
             */
            $fields = [
                "CPF" => function ($registration) {
                    return str_replace(['.', '-'], '', $registration->field_30);
                },
                'SEXO' => function ($registration) {
                    if ($registration->field_17 == 'Masculino') {
                        return 1;
                    } else if ($registration->field_17 == 'Feminino') {
                        return 2;
                    } else {
                        return 0;
                    }
                },
                "FLAG_CAD_ESTADUAL" => 1,
                "SISTEMA_CAD_ESTADUAL" => function () {
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
                "FLAG_ATUACAO_ARTES_CENICAS" => function ($registration) {
                    return in_array("Teatro", $registration->field_10) ? 1 : 0;
                },
                "FLAG_ATUACAO_AUDIOVISUAL" => function ($registration) {
                    return in_array("Audiovisual", $registration->field_10) ? 1 : 0;
                },
                "FLAG_ATUACAO_MUSICA" => function ($registration) {
                    return in_array("Música", $registration->field_10) ? 1 : 0;
                },
                "FLAG_ATUACAO_ARTES_VISUAIS" => function ($registration) {
                    return in_array("Artes Visuais", $registration->field_10) ? 1 : 0;
                },
                "FLAG_ATUACAO_PATRIMONIO_CULTURAL" => function ($registration) {
                    return in_array("*", $registration->field_10) ? 1 : "Informações";
                },
                "FLAG_ATUACAO_MUSEUS_MEMORIA" => function ($registration) {
                    return in_array("Museu", $registration->field_10) ? 1 : 0;
                },
                "FLAG_ATUACAO_HUMANIDADES" => function ($registration) {
                    return in_array("*", $registration->field_10) ? 1 : "Informações";
                },
                "FAMILIARCPF" => function ($registration) {
                    foreach($registration->field_5 as $key => $value){
                        return $value->cpf;
                    };
                },
                "GRAUPARENTESCO" => function ($registration) {
                    foreach($registration->field_5 as $key => $value){
                        return $value->relationship;
                    };
                },
            ];
            // var_dump($registration->field_5);
            // exit();
            foreach ($fields as $csv_column => $column) {
                if (is_callable($column)) {
                    if ($csv_column == "FAMILIARCPF" || $csv_column == "GRAUPARENTESCO") {
                        $data_candidate[$key][$csv_column] = null;
                        $data_familyGroup[$key][$csv_column] = $column($registration); 
                        // var_dump($column($registration));
                        // exit();                       
                    } else {
                        $data_candidate[$key][$csv_column] = $column($registration);
                        $data_familyGroup[$key][$csv_column] = null;
                    }

                } else if (is_string($column) && strlen($column) > 0) {
                    if (($csv_column == "FAMILIARCPF" || $csv_column == "GRAUPARENTESCO")) {
                        $data_candidate[$key][$csv_column] = null;
                        $data_familyGroup[$key][$csv_column] = null;
                    } else {
                        $data_candidate[$key][$csv_column] = $registration->$column;
                        $data_familyGroup[$key][$csv_column] = null;
                    }

                } else {
                    $data_candidate[$key][$csv_column] = $column;
                    $data_familyGroup[$key][$csv_column] = null;
                }
            }
            $csv->insertOne($data_candidate[$key]);
            $csv->insertOne($data_familyGroup[$key]);
           
        }

        
        //Faz o output do CSV
        $csv->output("user.csv");
    }
}
