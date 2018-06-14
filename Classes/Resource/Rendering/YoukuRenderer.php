<?php
namespace T3v\T3vMedia\Resource\Rendering;

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\OnlineMediaHelperInterface;
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\OnlineMediaHelperRegistry;
use TYPO3\CMS\Core\Resource\Rendering\FileRendererInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * The Youku renderer class.
 *
 * @package T3v\T3vMedia\Resource\Rendering
 */

class YoukuRenderer implements FileRendererInterface {
  /**
   * The online media helper.
   *
   * @var OnlineMediaHelperInterface
   */
  protected $onlineMediaHelper;

  /**
   * Returns the priority of the renderer.
   *
   * This way it is possible to define or overrule a renderer for a specific file type or context. Should be between `1`
   * and `100`.
   *
   * @return int The priority
   */
  public function getPriority() {
    return 1;
  }

  /**
   * Checks if the given file or reference can be rendered.
   *
   * @param FileInterface $file The file or reference to render
   * @return bool If the given file or reference can be rendered
   */
  public function canRender(FileInterface $file) {
    return ($file->getMimeType() === 'video/youtube' || $file->getExtension() === 'youtube') && $this->getOnlineMediaHelper($file) !== false;
  }

  /**
   * Gets online media helper.
   *
   * @param FileInterface $file The file or reference
   * @return bool|OnlineMediaHelperInterface
   */
  protected function getOnlineMediaHelper(FileInterface $file) {
    if ($this->onlineMediaHelper === null) {
      $orgFile = $file;

      if ($orgFile instanceof FileReference) {
        $orgFile = $orgFile->getOriginalFile();
      }

      if ($orgFile instanceof File) {
        $this->onlineMediaHelper = OnlineMediaHelperRegistry::getInstance()->getOnlineMediaHelper($orgFile);
      } else {
        $this->onlineMediaHelper = false;
      }
    }

    return $this->onlineMediaHelper;
  }

  /**
   * The render for a given file or reference, renders HTML output.
   *
   * @param FileInterface $file The file or reference
   * @param int|string $width TYPO3 known format; examples: `220`, `200m` or `200c`
   * @param int|string $height TYPO3 known format; examples: `220`, `200m` or `200c`
   * @param array $options The options
   * @param bool $usedPathsRelativeToCurrentScript See `$file->getPublicUrl()`
   * @return string The rendered output
   */
  public function render(FileInterface $file, $width, $height, array $options = null, $usedPathsRelativeToCurrentScript = false) {
    // Checks for an autoplay option at the file reference itself, if not overriden yet.
    if (!isset($options['autoplay']) && $file instanceof FileReference) {
      $autoplay = $file->getProperty('autoplay');

      if ($autoplay !== null) {
        $options['autoplay'] = $autoplay;
      }
    }

    if ($file instanceof FileReference) {
      $orgFile = $file->getOriginalFile();
    } else {
      $orgFile = $file;
    }

    $videoId = $this->getOnlineMediaHelper($file)->getOnlineMediaId($orgFile);

    $urlParams = ['autohide=1'];

    $options['controls'] = MathUtility::canBeInterpretedAsInteger($options['controls']) ? (int)$options['controls'] : 2;
    $options['controls'] = MathUtility::forceIntegerInRange($options['controls'], 0, 2);
    $urlParams[] = 'controls=' . $options['controls'];

    if (!empty($options['autoplay'])) {
      $urlParams[] = 'autoplay=1';
    }

    if (!empty($options['loop'])) {
      $urlParams[] = 'loop=1&amp;playlist=' . $videoId;
    }

    if (isset($options['relatedVideos'])) {
      $urlParams[] = 'rel=' . (int)(bool)$options['relatedVideos'];
    }

    if (!isset($options['enablejsapi']) || !empty($options['enablejsapi'])) {
      $urlParams[] = 'enablejsapi=1&amp;origin=' . rawurlencode(GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST'));
    }

    $urlParams[] = 'showinfo=' . (int)!empty($options['showinfo']);

    $src = sprintf(
      'https://www.youtube%s.com/embed/%s?%s',
      !isset($options['no-cookie']) || !empty($options['no-cookie']) ? '-nocookie' : '',
      $videoId,
      implode('&amp;', $urlParams)
    );

    $attributes = ['allowfullscreen'];

    if ((int)$width > 0) {
      $attributes[] = 'width="' . (int)$width . '"';
    }

    if ((int)$height > 0) {
      $attributes[] = 'height="' . (int)$height . '"';
    }

    if (is_object($GLOBALS['TSFE']) && $GLOBALS['TSFE']->config['config']['doctype'] !== 'html5') {
      $attributes[] = 'frameborder="0"';
    }

    foreach (['class', 'dir', 'id', 'lang', 'style', 'title', 'accesskey', 'tabindex', 'onclick', 'poster', 'preload'] as $key) {
      if (!empty($options[$key])) {
        $attributes[] = $key . '="' . htmlspecialchars($options[$key]) . '"';
      }
    }

    return sprintf(
      '<iframe src="%s"%s></iframe>',
      $src,
      empty($attributes) ? '' : ' ' . implode(' ', $attributes)
    );
  }
}