<?php

use Foomo\TypeScript\Rosetta;

?>
// this is an autogenerated file do not edit it


<?

$renderTypeDunc = function($type) {

?>
<?
if(true || !empty($type->phpDocEntry->comment)):
	echo Rosetta::getJSDocComment($type, 1);
?>
<? endif; ?>
	export class <? $classParts = explode('.', Rosetta::getInterfaceName($type)); echo end($classParts) ?> {
<?
foreach($type->constants as $name => $value):
	$constantType = 'any';
	switch(true) {
		case is_string($value):
			$constantType = 'string';
			break;
		case is_integer($value):
		case is_float($value):
			$constantType = 'number';
			break;
		case is_bool($value):
			$constantType = 'boolean';
			break;
	}
?>
		static <?= $name ?>:<?= $constantType ?> = <?= json_encode($value) ?>;
<? endforeach; ?>
<?

$refl = new ReflectionClass($type->type);
$defaultValues = $refl->getDefaultProperties();
foreach($type->props as $name => $propType):
	/* @var $propType \Foomo\Services\Reflection\ServiceObjectType */
	echo Rosetta::getJSDocComment($propType, 2);
	$defaultValue = $defaultValues[$name];
	if(!is_scalar($defaultValue)) {
		$defaultValue = null;
	}
?>
		<?= $name ?>:<?= Rosetta::getInterfaceName($propType) ?><?= ($propType->isArrayOf?'[]':'') ?><?= ($defaultValue !== null)?' = ' . json_encode($defaultValue):'' ?>;
<? endforeach; ?>

	}
<?
};

$mapRenderFunc = function($typeRenderFunc, $mapRenderFunc, \Foomo\TypeScript\VoModuleMapper\ModuleVoMap $map, $namespace, $level = 0) {
	$module = implode('.', explode('\\', substr($map->namespace, (strlen($namespace) == 0)?0:strlen($namespace)+1)));
	//$module = str_replace('\\', '.', $map->namespace);
	echo str_repeat('	', $level) . ($level>0?'export ':'') . 'module ' . $module . ' {' . PHP_EOL;
	foreach($map->types as $type) {
		ob_start();
		$typeRenderFunc($type, $level+1);
		$typeRendering = ob_get_clean();
		$lines = explode(PHP_EOL, $typeRendering);
		$classIndent = str_repeat('	', $level);
		foreach($lines as $line) {
			echo $classIndent . $line . PHP_EOL;
		}
	}
	foreach($map->voMaps as $nestedMap) {
		$mapRenderFunc($typeRenderFunc, $mapRenderFunc, $nestedMap, $map->namespace, $level + 1);
	}
	echo str_repeat('	', $level) . '}' . PHP_EOL;
};

foreach($model->maps as $map) {
	$mapRenderFunc($renderTypeDunc, $mapRenderFunc, $map, '', 0);
}
?>


