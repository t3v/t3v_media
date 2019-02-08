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
    return ($file->getMimeType() === 'video/youku' || $file->getExtension() === 'youku') && $this->getOnlineMediaHelper($file) !== false;
  }

  /**
   * Gets the online media helper.
   *
   * @param FileInterface $file The file or reference
   * @return bool|OnlineMediaHelperInterface The online media helper
   */
  protected function getOnlineMediaHelper(FileInterface $file) {
    if ($this->onlineMediaHelper === null) {
      $originalFile = $file;

      if ($originalFile instanceof FileReference) {
        $originalFile = $originalFile->getOriginalFile();
      }

      if ($originalFile instanceof File) {
        $this->onlineMediaHelper = OnlineMediaHelperRegistry::getInstance()->getOnlineMediaHelper($originalFile);
      } else {
        $this->onlineMediaHelper = false;
      }
    }

    return $this->onlineMediaHelper;
  }

  /**
   * The render for a given file or reference, renders output as HTML.
   *
   * @param FileInterface $file The file or reference
   * @param int|string $width The optional width in pixel, defaults to `480`
   * @param int|string $height The optional height in pixel, defaults to `270`
   * @param array $options The optional options
   * @param bool $relativeToCurrentScript Use relative paths to the current script, defaults to `false`
   * @return string The rendered output
   */
  public function render(FileInterface $file, $width = 480, $height = 270, array $options = null, $relativeToCurrentScript = false) {
    $width  = (int) $width;
    $height = (int) $height;

    // Checks for an autoplay option at the file reference itself, if not overriden yet
    if (!isset($options['autoplay']) && $file instanceof FileReference) {
      $autoplay = $file->getProperty('autoplay');

      if ($autoplay !== null) {
        $options['autoplay'] = $autoplay;
      }
    }

    if ($file instanceof FileReference) {
      $originalFile = $file->getOriginalFile();
    } else {
      $originalFile = $file;
    }

    $mediaId = $this->getOnlineMediaHelper($file)->getOnlineMediaId($originalFile);

    $urlParams = [];

    // $urlParams = ['autohide=1'];
    //
    // $options['controls'] = MathUtility::canBeInterpretedAsInteger($options['controls']) ? (int) $options['controls'] : 2;
    // $options['controls'] = MathUtility::forceIntegerInRange($options['controls'], 0, 2);
    // $urlParams[] = 'controls=' . $options['controls'];
    //
    // if (!empty($options['autoplay'])) {
    //   $urlParams[] = 'autoplay=1';
    // }
    //
    // if (!empty($options['loop'])) {
    //   $urlParams[] = 'loop=1&playlist=' . rawurlencode($mediaId);
    // }
    // if (isset($options['relatedVideos'])) {
    //   $urlParams[] = 'rel=' . (int) (bool) $options['relatedVideos'];
    // }
    //
    // if (!isset($options['enablejsapi']) || !empty($options['enablejsapi'])) {
    //   $urlParams[] = 'enablejsapi=1&origin=' . rawurlencode(GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST'));
    // }
    //
    // $urlParams[] = 'showinfo=' . (int) !empty($options['showinfo']);

    if (empty($urlParams)) {
      $src = sprintf('https://player.youku.com/embed/%s==', rawurlencode($mediaId));
    } else {
      $src = sprintf('https://player.youku.com/embed/%s==?%s', rawurlencode($mediaId), implode('&', $urlParams));
    }

    $attributes = [];
    $attributes['allowfullscreen'] = true;

    if ($width > 0) {
      $attributes['width'] = $width;
    }

    if ($height > 0) {
      $attributes['height'] = $height;
    }

    if (isset($GLOBALS['TSFE']) && is_object($GLOBALS['TSFE']) && $GLOBALS['TSFE']->config['config']['doctype'] !== 'html5') {
      $attributes['frameborder'] = 0;
    }

    foreach (['accesskey', 'class', 'dir', 'id', 'lang', 'onclick', 'poster', 'preload', 'style', 'tabindex', 'title'] as $key) {
      if (!empty($options[$key])) {
        $attributes[$key] = $options[$key];
      }
    }

    return sprintf(
      '<iframe src="%s"%s></iframe>',
      htmlspecialchars($src, ENT_QUOTES | ENT_HTML5),
      empty($attributes) ? '' : ' ' . $this->implodeAttributes($attributes)
    );
  }

  /**
   * Implodes attributes.
   *
   * @internal
   * @param array $attributes The attributes
   * @return string The implode attributes
   */
  protected function implodeAttributes(array $attributes) {
    $attributeList = [];

    foreach ($attributes as $name => $value) {
      $name = preg_replace('/[^\p{L}0-9_.-]/u', '', $name);

      if ($value === true) {
        $attributeList[] = $name;
      } else {
        $attributeList[] = $name . '="' . htmlspecialchars($value, ENT_QUOTES | ENT_HTML5) . '"';
      }
    }

    return implode(' ', $attributeList);
  }
}
