<?
	/* @var $model \Foomo\TypeScript\Services\TypeDefinitionRenderer */
?>

<?

foreach($model->types as $type):
	if(empty($type->type) || !class_exists($type->type)) {
		continue;
	}
	?>
module <?= \Foomo\TypeScript\Rosetta::getIntefaceModule($type) ?> {
	interface I<?= basename(str_replace('\\', DIRECTORY_SEPARATOR, $type->type)) ?> {
<?
	foreach($type->props as $name => $propType):
		/* @var $propType \Foomo\Services\Reflection\ServiceObjectType */
		?>
		<?= $name ?>: <?= \Foomo\TypeScript\Rosetta::getInterfaceName($propType) ?><?= ($propType->isArrayOf?'[]':'') ?>;
<? endforeach; ?>
	}
}
<? endforeach ?>
declare module <?= $model->module ?> {
	class Operation {
		public pending: bool;
	}
<?
foreach($model->operations as $op):
	/* @var $op \Foomo\Services\Reflection\ServiceOperation */
	if(is_object($op->returnType)) {
		$returnType = new \Foomo\Services\Reflection\ServiceObjectType($op->returnType->type);
	} else {
		$returnType = new \Foomo\Services\Reflection\ServiceObjectType('null');
	}
	$opName  = 'Operation' . ucfirst($op->name);
?>
	export class <?= $opName ?> extends Operation {
		public data: {
			exception: any;
			arguments: any[];
			messages: any[];
			result: <?= \Foomo\TypeScript\Rosetta::getInterfaceName($returnType) ?>;
		};
		execute(successHandler:(op: <?= $opName ?>) => undefined );
		error(errorHandler:(op: <?= $opName ?>) => undefined);
	}
<? endforeach; ?>
	declare var operations : {
<?
	$i = 0;
	foreach($model->operations as $op):
		/* @var $op \Foomo\Services\Reflection\ServiceOperation */
		$parms = array();
		foreach($op->parameters as $name => $parameterType) {
			$parms[] = $name . ':' . $model->renderInlineType(new \Foomo\Services\Reflection\ServiceObjectType($parameterType));
		}

?>
		<?= $op->name ?>: (<?= implode(', ', $parms) ?>) => Operation<?= ucfirst($op->name) . (++$i < count($model->operations)?';':';')?>

<? endforeach; ?>
	}
}

