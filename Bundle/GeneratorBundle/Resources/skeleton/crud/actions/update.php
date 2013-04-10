
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
		
	        if ($entity) {
				unset($data_entity->id);
				$entity->fromArray($data_entity);
	
				$em->persist($entity);
	        }
		}
	
	    $em->flush();
	
	    return new Response(1);
	}