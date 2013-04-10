<?php

/* @var $model Foomo\TypeScript\Frontend\Model */
/* @var $view Foomo\MVC\View */


?>
<?= $view->link('build all', 'buildAll') ?>
<ul>
	<? foreach($model->buildReport as $report): ?>
		<li><?= $view->escape($report) ?></li>
	<? endforeach; ?>
</ul>
