<?php

namespace Uigen\Bundle\GeneratorBundle\Entity;

/**
 * Uigen\Bundle\GeneratorBundle\Entity\Entityobject
 *
 */
class Entityobject
{
	
	public function toArray() { 
		$reflection = new \ReflectionClass($this); 
		$details = array(); 
		foreach($reflection->getProperties() as $property) { 
			if(!$property->isStatic()) { 
				switch(gettype($this->{'get'.ucfirst($property->getName())}())){
					case 'string':
					case 'integer':
					default:
						$details[$property->getName()] = $this->{'get'.ucfirst($property->getName())}();
						break;
					case 'object':
						$details[$property->getName()] = $this->{'get'.ucfirst($property->getName())}()->format('Y-m-d H:i:s');
						break;
				}
			} 
		} 
		return $details; 
	}

	/**
	 * 
	 * 
	 */
	public function fromArray($data_array) {
		$annotation_reader = new \Doctrine\Common\Annotations\AnnotationReader();
		$reflection = new \ReflectionClass($this); 
		
		foreach($reflection->getProperties() as $property) { 
			$prop_ann = $annotation_reader->getPropertyAnnotations($property);
			
			if(isset($data_array->{$property->getName()}) && $property->getName() != 'id') { 
				switch($prop_ann[0]->type){
					case 'datetime':
					case 'date':
						$this->{'set'.ucfirst($property->getName())}(new \DateTime($data_array->{$property->getName()}));
						break;
					default:
						$this->{'set'.ucfirst($property->getName())}($data_array->{$property->getName()});
						break;
				}
				
			} 
		} 
		return 1; 
	}
	
}