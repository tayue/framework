<?php

namespace Framework\SwServer\Event;

/**
 * Class LazyListener - 将callable包装成对象
 * @package Framework\SwServer\Event
 */
class LazyListener implements EventHandlerInterface
{
    /**
     * @var callable
     */
    private $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @param EventInterface $event
     * @return mixed
     */
    public function handle(EventInterface $event)
    {
        return self::call($this->callback, [$event]);
    }

    /**
     * @return callable|mixed
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * 调用
     *
     * @param mixed $cb   callback函数，多种格式
     * @param array $args 参数
     *
     * @return mixed
     */
    public static function call($cb, array $args = [])
    {
        $ret = null;
        if (\is_object($cb) || (\is_string($cb) && \function_exists($cb))) {
            $ret = $cb(...$args);
        } elseif (\is_array($cb)) {
            list($obj, $mhd) = $cb;
            $ret = \is_object($obj) ? $obj->$mhd(...$args) : $obj::$mhd(...$args);
        } else {
            if (SWOOLE_VERSION >= '4.0') {
                $ret = call_user_func_array($cb, $args);
            } else {
                $ret = \Swoole\Coroutine::call_user_func_array($cb, $args);
            }
        }

        return $ret;
    }
}
