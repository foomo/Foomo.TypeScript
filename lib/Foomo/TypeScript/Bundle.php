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

use Foomo\JS\Bundle\AbstractBundle;
use Foomo\JS\Bundle\Compiler\Result;
use Foomo\Modules\MakeResult;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 */
class Bundle extends AbstractBundle
{
	/**
	 * @var string
	 */
	public $locale;
	/**
	 * @var string
	 */
	public $path;

	/**
	 * @var bool
	 */
	public $writeTypeDefinition = false;
	/**
	 * @var TemplateRenderer[]
	 */
	public $templateRenderers = array();
	/**
	 * @var mixed
	 */
	public $preProcessingData;


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
	public function getBundleFile()
	{
		$bundleFile = $this->path . DIRECTORY_SEPARATOR . 'bundle.ts.tpl';
		if(file_exists($bundleFile)) {
			return $bundleFile;
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
	public function compile(Result $result)
	{

	}

	/**
	 *
	 *
	 * @param string $name
	 * @param string $path
	 *
	 * @return Bundle
	 */
	public static function create($name, $path)
	{
		$ret = parent::create($name);
		$ret->path = $path;
		return $ret;
	}
}