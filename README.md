# api-symfony-queue
work with api

#### What do i want implement in this project
## We have some apis which are for sending sms and i want to make a program that give you a phone number and a body and send it with with one is available
# This project
## Put datas on queue
## Try to send some smses which it can't send at the last time, every 60 min
## Has report page which use caching system and show you the details

#### What i used
#### -Symfony
#### -Redis
#### -Memcached
#### -Rabbitmq


## How run it
```php
php bin/console server:run
sudo bin/console rabbitmq:consumer schedule
sudo bin/console rabbitmq:consumer send
```
## Then do it
```php
http://localhost:8000/send/09100000000/hello
```
and you can see reports from this url
```php
http://localhost:8000/reports
```

## What is inside it
It is first func
It add requests to our queue for sending data by api 
```php
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
```

It is inside the queue func
```php
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
    
  ..................
  ```
  
  
  
About schedule task you should add some things for run schedule, for more detaile look at these links
```php
https://github.com/rewieer/TaskSchedulerBundle
https://github.com/glooby/task-bundle
https://vitux.com/how-to-schedule-tasks-on-ubuntu-using-crontab/
```

It is inside the task
```php


<?php

namespace App\task;

use App\Entity\Sms;
use Rewieer\TaskSchedulerBundle\Task\Schedule;
use Rewieer\TaskSchedulerBundle\Task\AbstractScheduledTask;

class Task extends AbstractScheduledTask {

  protected function initialize(Schedule $schedule) {
    $schedule
      ->everyMinutes(60); // Perform the task every 60 minutes
  }


  /**
   * run schedule
   *
   * @return void
   */
  public function run() {

    $repository = $this->getDoctrine()->getRepository(Sms::class);
    $datas = $repository->findBy(
        ['sendOrNot' => 0]

    );

    foreach($datas as $data)
    {
        $data = array(
            'number' => $data->getPhone(),
            'body' => $data->getBody(),
            'id' => $data->getId()
        );

        $jsonData = json_encode($data);

        $this->get('old_sound_rabbit_mq.schedule_producer')
             ->publish($jsonData);
    }
  }
}

```


And for caching system you can see below codes
```php

    /**
     * save item in cache system
     *
     * @return array
     */
    public function saveItem($key, $value)
    {
        return $this->cacheUtil->saveItem($this->cachePool, $key, $value);
    }
 
    /**
     * get item from cache system
     *
     * @return array
     */
    public function getItem($key)
    {
        return $this->cacheUtil->getItem($this->cachePool, $key);
    }
 
    /**
     * delete item from cache system
     *
     * @return void
     */
    public function deleteItem($key)
    {
        return $this->cacheUtil->deleteItem($this->cachePool, $key);
    }
 
    /**
     * delete cache system
     *
     * @return void
     */
    public function deleteAll()
    {
        return $this->cacheUtil->deleteAll($this->cachePool);
    }


```
and for instance of caching
```php

    /**
     * return api Usage
     *
     * @return array
     */
    public function apiUsage()
    {
        $data = $this->getItem("apiUsage");
        if(isset($data))
        {
            $apiUsage = $data["apiUsage"];
        } else {
            $conn = $this->getDoctrine()->getManager()->getConnection();
            $sql = '
            SELECT COUNT(id) as count, api_url
            FROM api
            GROUP BY api_url;
                ';
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $apiUsage = $stmt->fetchAll();
            $this->saveItem("apiUsage", array("apiUsage" => $apiUsage));
        }
        return $apiUsage;
    }


```

I hope this article will be useful to you. :sunglasses:

