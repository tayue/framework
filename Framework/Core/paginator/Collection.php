<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2017 http://Framework\Corephp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: zhangyajun <448901948@qq.com>
// +----------------------------------------------------------------------

namespace Framework\Core\paginator;

use Exception;
use Framework\Core\Paginator;

/**
 * Class Collection
 * @package Framework\Core\paginator
 * @method integer total()
 * @method integer listRows()
 * @method integer currentPage()
 * @method string render()
 * @method Paginator fragment($fragment)
 * @method Paginator appends($key, $value)
 * @method integer lastPage()
 * @method boolean hasPages()
 */
class Collection extends \Framework\Core\Collection
{

    /** @var Paginator */
    protected $paginator;

    public function __construct($items = [], Paginator $paginator = null)
    {
        $this->paginator = $paginator;
        parent::__construct($items);
    }

    public static function make($items = [], Paginator $paginator = null)
    {
        return new static($items, $paginator);
    }

    public function toArray()
    {
        if ($this->paginator) {
            try {
                $total = $this->total();
            } catch (Exception $e) {
                $total = null;
            }

            return [
                'total'        => $total,
                'per_page'     => $this->listRows(),
                'current_page' => $this->currentPage(),
                'data'         => parent::toArray(),
            ];
        } else {
            return parent::toArray();
        }
    }

    public function __call($method, $args)
    {
        if ($this->paginator && method_exists($this->paginator, $method)) {
            return call_user_func_array([$this->paginator, $method], $args);
        } else {
            throw new Exception('method not exists:' . __CLASS__ . '->' . $method);
        }
    }
}
