<?php

namespace Hunter\Core\App\ServiceProvider;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Zend\Diactoros\ServerRequestFactory;

class HttpMessageServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = ['response', 'emitter', 'request'];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->getContainer()->add('response', 'Zend\Diactoros\Response');

        $this->getContainer()->share('emitter', 'Zend\Diactoros\Response\SapiEmitter');

        $this->getContainer()->share('request', function () {
            $config = $this->getContainer()->get('config');

            if ($config['environment'] === 'development') {
                $_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
            }

            return ServerRequestFactory::fromGlobals();
        });
    }
}
