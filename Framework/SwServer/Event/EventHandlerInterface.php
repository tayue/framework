<?php

namespace Framework\SwServer\Event;

/**
 * Interface EventHandlerInterface - 独立的事件监听器接口
 * @package Framework\SwServer\Event
 * @license   PHP Version 7.x {@link http://www.php.net/license/3_0.txt}
 */
interface EventHandlerInterface
{
    /**
     * @param EventInterface $event
     */
    public function handle(EventInterface $event);
}
