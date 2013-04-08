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

use Foomo\Utils;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 */
class TypeScript
{
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
	protected $displayCompilerErrors = false;
	private function __construct($file)
	{
		$this->file = $file;
	}
	/**
	 * @return string
	 */
	public function getOutputPath()
	{
		return TypeScript\Module::getHtdocsVarPath() . DIRECTORY_SEPARATOR . $this->getOutputBasename();
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
		return TypeScript\Module::getHtdocsVarDir() . DIRECTORY_SEPARATOR . $this->getOutputBasename();
	}
	/**
	 * @return string
	 */
	public function getOutputBasename()
	{
		return  \md5($this->file) . '.js';
	}
	/**
	 * @return $this
	 */
	public function compile()
	{
		$out = $this->getOutputFilename();
		$mTime = 0;
		if(file_exists($out)) {
			$mTime = filemtime($out);
		}
		if($mTime < self::getLastChange($this->file)) {
			unlink($out);
			$arguments = array();
			if($this->comments) {
				$arguments[] = '--comments';
			}
			if($this->sourceMap) {
				$arguments[] = '--sourcemap';
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
					header('Content-Type: text/plain;charset=utf-8;');
					echo $call->stdErr;
					exit;
				}
				trigger_error('tsc threw up ' . $call->report, E_USER_ERROR);
			} else {
				Utils::appendToPhpErrorLog($call->report);
			}
			self::fixSourceMap($out);
		}
		return $this;
	}
	private static function fixSourcemap($out)
	{
		$mapFile = $out . '.map';
		$map = json_decode(file_get_contents($mapFile));
		$newSources = array();
		foreach($map->sources as $src) {
			$newSources[] = TypeScript\SourceServer::mapSource($src);
		}
		$map->sources = $newSources;
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
			$depMTime = filemtime($dep);
			if($depMTime > $mTime) {
				$mTime = $depMTime;
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
	 * @param string $file .ts main source file as an absoulte path
	 *
	 * @return TypeScript
	 */
	public static function create($file)
	{
		return new self($file);
	}
}