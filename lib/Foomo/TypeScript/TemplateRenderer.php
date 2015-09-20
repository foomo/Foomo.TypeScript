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

use Foomo\Template;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 */
class TemplateRenderer
{
	/**
	 * @var string
	 */
	protected $templateFile;
	/**
	 * @var string
	 */
	protected $targetFile;
	/**
	 * @var mixed
	 */
	protected $templateModel;
	/**
	 * @param string $targetFile where to write the generated code to
	 * @param string $templateFile which template to use for the template generation
	 * @param mixed $templateModel whatever shall be passed as a general model into the template
	 */
	public function __construct($targetFile, $templateFile, $templateModel)
	{
		$this->templateFile = $templateFile;
		$this->targetFile = $targetFile;
		$this->templateModel = $templateModel;
	}
	public function renderTemplates(array $templates)
	{
		$template = new Template('template', $this->templateFile);
		$oldDebug = Template::$debug;
		Template::$debug = false;
		$templateString = $template->render(
			$this->templateModel, null, null,
			array(
				'templates' => $templates
			)
		);
 		if(!file_exists($this->targetFile) || $templateString != file_get_contents($this->targetFile)) {
			file_put_contents(
				$this->targetFile,
				$templateString
			);
		}
		Template::$debug = $oldDebug;
	}
}