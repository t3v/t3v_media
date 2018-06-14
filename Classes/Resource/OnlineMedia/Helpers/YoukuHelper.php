<?php
namespace T3v\T3vMedia\Resource\OnlineMedia\Helpers;

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\AbstractOEmbedHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * The Youku helper class.
 *
 * @package T3v\T3vMedia\Resource\OnlineMedia\Helpers
 */
class YoukuHelper extends AbstractOEmbedHelper {
  /**
   * Gets public URL.
   *
   * @param File $file
   * @param bool $relativeToCurrentScript
   * @return string|NULL The public URL.
   */
  public function getPublicUrl(File $file, $relativeToCurrentScript = false) {
    $videoId = $this->getOnlineMediaId($file);

    return sprintf('https://www.youtube.com/watch?v=%s', $videoId);
  }

  /**
   * Gets local absolute file path to preview image.
   *
   * @param File $file The File
   * @return string The local absolute file path to preview image
   */
  public function getPreviewImage(File $file) {
    $videoId           = $this->getOnlineMediaId($file);
    $temporaryFileName = $this->getTempFolderPath() . 'youtube_' . md5($videoId) . '.jpg';

    if (!file_exists($temporaryFileName)) {
      $tryNames = ['maxresdefault.jpg', '0.jpg'];

      foreach ($tryNames as $tryName) {
        $previewImage = GeneralUtility::getUrl(
          sprintf('https://img.youtube.com/vi/%s/%s', $videoId, $tryName)
        );

        if ($previewImage !== false) {
          file_put_contents($temporaryFileName, $previewImage);

          GeneralUtility::fixPermissions($temporaryFileName);

          break;
        }
      }
    }

    return $temporaryFileName;
  }

  /**
   * Trys to transform the given URL to an file.
   *
   * @param string $url The URL
   * @param Folder $targetFolder The target folder
   * @return File|NULL
   */
  public function transformUrlToFile($url, Folder $targetFolder) {
    $videoId = null;

    // Try to get the YouTube code from given url.
    // These formats are supported with and without http(s)://
    // - youtu.be/<code> # Share URL
    // - www.youtube.com/watch?v=<code> # Normal web link
    // - www.youtube.com/v/<code>
    // - www.youtube-nocookie.com/v/<code> # youtube-nocookie.com web link
    // - www.youtube.com/embed/<code> # URL form iframe embed code, can also get code from full iframe snippet

    if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
      $videoId = $match[1];
    }

    if (empty($videoId)) {
      return null;
    }

    return $this->transformMediaIdToFile($videoId, $targetFolder, $this->extension);
  }

  /**
   * Gets oEmbed URL to retrieve oEmbed data.
   *
   * @param string $mediaId The media ID
   * @param string $format The format
   * @return string The oEmbed URL.
   */
  protected function getOEmbedUrl($mediaId, $format = 'json') {
    return sprintf('https://www.youtube.com/oembed?url=%s&format=%s',
      urlencode(sprintf('https://www.youtube.com/watch?v=%s', $mediaId)),
      rawurlencode($format)
    );
  }
}