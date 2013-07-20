<?php
/**
 * @author Manuele Menozzi <mmenozzi@webgriffe.com>
 */

namespace Webgriffe\MagentoInstaller;


class PdoWrapper
{
    /**
     * @var \PDO
     */
    protected $pdo;

    public function init($dsn, $user, $password)
    {
        $this->pdo = new \PDO($dsn, $user, $password);
    }

    public function query($statement)
    {
        return $this->pdo->query($statement);
    }
}