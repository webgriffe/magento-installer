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
    public static function installMagento(Event $event)
    {
        $options = $event->getComposer()->getPackage()->getExtra();
        $command = static::getInstallCommand($options['install']);
        static::executeCommand($command);
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

    private static function getInstallCommand($installParametersFile)
    {
        $yml = Yaml::parse($installParametersFile);
        $parameters = self::getInstallParameters($yml['parameters']);
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
}