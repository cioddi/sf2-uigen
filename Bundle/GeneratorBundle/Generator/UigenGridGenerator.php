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
		}
		
		
		
		$uigenEntity->addRenderParams(array('actions' => $this->actions));
		
        $this->uigenEntity->renderFile('controller.php', $this->uigenEntity->getControllerPath().'/'.$this->uigenEntity->getEntityClassName().'Controller.php');

        $dir = $this->uigenEntity->getPublicPath().str_replace('\\', '/', $this->entity);

        if (!file_exists($dir)) {
            $this->filesystem->mkdir($dir, 0777);
        }

        $this->uigenEntity->renderFile('views/index.html.twig', $this->uigenEntity->getViewsPath().'/index.html');

        $this->uigenEntity->renderFile( 'js/grid.js', $this->uigenEntity->getPublicPath().'js/grid.js');
		$this->uigenEntity->renderFile('views/layout.html.twig', $this->uigenEntity->getViewsPath().'layout.html.twig');
        $this->uigenEntity->renderFile('tests/test.php', $this->uigenEntity->getTestPath().'/'.$this->uigenEntity->getEntityClassName().'ControllerTest.php');
    }


}
