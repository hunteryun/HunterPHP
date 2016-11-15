<?php

namespace Hunter\Core\App\Contract;

trait ConfigAwareTrait
{
    public $config;

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }
}
