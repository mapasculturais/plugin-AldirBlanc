<?php

namespace AldirBlanc\Controllers;

use DateInterval;
use DateTime;
use Exception;
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
class DataPrev_inciso2 extends \MapasCulturais\Controllers\Registration
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

    public function GET_export(){

         //Seta o timeout
         ini_set('max_execution_time', 0);
         ini_set('memory_limit','768M');
 
         $this->requireAuthentication();
         $app = App::i();
         if (!$app->user->is("admin")) {
             throw new Exception("Não autorizado");
         }

         //Oportunidade que a query deve filtrar
        $opportunity_id = $this->config['inciso2_opportunity_ids'];
        
        //Data ínicial que a query deve filtrar
        $startDate = new DateTime();
        $startDate = $startDate->sub(new DateInterval('P7D'))->format('Y-m-d 00:00'); //Retorna o startDate a 7 dias atraz

        //Data final que a query deve filtrar
        $finishDate = new DateTime();
        $finishDate = $finishDate->format('Y-m-d 23:59');

        //Satatus que a query deve filtrar
        $status = 1;

        //Inciso que a query deve filtrar
        $inciso = 1;

        /**
         * Recebe e verifica os dados contidos no endpoint
         * https://localhost:8080/dataprev_inciso2/export/opportunity:2/from:2020-09-01/to:2020-09-30/
         * @var string $startDate
         * @var string $finishDate
         * @var \DateTime $date
         */
        if (!empty($this->data)) {

            if (isset($this->data['from']) && isset($this->data['to'])) {

                if (!preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/", $this->data['from']) ||
                    !preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/", $this->data['to'])) {

                    throw new \Exception("O formato da data é inválido.");

                } else {
                    //Data ínicial
                    $startDate = new DateTime($this->data['to']);
                    $startDate = $startDate->format('Y-m-d 00:00');

                    //Data final
                    $finishDate = new DateTime($this->data['from']);
                    $finishDate = $finishDate->format('Y-m-d 23:59');
                }

            }

            //Pega o status do endpoint
            $status = isset($this->data['status']) && is_numeric($this->data['status']) ? $this->data['status'] : 1;

            //Pega a oportunidade do endpoint
            if(!isset($this->data['opportunity']) || empty($this->data['opportunity'])){
                throw new Exception("Informe a oportunidade! Ex.: opportunity:2");

            }elseif(!is_numeric($this->data['opportunity']) || !in_array($this->data['opportunity'],$this->config['inciso2_opportunity_ids'])){
                throw new Exception("Oportunidade inválida");

            }else{
                $opportunity_id = $this->data['opportunity'];
            }
        
        }else{
            throw new Exception("Informe a oportunidade! Ex.: opportunity:2");
            
        }

        /**
         * Pega a oprtunidade
         */
        $opportunity = $app->repo('Opportunity')->find($opportunity_id);
        $this->registerRegistrationMetadata($opportunity);
       
        var_dump($opportunity);
        exit();
        
    }

}