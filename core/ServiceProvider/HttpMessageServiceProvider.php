<?php

namespace Hunter\ServiceProvider;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernel;

class HttpMessageServiceProvider extends AbstractServiceProvider {
    /**
     * @var array
     */
    protected $provides = [
        'Symfony\Component\HttpFoundation\Response',
        'Symfony\Component\HttpFoundation\Request',
        'Symfony\Component\HttpKernel\HttpKernel'
    ];
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->getContainer()->add('Symfony\Component\HttpFoundation\Response');
        $this->getContainer()->share('Symfony\Component\HttpFoundation\Request', function () {
            return Request::createFromGlobals();
        });
        $this->getContainer()->add('Symfony\Component\HttpKernel\HttpKernel');
    }
}
