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


/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 */
class CompilerTest extends \PHPUnit_Framework_TestCase
{
	public function testRelativePathInSameFolder()
	{
		$this->assertEquals(
			'test.ts',
			Compiler::getRelativePathFromFolderToFile('/foo', '/foo/test.ts')
		);
		$this->assertEquals(
			'tief/tiefer/test.ts',
			Compiler::getRelativePathFromFolderToFile('/toll', '/toll/tief/tiefer/test.ts')
		);
	}
	public function testRelativeForMyNeighbour()
	{
		$this->assertEquals(
			'../bar/test.ts',
			Compiler::getRelativePathFromFolderToFile('/foo/la/boo', '/foo/la/bar/test.ts')
		);
		$this->assertEquals(
			'../alsoBar/also.ts',
			Compiler::getRelativePathFromFolderToFile(
				'/var/www/paperRoll/modules/Foomo.TypeScript/tests/Foomo/TypeScript/mock/bundles/bar',
				'/var/www/paperRoll/modules/Foomo.TypeScript/tests/Foomo/TypeScript/mock/bundles/alsoBar/also.ts'
			)
		);
	}
	public function testNoNeighbours()
	{
		$this->assertEquals(
			'/foo/la/bar/test.ts',
			Compiler::getRelativePathFromFolderToFile('/bla', '/foo/la/bar/test.ts')
		);
	}

}