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
use Foomo\TypeScript\Services\TypeDefinitionRenderer;
use Foomo\TypeScript;


/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 */
class Utils
{
	public static function buildAll()
	{
		$moduleTypes = array();
		$typescriptDirs = array();
		$buildReport = array();
		foreach(self::getAllServices() as $module => $serviceDescriptions) {
			$moduleTypes[$module] = array();
			foreach($serviceDescriptions as $serviceDescription) {
				/* @var $serviceDescription \Foomo\Services\ServiceDescription */
				$typescriptModuleDir = \Foomo\Config::getModuleDir($module) . DIRECTORY_SEPARATOR . 'typescript';
				$typescriptDirs[$module] = $typescriptModuleDir;
				if(is_writable($typescriptModuleDir)) {
					$tsFile = $typescriptModuleDir . DIRECTORY_SEPARATOR . $serviceDescription->package . '.d.ts';
					$report = 'wrote service definition for ' . $serviceDescription->name . ' to ' . $tsFile;
					$renderer = new TypeDefinitionRenderer($serviceDescription->package);
					file_put_contents(
						$tsFile,
						TypeDefinitionRenderer::render(
							$serviceDescription->name,
							$renderer
						)
					);
					foreach($renderer->types as $name => $type) {
						if(class_exists($name)) {
							$moduleTypes[$module][$name] = $type;
						}
					}
				} else {
					$report = 'no typescript dir or dir not writable in module ' . $module . ' ' . $typescriptModuleDir;
				}
				$buildReport[] = $report;
			}
		}
		foreach($moduleTypes as $module => $types) {
			if(!empty($types)) {
				$voDefsFilename = $typescriptDirs[$module] . DIRECTORY_SEPARATOR . 'serviceValueObjects.ts';
				$bytesWritten = file_put_contents(
					$typescriptDirs[$module] . DIRECTORY_SEPARATOR . 'serviceValueObjects.ts',
					\Foomo\TypeScript\Module::getView(
						'Foomo\\TypeScript\\Services\\Hack',
						'serviceValueObjects',
						(object) array(
							'types' => $types,
							'maps' => VoModuleMapper::getMaps($types)
						))
				);
				$buildReport[] = 'wrote ' . $bytesWritten .' to ' . $voDefsFilename;
				$jsFileName = TypeScript::create($voDefsFilename)
					->generateDeclaration()
					->compile()
					->getOutputFilename()
				;
				$moduleJSDir = \Foomo\Config::getHtdocsDir($module) . DIRECTORY_SEPARATOR . 'js';
				$targetJSFile = $moduleJSDir . DIRECTORY_SEPARATOR . 'serviceValueObjects.js';
				if(is_dir($moduleJSDir)) {
					file_put_contents(
						$targetJSFile,
						file_get_contents($jsFileName)
					);
					$buildReport[] = 'wrote ' . $targetJSFile;
				} else {
					$buildReport[] = 'js dir missing did not write ' . $targetJSFile;
				}

			}
		}
		return $buildReport;
	}
	public static function cleanAll()
	{
		$moduleTypes = array();
		$typescriptDirs = array();
		$buildReport = array();
		foreach(self::getAllServices() as $module => $serviceDescriptions) {
			$moduleTypes[$module] = array();
			foreach($serviceDescriptions as $serviceDescription) {
				$typescriptModuleDir = \Foomo\Config::getModuleDir($module) . DIRECTORY_SEPARATOR . 'typescript';
				$moduleVoDeclarationFile = $typescriptModuleDir . DIRECTORY_SEPARATOR . 'serviceValueObjects.d.ts';
				if(file_exists($moduleVoDeclarationFile)) {
					unlink($moduleVoDeclarationFile);
					$buildReport[] = 'removing value object declaration file serviceValueObjects.d.ts for module ' . $module;
				}
				/* @var $serviceDescription \Foomo\Services\ServiceDescription */
				$typescriptDirs[$module] = $typescriptModuleDir;
				$tsFile = $typescriptModuleDir . DIRECTORY_SEPARATOR . $serviceDescription->package . '.d.ts';
				if(file_exists($tsFile)) {
					$buildReport[] = 'removing service definition for ' . $serviceDescription->name . ' to ' . $tsFile;
					unlink($tsFile);
				}
				$renderer = new TypeDefinitionRenderer($serviceDescription->package);
				foreach($renderer->types as $name => $type) {
					if(class_exists($name)) {
						$moduleTypes[$module][$name] = $type;
					}
				}
			}
		}
		foreach($moduleTypes as $module => $types) {
			$serviceTypeDefinitionsFile = $typescriptDirs[$module] . DIRECTORY_SEPARATOR . 'serviceValueObjects.ts';
			if(file_exists($serviceTypeDefinitionsFile)) {
				$buildReport[] = 'removing ' . $serviceTypeDefinitionsFile;
				unlink($serviceTypeDefinitionsFile);
			}
		}
		foreach(new \DirectoryIterator(Module::getHtdocsVarDir()) as $fileInfo) {
			$name = $fileInfo->getFilename();
			if($fileInfo->isFile() && substr($name, 0, 1) != '.') {
				if(substr($name, -3) == '.js' || substr($name, -7) == '.js.map' || substr($name, -5) == '.d.ts') {
					if(unlink($fileInfo->getPathname())) {
						$buildReport[] = 'removed compiled file ' . $fileInfo->getFilename();
					} else {
						$buildReport[] = 'failed to remove compiled file ' . $fileInfo->getFilename();
					}

				}
			}
		}
		return $buildReport;
	}
	public static function getAllServices()
	{
		$services = \Foomo\Services\Utils::getAllLocalServiceDescriptions();
		// filter out json rpc
		$jsonServices = array();
		foreach($services as $module => $serviceDescriptions) {
			foreach($serviceDescriptions as $serviceDescription) {
				/* @var $serviceDescription \Foomo\Services\ServiceDescription */
				if($serviceDescription->type == 'serviceTypeRpcJson') {
					if(!isset($jsonServices[$module])) {
						$jsonServices[$module] = array();
					}
					$jsonServices[$module][] = $serviceDescription;
				}
			}
		}
		return $jsonServices;
	}

}