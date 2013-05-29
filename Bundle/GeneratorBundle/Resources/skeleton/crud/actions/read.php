
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
		$filterArray = array();
		$return_array = array();
	    $start = $this->getRequest()->query->get('start');
	
		{% for field, metadata in fields %}{%- if metadata.constraint == true %}
	    if($this->getRequest()->query->get('{{field}}') != 0)$filterArray['{{field}}'] = $this->getRequest()->query->get('{{field}}');

		{%- endif %}
		{% endfor %}
		
	    $em = $this->getDoctrine()->getEntityManager();
		
		{% if ('draganddrop' in actions) %}
		
	    $entities = $em->getRepository('{{ bundle }}:{{ entity_class }}')->findBy(
		$filterArray,    
		array('{{ dnd_column }}' => 'ASC'),
		25,
		$start
		);
		{% else %}
	    $entities = $em->getRepository('{{ bundle }}:{{ entity_class }}')->findBy(
		$filterArray,    
		array('id' => 'DESC'),
		25,
		$start
		);
		{% endif %}
		
		foreach($entities as $entity){
			$return_array[] = $entity->toArray();
		}
		if(!isset($return_array))$return_array = array();

		$returnObject['items'] = $return_array;
				
		$query = $em->createQuery('SELECT COUNT(e.id) FROM {{ bundle }}:{{ entity_class }} e');
		$returnObject['count'] = $query->getSingleScalarResult();
		
	    return new Response(json_encode($returnObject));
	}