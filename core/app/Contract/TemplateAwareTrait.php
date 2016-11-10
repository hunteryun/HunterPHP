<?php

namespace Hunter\Contract;

use Twig_Environment;

trait TemplateAwareTrait
{
    public $templateDriver;

    public function setTemplateDriver(Twig_Environment $driver)
    {
        $this->templateDriver = $driver;
    }

    public function getTemplateDriver()
    {
        return $this->templateDriver;
    }
}
