<?php
/**
 * @author Manuele Menozzi <mmenozzi@webgriffe.com>
 */

namespace Webgriffe\MagentoInstaller;


use Exception;

class FileNotFoundException extends \Exception
{
    public function __construct($filename)
    {
        parent::__construct(sprintf('The file %s doesn\'t exists.', $filename));
    }
}