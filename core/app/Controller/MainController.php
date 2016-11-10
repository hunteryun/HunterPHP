<?php

namespace Hunter\Controller;

use Hunter\Contract\TemplateAwareInterface;
use Hunter\Contract\TemplateAwareTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MainController implements TemplateAwareInterface
{
    use TemplateAwareTrait;

    /**
     * Hello world!
     *
     * @param  Psr\Http\Message\ServerRequestInterface $request
     * @param  Psr\Http\Message\ResponseInterface      $response
     *
     * @return Psr\Http\Message\ResponseInterface
     */
    public function index(ServerRequestInterface $request, ResponseInterface $response)
    {
        $response->getBody()->write(
            $this->getTemplateDriver()->render('landing.html')
        );
    }

    /**
     * Route that ensures .htaccess and overall routing is working
     *
     * @param  Psr\Http\Message\ServerRequestInterface $request
     * @param  Psr\Http\Message\ResponseInterface      $response
     *
     * @return Psr\Http\Message\ResponseInterface
     */
    public function testRoute(ServerRequestInterface $request, ResponseInterface $response)
    {
        $response->getBody()->write(
            'It\'s working!'
        );
    }
}
