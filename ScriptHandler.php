<?php
/**
 * @author Manuele Menozzi <mmenozzi@webgriffe.com>
 */

namespace Webgriffe\MagentoInstaller;


use Composer\Script\Event;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

class ScriptHandler
{
    const DATABASE_CHARACTER_SET = 'utf8';
    const DATABASE_COLLATE = 'utf8_general_ci';

    public static function installMagento(Event $event)
    {
        $options = $event->getComposer()->getPackage()->getExtra();
        $parametersFile = $options['install'];

        if (!file_exists($parametersFile)) {
            throw new FileNotFoundException($parametersFile);
        }

        $yml = Yaml::parse($parametersFile);
        $parameters = self::getInstallParameters($yml['parameters']);

        if (!self::askConfirmation($event, $parameters)) {
            return;
        }

        self::createMysqlDatabase($parameters);

        $command = static::getInstallCommand($parameters);
        self::executeCommand($command);
    }

    protected static function executeCommand($command)
    {
        $process = new Process(null);
        $process->setCommandLine($command);
        $process->setTimeout(300);
        $process->run(function ($type, $buffer) { echo $buffer; });
        if (!$process->isSuccessful()) {
            throw new \RuntimeException(sprintf('An error occurred while executing \'%s\'.', $command));
        }
    }

    private static function getInstallCommand(array $parameters)
    {
        $arguments = array();
        foreach ($parameters as $key => $value) {
            $arguments[] = sprintf('--%s "%s"', $key, $value);
        }

        $arguments = implode(' ', $arguments);
        return sprintf('php -f install.php -- %s', $arguments);
    }

    private static function getInstallParameters(array $parameters)
    {
        return array_merge(
            array(
                'license_agreement_accepted' => '1',
                'skip_url_validation' => '1',
                'use_rewrites' => '1',
                'use_secure' => '0',
                'use_secure_admin' => '0'
            ),
            $parameters,
            array(
                'secure_base_url' => $parameters['url']
            )
        );
    }

    /**
     * @param $parameters
     */
    private static function createMysqlDatabase(array $parameters)
    {
        $mysqlPdoWrapper = new PdoWrapper();
        $dsn = sprintf('mysql:host=%s', $parameters['db_host']);
        $mysqlPdoWrapper->init($dsn, $parameters['db_user'], $parameters['db_pass']);
        $createDatabaseQuery = sprintf(
            'CREATE DATABASE `%s` CHARACTER SET %s COLLATE %s;',
            $parameters['db_name'],
            self::DATABASE_CHARACTER_SET,
            self::DATABASE_COLLATE
        );
        $mysqlPdoWrapper->query($createDatabaseQuery);
    }

    /**
     * @param Event $event
     * @param $parameters
     * @return bool
     */
    private static function askConfirmation(Event $event, $parameters)
    {
        $confirmation = $event->getIO()->askConfirmation(
            sprintf(
                'Do you want to create MySQL database \'%s\' and install Magento on it [Y,n]?',
                $parameters['db_name']
            ),
            true
        );
        return $confirmation;
    }
}