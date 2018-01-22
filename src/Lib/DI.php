<?php

namespace SniTodos\Lib;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class DI
{
    private static $container;
    private static $projectPath;

    public static function getContainer(): ContainerInterface
    {
        if (!self::$container) {
            self::$container = new ContainerBuilder();

            $configPath = self::getProjectPath() . '/config';
            $loader = new YamlFileLoader(self::$container, new FileLocator($configPath));
            $loader->load('services.yaml');
            self::$container->compile();
        }

        return self::$container;
    }

    public static function getStoragePath(): string
    {
        return getenv('STORAGE_PATH')? : self::getProjectPath() . '/data';
    }

    public static function getProjectPath(): string
    {
        if (!self::$projectPath) {
            self::$projectPath = __DIR__;

            while (!in_array('src', scandir(self::$projectPath))) {
                self::$projectPath = dirname(self::$projectPath);

                if (self::$projectPath == '/') {
                    throw new \Exception('Unable to find project path. No src-Folder found.');
                }
            }
        }

        return self::$projectPath;
    }
}
