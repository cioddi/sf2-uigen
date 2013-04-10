	
	/**
	 * Show Grid
	 *
{% if 'annotation' == format %}
     * @Route("/", name="{{ route_name_prefix }}")
     * @Template()
{% endif %}
	 */
	public function indexAction()
	{

		return $this->render('{{ bundle }}:{{ entity|replace({'\\': '/'}) }}:index.html.twig');
	}