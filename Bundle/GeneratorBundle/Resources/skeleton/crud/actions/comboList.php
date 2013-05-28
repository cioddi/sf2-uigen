	/**
	 * return a json formatted list of {{ entity_class }} entities for combobox.
	 *
{% if 'annotation' == format %}
	 * @Route("/list{{ entity }}_idcombo", name="{{ route_name_prefix }}_list{{entity}}combo")
	 * @Template()
{% endif %}
	 */
	public function list{{entity}}IdcomboAction()
	{
		
	    $start = $this->getRequest()->query->get('start');
	
	    $em = $this->getDoctrine()->getEntityManager();
		
	    $entities = $em->getRepository('{{ bundle }}:{{ entity_class }}')->findBy(
		array()
		);
		
		$return_array[] = array('id' => '','name' => 'all');
		foreach($entities as $entity){
			$return_array[] = array('id' => $entity->getId(),'name' => $entity->getName());
		}
				
	    return new Response(json_encode($return_array));
	}