<?php

echo \Foomo\HTMLDocument::getInstance()
	->addBody(\Foomo\MVC::run(new \Foomo\TypeScript\Frontend(), null, false, true))
	->addJavascriptsToBody(\Foomo\TypeScript\Bundle\Manager::getInstance()->resolveBundles())
;