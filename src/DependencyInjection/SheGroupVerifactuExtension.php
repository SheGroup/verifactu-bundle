<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/** @noinspection PhpUnused */
final class SheGroupVerifactuExtension extends Extension
{
    /** @throws Exception */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        foreach ($config as $group => $values) {
            foreach ($values as $parameter => $value) {
                $container->setParameter(sprintf('%s.%s.%s', Configuration::NAME, $group, $parameter), $value);
            }
        }

        $servicesDirectory = sprintf('%s/../Resources/config/services', __DIR__);
        $finder = new Finder();
        $loader = new YamlFileLoader($container, new FileLocator($servicesDirectory));
        $finder->in($servicesDirectory);
        $files = $finder->name('*.yaml')->files();
        foreach ($files as $file) {
            $loader->load($file->getFilename());
        }
    }
}
