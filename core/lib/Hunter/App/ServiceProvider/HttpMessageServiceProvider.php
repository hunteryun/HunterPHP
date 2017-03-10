<?php

namespace Hunter\Core\App\ServiceProvider;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Zend\Diactoros\ServerRequestFactory;

class HttpMessageServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = ['Zend\Diactoros\Response', 'Zend\Diactoros\Response\SapiEmitter', 'Zend\Diactoros\ServerRequest'];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->getContainer()->add('Zend\Diactoros\Response', 'Zend\Diactoros\Response');

        $this->getContainer()->share('Zend\Diactoros\Response\SapiEmitter', 'Zend\Diactoros\Response\SapiEmitter');

        $this->getContainer()->share('Zend\Diactoros\ServerRequest', function () {
            if (isset($GLOBALS['root_dir']) && $GLOBALS['root_dir'] != '/' && !is_cli()) {
                $_SERVER['REQUEST_URI'] = str_replace($GLOBALS['root_dir'], '', $_SERVER['REQUEST_URI']);
            }
            return ServerRequestFactory::fromGlobals();
        });
    }
}
