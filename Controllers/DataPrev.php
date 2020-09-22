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

        /**
         * Header do documento
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

        /**
         * Mapeamento de campos do documento
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
            "FLAG_ATUACAO_ARTES_CENICAS" => function ($registrations) {
                return in_array("Teatro", $registrations->field_10) ? 1 : 0;
            },
            "FLAG_ATUACAO_AUDIOVISUAL" => function ($registrations) {
                return in_array("Audiovisual", $registrations->field_10) ? 1 : 0;
            },
            "FLAG_ATUACAO_MUSICA" => function ($registrations) {
                return in_array("Música", $registrations->field_10) ? 1 : 0;
            },
            "FLAG_ATUACAO_ARTES_VISUAIS" => function ($registrations) {
                return in_array("Artes Visuais", $registrations->field_10) ? 1 : 0;
            },
            "FLAG_ATUACAO_PATRIMONIO_CULTURAL" => function ($registrations) {
                return in_array("*", $registrations->field_10) ? 1 : "Informações";
            },
            "FLAG_ATUACAO_MUSEUS_MEMORIA" => function ($registrations) {
                return in_array("Museu", $registrations->field_10) ? 1 : 0;
            },
            "FLAG_ATUACAO_HUMANIDADES" => function ($registrations) {
                return in_array("*", $registrations->field_10) ? 1 : "Informações";
            },
            "FAMILIARCPF" => 'field_5',
            "GRAUPARENTESCO" => 'field_5',
        ];

        //Itera sobre os registros mapeados
        $data_candidate = [];
        $data_familyGroup = [];
        foreach ($registrations as $key_registrations => $registration) {
            foreach ($fields as $key_fields => $column) {
                if ($key_fields != "FAMILIARCPF" && $key_fields != "GRAUPARENTESCO") {
                    if (is_callable($column)) {
                        $data_candidate[$key_registrations][$key_fields] = $column($registration);
                    } else if (is_string($column) && strlen($column) > 0) {
                        $data_candidate[$key_registrations][$key_fields] = $registration->$column;
                    } else {
                        $data_candidate[$key_registrations][$key_fields] = $column;
                    }
                } else {
                    $data_candidate[$key_registrations][$key_fields] = null;
                    foreach ($registration->$column as $key_familyGroup => $familyGroup) {
                        foreach ($headers as $key => $value) {
                            if ($value == "FAMILIARCPF") {
                                $data_familyGroup[$key_registrations][$key_familyGroup][$value] = $familyGroup->cpf;
                            } elseif ($value == "GRAUPARENTESCO") {
                                $data_familyGroup[$key_registrations][$key_familyGroup][$value] = $familyGroup->relationship;
                            } else {
                                $data_familyGroup[$key_registrations][$key_familyGroup][$value] = null;
                            }
                        }

                    }
                }
            }
        }

        /**
         * Prepara as linhas do CSV
         */
        foreach ($data_candidate as $key_candidate => $candidate) {
            $data[] = $candidate;
            if (isset($data_familyGroup[$key_candidate])) {
                foreach ($data_familyGroup[$key_candidate] as $key_familyGroup => $familyGroup) {
                    foreach ($headers as $key_header => $header) {
                       if ($header == "FAMILIARCPF") {
                            $data[] = $familyGroup;
                        }
                    }
                }
            }
        }

        /**
         * Cria o CSV
         */
        $csv = Writer::createFromString("");
        $csv->insertOne($headers);
        foreach ($data as $key_csv => $csv_line) {
            $csv->insertOne($csv_line);
        }
        $csv->output("user.csv");
    }

}
