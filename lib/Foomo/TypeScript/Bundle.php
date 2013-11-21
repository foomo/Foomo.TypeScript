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
use Foomo\TypeScript;

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
	 * @var string
	 */
	public $target = TypeScript::TARGET_ES3;
	/**
	 * @var bool
	 */
	public $writeTypeDefinition = false;
	public $templateJobs = array();
	/**
	 * @var mixed
	 */
	public $preProcessingData = array();


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
	 * @param string $target
	 * @return $this
	 */
	public function target($target)
	{
		$this->target = $target;
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
	 * @param $dir
	 * @param TemplateRenderer $renderer
	 * @return $this
	 */
	public function lookForTemplates($dir, TemplateRenderer $renderer)
	{
		$this->templateJobs[] = array('renderer' => $renderer, 'dir' => $dir);
		return $this;
	}
	public function getBundleFile()
	{
		return $this->path . DIRECTORY_SEPARATOR . 'bundle.ts.tpl';
	}
	
	public function getAllTypeScriptFiles()
	{
		$typescriptFiles = array();
		$this->lookForFilesWithSuffixInPath($this->path, '.ts', $typescriptFiles);
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
	public function compile(Result $result)
	{
		Bundle\Compiler::compile($this, $result);
	}

	/**
	 *
	 *
	 * @param string $name
	 * @param string $path
	 *
	 * @return Bundle
	 */
	public static function create($name)
	{
		$path = func_get_arg(1);
		$ret = parent::create($name);
		$ret->path = $path;
		return $ret;
	}
}