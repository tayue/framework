<?php
namespace App\Listener;
use Framework\SwServer\Event\EventHandlerInterface;
use Framework\SwServer\Event\EventInterface;

class SendEmailsListener implements EventHandlerInterface
{

    /**
     * @param \Framework\SwServer\Event\EventInterface $event
     */
    public function handle(EventInterface $event)
    {
        echo "event:".$event->getName().",handle:sendemail \r\n";
        print_r($event->getParams());
    }
}