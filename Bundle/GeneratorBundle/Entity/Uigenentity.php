<?php

namespace Uigen\Bundle\GeneratorBundle\Entity;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\DoctrineBundle\Mapping\MetadataFactory;
use Sensio\Bundle\GeneratorBundle\Command as sensioCommand;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Bundle\DoctrineBundle\Registry;

/**
 *
 *
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
	/**
	 * constructor
	 *
	 **/
	function __construct($entityShortcutName,$doctrine)
	{

    $entity = sensioCommand\Validators::validateEntityName($entityShortcutName);
    list($bundle, $entity) = $this->parseShortcutNotation($entity);

    $this->setEntity($entity);
    $this->setBundle($bundle);

		$this->setDoctrine($doctrine);
		$this->setEntityNamespace($this->getDoctrine()->getEntityNamespace($bundle));

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
		
        $entityClass = $this->entityNamespace.'\\'.$this->entity;
		
        $factory = new MetadataFactory($this->getDoctrine());

        return $factory->getClassMetadata($entityClass)->getMetadata();
    }

	/**
	 * print the current column configuration
	 *
	 * @param OutputInterface $output
	 **/
	public function printColumnList(OutputInterface $output)
	{
		
		
		$entity_column_array = $this->getColumnArray($this->getEntity(),$this->getNamespace());
		$i = 0;
		
		foreach($entity_column_array as $column_name => $column_properties) { 

			$output->writeln(
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
	
			eval('$this->bundleObject = new \\'.$this->getBundleNamespace().'\\'.$this->bundle.'();');
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
	
			eval('$this->entityObject = new \\'.$this->getEntityNamespace().'\\'.$this->entity.'();');
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
	
	
	
	
}