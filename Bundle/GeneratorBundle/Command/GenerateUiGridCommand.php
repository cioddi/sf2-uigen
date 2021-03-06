<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Uigen\Bundle\GeneratorBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Command\Command;
use Uigen\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Uigen\Bundle\GeneratorBundle\Generator\UigenGridGenerator;
use Sensio\Bundle\GeneratorBundle\Command as sensioCommand;
use Sensio\Bundle\GeneratorBundle\Generator\DoctrineFormGenerator;
use Uigen\Bundle\GeneratorBundle\Entity\Uigenentity;

/**
 * Generates a CRUD for a Doctrine entity.
 *
 * @author Max Tobias Weber <tobias@plan-r.de,maxtobiasweber@gmail.com>
 */
class GenerateUiGridCommand extends GenerateUiCommand
{
    private $generator;
    private $formGenerator;

    /**
     * @see Command
     */
    protected function configure()
    {
        $this->setDefinition(array())
            ->setDescription('Generates a CRUD based on a Doctrine entity')
            ->setHelp(<<<EOT
The <info>uigen:generate:grid</info> command generates a CRUD based on a Doctrine entity.

<info>php app/console uigen:generate:grid</info>
EOT
            )
            ->setName('uigen:generate:grid');
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
		/**
		 * confirm code generation
		 */
		if(!$this->uigenEntity->confirmGeneration())return 1;

		
		/**
		 * 'actions' render param
		 */
        $this->actions = array('index', 'create', 'read', 'update', 'destroy');

		if($this->uigenEntity->getOption('dnd')){
			$this->actions[] = 'draganddrop';
		}
		
		$this->uigenEntity->addRenderParams(array('actions' => $this->actions));
		
		/**
		 * render files
		 */
		
		/**
		 * Controller
		 */
        $this->uigenEntity->renderFile('controller.php', $this->uigenEntity->getControllerPath().'/'.$this->uigenEntity->getEntityClassName().'Controller.php');

		/**
		 * Index template
		 */
        $this->uigenEntity->renderFile('views/index.html.twig', $this->uigenEntity->getViewsPath().'/index.html.twig');

		/**
		 * javascript extjs grid definition
		 */
        $this->uigenEntity->renderFile( 'js/grid.js', $this->uigenEntity->getPublicPath().'js/'.$this->uigenEntity->getEntityClassName().'_grid.js');
		
		/**
		 *  TODO test class
		 */
        // $this->uigenEntity->renderFile('tests/test.php', $this->uigenEntity->getTestPath().'/'.$this->uigenEntity->getEntityClassName().'ControllerTest.php');

		/**
		 * Layout template
		 */
	    $this->uigenEntity->renderFile('views/layout.html.twig', $this->uigenEntity->getBundlePath().'/Resources/views/layout.html.twig');
        

    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
		
		/**
		 * create Uigenentity generator object
		 */
		$this->uigenEntity = new Uigenentity($this->getContainer(),$output,__DIR__.'/../Resources/skeleton/crud');
		
		/**
		 * Display a short introduction about UIGEN
		 */
		$this->uigenEntity->uigenIntro();
		
		/**
		 * ask for namespace and entity classname
		 */
		$this->uigenEntity->askForEntity();
		
		/**
		 * configure drag and drop column
		 */
		$this->uigenEntity->configureDragAndDrop();
		
		/**
		 * configure constraints
		 */
		$this->uigenEntity->configureConstraints();
		
		/**
		 * configure route prefix (stored in option 'prefix')
		 */
		$this->uigenEntity->configureRoutePrefix();
    }

}
