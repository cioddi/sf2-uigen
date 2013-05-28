
	/**
	 * Edits an existing {{ entity }} entity.
	 *
{% if 'annotation' == format %}
	 * @Route("/update", name="{{ route_name_prefix }}_update")
	 * @Method("post")
{% endif %}
	 */
	public function updateAction()
	{

	    $data = $this->getRequest()->request->get('data'); 

		$entities = json_decode($data);

		$em = $this->getDoctrine()->getEntityManager();
	
		foreach($entities as $data_entity){
		 
			$entity = $em->getRepository('{{ bundle }}:{{ entity }}')->find($data_entity->id);
			
			$filterArray = array();
			
			
	        if ($entity) {
				{% for field, metadata in fields %}
				{%- if metadata.constraint == true %}
			    $filterArray['{{field}}'] = $entity->get{{ metadata.camelized|capitalize }}();

				{%- endif %}
				{% endfor %}
				
				
				unset($data_entity->id);
				$entity->fromArray($data_entity);
	
	
				
				{% for field, metadata in fields %}
				{%- if metadata.constraint == true %}
			    $new_filterArray['{{field}}'] = $data_entity->{{ field }};

				{%- endif %}
				{% endfor %}
				
				{% if 'draganddrop' in actions %}
				
				if($new_filterArray != $filterArray)$entity->set{{ dnd_column|capitalize }}($this->getNextPos($new_filterArray,$em));
				{% endif %}
				$em->persist($entity);
				
	        }
			
		    $em->flush();
		
			{% if 'draganddrop' in actions %}
			if($new_filterArray != $filterArray)$this->fixpos($filterArray,$em);
			{% endif %}
		}
	
	
	    return new Response(1);
	}