<?php

namespace {{ namespace }}\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
{% if 'annotation' == format -%}
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
{%- endif %}

use {{ namespace }}\Entity\{{ entity }};
{% if 'new' in actions or 'edit' in actions %}
use {{ namespace }}\Form\{{ entity }}Type;
{% endif %}

/**
 * {{ entity }} controller.
 *
{% if 'annotation' == format %}
 * @Route("/{{ route_prefix }}")
{% endif %}
 */
class {{ entity_class }}Controller extends Controller
{

    {%- if 'index' in actions %}
        {%- include 'actions/index.php' %}
    {%- endif %}


    {%- if 'create' in actions %}
        {%- include 'actions/create.php' %}
    {%- endif %}


    {%- if 'read' in actions %}
        {%- include 'actions/read.php' %}
    {%- endif %}


    {%- if 'update' in actions %}
        {%- include 'actions/update.php' %}
    {%- endif %}


    {%- if 'destroy' in actions %}
        {%- include 'actions/destroy.php' %}
    {%- endif %}


    {%- if 'draganddrop' in actions %}
        {%- include 'actions/draganddrop.php' %}
    {%- endif %}

    {%- include 'actions/comboList.php' %}

}