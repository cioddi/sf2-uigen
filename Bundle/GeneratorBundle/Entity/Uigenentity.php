<?php

namespace Uigen\Bundle\GeneratorBundle\Entity;



use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Doctrine\Bundle\DoctrineBundle\Mapping\MetadataFactory;
use Sensio\Bundle\GeneratorBundle\Command as sensioCommand;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Util\Inflector;
use Uigen\Bundle\GeneratorBundle\Generator\UigenGenerator;
use Uigen\Bundle\GeneratorBundle\Command\Helper\DialogHelper;


/**
 * Uigenentity
 *
 * @author Max Tobias Weber <maxtobiasweber@gmail.com>
 */

class Uigenentity extends ContainerAwareCommand{
	
	private $entity;
	
	private $columnArray;
	
	private $bundle;
	
	private $options;
	
	private $bundleObject;
	
	private $bundleNamespace;
	
	private $entityObject;
	
	private $entityNamespace;
	
	private $doctrine;
	
	private $metadata;
	
	private $renderParams;
	
	var $fieldMappings;
	
	var $generator;
	
	var $dialogHelper;
	/**
	 * constructor
	 *
	 **/
	function __construct($container,$output,$skeletonDir)
	{
		$this->dialog = $this->getDialogHelper();
		$this->output = $output;
		
        $this->filesystem  = $container->get('filesystem');
        $this->skeletonDir = $skeletonDir;
		
		$this->setDoctrine($container->get('doctrine'));
		
		$this->setOption('format','annotation');
		// create generator
		$this->generator = new UigenGenerator($container->get('filesystem'),$skeletonDir);
	}
	
	public function askForEntity()
	{
		
        $entity = $this->dialog->askAndValidate($this->output, $this->dialog->getQuestion('The Entity shortcut name', ''), array('Sensio\Bundle\GeneratorBundle\Command\Validators', 'validateEntityName'), false, '');
		$this->initEntity($entity);
	}
	
	public function initEntity($entityShortcutName)
	{
		
        $entity = sensioCommand\Validators::validateEntityName($entityShortcutName);
        list($bundle, $entity) = $this->parseShortcutNotation($entity);

        $this->setEntity($entity);
        $this->setBundle($bundle);

		$this->setEntityNamespace($this->getDoctrine()->getEntityNamespace($bundle));
		
		
		$this->getRenderParameterArray();
	}
	
	/**
	 * 
	 */
	public function getEntity()
	{
		return $this->entity;
	}
	
	/**
	 * 
	 */
	public function setEntity($entity){
		$this->entity = $entity;
	}
	
	public function getDialogHelper()
	{
		if (!$this->dialogHelper || get_class($this->dialogHelper) !== 'Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper') {
            $this->dialogHelper = new DialogHelper();
        }
		return $this->dialogHelper;
	}
	
	/**
	 * create and return an array containing information on each column of this entity
	 * @return Array
	 */
	protected function getColumnArray(){
		if(!$this->columnArray){
			if($this->bundle && $this->entity){
				$sample_object = $this->getEntityObject();
				$annotation_reader = new AnnotationReader();
				$reflection = new \ReflectionClass($sample_object);


				foreach($reflection->getProperties() as $property) {

					$prop_ann = $annotation_reader->getPropertyAnnotations($property);

					$this->columnArray[$property->getName()] = array(
						'type' 		=> $prop_ann[0]->type
					);
				}
			}
		}

		return $this->columnArray;
	}
	
	/**
	 * 
	 * @return string
	 **/
	public function getBundle()
	{
		return $this->bundle;
	}
	
	/**
	 * 
	 * @param string
	 */
	public function setBundle($bundle)
	{	
		$this->bundle = $bundle;
	}
	
	/**
	 * @param string 
	 * @param mixed array/string/int/float/bool
	 **/
	public function setOption($key,$value)
	{
		if(!$this->options)$this->options = array();
		
		$this->options[$key] = $value;
	}
	
	/**
	 * 
	 * @param string
	 */
	public function getOption($key){
		if(isset($this->options[$key])){
			return $this->options[$key];
		}
		return false;
	}
	
	
	/**
	 * @param string
	 * @param string 
	 * @param mixed array/string/int/float/bool
	 **/
	public function setColumnOption($column,$key,$value)
	{
		if(!$this->columnArray)$this->getColumnArray();
		
		if(isset($this->columnArray[$column]))$this->columnArray[$column][$key] = $value;
	}
	
	/**
	 * 
	 * @param string
	 */
	public function getColumnOption($column,$key){
		if(isset($this->columnArray[$column][$key])){
			return $this->columnArray[$column][$key];
		}
		return false;
	}
	
