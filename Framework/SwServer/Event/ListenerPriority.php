<?php

namespace Framework\SwServer\Event;

/**
 * Class ListenerPriority
 * @package Framework\SwServer\Event
 * @license   PHP Version 7.x {@link http://www.php.net/license/3_0.txt}
 */
final class ListenerPriority
{
    const MIN          = -300;
    const LOW          = -200;
    const BELOW_NORMAL = -100;
    const NORMAL       = 0;
    const ABOVE_NORMAL = 100;
    const HIGH         = 200;
    const MAX          = 300;
}
