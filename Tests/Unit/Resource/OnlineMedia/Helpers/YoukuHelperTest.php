<?php
namespace T3v\Media\Tests\Unit\Resource\OnlineMedia\Helpers;

use Nimut\TestingFramework\TestCase\UnitTestCase;

use T3v\T3vMedia\Resource\OnlineMedia\Helpers\YoukuHelper;

/**
 * The Youku helper test class.
 *
 * @package T3v\Media\Tests\Unit\Resource\OnlineMedia\Helpers
 */
class YoukuHelperTest extends UnitTestCase {
  /**
   * Tests the transform URL to media ID function.
   *
   * @test
   */
  public function transformUrlToMediaId() {
    $this->assertEquals('',                YoukuHelper::transformUrlToMediaId('https://t3v.com'));
    $this->assertEquals('XXXXXXXXXXXXXXX', YoukuHelper::transformUrlToMediaId('v.youku.com/v_show/id_XXXXXXXXXXXXXXX'));
    $this->assertEquals('XXXXXXXXXXXXXXX', YoukuHelper::transformUrlToMediaId('http://v.youku.com/v_show/id_XXXXXXXXXXXXXXX'));
    $this->assertEquals('XXXXXXXXXXXXXXX', YoukuHelper::transformUrlToMediaId('https://v.youku.com/v_show/id_XXXXXXXXXXXXXXX'));
    $this->assertEquals('XXXXXXXXXXXXXXX', YoukuHelper::transformUrlToMediaId('v.youku.com/v_show/id_XXXXXXXXXXXXXXX==.html'));
    $this->assertEquals('XXXXXXXXXXXXXXX', YoukuHelper::transformUrlToMediaId('http://v.youku.com/v_show/id_XXXXXXXXXXXXXXX==.html'));
    $this->assertEquals('XXXXXXXXXXXXXXX', YoukuHelper::transformUrlToMediaId('https://v.youku.com/v_show/id_XXXXXXXXXXXXXXX==.html'));
    $this->assertEquals('XXXXXXXXXXXXXXX', YoukuHelper::transformUrlToMediaId('player.youku.com/embed/XXXXXXXXXXXXXXX=='));
    $this->assertEquals('XXXXXXXXXXXXXXX', YoukuHelper::transformUrlToMediaId('http://player.youku.com/embed/XXXXXXXXXXXXXXX=='));
    $this->assertEquals('XXXXXXXXXXXXXXX', YoukuHelper::transformUrlToMediaId('https://player.youku.com/embed/XXXXXXXXXXXXXXX=='));
  }
}
