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

namespace Foomo\TypeScript\Frontend;


use Foomo\Services\Reflection\ServiceObjectType;
use Foomo\TypeScript\Services\TypeDefinitionRenderer;
use Foomo\TypeScript\VoModuleMapper;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan
 */
class Controller
{
	/**
	 * my model
	 *
	 * @var \Foomo\TypeScript\Frontend\Model
	 */
	public $model;
	private function loadServices()
	{
		$services = \Foomo\Services\Utils::getAllLocalServiceDescriptions();
		// filter out json rpc
		foreach($services as $module => $serviceDescriptions) {
			foreach($serviceDescriptions as $serviceDescription) {
				/* @var $serviceDescription \Foomo\Services\ServiceDescription */
				if($serviceDescription->type == 'serviceTypeRpcJson') {
					if(!isset($this->model->services[$module])) {
						$this->model->services[$module] = array();
					}
					$this->model->services[$module][] = $serviceDescription;
				}
			}
		}
	}
	public function actionDefault()
	{
		$this->loadServices();
	}
	public function actionBuildAll()
	{
		$this->loadServices();
		$moduleTypes = array();
		$typescriptDirs = array();
		foreach($this->model->services as $module => $serviceDescriptions) {
			$moduleTypes[$module] = array();
			foreach($serviceDescriptions as $serviceDescription) {
				/* @var $serviceDescription \Foomo\Services\ServiceDescription */
				$typescriptModuleDir = \Foomo\Config::getModuleDir($module) . DIRECTORY_SEPARATOR . 'typescript';
				$typescriptDirs[$module] = $typescriptModuleDir;
				if(is_writable($typescriptModuleDir)) {
					$tsFile = $typescriptModuleDir . DIRECTORY_SEPARATOR . $serviceDescription->package . '.d.ts';
					$report = 'wrote type definition for ' . $serviceDescription->name . ' to ' . $tsFile;
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
				$this->model->buildReport[] = $report;
			}
		}
		foreach($moduleTypes as $module => $types) {
			if(!empty($types)) {
				file_put_contents(
					$typescriptDirs[$module] . DIRECTORY_SEPARATOR . 'serviceValueObjects.ts',
					\Foomo\TypeScript\Module::getView(
						'Foomo\\TypeScript\\Services\\Hack',
						'serviceValueObjects',
						(object) array(
							'types' => $types,
							'maps' => VoModuleMapper::getMaps($types)
						))
				);
			}
		}
	}
}
