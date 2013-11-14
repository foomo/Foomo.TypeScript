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
class Bundle
{
	/**
	 * @var string
	 */
	public $name;
	/**
	 * @var bool
	 */
	public $debug;
	/**
	 * @var string
	 */
	public $locale;
	/**
	 * @var string[]
	 */
	public $paths = array();
	/**
	 * @var string[]
	 */
	public $javaScripts = array();
	/**
	 * @var Bundle[]
	 */
	public $dependencies = array();
	public $mergeBundles = array();
	/**
	 * @var bool
	 */
	public $writeTypeDefinition = false;
	/**
	 * @var string[]
	 */
	public $typeDefinitions = array();
	/**
	 * @var TemplateRenderer[]
	 */
	public $templateRenderers = array();
	/**
	 * @var mixed
	 */
	public $preProcessingData;
	private function __construct($name)
	{
		$this->name = $name;
	}
	/**
	 * @param string $name
	 *
	 * @return Bundle
	 */
	public static function create($name)
	{
		return new self($name);
	}

	/**
	 * merge with another bundle
	 *
	 * @param Bundle $bundle
	 *
	 * @return Bundle
	 */
	public function merge(Bundle $bundle)
	{
		$this->mergeBundles[] = $bundle;
		return $this;
	}
	/**
	 * @param Bundle $bundle
	 *
	 * @return Bundle
	 */
	public function addDependency(Bundle $bundle)
	{
		return $this->addEntryToPropArray($bundle, 'dependencies');
	}
	/**
	 * @param Bundle[] $bundles
	 *
	 * @return Bundle
	 */
	public function addDependencies(array $bundles)
	{
		return $this->addEntriesToPropArray($bundles, 'dependencies');
	}
	/**
	 * @param string $script
	 * @return Bundle
	 */
	public function addJavascript($script)
	{
		return $this->addEntryToPropArray($script, 'javaScripts');
	}

	/**
	 * @param string[] $scripts
	 * @return Bundle
	 */
	public function addJavaScripts(array $scripts)
	{
		return $this->addEntriesToPropArray($scripts, 'javaScripts');
	}
	/**
	 * @param string[] $paths
	 *
	 * @return Bundle
	 */
	public function addFolders(array $paths)
	{
		return $this->addEntriesToPropArray($paths, 'paths');
	}
	/**
	 * @param string $path
	 *
	 * @return Bundle
	 */
	public function addFolder($path)
	{
		return $this->addEntryToPropArray($path, 'paths');
	}
	/**
	 * @param string $typeDefinition
	 *
	 * @return Bundle
	 */
	public function addTypeDefinition($typeDefinition)
	{
		return $this->addEntryToPropArray($typeDefinition, 'typeDefinitions');
	}
	/**
	 * @param string[] $typeDefinitions
	 *
	 * @return $this
	 */
	public function addTypeDefinitions(array $typeDefinitions)
	{
		return $this->addEntriesToPropArray($typeDefinitions, 'typeDefinitions');
	}
	/**
	 * @param bool $yesOrNo
	 *
	 * @return Bundle
	 */
	public function writeTypeDefinition($yesOrNo = true)
	{
		$this->writeTypeDefinition = $yesOrNo;
		return $this;
	}
	public function locale($locale)
	{
		$this->locale = $locale;
		return $this;
	}
	/**
	 * @param bool $debug
	 * @return Bundle
	 */
	public function debug($debug)
	{
		$this->debug = $debug;
		return $this;
	}
	/**
	 * @param $data
	 *
	 * @return Bundle
	 */
	public function preProcessWithData($data)
	{
		$this->preProcessingData = $data;
		return $this;
	}
	/**
	 * @param TemplateRenderer $renderer
	 * @return Bundle
	 */
	public function addTemplateRenderer(TemplateRenderer $renderer)
	{
		return $this->addEntryToPropArray($renderer, 'templateRenderers');
	}
	/**
	 * @param TemplateRenderer[] $renderers
	 * @return Bundle
	 */
	public function addTemplateRenderers(array $renderers)
	{
		return $this->addEntriesToPropArray($renderers, 'templateRenderers');
	}
	public function getJSLinks()
	{

	}
	public function getJSFiles()
	{

	}
	/**
	 * @param array $entries
	 * @param string $propArrayName
	 *
	 * @return Bundle
	 */
	private function addEntriesToPropArray(array $entries, $propArrayName)
	{
		foreach($entries as $entry) {
			$this->addEntryToPropArray($entry, $propArrayName);
		}
		return $this;
	}
	/**
	 * @param string $entry
	 * @param string $propArrayName
	 * @return Bundle
	 */
	private function addEntryToPropArray($entry, $propArrayName)
	{
		if(!in_array($entry, $this->{$propArrayName})) {
			$this->{$propArrayName}[] = $entry;
		}
		return $this;
	}
	public function getTimestamp()
	{

	}
	public function getBundleFile()
	{
		foreach($this->paths as $path) {
			$bundleFile = $path . DIRECTORY_SEPARATOR . 'bundle.ts.tpl';
			if(file_exists($bundleFile)) {
				return $bundleFile;
			}
		}
		trigger_error('could not find the bundle file', E_USER_ERROR);
	}
	public function getAllTypeScriptFiles()
	{
		$typescriptFiles = array();
		foreach($this->paths as $path) {
			$this->lookForFilesWithSuffixInPath($path, '.ts', $typescriptFiles);
		}
		$typescriptFiles = array_unique($typescriptFiles);
		sort($typescriptFiles);
		return $typescriptFiles;
	}
	public function lookForFilesWithSuffixInPath($path, $suffix, array &$typescriptFiles)
	{
		$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::CHILD_FIRST);
		foreach ($iterator as $splFileInfo) {
			/* @var $splFileInfo \SplFileInfo */
			if($splFileInfo->isFile() && substr($splFileInfo->getFilename(), - strlen($suffix)) == $suffix) {
				$typescriptFiles[] = $splFileInfo->getPathname();
			}
		}
	}
	public function getAllTypeDefinitionFiles()
	{
		$typeDefinitionFiles = array();
		foreach($this->typeDefinitions as $fileOrFolder) {
			if(is_dir($fileOrFolder)) {
				self::lookForFilesWithSuffixInPath($fileOrFolder, '.d.ts' , $typeDefinitionFiles);
			} else {
				$typeDefinitionFiles[] = $fileOrFolder;
			}
		}
		$typeDefinitionFiles = array_unique($typeDefinitionFiles);
		sort($typeDefinitionFiles);
		return $typeDefinitionFiles;
	}
	public function getFingerprint()
	{

	}
	/**
	 * @param Bundle[] $satisfyingBundles
	 * @return bool
	 */
	public function dependenciesAreSatisfiedBy(array $satisfyingBundles)
	{
		foreach($this->dependencies as $dependencyBundle) {
			$satisfied = false;
			foreach($satisfyingBundles as $satisfyingBundle) {
				if($satisfyingBundle->getFingerprint() == $dependencyBundle->getFingerprint()) {
					$satisfied = true;
					break;
				}
			}
			if(!$satisfied) {
				return false;
			}
		}
		return true;
	}
}