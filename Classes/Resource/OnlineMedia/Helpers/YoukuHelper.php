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
   * The allowed hosts.
   */
  const ALLOWED_HOSTS = ['v.youku.com', 'player.youku.com'];

  /**
   * Gets public URL.
   *
   * @param File $file The file
   * @param bool $relativeToCurrentScript Use relative paths to the current script, defaults to `false`
   * @return string|null The public URL or null
   */
  public function getPublicUrl(File $file, $relativeToCurrentScript = false) {
    $mediaId = $this->getOnlineMediaId($file);

    return sprintf('https://v.youku.com/v_show/id_%s', $mediaId);
  }

  /**
   * Gets the preview image (local absolute file path).
   *
   * @param File $file The File
   * @return string The preview image
   */
  public function getPreviewImage(File $file) {
    $mediaId           = $this->getOnlineMediaId($file);
    $temporaryFileName = $this->getTempFolderPath() . 'youtube_' . md5($mediaId) . '.jpg';

    if (!file_exists($temporaryFileName)) {
      $previewImage = file_get_contents(GeneralUtility::getFileAbsFileName('EXT:t3v_media/Resources/Public/Preview/Youku.jpg'));

      if ($previewImage !== false) {
        file_put_contents($temporaryFileName, $previewImage);

        GeneralUtility::fixPermissions($temporaryFileName);
      }
    }

    return $temporaryFileName;
  }

  /**
   * Tries to transform the given URL to a media ID.
   *
   * These formats are supported with and without `http(s)://`:
   *
   * - v.youku.com/v_show/id_<VIDEO_ID>
   * - v.youku.com/v_show/id_<VIDEO_ID>==.html
   * - player.youku.com/embed/<VIDEO_ID>==
   *
   * @param string $url The URL to transform
   * @return string|null The media ID or null if the URL couldn't transformed to a media ID
   */
  public static function transformUrlToMediaId($url) {
    $mediaId = null;

    // Add protocol prefix if not present
    if (strpos($url, '://') === false && substr($url, 0, 1) != '/') {
      $url = 'https://' . $url;
    }

    $parsedUrl = parse_url($url);

    if (!empty($parsedUrl)) {
      $host = $parsedUrl['host'];

      if (in_array($host, self::ALLOWED_HOSTS)) {
        if (preg_match('/^(?:http(?:s)?:\/\/)?(?:v\.)?(?:player\.)?(?:youku\.com\/(?:v_show|embed)\/)?([^\?&\"\'>]+)/', $url, $match)) {
          if ($match[1]) {
            $mediaId = $match[1];
            $mediaId = str_replace('id_', '', $mediaId);
            $mediaId = str_replace('==', '', $mediaId);
            $mediaId = str_replace('.html', '', $mediaId);
            $mediaId = str_replace('.json', '', $mediaId);
          }
        }
      }
    }

    return $mediaId;
  }

  /**
   * Tries to transform the given URL to an file.
   *
   * @param string $url The URL
   * @param Folder $targetFolder The target folder
   * @return File|null The file or null
   */
  public function transformUrlToFile($url, Folder $targetFolder) {
    $mediaId = self::transformUrlToMediaId($url);

    if (empty($mediaId)) {
      return null;
    }

    return $this->transformMediaIdToFile($mediaId, $targetFolder, $this->extension);
  }

  /**
   * Gets the oEmbed URL to retrieve oEmbed data.
   *
   * @param string $mediaId The media ID
   * @param string $format The optional format, defaults to `json`
   * @return string The oEmbed URL
   */
  protected function getOEmbedUrl($mediaId, $format = 'json') {
    $url = sprintf('https://v.youku.com/v_show/id_%s==.%s', $mediaId, $format);

    return $url;
  }

  /**
   * Gets the OEmbed data.
   *
   * @param string $mediaId The media ID
   * @return array|null The OEmbed data or null if no OEmbed data is available
   */
  protected function getOEmbedData($mediaId) {
    // $oEmbedUrl  = $this->getOEmbedUrl($mediaId);
    // $oEmbedData = GeneralUtility::getUrl($oEmbedUrl);
    //
    // if ($oEmbedData) {
    //   $oEmbedData = json_decode($oEmbedData, true);
    // }
    //
    // return $oEmbedData;

    return null;
  }
}