<?php
function logger($fileName)
{
    $fileHandle = fopen($fileName, 'a');
    // while (true) {
    $a = [1, 2, 3];
    $str = (yield yielddfunc($a));
    var_dump($str);
    fwrite($fileHandle, yield . "\n");
    fwrite($fileHandle, yield . "\n");

    // }
}

function yielddfunc($params)
{
    echo "sleeping................\r\n";
    file_put_contents("./tt.txt", date("Y-m-d H:i:s"));

    return 11;
}

//$logger = logger(__DIR__ . '/log');
//var_dump($logger);
//$a = $logger->current();
//var_dump($a);
//$b = $logger->send(date("Y-m-d H:i:s"));
//var_dump($b);

//test.php


$i = 10000;


class Coroutine
{
    //可以根据需要更改定时器间隔，单位ms
    const TICK_INTERVAL = 1;
    private $routineList;
    private $tickId = -1;

    public function __construct()
    {
        $this->routineList = [];
    }

    public function start(Generator $routine)
    {
        $task = new Task($routine);
        $this->routineList[] = $task;
        $this->startTick();
    }

    public function stop(Generator $routine)
    {
        foreach ($this->routineList as $k => $task) {
            if ($task->getRoutine() == $routine) {
                unset($this->routineList[$k]);
            }
        }

    }

    private function startTick()
    {
        swoole_timer_tick(self::TICK_INTERVAL, function ($timerId) {
            $this->tickId = $timerId;
            $this->run();
        });
    }

    private function stopTick()
    {
        if ($this->tickId >= 0) {
            swoole_timer_clear($this->tickId);
        }
    }

    private function run()
    {
        if (empty($this->routineList)) {
            $this->stopTick();
            return;
        }
        foreach ($this->routineList as $k => $task) {
            $task->run();
            if ($task->isFinished()) {
                unset($this->routineList[$k]);
            }
        }
    }
}


class Task
{
    protected $stack;
    protected $routine;

    public function __construct(Generator $routine)
    {
        $this->routine = $routine;
        $this->stack = new SplStack();
    }

    public function run()
    {
        $routine = &$this->routine;
        try {
            if (!$routine) {
                return;
            }
            $value = $routine->current();
            //嵌套的协程
            if ($value instanceof Generator) {
                $this->stack->push($routine);
                $routine = $value;
                return;
            }

            //嵌套的协程返回
            if (!$routine->valid() && !$this->stack->isEmpty()) {
                $routine = $this->stack->pop();
            }

            $routine->next();

        } catch (Exception $e) {
            if ($this->stack->isEmpty()) {
                return;
            }
        }
    }

    //判断该task是否完成
    public function isFinished()
    {
        return $this->stack->isEmpty() && !$this->routine->valid();
    }

    public function getRoutine()
    {
        return $this->routine;
    }
}
//$i = 10000;
//$c = new Coroutine();
//$c->start(task1());
//$c->start(task2());

function task1(){
    global $i;
    echo "wait start" . PHP_EOL;
    while ($i-- > 0) {
        yield sleeps();
    }
    echo "wait end" . PHP_EOL;
};

function task2(){
    global $i;
    echo "Hello " . PHP_EOL;
    while ($i-- > 0) {
        yield sleeps1();
    }
    echo "world!" . PHP_EOL;
}


function sleeps(){
    echo "task1 sleeping....";
    sleep(3);
}

function sleeps1(){
    echo "task2 sleeping....";
    sleep(3);
}




die();

//$logger->send(date("Y-m-d H:i:s"));
//$logger->send(date("Y-m-d H:i:s"));



