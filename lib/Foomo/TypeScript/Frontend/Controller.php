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
use Foomo\TypeScript\VoModuleMapper;
use Foomo\TypeScript\Utils;

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
		$this->model->services = Utils::getAllServices();
	}
	public function actionDefault()
	{
		$this->loadServices();
	}
	public function actionBuildAll()
	{
		$this->loadServices();
		$this->model->buildReport = Utils::buildAll();
	}
}
