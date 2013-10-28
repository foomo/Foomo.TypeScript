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

namespace Foomo;

use Foomo\TypeScript\ErrorRenderer;
use Foomo\Utils;
use Foomo\Modules\Resource\Fs as FsResource;
use Foomo\Lock;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 */
class TypeScript
{
	/**
	 * @var string
	 */
	protected $hash;
	/**
	 * @var string
	 */
	protected $name;
	/**
	 * custom output file
	 *
	 * @var string
	 */
	protected $out;
	/**
	 * @var string main src file
	 */
	protected $file;
	/**
	 * tsc option -c / --comments
	 *
	 * @var bool
	 */
	protected $comments = true;
	/**
	 * tsc option --sourcemap
	 *
	 * @var bool
	 */
	protected $sourceMap = true;
	/**
	 * @var bool
	 */
	protected $generateDeclaration = false;
	/**
	 * @var bool
	 */
	protected $displayCompilerErrors = false;
	const TARGET_ES3 = 'ES3';
	const TARGET_ES5 = 'ES5';
	/**
	 * @var string
	 */
	protected $target = 'ES3';
	protected $outputFilters = array();
	/**
	 * where to look for templates on what to use there
	 * @var array
	 */
	protected $templateJobs = array();
	/**
	 * @var bool
	 */
	protected $watch = true;
	private function __construct($file)
	{
		static $moduleDir = null;
		if(is_null($moduleDir)) {
			$moduleDir = Config::getModuleDir();
		}
		$this->file = $file;
		if(strpos($this->file, $moduleDir) === 0) {
			$this->hash = md5(substr($this->file, strlen($moduleDir) + 1));
		} else {
			$this->hash = md5($this->file);
		}
	}

