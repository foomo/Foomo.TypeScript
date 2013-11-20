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

use Foomo\JS\Bundle\Compiler\Result;
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
		$newContents = $rendered . self::renderReferences($bundle->getAllTypescriptFiles(), dirname($bundleFile));
 		$oldContents = file_get_contents($bundleFilename);
		if($newContents != $oldContents) {
			file_put_contents(
				$bundleFilename,
				$newContents
			);
		}
		return $bundleFilename;
	}
	private static function renderReferences(array $references,  $bundleFolder)
	{
		$ret = '';
		foreach($references as $reference) {
			$relativePath = \Foomo\Typescript::getRelativePathFromFolderToFile($bundleFolder, $reference);
			if(in_array($relativePath, array('bundle.ts', 'bundle.d.ts'))) {
				continue;
			}
			$ret .= '/// <reference path=\'' . $relativePath . '\' />' . PHP_EOL;
		}
		return $ret;
	}
	public static function compile(Bundle $bundle, Result $result)
	{
		$bundleFilename = self::preProcess($bundle);
		TypeScript::renderTemplates($bundle->templateJobs);
		$tsCompiler = TypeScript::create($bundleFilename)
			->displayCompilerErrors($bundle->debug)
			->generateDeclaration($bundle->writeTypeDefinition)
			->target($bundle->target)
		;
		foreach($bundle->templateJobs as $templateJob) {
			$tsCompiler->lookForTemplates($templateJob['dir'], $templateJob['renderer']);
		}
		$tsCompiler->compile();
		$result->jsFiles[] = $tsCompiler->getOutputFilename();
		$result->jsLinks[] = $tsCompiler->getOutputPath();

	}
}