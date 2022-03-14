<?php
/**
 * Created by PhpStorm.
 * Date: 2021/7/8
 * Time: 10:54 PM
 */
namespace czh9527\tp6curd\strategy;

use czh9527\tp6curd\template\IAutoMake;

class AutoMakeStrategy
{
    protected $strategy;

    public function Context(IAutoMake $obj)
    {
        $this->strategy = $obj;
    }

    public function executeStrategy($flag, $path, $other)
    {
        if($this->strategy->check($flag, $path))
        {
            return false;
        }
        else
        {
            return $this->strategy->make($flag, $path, $other);
        }
    }
}