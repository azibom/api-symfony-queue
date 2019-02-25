<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SmsController extends Controller 
{
    /**
     * send data to queue and show data
     *
     * @param [string] $number
     * @param [string] $body
     * @return Response
     */
    public function Send($number, $body)
    {
        $data = array(
            'number' => $number,
            'body' => $body
        );

        $jsonData = json_encode($data);

        $this->get('old_sound_rabbit_mq.send_producer')
             ->publish($jsonData);

        return new Response(
            "<h1>
            We send your message:<br>
            body:  {$body}<br>
            number: {$number}<br>
            <h1>"
        );
    }
}