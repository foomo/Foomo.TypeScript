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

use Foomo\Template;
use Foomo\TypeScript\Bundle;
use Foomo\View;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 */
class Compiler
{
	public static function preProcess(Bundle $bundle)
	{
		// preprocess things
		$template = View::fromFile($bundleFile = $bundle->getBundleFile());
		file_put_contents(
			substr($bundleFile, 0 , -4),
			self::renderReferences($bundle->getAllTypescriptFiles(), dirname($bundleFile)) . PHP_EOL .
			self::renderReferences($bundle->getAllTypeDefinitionFiles(), dirname($bundleFile)) . PHP_EOL .
			$template->render($bundle->preProcessingData)
		);
	}
	private static function renderReferences(array $references,  $bundleFolder)
	{
		$ret = '';
		foreach($references as $reference) {
			$relativePath = self::getRelativePathFromFolderToFile($bundleFolder, $reference);
			$ret .= '/// <reference path=\'' . $relativePath . '\' />' . PHP_EOL;
		}
		return $ret;
	}
	public static function getRelativePathFromFolderToFile($from, $to)
	{
		$fromParts = array_slice(explode(DIRECTORY_SEPARATOR, $from), 1);
		$toParts = array_slice(explode(DIRECTORY_SEPARATOR, $to), 1);
		$inCommon = 0;
		$samePath = true;
		for($i = 0; $i < count($fromParts); $i++) {
			if(count($toParts) < $i || $fromParts[$i] != $toParts[$i]) {
				$samePath = false;
			}
			if($samePath) {
				$inCommon ++;
			}
		}
		if($inCommon > 0 && !$samePath) {
			$runUpPart = str_repeat('..' . DIRECTORY_SEPARATOR, count($fromParts) - $inCommon);
			$sliceFrom = count($toParts) - $inCommon;
			$runDownPart = implode(DIRECTORY_SEPARATOR, array_slice($toParts, - $sliceFrom));
			return $runUpPart . $runDownPart;
		} else if($samePath) {
			return substr($to, strlen($from) + 1);
		} else {
			return $to;
		}
	}
	public static function compile(Bundle $bundle)
	{
		self::preProcess($bundle);

	}
}