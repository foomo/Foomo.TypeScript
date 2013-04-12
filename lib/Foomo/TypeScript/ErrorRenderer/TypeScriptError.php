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

namespace Foomo\TypeScript\ErrorRenderer;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 */
class TypeScriptError
{
	public $file;
	public $line;
	public $column;
	public $error;
	public $plainError;
	public function __construct($plainError)
	{
		$this->plainError = $plainError;
		$parts = explode(':', $plainError);
		$reversedPosition = strrev($parts[0]);
		$posStart = strlen($parts[0]) - strpos($reversedPosition, '(');
		$this->error = trim(substr($plainError, strlen($parts[0]) + 1));
		$posPart = substr($parts[0], 0, -1);
		$position = explode(
			',',
			substr(
				$posPart,
				- (strlen($posPart) - $posStart)
			)
		);
		$file = trim(substr($parts[0], 0, $posStart - 1));
		if(file_exists($file)) {
			$this->file = $file;
			$this->line = $position[0];
			$this->column = $position[1];
		} else {
			$this->error = $this->plainError;
		}
	}
	public function getSnippet()
	{
		if($this->file) {
			$source =file_get_contents($this->file);
			$lines = explode(PHP_EOL, $source);
			$src = '';
			$firstLine = 0;
			for($i = $this->line - 6;$i < count($lines) && $i < $this->line + 4;$i++) {
				if(isset($lines[$i])) {
					if($firstLine == 0) {
						$firstLine = $i + 1;
					}
					$src .= $lines[$i] . PHP_EOL;
				}
			}
			if(class_exists('GeSHi')) {
				$geshi = new \GeSHi($src, 'Javascript');
				$geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
				$geshi->highlight_lines_extra($this->line - $firstLine + 1);
				$geshi->start_line_numbers_at($firstLine);
				return $geshi->parse_code();
			} else {
				return $src;
			}
		}

	}
}