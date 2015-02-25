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

use Foomo\Bundle\Compiler\Result;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 */
class BundleTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		self::cleanUp();
	}
	public function tearDown()
	{
		self::cleanUp();
	}
	private function cleanUp()
	{
        $bundle = self::getBarBundle();
		$bundleFile =  Bundle\Compiler::getBundleFilename($bundle);
		if(file_exists($bundleFile)) {
			unlink($bundleFile);
		}
	}
	private static function getBundlePath($bundleName)
	{
		return __DIR__ . '/mock/bundles/' . $bundleName;
	}
	private static function getBarBundle()
	{
		return Bundle::create('bar', self::getBundlePath('bar'))
			->preProcessWithData(array('debug' => true))
			->writeTypeDefinition()
		;
	}
	public function testFolderReferences()
	{
		$bundle = self::getBarBundle();
		Bundle\Compiler::compile($bundle, $result = new Result());

		$this->assertContains(
			'/// <reference path="test/nestedBar.ts" />',
			file_get_contents(Bundle\Compiler::getBundleFilename($bundle))
		);
	}
}