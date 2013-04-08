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

namespace Foomo;

use Foomo\Utils;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 */
class TypeScriptTest extends \PHPUnit_Framework_TestCase
{
	private static function getMockDir()
	{
		return __DIR__ . DIRECTORY_SEPARATOR . 'TypeScript' . DIRECTORY_SEPARATOR . 'mock';
	}
	private static function getReferencesDir()
	{
		return self::getMockDir() . DIRECTORY_SEPARATOR . 'references';
	}
	public function testExtractDependenciesFromFile()
	{
		$testDir = self::getReferencesDir();
		$deps = TypeScript::extractDependenciesFromFile( $testDir . DIRECTORY_SEPARATOR . 'references.ts');
		foreach(array('foo', 'bar', 'foo' . DIRECTORY_SEPARATOR . 'bar') as $relPath) {
			$this->assertContains(realpath($testDir . DIRECTORY_SEPARATOR . $relPath . '.ts'), $deps);
		}
	}
	public function testResolveDependencies()
	{
		$testDir = self::getReferencesDir();
		$deps = TypeScript::resolveDependencies($testDir . DIRECTORY_SEPARATOR . 'references.ts');
		$this->assertCount(5, $deps);
	}
}