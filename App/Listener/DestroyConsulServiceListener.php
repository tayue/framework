<?php
namespace App\Listener;
use Framework\SwServer\Event\EventHandlerInterface;
use Framework\SwServer\Event\EventInterface;
use Framework\SwServer\Helper\Helper;

class DestroyConsulServiceListener implements EventHandlerInterface
{

    /**
     * @param \Framework\SwServer\Event\EventInterface $event
     */
    public function handle(EventInterface $event)
    {
        echo "event:".$event->getName().",handle:DestroyConsulServiceListener \r\n";
        go(function(){
            $response=Helper::removeService('swoft');
            echo "Consul Destroy Service swoft :".json_encode($response)."\r\n";
        });


        //Helper::removeService($serviceId);
    }
}