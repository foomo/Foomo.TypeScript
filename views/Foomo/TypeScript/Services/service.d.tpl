<?
	/* @var $model \Foomo\TypeScript\Services\TypeDefinitionRenderer */
?>
module <?= $model->module ?> {
<?
foreach($model->operations as $op):
	/* @var $op \Foomo\Services\Reflection\ServiceOperation */
	if(is_object($op->returnType)) {
		$returnType = new \Foomo\Services\Reflection\ServiceObjectType($op->returnType->type);
	} else {
		$returnType = new \Foomo\Services\Reflection\ServiceObjectType('null');
	}

?>
	export class Operation<?= ucfirst($op->name) ?> {
		execute(successHandler:(op: <?= $model->renderInlineType($returnType, 2) ?>) => undefined );
		error();
	}
<? endforeach; ?>
	export var operations = {
<?
	$i = 0;
	foreach($model->operations as $op):
		/* @var $op \Foomo\Services\Reflection\ServiceOperation */
		$parms = array();
		foreach($op->parameters as $name => $parameterType) {
			$parms[] = $name . ':' . $model->renderInlineType(new \Foomo\Services\Reflection\ServiceObjectType($parameterType));
		}

?>
		<?= $op->name ?>: (<?= implode(', ', $parms) ?>) => Operation<?= ucfirst($op->name) . (++$i < count($model->operations)?',':'')?>

<? endforeach; ?>
	}
}


<?
// var_dump($model);