	/**
	 * @return string
	 */
	public function getOutputPath()
	{
		// @todo handle custom out ...
		return TypeScript\Module::getHtdocsVarBuildPath() . '/' . $this->getOutputBasename();
	}
	public function addOutputFilter($filter)
	{
		$this->outputFilters[] = $filter;
		return $this;
	}
	/**
	 *
	 * @param bool $display
	 *
	 * @return $this
	 */
	public function displayCompilerErrors($display = true)
	{
		$this->displayCompilerErrors = $display;
		return $this;
	}
	public function out($filename)
	{
		$this->out = $filename;
		return $this;
	}
	/**
	 * makes it easier find things in your doc
	 *
	 * @param string $name
	 *
	 * @return $this
	 */
	public function name($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * generate a declaration file next compiled file
	 *
	 * @param bool $generateDeclaration
	 *
	 * @return $this
	 */
	public function generateDeclaration($generateDeclaration = true)
	{
		$this->generateDeclaration = $generateDeclaration;
		return $this;
	}
	/**
	 * watch for changes
	 *
	 * @param bool $watch
	 *
	 * @return $this
	 */
	public function watch($watch = true)
	{
		$this->watch = $watch;
		return $this;
	}
	/**
	 * @return string
	 */
	public function getSourceFilename()
	{
		return $this->file;
	}
	/**
	 * @return string
	 */
	public function getOutputFilename()
	{
		if(!is_null($this->out)) {
			return $this->out;
		} else {
			return TypeScript\Module::getHtdocsVarDir() . DIRECTORY_SEPARATOR . $this->getOutputBasename();
		}
	}
	public function getDeclarationFilename()
	{
		if($this->generateDeclaration) {
			return substr($this->file, 0, -2) . 'd.ts';
		} else {
			return false;
		}
	}
	/**
	 * @return string
	 */
	public function getOutputBasename()
	{
		if(!is_null($this->out)) {
			return basename($this->out);
		} else {
			return  ($this->name?$this->name . '-':'') . $this->hash . '.js';
		}
	}
	/**
	 * @param string $dir absolute path of directory to look for templates for
	 *
	 * @param TypeScript\TemplateRenderer $renderer
	 *
	 * @return $this
	 */
	public function lookForTemplates($dir, TypeScript\TemplateRenderer $renderer)
	{
		$this->templateJobs[] = array(
			'dir' => $dir,
			'renderer' => $renderer
		);
		return $this;
	}

	/**
	 * @param string $ecmaScriptVersion
	 * @return $this
	 */
	public function target($ecmaScriptVersion)
	{
		$this->target = $ecmaScriptVersion;
		return $this;
	}
	private function needsRecompilation($out)
	{
		static $iRanBefore;
		if(is_null($iRanBefore)) {
			$iRanBefore = true;
		} else {
			clearstatcache();
		}
		$mTime = 0;
		if(file_exists($out)) {
			$mTime = filemtime($out);
		}
		return
			$mTime < self::getLastChange($this->file) ||
			$mTime < self::getLastTemplateChange($this->templateJobs)
		;
	}
	/**
	 * @return $this
	 */
	public function compile()
	{
		$out = $this->getOutputFilename();
		if($this->watch || !file_exists($out)) {
			$lockName = 'tsLock-' . basename($out);
			if(
				// have there been any changes ?
				$this->needsRecompilation($out) &&

				// ok i need to compile - let´s lock this
				Lock::lock($lockName, true) &&

				// did anybody else do my job?
				$this->needsRecompilation($out)
			) {
				if(file_exists($out)) {
					unlink($out);
				}
				self::renderTemplates($this->templateJobs);

				$arguments = array('--target', $this->target);

				if($this->comments) {
					// $arguments[] = '--comments';
				}
				$arguments[] = '--removeComments';
				if($this->sourceMap) {
					$arguments[] = '--sourcemap';
				}
				if($this->generateDeclaration) {
					$arguments[] = '--declaration';
				}
				$arguments[] = '--out';
				$arguments[] = $out;
				$arguments[] = $this->file;
				$call = CliCall::create('tsc', $arguments);
				$call->execute();
				if($call->exitStatus !== 0) {
					if(file_exists($out)) {
						unlink($out);
					}
					if($this->displayCompilerErrors) {
						ErrorRenderer::renderError($call);
						exit;
					}
					trigger_error('tsc threw up ' . $call->report, E_USER_ERROR);
				} else {
					Utils::appendToPhpErrorLog($call->report);
				}
				// run filters
				if(count($this->outputFilters) > 0) {
					$js = file_get_contents($out);
					foreach($this->outputFilters as $filter) {
						$js = $filter($js);
					}
					file_put_contents($out, $js);
				}
				$domainConfig = Config::getConf(TypeScript\Module::NAME, TypeScript\DomainConfig::NAME);
				if($domainConfig) {
					$sourceMapping = $domainConfig->sourceMapping;
				} else {
					$sourceMapping = array();
				}

				self::fixSourceMap($out, $this->getOutputPath(), $sourceMapping);
				file_put_contents(
					$out,
					str_replace(
						array('//@ sourceMappingURL=', '//# sourceMappingURL='),
						'//# sourceMappingURL=',
						file_get_contents($out)
					)
				);
				if($this->generateDeclaration) {
					$declarationFile = substr($out, 0, -2) . 'd.ts';
					if(file_exists($declarationFile)) {
						$targetDeclarationFile = $this->getDeclarationFilename();
						$newContents = file_get_contents($declarationFile);
						$oldContents = null;
						if(file_exists($targetDeclarationFile)) {
							$oldContents = file_get_contents($targetDeclarationFile);
						}
						if($oldContents != $newContents) {
							file_put_contents($targetDeclarationFile, $newContents);
						}
					}
				}
				Lock::release($lockName);
			}
		}
		return $this;
	}

	/**
	 * @param array $templateJobs
	 *
	 * @return int last change
	 */
	public static function getLastTemplateChange(array $templateJobs)
	{
		$mTime = 0;
		foreach($templateJobs as $templateJob) {
			$dir = $templateJob['dir'];
			foreach(self::scanForTemplatesInDir($dir) as $templateInfo) {
				$templateMTime = filemtime($templateInfo->filename);
				if($templateMTime > $mTime) {
					$mTime = $templateMTime;
				}
			}
		}
		return $mTime;
	}

	public static function renderTemplates(array $templateJobs)
	{
		foreach($templateJobs as $templateJob) {
			$dir = $templateJob['dir'];
			$renderer = $templateJob['renderer'];
			$renderer->renderTemplates(self::scanForTemplatesInDir($dir));
		}
	}
	/**
	 * scan for templates
	 *
	 * @param string $dir where to scan in
	 * @param array $path for recursion leave empty
	 * @param array $templateInfos for recursion leave empty
	 *
	 * @return TypeScript\TemplateRenderer\TemplateInfo[]
	 */
	public static function scanForTemplatesInDir($dir, array &$path = array(), array &$templateInfos = array())
	{
		$iterator = new \DirectoryIterator($dir);
		foreach($iterator as $fileInfo) {
			$name = $fileInfo->getFilename();
			/* @var $fileInfo \SplFileInfo */
			if(substr($name, 0, 1) == '.') {
				// skip hidden files
				continue;
			}
			if($fileInfo->isDir()) {
				$path .= DIRECTORY_SEPARATOR . $name;
				self::scanForTemplatesInDir(
					$fileInfo->getPathname(),
					$path,
					$templateInfos
				);
			} else if(substr($name, -5) == '.html') {
				$info = new TypeScript\TemplateRenderer\TemplateInfo();
				$info->name = substr($name, 0, -5);
				$info->path = $path;
				$info->relativeFilename = implode(
					DIRECTORY_SEPARATOR,
					array_merge(
						$path, array($name))
					)
					. '.html'
				;
				$info->filename = $fileInfo->getPathname();
				$templateInfos[$info->name] = $info;
			}
		}
		ksort($templateInfos);
		$templateInfos = array_values($templateInfos);
		return $templateInfos;
	}

	/**
	 * fixes the generated source map, so that it references the source server
	 *
	 * @param string $out tsc outfile
	 * @param string $outPath path from the outside
	 * @param array $sourceMapping
	 */
	private static function fixSourcemap($out, $outPath, array $sourceMapping)
	{
		$mapFile = $out . '.map';
		$map = json_decode(file_get_contents($mapFile));
		$newSources = array();
		foreach($map->sources as $src) {
			$newSources[] = TypeScript\SourceServer::mapSource($src, $sourceMapping, $out);
		}
		$map->sources = $newSources;
		$map->file = basename($outPath);
		file_put_contents($mapFile, json_encode($map));
	}
	/**
	 * latest change in file and it refernces
	 *
	 * @param string $file
	 *
	 * @return int
	 */
	public static function getLastChange($file)
	{
		$deps = self::resolveDependencies($file);
		$mTime = 0;
		foreach($deps as $dep) {
			if(file_exists($dep)) {
				$depMTime = filemtime($dep);
				if($depMTime > $mTime) {
					$mTime = $depMTime;
				}
			}
		}
		return $mTime;
	}
	/**
	 * recursively look for referenced typescript files
	 *
	 * @param string $filename top level typescript file
	 * @param string[] $deps filenames of dependent typescript files
	 *
	 * @return array
	 */
	public static function resolveDependencies($filename, array &$deps = array())
	{
		if(!in_array($filename, $deps)) {
			$deps[] = $filename;
			foreach(self::extractDependenciesFromFile($filename) as $dep) {
				self::resolveDependencies($dep, $deps);
			}
		}
		return $deps;
	}

	/**
	 * extract dependencies / references in a typescript file
	 *
	 * @param string $filename ts file to scan for references in
	 *
	 * @return array of file names
	 */
	public static function extractDependenciesFromFile($filename)
	{
		$deps = array();
		$lines = explode(PHP_EOL, file_get_contents($filename));
		$dir = dirname($filename);
		foreach($lines as $line) {
			$line = trim($line);
			if(substr($line, 0, 3) == '///' && strpos($line, '<reference path=') !== false) {
				$line = trim(substr($line, 3));
				$quote = substr($line, 16, 1);
				$line = substr($line, 17);
				$filename = realpath($dir . DIRECTORY_SEPARATOR . substr($line, 0, strpos($line, $quote)));
				if(file_exists($filename)) {
					$deps[] = $filename;
				}
			}
		}
		return $deps;
	}
	/**
	 * @param string $file .ts main source file as an absolute path
	 *
	 * @return TypeScript
	 */
	public static function create($file)
	{
		return new self($file);
	}

	/**
	 * to make your project "build" process more efficient you can use templates for your
	 * .ts file, that will be interpreted as php templates and basically serve as a
	 * primitive preprocessor on that file - please, that we are not running through
	 * your referenced files
	 *
	 * be aware, that the passed in data are not being watched !!!
	 * in case of doubt - change the output filename or touch the template
	 *
	 * be also aware, that the generated files should be ignored by your VCS
	 * (.names might be a good idea)
	 *
	 * @param string $template template file to generate ts from
	 * @param string $name filename may include a relative path from the templates directory
	 * @param array $data array of data that will be extracted into the template
	 *
	 * @return TypeScript
	 */
	public static function createDynamic($template, $name, array $data)
	{
		$dir = dirname($template);
		$file = $dir . DIRECTORY_SEPARATOR . $name . ((substr($name, -3) == '.ts')?'':'.ts');
		$dir = dirname($file);
		self::makeSureBuildPathExists($dir);
		if(!file_exists($file) || filemtime($file) < filemtime($template) ) {
			$view = View::fromFile($template);
			if(file_exists($file)) {
				$oldContents = file_get_contents($file);
			} else {
				$oldContents = null;
			}
			$newContents = $view->render($data);
			if($oldContents != $newContents) {
				file_put_contents($file, $newContents);
			}
		}
		return self::create($file);
	}
	private static function makeSureBuildPathExists($dir)
	{
		$resource = FsResource::getAbsoluteResource(FsResource::TYPE_FOLDER, $dir);
		if(!$resource->resourceValid()) {
			$resource->tryCreate();
		}
	}
}