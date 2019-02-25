<?php

namespace App\Consumer;

use PhpAmqpLib\Message\AMQPMessage;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Sms;
use App\Entity\Api;


class SendConsumer extends AbstractController implements ConsumerInterface
{

    public function execute(AMQPMessage $jsonData)
    {
        $response = json_decode($jsonData->body, true);
        $this->sendApi($response);
    }

    /**
     * send to apis
     *
     * @return void
     */
    private function sendApi($response) {
        
        $data = array(
            'number' => $response['number'],
            'body' => $response['body']
        );

        $urls = array(
            "https://avazeshkon.ir/api.php",
            "https://avazeshkon.ir/api.php",
        );

        $sendOrNot = 0;
        $status = array();
        foreach($urls as $url)
        {
            $thisStatus = $this->api($url, $data);
            $status[] = array($url => $thisStatus);

            if($thisStatus == 200)
                $sendOrNot = 1;

            // log datas
            $this->apiLogs($url, $thisStatus, $sendOrNot);

            if($sendOrNot) 
                break;
        }

        $jsonStats = json_encode($status);

         // log datas
        $this->smsLogs($data['number'], $data['body'], $jsonStats, $sendOrNot);
    }

    /**
     * api request
     *
     * @return integer
     */
    public function api($url, $data)
    {
        $success = 1;
        try {
            $ch = curl_init($url);      
            curl_setopt($ch, CURLOPT_POST, true);                                                                    
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $responseBody = json_decode($response);
            curl_close($ch);
        } catch(Exeption $e) {
            $success = 0;
            $error = $e->getMessage();
        }

        if($success)
            return $httpCode;
        else 
            return 404;
    }

    /**
     * insert data
     *
     * @return void
     */
    public function apiLogs($url, $thisStatus, $sendOrNot)
    {
        $entityManager = $this->getDoctrine()->getManager();
        date_default_timezone_set('Asia/Tehran');
        $time = date("Y-m-d H:i:s"); 

        $api = new Api();
        $api->setApiUrl($url);
        $api->setStatus($thisStatus);
        $api->setCreatedAt($time);
        $api->setSendOrNot($sendOrNot);
        $entityManager->persist($api);
        $entityManager->flush();
    }

    /**
     * insert data
     *
     * @return void
     */
    public function smsLogs($number, $body, $jsonStats, $sendOrNot)
    {
        $entityManager = $this->getDoctrine()->getManager();
        date_default_timezone_set('Asia/Tehran');
        $time = date("Y-m-d H:i:s"); 

        $sms = new Sms();
        $sms->setPhone($number);
        $sms->setBody($body);
        $sms->setStatus($jsonStats);
        $sms->setCreatedAt($time);
        $sms->setSendOrNot($sendOrNot);
        $entityManager->persist($sms);
        $entityManager->flush();

    }
}