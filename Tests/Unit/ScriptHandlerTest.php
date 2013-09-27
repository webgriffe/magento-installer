<?php
/**
 * @author Manuele Menozzi <mmenozzi@webgriffe.com>
 */

namespace Webgriffe\MagentoInstaller\Tests\Unit;


use Composer\IO\IOInterface;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Mockery as m;
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
        $event = $this->getEventMock($this->getIOMockThatAsksConfirmation(true));
        $this->createProcessOverloadMock(true);
        $this->createPdoWrapperOverloadMock();

        $scriptHandler = new ScriptHandler();
        $scriptHandler::installMagento($event);
    }

    public function testInstallFailed()
    {
        $event = $this->getEventMock($this->getIOMockThatAsksConfirmation(true));
        $this->createProcessOverloadMock(false);
        $this->createPdoWrapperOverloadMock();
        $this->setExpectedException('\RuntimeException');

        $scriptHandler = new ScriptHandler();
        $scriptHandler::installMagento($event);
    }

    public function testInstallWithoutYamlFile()
    {
        $this->root = vfsStream::setup('root', null, array('var' => array()));
        $event = $this->getEventMockWithoutIO();
        $this->createProcessOverloadMock(true);

        $this->setExpectedException('Webgriffe\MagentoInstaller\FileNotFoundException');

        $scriptHandler = new ScriptHandler();
        $scriptHandler::installMagento($event);
    }

    public function testInstallShouldSkipBecauseDatabaseAlreadyExists()
    {
        $event = $this->getEventMock($this->getIOMockForDatabaseThatAlreadyExists());
        $this->createPdoWrapperOverloadMock(true);

        $scriptHandler = new ScriptHandler();
        $scriptHandler::installMagento($event);
    }

    public function testInstallNoInteraction()
    {
        $event = $this->getEventMock($this->getIOMockThatAsksConfirmation(false));
        $this->createProcessOverloadMock(true);
        $this->createPdoWrapperOverloadMock();

        $scriptHandler = new ScriptHandler();
        $scriptHandler::installMagento($event);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getEventMock(IOInterface $ioMock = null)
    {
        $event = $this->getMockBuilder('Composer\Script\Event')
            ->disableOriginalConstructor()
            ->getMock();

        $event
            ->expects($this->once())
            ->method('getComposer')
            ->will($this->returnValue($this->getComposerMock()));

        if ($ioMock) {
            $event
                ->expects($this->atLeastOnce())
                ->method('getIO')
                ->will($this->returnValue($ioMock));
        }

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
            'install' => 'vfs://root/var/install.yml',
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
        $content[] = 'parameters:';
        $content[] = '  locale: it_IT';
        $content[] = '  timezone: Europe/Rome';
        $content[] = '  default_currency: EUR';
        $content[] = '  db_host: localhost';
        $content[] = '  db_name: magento';
        $content[] = '  db_user: magento';
        $content[] = '  db_pass: password';
        $content[] = '  url: http://magento.local/';
        $content[] = '  admin_firstname: Mario';
        $content[] = '  admin_lastname: Rossi';
        $content[] = '  admin_email: mario.rossi@foo.it';
        $content[] = '  admin_username: admin';
        $content[] = '  admin_password: password';

        return implode(PHP_EOL, $content);
    }

    private function getExpectedArguments()
    {
        return 'php -f install.php -- --license_agreement_accepted "1" --skip_url_validation "1" --use_rewrites "1" '.
            '--use_secure "0" --use_secure_admin "0" --locale "it_IT" --timezone "Europe/Rome" ' .
            '--default_currency "EUR" --db_host "localhost" --db_name "magento" --db_user "magento" ' .
            '--db_pass "password" --url "http://magento.local/" --admin_firstname "Mario" --admin_lastname "Rossi" ' .
            '--admin_email "mario.rossi@foo.it" --admin_username "admin" --admin_password "password" '.
            '--secure_base_url "http://magento.local/"';
    }

    protected function tearDown()
    {
        m::close();
    }

    private function createProcessOverloadMock($isSuccessful)
    {
        $process = m::mock('overload:Symfony\Component\Process\Process');
        $process->shouldReceive('setCommandLine')->times(1)->with($this->getExpectedArguments());
        $process->shouldReceive('setTimeout')->times(1)->with(300);
        $process->shouldReceive('run')->times(1);
        $process->shouldReceive('isSuccessful')->times(1)->andReturn($isSuccessful);
    }

    private function createPdoWrapperOverloadMock($databaseAlreadyExists = false)
    {
        $pdoStatement = m::mock('stdClass');
        $pdoStatement
            ->shouldReceive('rowCount')
            ->once()
            ->withNoArgs()
            ->andReturn((int)$databaseAlreadyExists);

        $pdo = m::mock('overload:Webgriffe\MagentoInstaller\PdoWrapper');
        $pdo->shouldReceive('init')->times(1)->with('mysql:host=localhost', 'magento', 'password');
        $pdo
            ->shouldReceive('query')
            ->once()
            ->with('SHOW DATABASES LIKE \'magento\';')
            ->andReturn($pdoStatement)
            ->ordered();
        if (!$databaseAlreadyExists) {
            $pdo
                ->shouldReceive('query')
                ->once()
                ->with('CREATE DATABASE `magento` CHARACTER SET utf8 COLLATE utf8_general_ci;')
                ->ordered();
        }
    }

    private function getIOMockThatAsksConfirmation($isInteractive, $confirmation = true)
    {
        $io = $this->getMock('\Composer\IO\IOInterface');

        $io->expects($this->once())
            ->method('isInteractive')
            ->will($this->returnValue($isInteractive));

        if (!$isInteractive) {
            $io->expects($this->never())->method('askConfirmation');
            return $io;
        }

        $io->expects($this->once())
            ->method('askConfirmation')
            ->with('Do you want to create MySQL database \'magento\' and install Magento on it [Y,n]?', true)
            ->will($this->returnValue($confirmation));

        return $io;
    }

    private function getIOMockForDatabaseThatAlreadyExists()
    {
        $io = $this->getMock('\Composer\IO\IOInterface');
        $io
            ->expects($this->once())
            ->method('write')
            ->with('Database \'magento\' already exists, installation skipped.');

        return $io;
    }

    private function getEventMockWithoutIO()
    {
        return $this->getEventMock();
    }
}