    protected function parseShortcutNotation($shortcut)
    {
        $entity = str_replace('/', '\\', $shortcut);

        if (false === $pos = strpos($entity, ':')) {
            throw new \InvalidArgumentException(sprintf('The entity name must contain a : ("%s" given, expecting something like AcmeBlogBundle:Blog/Post)', $entity));
        }

        return array(substr($entity, 0, $pos), substr($entity, $pos + 1));
    }

    public function getMetadata()
    {
		if(!$this->metadata){
			
	        $entityClass = $this->entityNamespace.'\\'.$this->entity;

	        $factory = new MetadataFactory($this->getDoctrine());
			
			$this->metadata = $factory->getClassMetadata($entityClass)->getMetadata();
		}

        return $this->metadata;
    }

	public function getFieldMappings()
    {
		if(!$this->fieldMappings){

			$this->getMetadata();
			$this->fieldMappings = $this->metadata[0]->fieldMappings;
		}
        return $this->fieldMappings;
    }


	public function getCamelizedFieldMappings()
    {
		$this->getFieldMappings();
		// print_r($this->fieldMappings);
		foreach($this->fieldMappings as $i => $mapping){
			$this->fieldMappings[$i]['camelized'] = Inflector::camelize($mapping['fieldName']);
		}
        return $this->fieldMappings;
    }

