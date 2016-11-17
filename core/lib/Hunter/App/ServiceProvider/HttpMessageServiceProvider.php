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
            if (isset($GLOBALS['root_dir']) && $GLOBALS['root_dir'] != '/') {
                $_SERVER['REQUEST_URI'] = str_replace($GLOBALS['root_dir'], '', $_SERVER['REQUEST_URI']);
            }
            return ServerRequestFactory::fromGlobals();
        });
    }
}
