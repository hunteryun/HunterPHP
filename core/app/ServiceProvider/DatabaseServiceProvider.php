<?php

namespace Hunter\ServiceProvider;

use Hunter\Core\App\Application;
use Hunter\Core\App\ServiceProviderInterface;
use Hunter\Core\Database;
use Hunter\Core\Database\Condition;

/**
 * Database DBAL Provider.
 */
class DatabaseServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['db.default_options'] = array(
            'driver' => 'pdo_mysql',
            'dbname' => null,
            'host' => 'localhost',
            'user' => 'root',
            'password' => null,
        );

        $app['dbs.options.initializer'] = $app->protect(function () use ($app) {
            static $initialized = false;

            if ($initialized) {
                return;
            }

            $initialized = true;

            if (!isset($app['dbs.options'])) {
                $app['dbs.options'] = array('default' => isset($app['db.options']) ? $app['db.options'] : array());
            }

            $tmp = $app['dbs.options'];
            foreach ($tmp as $name => &$options) {
                $options = array_replace($app['db.default_options'], $options);

                if (!isset($app['dbs.default'])) {
                    $app['dbs.default'] = $name;
                }
            }
            $app['dbs.options'] = $tmp;
        });

        $app['dbs'] = $app->share(function ($app) {
            $app['dbs.options.initializer']();

            $dbs = new \Pimple();
            foreach ($app['dbs.options'] as $name => $options) {
                if ($app['dbs.default'] === $name) {
                    // we use shortcuts here in case the default has been overridden
                    $config = $app['db.config'];
                    $manager = $app['db.event_manager'];
                } else {
                    $config = $app['dbs.config'][$name];
                    $manager = $app['dbs.event_manager'][$name];
                }

                $dbs[$name] = $dbs->share(function ($dbs) use ($options, $config, $manager) {
                    return DriverManager::getConnection($options, $config, $manager);
                });
            }

            return $dbs;
        });

        $app['dbs.config'] = $app->share(function ($app) {
            $app['dbs.options.initializer']();

            $configs = new \Pimple();
            foreach ($app['dbs.options'] as $name => $options) {
                $configs[$name] = new Configuration();

                if (isset($app['logger']) && class_exists('Symfony\Bridge\Doctrine\Logger\DbalLogger')) {
                    $configs[$name]->setSQLLogger(new DbalLogger($app['logger'], isset($app['stopwatch']) ? $app['stopwatch'] : null));
                }
            }

            return $configs;
        });

    }

    public function boot(Application $app)
    {
    }
}
