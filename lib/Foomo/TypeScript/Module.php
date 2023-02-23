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
use Foomo\Modules\MakeResult;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 */
class Module extends \Foomo\Modules\ModuleBase
{
	const VERSION = '3.7.2';
	//---------------------------------------------------------------------------------------------
	// ~ Constants
	//---------------------------------------------------------------------------------------------

	/**
	 * the name of this module
	 *
	 */
	const NAME = 'Foomo.TypeScript';

	//---------------------------------------------------------------------------------------------
	// ~ Overriden static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * Your module needs to be set up, before being used - this is the place to do it
	 */
	public static function initializeModule()
	{
	}

	/**
	 * Get a plain text description of what this module does
	 *
	 * @return string
	 */
	public static function getDescription()
	{
		return 'typescript integration';
	}

	/**
	 * get all the module resources
	 *
	 * @return \Foomo\Modules\Resource[]
	 */
	public static function getResources()
	{
		return array(
			\Foomo\Modules\Resource\Module::getResource('Foomo.Services', '0.3.*'),
			\Foomo\Modules\Resource\Module::getResource('Foomo.JS', '1.2.*'),
			//\Foomo\Modules\Resource\ComposerPackage::getResource('geshi/geshi', 'dev-master'),
			\Foomo\Modules\Resource\Config::getResource(self::NAME, 'Foomo.typeScript'),
			\Foomo\Modules\Resource\CliCommand::getResource('tsc'),
			// \Foomo\Modules\Resource\NPMPackage::getResource('typescript', '3.7.2', 'microsofts typescript compiler')
		);
	}

	public static function make($target, MakeResult $result)
	{
		switch($target) {
			case 'all':
				foreach(Utils::buildAll() as $line) {
					$result->addEntry($line);
				}
				break;
			case 'clean':
				foreach(Utils::cleanAll() as $line) {
					$result->addEntry($line);
				}
				break;
			default:
				$result->addEntry('nothing to make here for target ' . $target);
		}
	}



}
