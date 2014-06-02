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

namespace Foomo\TypeScript\Services;

use Foomo\Services\Reflection\ServiceObjectType;
use Foomo\Services\Reflection\ServiceOperation;
use Foomo\Services\Renderer\AbstractRenderer;
use Foomo\Template;

/**
 * generates type definition files for a given service
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 */
class TypeDefinitionRenderer extends AbstractRenderer
{
	/**
	 * @var ServiceObjectType[]
	 */
	public $types = array();
	/**
	 * @var ServiceOperation[]
	 */
	public $operations = array();
	/**
	 * @var string
	 */
	public $module;
	/**
	 * @var ServiceObjectType
	 */
	public $serviceType;
	public function __construct($module)
	{
		$this->module = $module;
	}
	/**
	 * prepare your assets
	 *
	 * @param string $serviceName name of the service class
	 */
	public function init($serviceName)
	{

	}

	/**
	 * render the service type itself
	 *
	 * @param \Foomo\Services\Reflection\ServiceObjectType $type
	 */
	public function renderServiceType(ServiceObjectType $type)
	{
		$this->serviceType = $type;
	}

	/**
	 * render an operation / method of the services class
	 *
	 * @param \Foomo\Services\Reflection\ServiceOperation $op
	 */
	public function renderOperation(\Foomo\Services\Reflection\ServiceOperation $op)
	{
		$this->operations[] = $op;
	}

	/**
	 * render a Type
	 *
	 * @param \Foomo\Services\Reflection\ServiceObjectType $type
	 */
	public function renderType(ServiceObjectType $type)
	{
		$this->types[$type->type] = $type;
	}
	public function renderInlineType(ServiceObjectType $type, $level = 0)
	{
		$suffix = $type->isArrayOf?'[]':'';
		switch($type->type) {
			case 'mixed':
				return 'any' . $suffix;
			case 'array':
				return 'any[]' . $suffix;
			case 'int':
			case 'integer':
			case 'float':
			case 'double':
				return 'number' . $suffix;
			case 'bool':
			case 'boolean':
				return 'boolean' . $suffix;
			case 'string':
				return 'string' . $suffix;
			case 'null':
				return 'void' . $suffix;
			default:
				if(class_exists($type->type)) {
					$ret = '{' . PHP_EOL;
					$props = array();
					$i = 0;
					$propCount = count($type->props);
					foreach($type->props as $name => $type) {
						$indent = str_repeat('	', $level + 1);
						$ret .= $indent . $name . ' : ' . $this->renderInlineType($type, $level + 1) . ';' . PHP_EOL;
					}
					$indent = str_repeat('	', $level);
					$ret .= $indent . '}' . $suffix;
					return $ret;
				} else {
					return 'any( unknown :: ' . $type->type . ')' . $suffix;;
				}
		}
	}
	/**
	 * return the thing you rendered
	 *
	 * @return mixed
	 */
	public function output()
	{
		return \Foomo\TypeScript\Module::getView(
			$this,
			'service.d.tpl',
			$this
		)->render();
	}
}