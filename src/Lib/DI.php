<?php

namespace APPointer\Lib;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Validator\ContainerConstraintValidatorFactory;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DI
{
    private static $container;
    private static $projectPath;
    private static $validator;

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

    public static function getLocalPath(): string
    {
        return getenv('APPOINT_LOCAL_FILE') ? : self::getProjectPath() . '/todos.yml';
    }

    public static function getHomePath(): string
    {
        $path = $_SERVER['HOME'];
        if (substr($path, 0, 6) == '/home') {
            throw new \Exception("Home-path {$path} does not start with '/home/'.");
        }
        return $path . '/';
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

    public function getStoragePath()
    {
        return getenv('STORAGE_DIR') ? : self::getProjectPath() . '/data';
    }

    public static function getValidator(): ValidatorInterface
    {
        if (!self::$validator) {
            $factory = new ContainerConstraintValidatorFactory(self::getContainer());
            self::$validator = Validation::createValidatorBuilder()
                ->setConstraintValidatorFactory($factory)
                ->addMethodMapping('loadValidatorMetadata')
                ->getValidator();
        }

        return self::$validator;
    }
}
