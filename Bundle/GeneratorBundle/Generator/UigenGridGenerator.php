<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Uigen\Bundle\GeneratorBundle\Generator;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Sensio\Bundle\GeneratorBundle\Generator as sensioGenerator;
use Uigen\Bundle\GeneratorBundle\Entity\Uigenentity;

/**
 * Generates a CRUD controller.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class UigenGridGenerator extends sensioGenerator\Generator
{
    private $filesystem;
    private $skeletonDir;
    private $routePrefix;
    private $routeNamePrefix;
    private $bundle;
    private $entity;
    private $metadata;
    private $format;
    private $actions;

    /**
     * Constructor.
     *
     * @param Filesystem $filesystem A Filesystem instance
     * @param string $skeletonDir Path to the skeleton directory
     */
    public function __construct(Filesystem $filesystem, $skeletonDir)
    {
        $this->filesystem  = $filesystem;
        $this->skeletonDir = $skeletonDir;
    }

    /**
     * Generate the CRUD controller.
     *
     * @param BundleInterface $bundle A bundle object
     * @param string $entity The entity relative class name
     * @param ClassMetadataInfo $metadata The entity class metadata
     * @param string $format The configuration format (xml, yaml, annotation)
     * @param string $routePrefix The route name prefix
     * @param array $needWriteActions Wether or not to generate write actions
     *
     * @throws \RuntimeException
     */
    public function generate(Uigenentity $uigenEntity)
    {
		$this->uigenEntity = $uigenEntity;
        $this->routePrefix = $this->uigenEntity->getOption('prefix');
        $this->routeNamePrefix = str_replace('/', '_', $this->uigenEntity->getOption('prefix'));
        $this->actions = array('index', 'create', 'read', 'update', 'destroy');

		if($uigenEntity->getOption('dnd')){
			$this->actions[] = 'draganddrop';
			$this->dnd_column = $uigenEntity->getOption('dnd_column');
		}

		$this->format	= 'annotation';
        $this->entity   = $this->uigenEntity->getEntity();
        $this->bundle   = $this->uigenEntity->getBundleObject();
        $this->metadata = $this->uigenEntity->getMetadata();
		$this->metadata = $this->metadata[0];
		
        $this->generateControllerClass();

        $dir = sprintf('%s/Resources/views/%s', $this->bundle->getPath(), str_replace('\\', '/', $this->entity));

        if (!file_exists($dir)) {
            $this->filesystem->mkdir($dir, 0777);
        }

        $this->generateIndexView($dir);

        $this->generateGridJs();
		$this->generateLayoutView();
        $this->generateTestClass();
    }


    /**
     * Generates the controller class only.
     *
     */
    private function generateControllerClass()
    {
        $dir = $this->bundle->getPath();

        $parts = explode('\\', $this->entity);
        $entityClass = array_pop($parts);
        $entityNamespace = implode('\\', $parts);

        $target = sprintf(
            '%s/Controller/%s/%sController.php',
            $dir,
            str_replace('\\', '/', $entityNamespace),
            $entityClass
        );

        if (file_exists($target)) {
            throw new \RuntimeException('Unable to generate the controller as it already exists. at '.$target);
        }

        $this->renderFile($this->skeletonDir, 'controller.php', $target, array(
            'actions'           => $this->actions,
            'route_prefix'      => $this->routePrefix,
            'route_name_prefix' => $this->routeNamePrefix,
            'dir'               => $this->skeletonDir,
            'bundle'            => $this->bundle->getName(),
            'entity'            => $this->entity,
            'entity_class'      => $entityClass,
            'namespace'         => $this->bundle->getNamespace(),
            'entity_namespace'  => $entityNamespace,
            'format'            => $this->format,
            'fields'            => $this->metadata->fieldMappings,
			'dnd_column'		=> $this->uigenEntity->getOption('dnd_column')
        ));
    }

    /**
     * Generates the functional test class only.
     *
     */
    private function generateTestClass()
    {
        $parts = explode('\\', $this->entity);
        $entityClass = array_pop($parts);
        $entityNamespace = implode('\\', $parts);

        $dir    = $this->bundle->getPath() .'/Tests/Controller';
        $target = $dir .'/'. str_replace('\\', '/', $entityNamespace).'/'. $entityClass .'ControllerTest.php';

        $this->renderFile($this->skeletonDir, 'tests/test.php', $target, array(
            'route_prefix'      => $this->routePrefix,
            'route_name_prefix' => $this->routeNamePrefix,
            'entity'            => $this->entity,
            'entity_class'      => $entityClass,
            'namespace'         => $this->bundle->getNamespace(),
            'entity_namespace'  => $entityNamespace,
            'actions'           => $this->actions,
            'dir'               => $this->skeletonDir,
        ));
    }

    /**
     * Generates the index.html.twig template in the final bundle.
     *
     * @param string $dir The path to the folder that hosts templates in the bundle
     */
    private function generateIndexView($dir)
    {
		$public_dir = '/bundles/' . strtolower( str_replace('Bundle','',$this->bundle->getName()) );
        $this->renderFile($this->skeletonDir, 'views/index.html.twig', $dir.'/index.html.twig', array(
            'dir'               => $this->skeletonDir,
            'entity'            => $this->entity,
            'fields'            => $this->metadata->fieldMappings,
            'actions'           => $this->actions,
            'route_prefix'      => $this->routePrefix,
            'route_name_prefix' => $this->routeNamePrefix,
			'bundle_path'		=> $this->bundle->getPath(),
            'bundle'            => $this->bundle->getName(),
			'public_dir'		=> $public_dir
        ));
    }

    /**
     * Generates the layout.html.twig template in the final bundle.
     *
     */
    private function generateLayoutView()
    {
		$dir    = $this->bundle->getPath() .'/Resources/views/';
        $this->renderFile($this->skeletonDir, 'views/layout.html.twig', $dir.'/layout.html.twig', array());
    }

    /**
     * Generates public/js/grid.js in the final bundle.
     *
     */
    private function generateGridJS()
    {
	
        $parts = explode('\\', $this->entity);
        $entityClass = array_pop($parts);
        $entityNamespace = implode('\\', $parts);

        $dir    = $this->bundle->getPath() .'/Resources/public/js/';
        $target = $dir .'/'. str_replace('\\', '/', $entityNamespace).'/'. $entityClass .'_grid.js';

        $this->renderFile($this->skeletonDir, 'js/grid.js', $target, array(
            'dir'               => $this->skeletonDir,
            'entity'            => $this->entity,
            'fields'            => $this->metadata->fieldMappings,
            'actions'           => $this->actions,
            'route_prefix'      => $this->routePrefix,
            'route_name_prefix' => $this->routeNamePrefix,
        ));
    }
}
