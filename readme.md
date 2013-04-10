#uigen - symfony2 extjs4 generator

This code is currently not maintained.

install:

- cd src/
- git clone https://github.com/cioddi/sf2-uigen.git
- mv sf2-uigen Uigen
- insert ```
            new Uigen\Bundle\GeneratorBundle\UigenGeneratorBundle(),``` into app/Resources/AppKernel.php

videos:

- http://www.youtube.com/watch?v=lLgBOQkPzY0
- http://www.youtube.com/watch?v=yj-NuXEq2OE
- http://www.youtube.com/watch?v=BGpS275i0bg
- http://www.youtube.com/watch?v=cPvRE32ZT-s

usage:

1.) 	php symfony doctrine:generate:entity
2.) 	add 'use Uigen\Bundle\GeneratorBundle\Entity\Entityobject;'
	to entity class and extend class definition by Entityobject
	( Doctrine < 2.2 and PHP < 5.4)
3.) 	php symfony doctrine:schema:update --force
4.) 	php symfony uigen:generate:grid
5.) 	php symfony assets:install web/
6.) 	php symfony cache:clear --env=prod

changelog:

2012-06-02 - foreign keys, filtering for foreign keys
2012-02-02 - optional drag and drop positioning of entries
2012-01-31 - type recognition ('boolean' -> checkcolumn,
					'integer' || 'float' -> numberfield,
					'date' || 'datefield' -> datefield)
		 grid get a working bottom bar for pagination and 		 reload functionality