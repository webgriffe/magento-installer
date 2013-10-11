<?php
/**
 * @author Manuele Menozzi <mmenozzi@webgriffe.com>
 */

namespace Webgriffe\MagentoInstaller;


class DirectoryNotFoundException extends \Exception
{
    public function __construct($dirname)
    {
        parent::__construct(sprintf('The directory %s doesn\'t exists.', $dirname));
    }
}