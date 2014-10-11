<?php
namespace Ttree\Plugin\LatexEditor\Service;

/*                                                                            *
 * This script belongs to the TYPO3 Flow package "Ttree.Plugin.LatexEditor".  *
 *                                                                            *
 * It is free software; you can redistribute it and/or modify it under        *
 * the terms of the GNU General Public License, either version 3 of the       *
 * License, or (at your option) any later version.                            *
 *                                                                            *
 * The TYPO3 project - inspiring people to share!                             *
 *                                                                            */

use Symfony\Component\DomCrawler\Crawler;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\PersistenceManagerInterface;
use TYPO3\Flow\Resource\ResourceManager;
use TYPO3\Flow\Utility\Files;
use TYPO3\Media\Domain\Model\Image;
use TYPO3\Media\Domain\Repository\ImageRepository;

/**
 * Convert the Latex Source to a HTML string
 *
 * @Flow\Scope("singleton")
 * @api
 */
class LatexConverterService {

	const PARSE_ERROR = 'Please insert a valid LaTeX Source';
	const EXTRACT_BODY_REGEXP = '/(?:<body[^>]*>)(.*)<\/body>/isU';

	/**
	 * @Flow\Inject
	 * @var ResourceManager
	 */
	protected $resourceManager;

	/**
	 * @Flow\Inject
	 * @var ImageRepository
	 */
	protected $imageRepository;

	/**
	 * @Flow\Inject
	 * @var PersistenceManagerInterface
	 */
	protected $persitenceManager;

	/**
	 * @Flow\Inject(setting="temporaryPath")
	 * @var string
	 */
	protected $temporaryPath;

	/**
	 * Just return the processed value
	 *
	 * @param string $source
	 * @param string
	 * @return string
	 */
	public function convert($source, $temporaryFile = NULL) {
		$source = trim($source);

		if ($source === '') {
			return self::PARSE_ERROR;
		}

		$temporaryFile = $temporaryFile ?: uniqid();
		$texFile = $this->temporaryPath . $temporaryFile . '.tex';
		$htmlFile = $this->temporaryPath . $temporaryFile . '.html';
		Files::createDirectoryRecursively($this->temporaryPath);
		file_put_contents($texFile, $source);
		chdir($this->temporaryPath);
		$command = 'sudo /usr/texbin/htlatex ' . $texFile . ' "xhtml, charset=utf-8" " -cunihtf -utf8"';
		shell_exec($command);
		if (@is_file($htmlFile)) {
			$content = Files::getFileContents($htmlFile);
			preg_match(self::EXTRACT_BODY_REGEXP, $content, $matches);
			$content = isset($matches[1]) ? $matches[1] : '';
			if ($content !== '') {
				$content = $this->persistResources($content);
			}
			return $content ?: self::PARSE_ERROR;
		}
		return self::PARSE_ERROR;
	}

	/**
	 * @param string $content
	 * @return string
	 */
	protected function persistResources($content) {
		$crawler = new Crawler($content);

		$filter = $crawler->filter('img');

		foreach ($filter as $i => $value) {
			/** @var \DOMElement $value */
			$crawler = new Crawler($value);
			$sourceImage = $crawler->attr('src');
			if (trim($sourceImage) !== '') {
				$imageIdentifier = $this->persistResource($this->temporaryPath . $sourceImage);
				$content = str_replace($sourceImage, 'asset://' . $imageIdentifier, $content);
			}
		}

		return $content;
	}

	/**
	 * @param string $imagePathAndFilename
	 * @return mixed
	 * @throws \TYPO3\Flow\Persistence\Exception\IllegalObjectTypeException
	 */
	protected function persistResource($imagePathAndFilename) {
		$resource = $this->resourceManager->importResource($imagePathAndFilename);
		$image = new Image($resource);
		$imageIdentifier = $this->persitenceManager->getIdentifierByObject($image);
		$this->persitenceManager->whitelistObject($image);
		$this->persitenceManager->whitelistObject($resource);
		$this->persitenceManager->whitelistObject($resource->getResourcePointer());
		$this->imageRepository->add($image);

		return $imageIdentifier;
	}

}
