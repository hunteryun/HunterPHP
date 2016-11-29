<?php

namespace Hunter\test\Plugin;

class TestPlugin
{
    public $bar;

    public function __construct(TestBar $bar)
    {
        $this->bar = $bar;
    }

}
