<?php 

/**
 * FOOTUP - 0.1.3 - 11.2021
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup/Orm
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

namespace Footup\Orm;

use App\Config\Config;
use PDO;
use Exception;

class DbConnection
{
    /**
     * @var \PDO $db connection
     */
    protected static $db = null;

    /**
     * Sets the database connection.
     *
     * @param \PDO|DbConnection|string|array $config
     * @param boolean $init
     * @throws \Exception For connection error
     * @return \PDO
     */
    public static function setDb($config = null, $init = true)
    {
        if($config instanceof PDO)
        {
            return self::$db = $config;
        }
        if($config instanceof DbConnection)
        {
            return self::$db = $config::getDb();
        }

        if ($init && self::$db == null) {
            // Connection string
            if (is_string($config)) {
                return self::setDb(self::parseConnection($config));
            }
            // Connection information
            else if (is_array($config) || is_null($config)) {
                $Config = (new Config($config))->config;

                switch ($Config['db_type']) {
                    case 'pdopgsql':
                        $dsn = sprintf(
                            'pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s',
                            $Config['db_host'],
                            isset($Config['db_port']) ? $Config['db_port'] : 5432,
                            $Config['db_name'],
                            $Config['db_user'],
                            $Config['db_pass']
                        );

                        return self::$db = new PDO($dsn);

                    case 'pdosqlite':
                        return self::$db = new PDO('sqlite:/' . $Config['db_name']);

                    case 'pdomysql':
                    default:
                        $dsn = sprintf(
                            'mysql:host=%s;port=%d;dbname=%s',
                            $Config['db_host'],
                            isset($Config['db_port']) ? $Config['db_port'] : 3306,
                            $Config['db_name']
                        );
                        return self::$db = new PDO($dsn, $Config['db_user'], $Config['db_pass']);
                }

                if (self::$db == null) {
                    throw new Exception(text("Db.undefinedDb"));
                }
            }
            // Connection object or resource
            else {
                throw new Exception(text("Db.unsupportedType"));
            }
        } else {
            return self::$db;
        }
    }

    /**
     * Gets the database connection.
     *
     * @return PDO Database connection
     */
    public static function getDb()
    {
        return self::$db;
    }

    /**
     * Annalyse d'un url et convert à un objet.
     *
     * @param string $connection Connection string
     * @return array Connection information
     * @throws Exception For invalid connection string
     */
    public static function parseConnection($connection)
    {
        $url = parse_url($connection);

        if (empty($url)) {
            throw new Exception(text('Db.urlInvalid'));
        }

        $cfg = array();

        $cfg['db_type'] = isset($url['scheme']) ? $url['scheme'] : $url['path'];
        $cfg['db_host'] = isset($url['host']) ? $url['host'] : null;
        $cfg['db_name'] = isset($url['path']) ? substr($url['path'], 1) : null;
        $cfg['db_user'] = isset($url['user']) ? $url['user'] : null;
        $cfg['db_pass'] = isset($url['pass']) ? $url['pass'] : null;
        $cfg['db_port'] = isset($url['port']) ? $url['db_port'] : null;

        return $cfg;
    }
}