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

use Foomo\Services\Reflection\ServiceObjectType;
use Foomo\TypeScript\VoModuleMapper\ModuleVoMap;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 */
class VoModuleMapper
{
	/**
	 * get a nested module vo map
	 *
	 * @param ServiceObjectType[] $types
	 *
	 * @return ModuleVoMap[]
	 */
	public static function getMaps(array $types)
	{
		// sort types in namespaces
		$namespaces = array();
		foreach($types as $type) {
			/* @var $type ServiceObjectType */
			if(!isset($namespaces[$type->namespace])) {
				$namespaces[$type->namespace] = array();
			}
			$namespaces[$type->namespace][] = $type;
		}

		// pad empty namespaces
		foreach(array_keys($namespaces) as $namespace) {
			$missingNamespace = '';
			foreach(explode('\\', $namespace) as $namespacePart) {
				$missingNamespace .= (!empty($missingNamespace)?'\\':'') . $namespacePart;
				if(!isset($namespaces[$missingNamespace])) {
					$namespaces[$missingNamespace] = array();
				}
			}
		}

		// find top level namespaces
		$maps = array();
		foreach($namespaces as $namespace => $types) {
			$rootNamespace = self::getRootNamespace($namespace, array_keys($namespaces));
			if(!isset($maps[$rootNamespace])) {
				$maps[$rootNamespace] = array();
			}
			$moduleVoMap = new ModuleVoMap();
			$moduleVoMap->types = $types;
			$moduleVoMap->namespace = $namespace;
			$maps[$rootNamespace][$namespace] = $moduleVoMap;
		}

		// sort in top level namespaces
		foreach($maps as $rootNamespace => $rootNamespaceMaps) {
			foreach($rootNamespaceMaps as $map) {
				self::addVoMapsToMap($map, $rootNamespaceMaps);
			}
		}

		//return $maps;
		// sort out the top level ones
		$topLevelMaps = array();
		foreach($maps as $rootNamespace => $rootNamespaceMaps) {
			foreach($rootNamespaceMaps as $rootNamespaceMap) {
				if($rootNamespaceMap->namespace == $rootNamespace) {
					$topLevelMaps[] = $rootNamespaceMap;
				}
			}
		}

		return $topLevelMaps;
	}
	private static function getRootNamespace($namespace, array $namespaces)
	{
		$rootNamespace = $namespace;
		foreach($namespaces as $rootNamespaceCandidate) {
			if(
				self::namespaceIsInNamespace($rootNamespace, $rootNamespaceCandidate)
			) {
				$rootNamespace = $rootNamespaceCandidate;
			}
		}
		return $rootNamespace;
	}
	private static function addVoMapsToMap(ModuleVoMap $map, array $maps)
	{
		$mapLevel = count(explode('\\', $map->namespace));
		foreach($maps as $nestedMapCandidate) {
			/* @var $nestedMapCandidate ModuleVoMap */
			if(self::namespaceIsInNamespace($nestedMapCandidate->namespace, $map->namespace)) {
				$candidateLevel = count(explode('\\', $nestedMapCandidate->namespace));
				if($candidateLevel == $mapLevel + 1) {
					$map->voMaps[] = $nestedMapCandidate;
				}
			}
		}
	}
	private static function namespaceIsInNamespace($namespace, $topLevelNamespace)
	{
		return
			strpos($namespace . '.', $topLevelNamespace . '.') === 0 &&
			strlen($topLevelNamespace) < strlen($namespace)
		;
	}
}