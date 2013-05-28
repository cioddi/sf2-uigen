
	/**
	 * create a new {{ entity }} entity.
	 *
{% if 'annotation' == format %}
	 * @Route("/create", name="{{ route_name_prefix }}_create")
	 * @Template()
{% endif %}
	 */
	public function createAction()
	{
		$filterArray = array();
		{% for field, metadata in fields %}
		{%- if metadata.constraint == true %}
	    $filterArray['{{field}}'] = $this->getRequest()->query->get('{{field}}');

		{%- endif %}
		{% endfor %}
		$form = $this->getRequest()->request->get('form');
		
	    $em = $this->getDoctrine()->getEntityManager();
	
	    $entity  = new {{ entity_class }}();
	
{% for field, metadata in fields %}		
{% if field != 'id' %}
{% if field == dnd_column and 'draganddrop' in actions %}

		$highest_entity = $em->getRepository('{{ bundle }}:{{ entity_class }}')->findBy(
		$filterArray,    
		array('{{ dnd_column }}' => 'DESC'),
		1);
		if(isset($highest_entity[0])){
	
			$next_pos = $highest_entity[0]->get{{ metadata.camelized|capitalize }}()+1;
			
		}else{
			$next_pos = 1;
		}
		
		$entity->set{{ metadata.camelized|capitalize }}($next_pos);
{% else %}

{% if metadata.constraint == true %}
		$entity->set{{ metadata.camelized|capitalize }}($filterArray['{{field}}']);
{% elseif metadata.type == 'integer' %}
		$entity->set{{ metadata.camelized|capitalize }}(0);
{% elseif metadata.type == 'float' %}
		$entity->set{{ metadata.camelized|capitalize }}(0.0);
{% elseif metadata.type == 'boolean' %}
		$entity->set{{ metadata.camelized|capitalize }}(false);
{% elseif metadata.type == 'date' %}
		$entity->set{{ metadata.camelized|capitalize }}(new \DateTime('now'));
{% elseif metadata.type == 'datetime' %}
		$entity->set{{ metadata.camelized|capitalize }}(new \DateTime('now'));
{% else %}
		$entity->set{{ metadata.camelized|capitalize }}('');
{% endif %}
{% endif %}
{% endif %}
{% endfor  %}

		if($form)$entity->fromArray(json_decode($form));
	    $em->persist($entity);
	    $em->flush();

    
	    return new Response(1);
	}
