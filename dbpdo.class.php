<?php
/**
 * Simple PHP PDO Class
 * @author Miks Zvirbulis (twitter.com/MiksZvirbulis)
 * @version 1.1
 * 1.0 - First version launched. Allows access to one database and a few regular functions have been created.
 * 1.1 - Added a constructor which allows multiple databases to be called on different variables.
 */
class dbpdo {
    # Database host address, defined in construction.
    protected $host;
    # Username for authentication, defined in construction.
    protected $username;
    # Password for authentication, defined in construction.
    protected $password;
    # Database name, defined in construction.
    protected $database;

    # Connection variable. DO NOT CHANGE!
    protected $connection;

    # @bool default for this is to be left to FALSE, please. This determines the connection state.
    public $connected = false;

    # @bool this controls if the errors are displayed. By default, this is set to true.
    private $errors = true;

    function __construct($db_host, $db_username, $db_password, $db_database, $charset = 'utf8mb4') {
        global $c;
        try {
            $this->host = $db_host;
            $this->username = $db_username;
            $this->password = $db_password;
            $this->database = $db_database;
            $this->connected = true;
            $this->charset = $charset;

            $this->connection = new PDO("mysql:host=$this->host;dbname=$this->database;charset=$this->charset", $this->username, $this->password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {
            $this->connected = false;
            if ($this->errors === true) {
                return $this->error($e->getMessage());
            } else {
                return false;
            }
        }
    }

    function __destruct() {
        $this->connected = false;
        $this->connection = null;
    }

    public function error($error, $query, $parameters) {
        echo "<pre style='direction: ltr; text-align: left; background: white'>" . print_r(['DB error:' => $error, 'query:' => $query, 'parameters:' => $parameters, 'file:' => debug_backtrace()[1]['file'], 'line:' => debug_backtrace()[1]['line']], 1);
        die;
    }

    public function fetch($query, $parameters = array()) {
        if ($this->connected === true) {
            try {
                $query = $this->connection->prepare($query);
                $query->execute($parameters);
                return $query->fetch(PDO::FETCH_OBJ);
            }
            catch(PDOException $e) {
                if ($this->errors === true) {
                    return $this->error($e->getMessage(), $query, $parameters);
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    public function fetchAll($query, $parameters = array()) {
        if ($this->connected === true) {
            try {
                $query = $this->connection->prepare($query);
                $query->execute($parameters);
                return $query->fetchAll(PDO::FETCH_OBJ);
            }
            catch(PDOException $e) {
                if ($this->errors === true) {
                    return $this->error($e->getMessage(), $query, $parameters);
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    public function count($query, $parameters = array()) {
        if ($this->connected === true) {
            try {
                $query = $this->connection->prepare($query);
                $query->execute($parameters);
                return $query->rowCount();
            }
            catch(PDOException $e) {
                if ($this->errors === true) {
                    return $this->error($e->getMessage(), $query, $parameters);
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    public function execute($query, $parameters = array()) {
        if ($this->connected === true) {
            try {
                $query = $this->connection->prepare($query);
                $query->execute($parameters);
                return true;
            }
            catch(PDOException $e) {
                if ($this->errors === true) {
                    return $this->error($e->getMessage(), $query, $parameters);
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    public function tableExists($table) {
        if ($this->connected === true) {
            try {
                $query = $this->count("SHOW TABLES LIKE '$table'");
                return ($query > 0) ? true : false;
            }
            catch(PDOException $e) {
                if ($this->errors === true) {
                    return $this->error($e->getMessage(), $query);
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }
}

function sql_escape($value) {
    $return = '';

    for($i = 0; $i < strlen($value); ++$i) {
        $char = $value[$i];
        $ord = ord($char);
        if($char !== "'" && $char !== "\"" && $char !== '\\' && $ord >= 32 && $ord <= 126) {
            $return .= $char;
        } else {
            $return .= '\\x' . dechex($ord);
        }
    }

    return $return;
}

function db() {
    static $DB = null;

    if (!isset($DB)) {
        $DB = new dbpdo('localhost', 'root', '1234', 'test', 'utf8mb4');
    }

    return $DB;
}
