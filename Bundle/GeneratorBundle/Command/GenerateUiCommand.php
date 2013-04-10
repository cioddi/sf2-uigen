<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Uigen\Bundle\GeneratorBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

abstract class GenerateUiCommand extends ContainerAwareCommand
{
	/**
	 * 
	 *
	 **/
	public function askRoutePrefix($output,$dialog,$default)
	{
		
		// route prefix
	    $output->writeln(array(
	        '',
	        'Determine the routes prefix (all the routes will be "mounted" under this',
	        'prefix: /prefix/, /prefix/new, ...).',
	        '',
	    ));
	    $prefix = $dialog->ask($output, $dialog->getQuestion('Routes prefix', $default), '/'.$default);

		if($prefix[0] === '/')$prefix = substr($prefix,1);
		return $prefix;
	}


}
