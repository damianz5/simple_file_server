<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

$loader = require __DIR__.'/../vendor/autoload.php';
AnnotationRegistry::registerLoader([$loader, 'loadClass']);

class AppKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles()
    {
        $bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
        ];

        if ($this->getEnvironment() == 'dev') {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
        }

        return $bundles;
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        if ($this->getEnvironment() == 'test') {
            $loader->load(__DIR__.'/config/config_test.yml');
        } else {
            $loader->load(__DIR__.'/config/config.yml');
        }

        if (isset($this->bundles['WebProfilerBundle'])) {
            $c->loadFromExtension('web_profiler', [
                'toolbar'             => true,
                'intercept_redirects' => false,
            ]);
        }

        $c->register('file_uploader', 'App\\Uploader\\FileUploader');
        $c->register('file_validator', 'App\\Validator\\FileValidator');

        $uploadManagerDefinition = (new Definition(
            'App\\Manager\\UploadManager'
        ))->setAutowired(true);

        $c->setDefinition('upload_manager', $uploadManagerDefinition);

        $c->setDefinition('file_collection_manager', new Definition(
            'App\\Manager\\FileCollectionManager',
            [
                $c->getParameter('upload_directory'),
                $c->getParameter('file_collection_prefix'),
            ]
        ));
    }

    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $routes->add('/', 'kernel:indexAction', 'index');

        if (isset($this->bundles['WebProfilerBundle'])) {
            $routes->import('@WebProfilerBundle/Resources/config/routing/wdt.xml', '/_wdt');
            $routes->import('@WebProfilerBundle/Resources/config/routing/profiler.xml', '/_profiler');
        }

        $routes->import(__DIR__.'/../src/App/Controller/', '/', 'annotation');
    }

    public function getCacheDir()
    {
        return __DIR__.'/../var/cache/'.$this->getEnvironment();
    }

    public function getLogDir()
    {
        return __DIR__.'/../var/logs';
    }

    public function indexAction()
    {
        return new Response('');
    }
}
