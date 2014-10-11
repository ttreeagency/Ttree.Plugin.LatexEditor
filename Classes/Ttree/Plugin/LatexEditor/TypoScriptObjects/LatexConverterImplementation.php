<?php
namespace Ttree\Plugin\LatexEditor\TypoScriptObjects;

/*                                                                            *
 * This script belongs to the TYPO3 Flow package "Ttree.Plugin.LatexEditor".  *
 *                                                                            *
 * It is free software; you can redistribute it and/or modify it under        *
 * the terms of the GNU General Public License, either version 3 of the       *
 * License, or (at your option) any later version.                            *
 *                                                                            *
 * The TYPO3 project - inspiring people to share!                             *
 *                                                                            */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Files;
use TYPO3\TypoScript\TypoScriptObjects\AbstractTypoScriptObject;

/**
 * Convert the Latex Source to a HTML string
 *
 * @api
 */
class LatexConverterImplementation extends AbstractTypoScriptObject {

	const PARSE_ERROR = 'Please insert a valid LaTeX Source';

	/**
	 * @Flow\Inject(setting="temporaryPath")
	 * @var string
	 */
	protected $temporaryPath;

	/**
	 * @return string
	 */
	public function getSource() {
		return $this->tsValue('source');
	}

	/**
	 * Just return the processed value
	 *
	 * @return string
	 */
	public function evaluate() {
		$source = $this->getSource() ?: NULL;

		if ($source === NULL) {
			return self::PARSE_ERROR;
		}

		$temporaryFile = $this->temporaryPath . uniqid();
		$texFile = $temporaryFile . '.tex';
		$htmlFile = $temporaryFile . '.html';
		Files::createDirectoryRecursively($this->temporaryPath);
		file_put_contents($texFile, $source);
		chdir($this->temporaryPath);
		$command = 'sudo /usr/texbin/htlatex ' . $texFile . ' "xhtml, charset=utf-8" " -cunihtf -utf8"';
		shell_exec($command);
		if (@is_file($htmlFile)) {
			$content = Files::getFileContents($htmlFile);
			preg_match("/(?:<body[^>]*>)(.*)<\/body>/isU", $content, $matches);
			return isset($matches[1]) ? $matches[1] : '';
		}
		return self::PARSE_ERROR;
	}
}
