<?php

namespace Hunter\test\Plugin;

class TestPlugin
{
    public $bar;

    public function __construct()
    {
        $this->bar = new Bar;
    }
}

class Bar
{

}
