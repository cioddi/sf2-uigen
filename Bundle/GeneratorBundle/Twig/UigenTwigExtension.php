<?php
// from http://www.solidwebcode.com/web-development/twig-extensions-symfony-2/
namespace Uigen\Bundle\GeneratorBundle\Twig;
use Twig_Extension;
use Twig_Filter_Method;
use Twig_Function_Method;
use Doctrine\Common\Util\Inflector;

class UigenTwigExtension extends Twig_Extension
{
	
public function getFilters()
{
return array(
'camelize' => new Twig_Filter_Method($this, 'camelize')
);
}

public function camelize($string, $prefix = '')
{
    return Inflector::camelize($string);
}


public function getName()
{
return 'UigenTwigExtension';
}
}

