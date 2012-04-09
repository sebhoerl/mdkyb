<?php

require_once  __DIR__ . '/../app/bootstrap.php.cache';
require_once __DIR__ . '/../app/autoload.php';

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;

error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
ini_set('display_errors', 'On');

class ExternalService
{
    static $instance = null;

    private $connection = null;
    private $user = null;

    private $sessionId = null;
    private $sessionName = null;

    static public function getInstance()
    {
        return static::$instance ? static::$instance : (static::$instance = new static());
    }

    public function getConnection()
    {
        if ($this->connection) {
            return $this->connection;
        }

        $ini = parse_ini_file(__DIR__ . '/../app/config/parameters.ini');
        $config = new Configuration();

        return $this->connection = DriverManager::getConnection(array(
            'dbname' => $ini['database_name'],
            'user' => $ini['database_user'],
            'password' => $ini['database_password'],
            'host' => $ini['database_host'],
            'driver' => $ini['database_driver'],
        ), $config);
    }

    public function generatePath($suffix)
    {
        $uri = $_SERVER['REQUEST_URI'];
        $uri = preg_replace('@/(forum|wiki)(.*)$@', '', $uri);
        $uri .= $suffix;
        return $uri;
    }

    protected function enterSession()
    {
        session_write_close();
        $this->sessionId = session_id($_COOKIE['mdkyb']);
        $this->sessionName = session_name("mdkyb");
        session_start();
    }

    protected function leaveSession()
    {
        session_write_close();
        if ($this->sessionId) {
            session_id($this->sessionId);
            session_name($this->sessionName);
            session_start();
        }
    }

    public function getUser()
    {
        if ($this->user) {
            return $this->user;
        }

        $this->enterSession();

        if (isset($_SESSION['_symfony2'])) {
            $symfony = $_SESSION['_symfony2'];
            if (isset($symfony['attributes']))
            {
                $attributes = $symfony['attributes'];
                if (isset($attributes['_security_secured'])) {
                    $token = $attributes['_security_secured'];
                    $token = unserialize($token);
                    $this->user = $token->getUser();
                }
            }
        }

        $this->leaveSession();

        return $this->user;
    }

    public function setUser($user)
    {
        $this->enterSession();

        $token = unserialize($_SESSION['_symfony2']['attributes']['_security_secured']);
        $token->setUser($user);
        $token = serialize($token);
        $_SESSION['_symfony2']['attributes']['_security_secured'] = $token;
        
        $this->leaveSession();
    }

    public function changeField($field, $value)
    {
        if (in_array($field, array('forumId', 'wikiId'))) {
            if ($user = $this->getUser()) {
                $statement = $this->getConnection()
                    ->prepare(sprintf('UPDATE Member SET %s=? WHERE id=?', $field));
                $statement->bindValue(1, $value);
                $statement->bindValue(2, $user->getId());
                $statement->execute();

                $setter = 'set' . $field;
                $user->{$setter}($value);

                $this->setUser($user);
            }
        }
    }
}
