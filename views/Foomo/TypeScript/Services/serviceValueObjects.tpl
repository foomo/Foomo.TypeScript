<?php

use Foomo\TypeScript\Rosetta;

?>
// this is an autogenerated file do not edit it
// generated on <?= \Foomo\Utils::getServerUrl() ?> <?= date('Y-m-d H:i:s') ?>


<?

foreach($model->types as $type):
	if(empty($type->type) || !class_exists($type->type)) {
		continue;
	}
?>
module <?= Rosetta::getIntefaceModule($type) ?> {
<?
if(true || !empty($type->phpDocEntry->comment)):
	echo Rosetta::getJSDocComment($type, 1);
?>
<? endif; ?>
	export class Vo<?= basename(str_replace('\\', DIRECTORY_SEPARATOR, $type->type)) ?> {
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
			$constantType = 'bool';
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
}


<? endforeach ?>


