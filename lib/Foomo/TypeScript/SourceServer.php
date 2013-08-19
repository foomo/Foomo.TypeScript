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

use Foomo\Config;
use Foomo\Modules\Manager;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 */
class SourceServer
{
	public static function mapSource($src, array $sourceMapping, $out)
	{
		static $moduleDir = null;
		if(is_null($moduleDir)) {
			$moduleDir = Config::getModuleDir();
		}
		$originalSrc = $src;
		$src = realpath(dirname($out) . DIRECTORY_SEPARATOR . $src);
		if($src === false) {
			trigger_error('could not find src ' . $src . ' in ' . dirname($out));
		}
		if(!empty($sourceMapping)) {
			foreach($sourceMapping as $local => $remote) {
				if(strpos($src, $local) === 0) {
					$src = $remote . substr($src, strlen($local));
					return 'file://' . $src;
				}
			}
		}
		if(strpos($src, $moduleDir) === 0) {
			// typescript convention match
			$parts = explode(DIRECTORY_SEPARATOR, substr($src, strlen($moduleDir) + 1));
			if(count($parts) > 2 && $parts[1] == 'typescript') {
				unset($parts[1]);
				return Module::getHtdocsPath() . '/sourceServer.php/' . implode('/', $parts);
			} else {
				return $originalSrc;
			}
		} else {
			return $originalSrc;
		}
	}
	public static function resolveSource($path)
	{
		$parts = explode('/', $path);
		if(count($parts) > 1) {
			$moduleName = $parts[0];
			if(Manager::isModuleEnabled($moduleName)) {
				$filename = Config::getModuleDir($moduleName) . DIRECTORY_SEPARATOR . 'typescript';
				if(file_exists($filename) == is_dir($filename)) {
					unset($parts[0]);
					$filename = realpath($filename) . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts);
					if($filename == realpath($filename)) {
						return $filename;
					} else {
						trigger_error('smbdy is trying to bullshit us and trying to exit the typescript dir for a module ' . $filename . ' != ' . realpath($filename), E_USER_WARNING);
						return null;
					}
				} else {
					trigger_error('module has no typescript dir', E_USER_WARNING);
					return null;
				}
			}
		} else {
			trigger_error('illegal path to resolve');
			return null;
		}
		return '';
	}

	public static function run()
	{
		header('Content-Type: text/x-typescript');
		if(!Config::isProductionMode()) {
			$path = substr($_SERVER['REQUEST_URI'], strlen($_SERVER['SCRIPT_NAME']) + 1);
			$sourceFilename = self::resolveSource($path);
			if(file_exists($sourceFilename)) {
				echo file_get_contents($sourceFilename);
			} else {
				trigger_error('could not resolve typescript source', E_USER_WARNING);
				echo '// source not found';
			}
		} else {
			echo '// no sources in prod mode';
		}
	}
}