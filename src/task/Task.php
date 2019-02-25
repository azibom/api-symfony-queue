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
