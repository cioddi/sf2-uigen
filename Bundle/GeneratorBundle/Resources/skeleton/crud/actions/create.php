
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

	    $em = $this->getDoctrine()->getManager();
	
	    $entity  = new {{ entity_class }}();
	
{% for field, metadata in fields %}		
{% if field != 'id' %}
{% if field == dnd_column and 'draganddrop' in actions %}

		$highest_entity = $em->getRepository('{{ bundle }}:{{ entity_class }}')->findBy(
		array(),    
		array('{{ dnd_column }}' => 'DESC'),
		1);
		if(isset($highest_entity[0])){
	
			$next_pos = $highest_entity[0]->get{{ dnd_column|capitalize }}()+1;
			
		}else{
			$next_pos = 1;
		}
		
		$entity->set{{ field|capitalize }}($next_pos);
{% else %}
{% if metadata.type == 'integer' %}
		$entity->set{{ field|capitalize }}(0);
{% elseif metadata.type == 'float' %}
		$entity->set{{ field|capitalize }}(0.0);
{% elseif metadata.type == 'boolean' %}
		$entity->set{{ field|capitalize }}(false);
{% elseif metadata.type == 'date' %}
		$entity->set{{ field|capitalize }}(new \DateTime('now'));
{% elseif metadata.type == 'datetime' %}
		$entity->set{{ field|capitalize }}(new \DateTime('now'));
{% else %}
		$entity->set{{ field|capitalize }}('');
{% endif %}
{% endif %}
{% endif %}
{% endfor  %}

	    $em->persist($entity);
	    $em->flush();

    
	    return new Response(1);
	}
