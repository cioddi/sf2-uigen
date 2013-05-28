<?php

namespace Uigen\Bundle\GeneratorBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator as sensioGenerator;

/**
 * Uigen Generator Object
 *
 * @author Max Tobias Weber <maxtobiasweber@gmail.com>
 */
class UigenGenerator extends sensioGenerator\Generator
{

    /**
     * render a Template file
     *
     */
    public function renderTemplate($skeletonDir, $srcFile, $targetFile, $RenderParameterArray)
    {
	
		$this->renderFile($skeletonDir, $srcFile, $targetFile, $RenderParameterArray);
    }
}
