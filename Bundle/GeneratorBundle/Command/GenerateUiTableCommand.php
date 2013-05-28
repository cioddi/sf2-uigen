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
class GenerateUiTableCommand extends GenerateUiCommand
{
    private $generator;
    private $formGenerator;

    /**
     * @see Command
     */
    protected function configure()
    {
        $this->setName('uigen:generate:table');
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

		

		if($this->uigenEntity->getOption('dnd')){
			$this->actions[] = 'draganddrop';
		}
		
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
		 * Layout template
		 */
	    $this->uigenEntity->renderFile('views/layout.html.twig', $this->uigenEntity->getBundlePath().'/Resources/views/layout.html.twig');
        

    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
		
		/**
		 * create Uigenentity generator object
		 */
		$this->uigenEntity = new Uigenentity($this->getContainer(),$output,__DIR__.'/../Resources/skeleton/table');
		
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
		 * configure route prefix (stored in option 'prefix')
		 */
		$this->uigenEntity->configureRoutePrefix();
    }

}
