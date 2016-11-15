<?php

namespace Hunter\Core\App\ServiceProvider;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Twig_Loader_Filesystem;
use Twig_Environment;
use Twig_Extension_Debug;
use Twig_SimpleFilter;

class TemplateServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        'Twig_Environment'
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $config = $this->getContainer()->get('config');
        $globals = [
            'base_url'    => $config['base_url'],
            'asset_url'   => $config['base_url'] . '/theme/assets',
            'environment' => $config['environment']
        ];

        $this->getContainer()->share('Twig_Environment', function () use ($globals, $config) {
            $template_dir = dirname(substr(__DIR__, 0, -strlen(__NAMESPACE__)));
            $loader = new Twig_Loader_Filesystem($template_dir . '/theme/hunter');
            $twig   = new Twig_Environment($loader, [
                'cache' => $config['environment'] === 'production' ? true : false,
                'debug' => $config['environment'] === 'production' ? false : true
            ]);

            // Add Globals
            foreach ($globals as $key => $val) {
                $twig->addGlobal($key, $val);
            }

            // Add current path
            $request = $this->getContainer()->get('Request');
            $twig->addGlobal('current_path', $request->getServerParams()['REQUEST_URI']);

            // Add extensions
            if ($config['environment'] !== 'production') {
                $twig->addExtension(new Twig_Extension_Debug);
            }

            return $twig;
        });
    }
}
