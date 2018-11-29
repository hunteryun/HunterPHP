<?php

namespace Hunter\Core\App\Strategy;

use \Exception;
use League\Route\Http\Exception\MethodNotAllowedException;
use League\Route\Http\Exception\NotFoundException;
use League\Route\Middleware\ExecutionChain;
use League\Route\Route;
use League\Route\Strategy\StrategyInterface;
use League\Route\Strategy\ApplicationStrategy;
use RuntimeException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\RedirectResponse;

class HunterStrategy extends ApplicationStrategy implements StrategyInterface {
    /**
     * {@inheritdoc}
     */
    public function getCallable(Route $route, array $vars) {
        return function (ServerRequestInterface $request, ResponseInterface $response, callable $next) use ($route, $vars) {
            global $default_theme, $hunter_static, $app;
            $generate_html = false;
            $path = $route->getPath();
            $routeNames = $route->getContainer()->get('routeNames');
            $routeOptions = $route->getContainer()->get('routeOptions');
            //if enabled html static, and file exists, then load it
            if($hunter_static && isset($routeNames[$path]) && !isset($routeOptions[$path]['no_cache']) && substr($path, 0, 6) != '/admin' && substr($path, 0, 5) != '/api/') {
              $generate_file = 'sites/html/'.$default_theme.'/'.str_replace('.', '/', $routeNames[$path]);
              if($vars){
                $generate_file .= '_'.implode('_',array_values($vars));
              }
              if(is_file($generate_file.'.html')){
                require_once($generate_file.'.html');
                die;
              }else {
                $generate_html = true;
              }
            }

            if(isset($routeOptions[$path]['init'])){
              foreach ($app->getModuleHandle()->getImplementations('init') as $module) {
                $app->getModuleHandle()->invoke($module, 'init', array($request));
              }
            }

            $routeTitles = $route->getContainer()->get('routeTitles');

            if(isset($routeTitles[$path]) && function_exists('theme')){
              theme()->getEnvironment()->addGlobal('page_title', $routeTitles[$path]);
            }
            $vars['vars'] = $vars;
            $body = $route->getContainer()->call($route->getCallable(), $vars);

            if($generate_html){
              $this->htmlMake($body, $generate_file);
            }

            if(is_array($body) || (is_object($body) && get_class($body) == 'stdClass')){
              $response->getBody()->write(json_encode($body));
              return $response->withAddedHeader('content-type', 'application/json');
            }

            if(is_string($body) || is_bool($body)){
                if ($response->getBody()->isWritable()) {
                    $response->getBody()->write($body);
                }
                return $response;
            }

            return $body;
        };
    }

    /**
     * {@inheritdoc}
     */
    public function getNotFoundDecorator(NotFoundException $exception) {
        return function (ServerRequestInterface $request, ResponseInterface $response) use ($exception) {
            $response->getBody()->write('Sorry, this page have a error : '.$exception->getMessage());
            return $response;
        };
    }

    /**
     * {@inheritdoc}
     */
    public function getMethodNotAllowedDecorator(MethodNotAllowedException $exception) {
        return function (ServerRequestInterface $request, ResponseInterface $response) use ($exception) {
            $response->getBody()->write('Sorry, this page have a error : '.$exception->getMessage());
            return $response;
        };
    }

    /**
     * {@inheritdoc}
     */
    public function getExceptionDecorator(Exception $exception) {
        global $hunter_debug;
        return function (ServerRequestInterface $request, ResponseInterface $response) use ($exception, $hunter_debug) {
          $msg = $exception->getMessage();

          if (!$hunter_debug) {
            echo 'This page have a error, please contact the administrator!';
            exit;
          }

          logger()->error($msg);

          header('HTTP/1.1 500 Internal Server Error');
          header("status: 500 Internal Server Error");
          $trace = $exception->getTrace();
          $runTrace = $exception->getTrace();
          krsort($runTrace);
          $traceMessageHtml = null;
          $sqlTraceHtml = '';
          $k = 1;
          foreach ($runTrace as $v) {
           if(isset($v['file'])){
             $traceMessageHtml.='<tr class="bg1"><td>'.$k.'</td><td>'.$v['file'].'</td><td>'.$v['line'].'</td><td>'.self::getLineCode($v['file'], $v['line']).'</td></tr>';
           }
           $k++;
          }
          unset($k);unset($trace);unset($runTrace);unset($trace);
          $body = '
          <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
          <html><head><title>'.$_SERVER['HTTP_HOST'].' - PHP Error</title>
          <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
          <meta name="ROBOTS" content="NOINDEX,NOFOLLOW,NOARCHIVE" />
          <style type="text/css">
          <!--
          body { background-color: white; color: black; font: 9pt/11pt verdana, arial, sans-serif;}
          #container { width: 90%;margin-left:auto;margin-right:auto; }
          #message   { width: 90%; color: black; }
          .red  {color: red;}
          a:link     { font: 9pt/11pt verdana, arial, sans-serif; color: red; }
          a:visited  { font: 9pt/11pt verdana, arial, sans-serif; color: #4e4e4e; }
          h1 { color: #FF0000; font: 18pt "Verdana"; margin-bottom: 0.5em;}
          .bg1{ background-color: #FFFFCC;}
          .bg2{ background-color: #EEEEEE;}
          .table {background: #AAAAAA; font: 11pt Menlo,Consolas,"Lucida Console"}
          .info {background: none repeat scroll 0 0 #F3F3F3;border: 0px solid #aaaaaa;border-radius: 10px 10px 10px 10px;color: #000000;font-size: 11pt;line-height: 160%;margin-bottom: 1em;padding: 1em;}
          .help {
          background: #F3F3F3;border-radius: 10px 10px 10px 10px;font: 12px verdana, arial, sans-serif;text-align: center;line-height: 160%;padding: 1em;}
          .mind {
          background: none repeat scroll 0 0 #FFFFCC;
          border: 1px solid #aaaaaa;
          color: #000000;
          font: arial, sans-serif;
          font-size: 9pt;
          line-height: 160%;
          margin-top: 1em;
          padding: 4px;}
          -->
          </style></head><body><div id="container"><h1>HunterPHP DEBUG</h1><div class="info">(1146)'.$msg.'</div><div class="info"><p><strong>PHP Trace</strong></p><table cellpadding="5" cellspacing="1" width="100%" class="table"><tr class="bg2"><td style="width:2%">No.</td><td style="width:45%">File</td><td style="width:5%">Line</td><td style="width:48%">Code</td></tr>'.$traceMessageHtml.'</table></div> <div class="help"><a href="http://'.$_SERVER['HTTP_HOST'].'">'.$_SERVER['HTTP_HOST'].'</a> 已经将此出错信息详细记录, 由此给您带来的访问不便我们深感歉意.</div></div></body></html>';

          $response->getBody()->write($body);
          return $response;
        };
    }

    /**
     * get line of code
     */
    private static function getLineCode($file,$line) {
      $fp = fopen($file,'r');
      $i = 0;
      while(!feof($fp)) {
        $i++;
        $c = fgets($fp);
        if($i==$line) {
          return $c;
          break;
        }
      }
    }

    /**
     * generate html
     */
    public function htmlMake($body, $file) {
      if(!is_dir(dirname($file))) {
        mkdir(dirname($file), 0777, true);
      }

      return file_put_contents($file.'.html', $body) !== false;
    }

}
