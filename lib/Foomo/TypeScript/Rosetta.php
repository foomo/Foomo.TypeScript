<?php
/*
 * This file is part of the foomo Opensource Framework.
 * 
 * The foomo Opensource Framework is free software: you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General Public License as
 * published  by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * 
 * The foomo Opensource Framework is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License along with
 * the foomo Opensource Framework. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Foomo\TypeScript;

use Foomo\MVC\AbstractApp;
use Foomo\Services\Reflection\ServiceObjectType;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan
 */

class Rosetta
{
	/**
	 * @param ServiceObjectType $type
	 * @return string
	 */
	public static function getIntefaceModule(ServiceObjectType $type)
	{
		/*
		if(class_exists($type->type) && class_exists($type->namespace)) {
			$ns = $type->namespace . 'Objects';
		} else {
			$ns = $type->namespace;
		}
		*/
		return str_replace('\\', '.', $type->namespace);
	}

	/**
	 * @param ServiceObjectType $type
	 *
	 * @return string
	 */
	public static function getInterfaceName(ServiceObjectType $type = null)
	{
		if($type) {
			if(class_exists($type->type)) {
				$ns = self::getIntefaceModule($type);
				return  $ns . (!empty($ns)?'.':'') . end(explode('\\', $type->type)) . 'Vo';
			} else {
				switch($type->type) {
					case 'mixed':
						return 'any';
					case 'array':
						return 'any[]';
					case 'int':
					case 'integer':
					case 'float':
					case 'double':
						return 'number';
					case 'bool':
					case 'boolean':
						return 'bool';
					case 'string':
						return 'string';;
					case 'null':
						return 'undefined';;
					default:
						return 'wtf - ' . $type->type;
				}
			}
		} else {
			return 'undefined';
		}
	}
	public static function getJSDocComment(ServiceObjectType $type, $level = 0)
	{
		if(!empty($type->phpDocEntry->comment)) {
			$whitespace = str_repeat(chr(9), $level);
			$lines = $whitespace . '/**' . PHP_EOL;
			foreach(explode(PHP_EOL, $type->phpDocEntry->comment) as $line) {
				$lines .= $whitespace . ' * ' . $line . PHP_EOL;
			}
			// $lines .= $whitespace . json_encode($type->phpDocEntry, JSON_PRETTY_PRINT) . PHP_EOL;
			$lines .= $whitespace . ' */' . PHP_EOL;
			return $lines;
		}
	}
}