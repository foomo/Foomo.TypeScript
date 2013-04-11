<?
	/* @var $model \Foomo\TypeScript\Services\TypeDefinitionRenderer */
?>
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
			result: <?= $model->renderInlineType($returnType, 3) ?>;
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


<?
// var_dump($model);