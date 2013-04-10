
	/**
	 * drops an existing {{ entity }} entity to another position.
	 *
	{% if 'annotation' == format %}
	 * @Route("/draganddrop", name="{{ route_name_prefix }}_draganddrop")
	 * @Method("post")
	{% endif %}
	 */
	public function draganddropAction()
	{
	
	    $drag_id = $this->getRequest()->request->get('drag_id');
	    $target_id = $this->getRequest()->request->get('target_id');
	    $position = $this->getRequest()->request->get('position');

		$em = $this->getDoctrine()->getEntityManager();
		
		$drag_entity = $em->getRepository('{{ bundle }}:{{ entity_class }}')->find($drag_id);
		
		$target_entity = $em->getRepository('{{ bundle }}:{{ entity_class }}')->find($target_id);
		
		
		if($drag_entity->get{{ dnd_column|capitalize }}() < $target_entity->get{{ dnd_column|capitalize }}()){
			
			switch($position){
				case 'after':
					//all (pos > $drag_entity->pos && pos <= $target_entity->pos) [ pos--;$drag_entity->pos = $target_entity->pos]
					$q = $em->createQuery('select tb from {{ bundle }}:{{ entity_class }} tb where tb.{{ dnd_column }} > '.$drag_entity->get{{ dnd_column|capitalize }}().' and tb.{{ dnd_column }} <= '.$target_entity->get{{ dnd_column|capitalize }}());
					$move_entities = $q->getResult();
					
					$drag_entity->set{{ dnd_column|capitalize }}($target_entity->get{{ dnd_column|capitalize }}());
					break;
				case 'before':
					//all (pos > $drag_entity->pos && pos < $target_entity->pos) [ pos--;$drag_entity->pos = ($target_entity->pos-1)]
					$q = $em->createQuery('select tb from {{ bundle }}:{{ entity_class }} tb where tb.{{ dnd_column }} > '.$drag_entity->get{{ dnd_column|capitalize }}().' and tb.{{ dnd_column }} < '.$target_entity->get{{ dnd_column|capitalize }}());
					$move_entities = $q->getResult();
					
					$drag_entity->set{{ dnd_column|capitalize }}(($target_entity->get{{ dnd_column|capitalize }}()-1));
					break;
			}
				
			foreach($move_entities as $move_entity){
				$move_entity->set{{ dnd_column|capitalize }}(($move_entity->get{{ dnd_column|capitalize }}()-1));
				$em->persist($move_entity);
			}
			
		}else{
			
			switch($position){
				case 'after':
					//all (pos < $drag_entity->pos && pos > $target_entity->pos) [ pos--;$drag_entity->pos = ($target_entity->pos+1)]
					$q = $em->createQuery('select tb from {{ bundle }}:{{ entity_class }} tb where tb.{{ dnd_column }} < '.$drag_entity->get{{ dnd_column|capitalize }}().' and tb.{{ dnd_column }} > '.$target_entity->get{{ dnd_column|capitalize }}());
					$move_entities = $q->getResult();
					
					$drag_entity->set{{ dnd_column|capitalize }}($target_entity->get{{ dnd_column|capitalize }}()+1);
					break;
				case 'before':
					//all (pos < $drag_entity->pos && pos >= $target_entity->pos) [ pos--;$drag_entity->pos = $target_entity->pos]
					$q = $em->createQuery('select tb from {{ bundle }}:{{ entity_class }} tb where tb.{{ dnd_column }} < '.$drag_entity->get{{ dnd_column|capitalize }}().' and tb.{{ dnd_column }} >= '.$target_entity->get{{ dnd_column|capitalize }}());
					$move_entities = $q->getResult();

					$drag_entity->set{{ dnd_column|capitalize }}($target_entity->get{{ dnd_column|capitalize }}());
					break;
			}
				
			foreach($move_entities as $move_entity){
				$move_entity->set{{ dnd_column|capitalize }}(($move_entity->get{{ dnd_column|capitalize }}()+1));
				$em->persist($move_entity);
			}
			
		}
		
		$em->persist($drag_entity);
	    $em->flush();

	    return new Response(1);
	}