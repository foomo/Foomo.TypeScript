<?php

/* @var $model Foomo\TypeScript\Frontend\Model */
/* @var $view Foomo\MVC\View */


?>
<?= $view->link('build all', 'buildAll') ?>
<? foreach($model->services as $module => $serviceDescriptions): ?>
<ul>
	<li><?= $view->escape($module); ?></li>
	<ul>
		<? foreach($serviceDescriptions as $serviceDescription): ?>
			<li><?= $serviceDescription->name; ?></li>
		<? endforeach; ?>
	</ul>
</ul>
<? endforeach; ?>