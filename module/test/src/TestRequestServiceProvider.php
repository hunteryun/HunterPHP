<?php

namespace Hunter\test;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Hunter\test\Plugin\TestPlugin;

/**
 * Provides test module permission auth.
 */
class TestRequestServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = ['Hunter\test\Plugin\TestPlugin'];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->getContainer()->share('Hunter\test\Plugin\TestPlugin');
    }
}
