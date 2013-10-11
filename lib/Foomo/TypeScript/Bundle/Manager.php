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

use Foomo\TypeScript\Bundle;
use Foomo\HTMLDocument;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 */
class Manager
{
	const DEPENDENCY_MODEL_RESOLUTION_LIMIT = 100000;
	/**
	 * @var Bundle[]
	 */
	private $bundles = array();
	public function __construct()
	{

	}
	/**
	 * @return Mananger
	 */
	public static function getInstance()
	{
		static $instance;
		if(is_null($instance)) {
			$instance = new self;
		}
		return $instance;
	}
	/**
	 * @param Bundle $bundle
	 *
	 * @return Manager
	 */
	public function registerBundle(Bundle $bundle)
	{
		if(!in_array($bundle, $this->bundles)) {
			$this->bundles[] = $bundle;
		}
		return $this;
	}
	/**
	 * @return Bundle[]
	 */
	public function getSortedBundles()
	{
		$ret = array();
		$i = 0;
		while(count($ret) < count($this->bundles)) {
			$ret = array_merge($ret, $this->getBundlesSatisfiedByBundles($ret));
			if($i > self::DEPENDENCY_MODEL_RESOLUTION_LIMIT) {
				trigger_error('can not resolve dependencies', E_USER_ERROR);
			}
			$i ++;
		}
		return $ret;
	}

	/**
	 * @param Bundle[] $satisfyingBundles
	 *
	 * @return Bundle[]
	 */
	private function getBundlesSatisfiedByBundles(array $satisfyingBundles)
	{
		$ret = array();
		foreach($this->bundles as $bundle) {
			if($bundle->dependenciesAreSatisfiedBy($satisfyingBundles)) {
				$ret[] = $bundle;
			}
		}
		return $ret;
	}
	/**
	 * @return string[] javascript links to add to a document
	 */
	public function resolveBundles()
	{
		$javascripts = array();
		foreach($this->getSortedBundles() as $bundle) {
			$javascripts = array_merge($javascripts, Compiler::compile($bundle));
		}
		return $javascripts;
	}
}