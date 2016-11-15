<?php

namespace Hunter\Core\App\Contract;

interface ConfigAwareInterface
{
    public function setConfig(array $config);

    public function getConfig();
}
