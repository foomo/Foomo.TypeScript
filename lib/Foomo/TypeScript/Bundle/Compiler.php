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

namespace Foomo\TypeScript\Bundle;

use Foomo\Bundle\Compiler\Result;
use Foomo\JS;
use Foomo\Template;
use Foomo\TypeScript\Bundle;
use Foomo\TypeScript;
use Foomo\View;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 */
class Compiler
{
	/**
	 * @param Bundle $bundle
	 * @return string
	 */
	public static function preProcess(Bundle $bundle)
	{
		// preprocess things
		$bundleFile = $bundle->getBundleFile();
		$bundleFilename = substr($bundleFile, 0 , -4);
		$rendered = '';
		if(file_exists($bundleFile)) {
			$template = View::fromFile($bundleFile);
			$rendered = $template->render($bundle->preProcessingData) . PHP_EOL;
		}
		$newContents = '// do not edit this file - it was generated - edit ./bundle.ts.tpl' . PHP_EOL . PHP_EOL . $rendered;
 		$oldContents = file_get_contents($bundleFilename);
		if($newContents != $oldContents) {
			file_put_contents(
				$bundleFilename,
				$newContents
			);
		}
		return $bundleFilename;
	}

	public static function compile(Bundle $bundle, Result $result)
	{
		$bundleFilename = self::preProcess($bundle);
		TypeScript::renderTemplates($bundle->templateJobs);
		$tsCompiler = TypeScript::create($bundleFilename)
			->name($bundle->name)
			->displayCompilerErrors($bundle->debug)
			->generateDeclaration($bundle->writeTypeDefinition)
			->target($bundle->target)
		;
		foreach($bundle->templateJobs as $templateJob) {
			$tsCompiler->lookForTemplates($templateJob['dir'], $templateJob['renderer']);
		}
		$tsCompiler->compile();
		$result->mimeType = Result::MIME_TYPE_JS;
		if($bundle->debug) {
			$result->files[] = $tsCompiler->getOutputFilename();
			$result->links[] = $tsCompiler->getOutputPath();
		} else {
			$jsCompiler = JS::create($tsCompiler->getOutputFilename())
				->name($bundle->name)
				->compress()
				->compile()
			;
			$result->files[] = $jsCompiler->getOutputFilename();
			$result->links[] = $jsCompiler->getOutputPath();
		}
	}
}