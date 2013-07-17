<?php
/**
 * @author Manuele Menozzi <mmenozzi@webgriffe.com>
 */

namespace Webgriffe\MagentoInstaller\Tests\Unit;


use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Webgriffe\MagentoInstaller\ScriptHandler;

class ScriptHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $root;

    public function setUp()
    {
        $structure = array('var' => array('install.yml' => $this->getInstallYmlContent()));
        $this->root = vfsStream::setup('root', null, $structure);
    }

    public function testInstallMagento()
    {
        $event = $this->getEventMock();

        ScriptHandler::installMagento($event);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getEventMock()
    {
        $event = $this->getMockBuilder('Composer\Script\Event')
            ->disableOriginalConstructor()
            ->getMock();

        $event
            ->expects($this->once())
            ->method('getComposer')
            ->will($this->returnValue($this->getComposerMock()));

        return $event;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getComposerMock()
    {
        $package = $this->getPackageMock();

        $composer = $this->getMockBuilder('Composer\Composer')
            ->disableOriginalConstructor()
            ->getMock();
        $composer
            ->expects($this->once())
            ->method('getPackage')
            ->will($this->returnValue($package));

        return $composer;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getPackageMock()
    {
        $extra = array(
            'install' => 'var/install.yml'
        );
        $package = $this->getMockBuilder('Composer\Package\RootPackageInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $package
            ->expects($this->once())
            ->method('getExtra')
            ->will($this->returnValue($extra));

        return $package;
    }

    private function getInstallYmlContent()
    {
        $content = array();
        $content[] = 'parameters';
        $content[] = '  locale: it_IT';
        $content[] = '  timezone: Europe/Rome';
        $content[] = '  currency: EUR';
        $content[] = '  db_host: localhost';
        $content[] = '  db_name: magento';
        $content[] = '  db_user: magento';
        $content[] = '  db_pass: password';
        $content[] = '  url: http://magento.local/';
        $content[] = '  admin_first_name: Mario';
        $content[] = '  admin_last_name: Rossi';
        $content[] = '  admin_email: mario.rossi@foo.it';
        $content[] = '  admin_username: admin';
        $content[] = '  admin_password: password';

        return implode(PHP_EOL, $content);
    }
}