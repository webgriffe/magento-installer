<?php
/**
 * @author Manuele Menozzi <mmenozzi@webgriffe.com>
 */

namespace Webgriffe\MagentoInstaller\Tests\Unit;


use Webgriffe\MagentoInstaller\ScriptHandler;

class ScriptHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testInstallMagento()
    {
        $event = $this->getMockBuilder('Composer\Script\Event')
            ->disableOriginalConstructor()
            ->getMock();
        ScriptHandler::installMagento($event);
    }
}