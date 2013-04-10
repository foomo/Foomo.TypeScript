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

namespace Foomo\TypeScript\TemplateRenderer;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 */
class TemplateInfo
{
	/**
	 * name without the suffix
	 *
	 * @var string
	 */
	public $name;
	/**
	 * relative path split with directory separator
	 *
	 * @var string[]
	 */
	public $path = array();
	/**
	 * relative to the directory it was looked in for
	 *
	 * @var string
	 */
	public $relativeFilename;
	/**
	 * absolute filename
	 *
	 * @var string
	 */
	public $filename;
	/**
	 * template contents
	 *
	 * @var string
	 */
	public $templateContents;
	public function getTemplateContents()
	{
		return file_get_contents($this->filename);
	}
}