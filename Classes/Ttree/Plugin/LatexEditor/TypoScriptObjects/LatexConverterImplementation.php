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

use Ttree\Plugin\LatexEditor\Service\LatexConverterService;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TypoScript\TypoScriptObjects\AbstractTypoScriptObject;

/**
 * TypoScript2 implementation to convert the Latex Source to a HTML string
 *
 * @api
 */
class LatexConverterImplementation extends AbstractTypoScriptObject {

	/**
	 * @Flow\Inject
	 * @var LatexConverterService
	 */
	protected $latexConverter;

	/**
	 * @return NodeInterface
	 */
	public function getNode() {
		return $this->tsValue('node');
	}

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
		$node = $this->getNode();
		return $this->latexConverter->convert($this->getSource(), $node ? $node->getIdentifier() : NULL);
	}
}
