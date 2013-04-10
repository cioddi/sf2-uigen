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
 * @author Max Tobias Weber <tobias@plan-r.de>
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
        $this
            ->setDefinition(array(	
            ))
            ->setDescription('Generates a CRUD based on a Doctrine entity')
            ->setHelp(<<<EOT
The <info>uigen:generate:grid</info> command generates a CRUD based on a Doctrine entity.

<info>php app/console doctrine:generate:grid --entity=AcmeBlogBundle:Post --route-prefix=post_admin</info>

Using the --with-write option allows to generate the new, edit and delete actions.

<info>php app/console doctrine:generate:crud --entity=AcmeBlogBundle:Post --route-prefix=post_admin --with-write</info>
EOT
            )
            ->setName('uigen:generate:grid')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();

        if ($input->isInteractive()) {
            if (!$dialog->askConfirmation($output, $dialog->getQuestion('Do you confirm generation', 'yes', '?'), true)) {
                $output->writeln('<error>Command aborted</error>');

                return 1;
            }
        }

        $generator = $this->getGenerator();
        $generator->generate($this->uigenEntity);

        $output->writeln('Generating the grid code: <info>OK</info>');

        $errors = array();
        $runner = $dialog->getRunner($output, $errors);

        $dialog->writeGeneratorSummary($output, $errors);
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();
        $dialog->writeSection($output, 'Uigen ExtJs-user-interface generator');

        // namespace
        $output->writeln(array(
            '',
            'This command helps you generate CRUD controllers and templates.',
            '',
            'First, you need to give the entity for which you want to generate a CRUD.',
            'You can give an entity that does not exist yet and the wizard will help',
            'you defining it.',
            '',
            'You must use the shortcut notation like <comment>AcmeBlogBundle:Post</comment>.',
            '',
        ));

        $entity = $dialog->askAndValidate($output, $dialog->getQuestion('The Entity shortcut name', ''), array('Sensio\Bundle\GeneratorBundle\Command\Validators', 'validateEntityName'), false, '');

				// 
			$this->uigenEntity = new Uigenentity($entity,$this->getContainer()->get('doctrine'));
				// $this->uigenEntity->execute();
				// 
								$entity = $this->uigenEntity->getEntity();
								$bundle = $this->uigenEntity->getBundle();

        // Entity exists?
        $entityClass = $this->getContainer()->get('doctrine')->getEntityNamespace($bundle).'\\'.$entity;
		
	    $metadata = $this->uigenEntity->getMetadata();

		// Enable drag and drop positioning
		$output->writeln(array(
		    ''.print_r( $metadata,true),
		    'type yes to enable draganddrop positioning for the grid',
		    '',
		));
		$withDND = $dialog->askConfirmation($output, $dialog->getQuestion('Do you want to generate the "draganddrop" action',  'no'), 'no');

		
		$this->uigenEntity->setOption('dnd',$withDND);
		
		if($withDND){
			
	        $draganddrop_column = $dialog->ask($output, $dialog->getQuestion('The name of your positioning column', 'pos'), 'pos');
	
	
			$this->uigenEntity->setOption('dnd_column',$draganddrop_column);
		}

        $prefix = $this->askRoutePrefix($output,$dialog,$entity);
		$this->uigenEntity->setOption('prefix',$prefix);

        // summary
        $output->writeln(array(
            '',
            $this->getHelper('formatter')->formatBlock('Summary before generation', 'bg=blue;fg=white', true),
            '',
            sprintf("You are going to generate a CRUD controller for \"<info>%s:%s</info>\"", $bundle, $entity),
            '',
        ));
    }


    private function updateRouting($dialog, InputInterface $input, OutputInterface $output, $bundle, $format, $entity, $prefix)
    {
        $auto = true;
        if ($input->isInteractive()) {
            $auto = $dialog->askConfirmation($output, $dialog->getQuestion('Confirm automatic update of the Routing', 'yes', '?'), true);
        }

        $output->write('Importing the CRUD routes: ');
        $this->getContainer()->get('filesystem')->mkdir($bundle->getPath().'/Resources/config/');
        $routing = new RoutingManipulator($bundle->getPath().'/Resources/config/routing.yml');
        $ret = $auto ? $routing->addResource($bundle->getName(), $format, '/'.$prefix, 'routing/'.strtolower(str_replace('\\', '_', $entity))) : false;
        if (!$ret) {
            $help = sprintf("        <comment>resource: \"@%s/Resources/config/routing/%s.%s\"</comment>\n", $bundle->getName(), strtolower(str_replace('\\', '_', $entity)), $format);
            $help .= sprintf("        <comment>prefix:   /%s</comment>\n", $prefix);

            return array(
                '- Import the bundle\'s routing resource in the bundle routing file',
                sprintf('  (%s).', $bundle->getPath().'/Resources/config/routing.yml'),
                '',
                sprintf('    <comment>%s:</comment>', $bundle->getName().('' !== $prefix ? '_'.str_replace('/', '_', $prefix) : '')),
                $help,
                '',
            );
        }
    }


    protected function getGenerator()
    {
        if (null === $this->generator) {
            $this->generator = new UigenGridGenerator($this->getContainer()->get('filesystem'), __DIR__.'/../Resources/skeleton/crud');
        }

        return $this->generator;
    }

    public function setGenerator(UigenCrudGenerator $generator)
    {
        $this->generator = $generator;
    }


    protected function getDialogHelper()
    {
        $dialog = $this->getHelperSet()->get('dialog');
        if (!$dialog || get_class($dialog) !== 'Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper') {
            $this->getHelperSet()->set($dialog = new DialogHelper());
        }

        return $dialog;
    }
}
