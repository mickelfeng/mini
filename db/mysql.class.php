<?php
class mini_db_mysql implements mini_db_interface
{
    /**
     * db config array
     *
     * @var array
     */
    private $config = array();
    /**
     * eturns a MySQL link identifier on success or FALSE on failure.
     *
     * @var resource
     */
    private $link = null;

    /**
     * mini_db_mysql __construct
     *
     * @param array $config
     */
    public function __construct($config = array())
    {
        $this->config = $config;
    
    }

    /**
     * Open a connection to a MySQL Server
     *
     * @throws if not connect
     */
    public function connect()
    {
        $this->link = @mysql_connect($this->config['host'] ,$this->config['user'] ,$this->config['pass']);
        
        if(! $this->link) {
            mini::e('Could not connect: {error}' ,array('{error}'=>mysql_error()));
        }
        mysql_select_db($this->config['dbname'] ,$this->link);
        $charset = $this->config['charset'];
        if(empty($charset)) {
            $charset = 'utf8';
        }
        mysql_query("set names $charset" ,$this->link);
    
    }

    /**
     * Send a MySQL query
     *
     * @param string $sql
     * @return resource
     */
    public function query($sql)
    {
        if($this->link == null) {
            $this->connect();
        }
        $query = mysql_query($sql ,$this->link);
        if(! $query) {
            mini::e('mysql query error:{error}:{link}:{sql}.' ,array('{error}'=>mysql_errno($this->link),'{link}'=>mysql_error($this->link),'{sql}'=>$sql));
        }
        return $query;
    
    }

    /**
     * Send an SQL query to MySQL without fetching and buffering the result
     * rows.
     *
     * @param string $sql
     * @param mini_db_unbuffer $unbuffer
     * @throws if $unbuffer not instanceof mini_db_unbuffer
     */
    public function unbuffer($sql, $unbuffer, $callback='')
    {
        if($unbuffer instanceof mini_db_unbuffer) {
            $query = $this->query($sql);
            while($row = mysql_fetch_assoc($query)) {
                
                if(empty($callback)) {
                    $unbuffer->callback($row);
                } else {
                    $unbuffer->$callback($row);
                }
            }
            $this->free($query);
        } else {
            mini::e('if use unbuffer must expend mini_db_unbuffer interface');
        }
    
    }

    /**
     * Get number of affected rows in previous MySQL operation
     *
     * @return int
     */
    public function affected()
    {
        return mysql_affected_rows($this->link);
    
    }

    /**
     * Fetch a result row as an associative array
     *
     * @param string $sql
     * @return array
     */
    public function find($sql)
    {
        $query = $this->query($sql);
        $row = mysql_fetch_assoc($query);
        $this->free($query);
        return $row;
    
    }

    public function lastInsertId()
    {
        $lastid = $this->find("select LAST_INSERT_ID() as id");
        return $lastid['id'];
    
    }

    /**
     * Fetch a result row as an object.
     *
     * @param string $sql
     * @return array
     */
    public function findObj($sql)
    {
        $query = $this->query($sql);
        $row = mysql_fetch_object($query);
        $this->free($query);
        return $row;
    
    }

    /**
     * Fetch a result rows as an associative array
     *
     * @param string $sql
     * @return array
     */
    public function findAll($sql)
    {
        $rows = array();
        $query = $this->query($sql);
        while($row = mysql_fetch_assoc($query)) {
            $rows[] = $row;
        }
        $this->free($query);
        return $rows;
    
    }

    /**
     * Fetch a result rows as an object.
     *
     * @param string $sql
     * @return array
     */
    public function findObjAll($sql)
    {
        $query = $this->query($sql);
        while($row = mysql_fetch_object($query)) {
            $rows[] = $row;
        }
        $this->free($query);
        return $rows;
    
    }

    /**
     * Free result memory.
     *
     * @param resource $query
     */
    private function free($query)
    {
        mysql_free_result($query);
    
    }
}
?>