	/**
	 * print the current column configuration
	 *
	 * @param OutputInterface $output
	 **/
	public function printColumnList()
	{
		
		
		$entity_column_array = $this->getColumnArray($this->getEntity(),$this->getNamespace());
		$i = 0;
		
		foreach($entity_column_array as $column_name => $column_properties) { 

			$this->output->writeln(
			   $i .' '. $column_name .' - ('.str_replace('    ',', ',str_replace(array('
','Array(    ',')'),'',print_r($column_properties,true))).')'
			);
			$i++;
			
		}
	}
	
	
    /**
     * Gets a bundle sample object.
     *
     * @return object
     *
     */
    public function getBundleObject()
    {
        if (null === $this->bundleObject) {
			$classname = '\\'.$this->getBundleNamespace().'\\'.$this->bundle; 

			$this->bundleObject = new $classname();

        }

        return $this->bundleObject;
    }
	
    /**
     * Gets a entity sample object.
     *
     * @return object
     *
     */
    public function getEntityObject()
    {
        if (null === $this->entityObject) {
	

			
				$classname = '\\'.$this->getEntityNamespace().'\\'.$this->entity; 
				$this->entityObject = new $classname();
        }

        return $this->entityObject;
    }

	/**
	 *
	 * @return void
	 **/
	public function setEntityNamespace($entityNamespace)
	{
		$this->entityNamespace = $entityNamespace;
	}

	/**
	 *
	 * @return string
	 **/
	public function getEntityNamespace()
	{
		return $this->entityNamespace;
	}
	
	/**
	 * undocumented function
	 *
	 * @return string
	 **/
	public function getNamespace()
	{
		return $this->getEntityObject()->getNamespace();
	}
	
	/**
	 * @param string
	 **/
	public function setDoctrine($Doctrine)
	{
		$this->doctrine = $Doctrine;
	}
	
	/**
	 * @return Doctrine\Bundle\DoctrineBundle\RegistryDoctrine\Bundle\DoctrineBundle\Registry
	 **/
	public function getDoctrine()
	{
		return $this->doctrine;
	}
	
	/**
	 * @return string
	 **/
	public function getBundleNamespace()
	{
		return substr($this->getEntityNamespace(),0,(strlen($this->getEntityNamespace())-7));
	}
	
	
	/**
	 * @return string
	 **/
	public function getBundlePath()
	{

		$this->getBundleObject();
		
		return $this->bundleObject->getPath();
	}
	
	/**
	 * @return string
	 **/
	public function getViewsPath()
	{
		return $this->getBundlePath().'/Resources/views/'.$this->getEntity();
	}
	
	/**
	 * @return string
	 **/
	public function getPublicPath()
	{
		return $this->getBundlePath().'/Resources/public/';
	}
	
	/**
	 * @return string
	 **/
	public function getControllerPath()
	{
		return $this->getBundlePath().'/Controller/';
	}
	
	/**
	 * @return string
	 **/
	public function getTestPath()
	{
     	return $this->getBundlePath() .'/Tests/Controller/';
	}
	
	/**
	 * @return string
	 **/
	public function getEntityClassName()
	{
		
        $parts = explode('\\', $this->getEntity());
        $entityClass = array_pop($parts);

		return $entityClass;
	}
	
	/**
	 * @return array
	 */
	public function getRenderParameterArray()
	{
		$this->addRenderParams(array(
            'route_prefix'      => $this->getOption('prefix'),
            'route_name_prefix' => str_replace('/', '_', $this->getOption('prefix')),
            'dir'               => $this->skeletonDir,
            'bundle'            => $this->getBundleObject()->getName(),
            'entity'            => $this->getEntity(),
            'entity_class'      => $this->getEntityClassName(),
            'namespace'         => $this->getBundleObject()->getNamespace(),
            'entity_namespace'  => $this->getEntityNamespace(),
            'format'            => $this->getOption('format'),
            'fields'            => $this->getCamelizedFieldMappings(),
			'dnd_column'		=> $this->getOption('dnd_column'),
			'dndAndConstraint'	=> $this->getOption('dndAndConstraint'),
			'public_dir'		=> '/bundles/' . strtolower( str_replace('Bundle','',$this->getBundleObject()->getName()) ),
			'bundle_path'		=> $this->getBundleObject()->getPath(),
        ));

		return $this->renderParams;
	}	







	
	/**
	 * add or update render parameters
	 */
	public function addRenderParams($renderParams)
	{	

		if($this->renderParams === null)$this->renderParams = array();
		foreach($renderParams as $key => $renderParam){
			$this->renderParams[$key] = $renderParam;
		}

	}
	
	public function configureConstraints()
	{
		
		// register constraints
		$this->getCamelizedFieldMappings();
		
		foreach($this->fieldMappings as $i => $field){
			if(strpos($i,'_id')){
				
				$constraint_entityName = explode('_',$i);
				$constraint_entityName = $constraint_entityName[0];
				
		        $this->fieldMappings[$i]['constraint'] = $this->dialog->ask($this->output, $this->dialog->getQuestion('Do you want to configure a foreign key for '.$i, 'yes'), true);
				
				if($this->fieldMappings[$i]['constraint']){
					if($this->getOption('dnd'))$this->setOption('dndAndConstraint',true);
					
					$this->fieldMappings[$i]['constraintBundle'] = $this->dialog->ask($this->output, $this->dialog->getQuestion('foreign key entity bundle ', $this->getBundle()), $this->getBundle());

			        $this->fieldMappings[$i]['constraintEntity'] = $this->dialog->ask($this->output, $this->dialog->getQuestion('foreign key entity name ', $constraint_entityName), $constraint_entityName);
					
				}
		        
				
			}else{
				$this->fieldMappings[$i]['constraint'] = false;
			}
		}
	}
	
	public function configureDragAndDrop()
	{
		
		// Enable drag and drop positioning
		$withDND = $this->dialog->askConfirmation($this->output, $this->dialog->getQuestion('Do you want to configure "draganddrop" drag and drop positioning','no'), false);

		
		$this->setOption('dnd',$withDND);
		
		if($withDND){
			
	        $draganddrop_column = $this->dialog->ask($this->output, $this->dialog->getQuestion('The name of your positioning column', 'pos'), 'pos');
	
	
			$this->setOption('dnd_column',$draganddrop_column);
		}
	}
	
	public function configureRoutePrefix()
	{
		// ask for route prefix
	    $this->output->writeln(array(
	        '',
	        'Define the routes prefix (annotation only)',
	        'prefix: /prefix/, /prefix/new, ...).',
	        '',
	    ));
	    $prefix = $this->dialog->ask($this->output, $this->dialog->getQuestion('Routes prefix', $this->entity), '/'.$this->entity);

		if($prefix[0] === '/')$prefix = substr($prefix,1);
		
		$this->setOption('prefix',$prefix);
	}
	
	public function uigenIntro()
	{
        $this->output->writeln('<comment>Uigen ExtJs-user-interface generator</comment>');

        // namespace
        $this->output->writeln(array(
            '',
            'This command helps you generate code.',
            '',
            'give the entity for which you want to generate a UI.',
            '',
            'use shortcut notation like <comment>AcmeBlogBundle:Post</comment>.',
            '',
        ));
	}
	
	/**
	 * render Template File
	 * @param string
	 * @param string
	 */
	public function renderFile($srcFile,$targetFile)
	{
		$this->generator->renderTemplate($this->skeletonDir, $srcFile, $targetFile, $this->getRenderParameterArray());
	}
	
	public function confirmGeneration()
	{
		if (!$this->dialog->askConfirmation($this->output, $this->dialog->getQuestion('Do you confirm generation', 'yes', '?'), true)) {
            $this->output->writeln('<error>Command aborted</error>');

            return false;
        }
		return true;
	}
	
	public function showGenerationErrors()
	{
		

        $errors = array();
        $runner = $this->dialog->getRunner($this->output, $errors);

        $this->dialog->writeGeneratorSummary($this->output, $errors);
	}
}
