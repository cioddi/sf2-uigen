
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
		$filterArray = array();
		
	    $em = $this->getDoctrine()->getEntityManager();
	    $entity = $em->getRepository('{{ bundle }}:{{ entity }}')->find($id);

	    if (!$entity) {
	        return new Response(0);
	    }
		
		{% for field, metadata in fields %}
		{%- if metadata.constraint == true %}
	    $filterArray['{{field}}'] = $entity->get{{ metadata.camelized|capitalize }}();

		{%- endif %}
		{% endfor %}
		
		{% if 'draganddrop' in actions %}
		$del_pos = $entity->get{{ dnd_column|capitalize }}();
		{% endif %}

	    $em->remove($entity);
		
		$em->flush();
		{% if 'draganddrop' in actions %}
		$this->fixpos($filterArray,$em);
		{% endif %}
	
	    return new Response(1);
	}