
	/**
	 * Deletes a {{ entity }} entity.
	 *
{% if 'annotation' == format %}
	 * @Route("/{id}/destroy", name="{{ route_name_prefix }}_destroy")
	 * @Method("get")
{% endif %}
	 */
	public function destroyAction($id)
	{
	    $em = $this->getDoctrine()->getManager();
	    $entity = $em->getRepository('{{ bundle }}:{{ entity }}')->find($id);

	    if (!$entity) {
	        return new Response(0);
	    }
		
		{% if 'draganddrop' in actions %}
		$del_pos = $entity->get{{ dnd_column|capitalize }}();
		{% endif %}

	    $em->remove($entity);
	
		{% if 'draganddrop' in actions %}
		$q = $em->createQuery('select tb from {{ bundle }}:{{ entity_class }} tb where tb.{{ dnd_column }} > '.$del_pos);
		$move_entities = $q->getResult();

		
		foreach($move_entities as $move_entity){
			$move_entity->set{{ dnd_column|capitalize }}(($move_entity->get{{ dnd_column|capitalize }}()-1));
			$em->persist($move_entity);
		}
		{% endif %}
		$em->flush();
	
	    return new Response(1);
	}