<?
/* @var $model \Foomo\TypeScript\ErrorRenderer\TypeScriptError */
?>
<li class="errorItem">
	<div class="error" title="<?= htmlspecialchars($model->plainError) ?>">
		<pre><? $i = 0;foreach(explode(PHP_EOL, $model->error) as $line): ?><?= ($i>0?PHP_EOL:'') . htmlspecialchars($line) ?><? $i++; endforeach ?></pre>
	</div>
	<div class="snippet">
		<?= $model->getSnippet() ?>
	</div>
	<div class="file">
		<?= htmlspecialchars($model->file) ?>
		<div class="position">
			line: <?= $model->line ?>, column: <?= $model->column ?>
		</div>
	</div>
</li>