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

use Foomo\Utils;

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
		// self::cleanUp();
	}


	private function cleanUp()
	{
		$bundleFile = self::getBundlePath('bar') . DIRECTORY_SEPARATOR . 'bundle.ts';
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
		return Bundle::create('bar')
			->addFolder(self::getBundlePath('bar'))
			->addFolder(self::getBundlePath('alsoBar'))
			->addTypeDefinition(self::getBundlePath('definitions'))
			->preProcessWithData(array('debug' => true))
			->writeTypeDefinition()
		;
	}

	public function testPaths()
	{
		$bundle = Bundle::create('test')
			->addFolder(self::getBundlePath('bar'))
			->addFolders(array(
				self::getBundlePath('foo'),
				self::getBundlePath('bar')
			))
		;
		$this->assertEquals(array(
			self::getBundlePath('bar'),
			self::getBundlePath('foo')
		), $bundle->paths);
	}
	public function testFolderReferences()
	{
		$bundle = self::getBarBundle();
		Bundle\Compiler::compile($bundle);
		$this->assertContains(
			'/// <reference path=\'test' . DIRECTORY_SEPARATOR . 'nestedBar.ts\' />',
			file_get_contents(self::getBundlePath('bar') . DIRECTORY_SEPARATOR . 'bundle.ts')
		);
	}
}