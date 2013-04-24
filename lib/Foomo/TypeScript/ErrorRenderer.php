<?php

/*
 * This file is part of the foomo Opensource Framework.
 *
 * The foomo Opensource Framework is free software: you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General Public License as
 * published  by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * The foomo Opensource Framework is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along with
 * the foomo Opensource Framework. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Foomo\TypeScript;

use Foomo\CliCall;
use Foomo\MVC;
use Foomo\TypeScript\ErrorRenderer\TypeScriptError;
use Foomo\View;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 */
class ErrorRenderer
{
	public static function renderError(CliCall $call)
	{
		MVC::abort();
		ob_end_clean();
		$doc = new \Foomo\HTMLDocument();
		$doc->addStylesheets(array(
			Module::getHtdocsPath('css/errorRenderer.css')
		));
		$doc->setTitle('tsc did not like what you gave it to ...');
		$doc->addBody(
			'<h1>TypeScript compiler errors</h1>' .
			'<div class="cmd">' . htmlspecialchars($call->renderCommand()) . '</div>' .
			'<div class="error"><code>' . implode('<br>', explode(PHP_EOL, htmlspecialchars($call->stdErr))) . '</code></div>' .
			'<ul>'
		);
		$plainErrors = array();
		foreach(explode(PHP_EOL, $call->stdErr) as $plainError) {
			if(substr($plainError, 0, 1) == chr(9)) {
				$plainErrors[count($plainErrors)-1] .= PHP_EOL . $plainError;
			} else {
				$plainErrors[] = $plainError;
			}
		}
		foreach($plainErrors as $plainError) {
			$error = new TypeScriptError($plainError);
			$view = View::fromFile(
				implode(DIRECTORY_SEPARATOR, array(
					Module::getViewsDir(),
					'Foomo',
					'TypeScript',
					'ErrorRenderer',
					'TypeScriptError.tpl'
				)),
				$error
			);
			$doc->addBody($view->render());
		}
		$doc->addBody('</ul>');
		echo $doc;
	}
}