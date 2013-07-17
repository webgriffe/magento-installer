<?php
/**
 * @author Manuele Menozzi <mmenozzi@webgriffe.com>
 */

namespace Webgriffe\MagentoInstaller;


use Composer\Script\Event;
use Symfony\Component\Yaml\Yaml;

class ScriptHandler
{
    public static function installMagento(Event $event)
    {
        $options = $event->getComposer()->getPackage()->getExtra();
        $installArguments = static::computeInstallArguments($options['install']);
        static::doInstall($installArguments);
    }

    protected static function doInstall($arguments)
    {
        return $arguments;
    }

    private static function computeInstallArguments($installParametersFile)
    {
        $yml = Yaml::parse($installParametersFile);
        $parameters = self::getInstallParameters($yml['parameters']);
        $arguments = array();
        foreach ($parameters as $key => $value) {
            $arguments[] = sprintf('--%s "%s"', $key, $value);
        }

        return implode(' ', $arguments);
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