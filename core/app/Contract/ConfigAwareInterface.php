<?php

namespace Hunter\Contract;

interface ConfigAwareInterface
{
    public function setConfig(array $config);

    public function getConfig();
}
