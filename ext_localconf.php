<?php
defined('TYPO3_MODE') or die();

// === Variables ===

$extensionKey = $_EXTKEY;
$iconsFolder  = \T3v\T3vCore\Utility\ExtensionUtility::iconsFolder($extensionKey);

// === Youku Integration ===

// Add Youku as new mime type
$GLOBALS['TYPO3_CONF_VARS']['SYS']['FileInfo']['fileExtensionToMimeType']['youku'] = 'video/youku';

// Add Youku to allowed media file extensions
$GLOBALS['TYPO3_CONF_VARS']['SYS']['mediafile_ext'] .= ',youku';

// Register the Youku helper
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['onlineMediaHelpers']['youku'] = \T3v\T3vMedia\Resource\OnlineMedia\Helpers\YoukuHelper::class;

// Register the Youku renderer
$rendererRegistry = \TYPO3\CMS\Core\Resource\Rendering\RendererRegistry::getInstance();
$rendererRegistry->registerRendererClass(\T3v\T3vMedia\Resource\Rendering\YoukuRenderer::class);

// === Backend ===

if (TYPO3_MODE === 'BE') {
  $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);

  // Register the icon for the Youku mime type
  $iconRegistry->registerIcon(
    'mimetypes-media-video-youku',
    \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
    ['source' => "{$iconsFolder}/MimeTypes/Youku.svg"]
  );

  // Register the file extension icon
  $iconRegistry->registerFileExtension('youku', 'mimetypes-media-video-youku');
}