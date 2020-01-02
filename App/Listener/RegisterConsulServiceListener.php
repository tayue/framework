<?php
namespace App\Listener;
use Framework\SwServer\Event\EventHandlerInterface;
use Framework\SwServer\Event\EventInterface;
use Framework\SwServer\Helper\Helper;

class RegisterConsulServiceListener implements EventHandlerInterface
{

    /**
     * @param \Framework\SwServer\Event\EventInterface $event
     */
    public function handle(EventInterface $event)
    {
        echo "event:".$event->getName().",handle:RegisterConsulServiceListener \r\n";
        go(function(){
            $serviceId = Helper::registerService('swoft',"192.168.99.88",9501);
            echo "Consul Register:".$serviceId."\r\n";
        });


        //Helper::removeService($serviceId);
    }
}