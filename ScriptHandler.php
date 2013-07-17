<?php
/**
 * @author Manuele Menozzi <mmenozzi@webgriffe.com>
 */

namespace Webgriffe\MagentoInstaller;


use Composer\Script\Event;

class ScriptHandler
{
    public static function installMagento(Event $event)
    {
        $event->getComposer()->getPackage()->getExtra();
    }
}