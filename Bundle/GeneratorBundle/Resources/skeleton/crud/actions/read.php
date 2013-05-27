
	/**
	 * return a json formatted list of {{ entity_class }} entities.
	 *
{% if 'annotation' == format %}
	 * @Route("/read", name="{{ route_name_prefix }}_read")
	 * @Template()
{% endif %}
	 */
	public function readAction()
	{
		
	    $start = $this->getRequest()->query->get('start');
	
	    $em = $this->getDoctrine()->getManager();
		
		{% if ('draganddrop' in actions) %}
		
	    $entities = $em->getRepository('{{ bundle }}:{{ entity_class }}')->findBy(
		array(),    
		array('{{ dnd_column }}' => 'ASC'),
		25,
		$start
		);
		{% else %}
	    $entities = $em->getRepository('{{ bundle }}:{{ entity_class }}')->findBy(
		array(),    
		array('id' => 'DESC'),
		25,
		$start
		);
		{% endif %}
		
		foreach($entities as $entity){
			$return_array[] = $entity->toArray();
		}
		if(!isset($return_array))$return_array = array();
		$returnObject = new \stdClass;
		
		$returnObject->items = $return_array;
				
		$query = $em->createQuery('SELECT COUNT(e.id) FROM {{ bundle }}:{{ entity_class }} e');
		$returnObject->count = $query->getSingleScalarResult();
		
	    return new Response(json_encode($returnObject));
	}