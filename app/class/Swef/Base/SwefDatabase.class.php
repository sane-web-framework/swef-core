<?php

namespace Swef\Base;

class SwefDatabase {

    private $calls          = array ();
    private $DSN;
    public  $errors         = array ();
    private $filter;
    public  $inputs;
    public  $notices        = array ();
    private $PDO;
    private $type;
    private $types;

    public function __construct ($dsn,$usr,$pwd) {
        $this->types        = explode (SWEF_STR__COMMA,SWEF_SUPPORTED_DATABASE_DRIVERS);
        $this->dbConnect ($dsn,$usr,$pwd);
    }

    public function __destruct ( ) {
        $this->dbClose ();
    }

    public function dbCall ( ) {
        $this->notices      = array ();
        if (!in_array($this->type,$this->types)) {
            array_push ($this->errors,'Database type "'.$this->type.'" is not supported');
            \Swef\Bespoke\Swef::statusHeader (SWEF_HTTP_STATUS_CODE_555);
            return SWEF_BOOL_FALSE;
        }
        // Process inputs
        $args               = func_get_args ();
        $proc               = array_shift ($args);
        if (!$proc) {
            array_push ($this->errors,'Stored procedure name not given!');
            \Swef\Bespoke\Swef::statusHeader (SWEF_HTTP_STATUS_CODE_555);
            return SWEF_BOOL_FALSE;
        }
        // Validation filter and build argument placeholders
        $phs                = array ();
        foreach ($args as $i=>$arg) {
            // Validate
            if ($this->dbInputFiltering()) {
                if ($this->dbInputFilter($proc,($i+1),(string)$arg)===SWEF_BOOL_FALSE) {
                    $e      = $proc.'(['.($i+1).']='.$arg.') failed input validation';
                    if ($n=$this->dbCallNoticeLast()) {
                        $e .= ': '.$this->dbCallNoticeLast();
                    }
                    array_push ($this->errors,$e);
                    return SWEF_BOOL_FALSE;
                }
            }
            // Placeholder
            if (!strlen($proc) && $proc!==SWEF_STR__EMPTY) {
                array_push ($this->errors,$proc.'(): argument cannot be false or null!');
                \Swef\Bespoke\Swef::statusHeader (SWEF_HTTP_STATUS_CODE_555);
                return SWEF_BOOL_FALSE;
            }
            array_push ($phs,SWEF_STR__QMARK);
        }
        // Construct procedure call query
        if ($this->type==SWEF_SUPPORTED_DATABASE_POSTGRESQL) {
            $query          = 'SELECT '.$proc.' (';
        }
        else {
            $query          = 'CALL '.$proc.' (';
        }
        $query             .= implode (SWEF_STR__COMMA,$phs);
        $query             .= ')';
        array_push ($this->calls,$query.'  [ '.implode(SWEF_STR__COMMA,$args).' ]');
        try {
            $stmt           = $this->PDO->prepare ($query);
        }
        catch (\PDOException $e) {
            array_push ($this->errors,$proc.'() statement not prepared: '.$e->getMessage());
            \Swef\Bespoke\Swef::statusHeader (SWEF_HTTP_STATUS_CODE_555);
            return SWEF_BOOL_FALSE;
        }
        // Escape and bind arguments
        foreach ($args as $i=>$arg) {
            try {
                $stmt->bindValue (($i+1),$arg,\PDO::PARAM_STR);
            }
            catch (\PDOException $e) {
                array_push ($this->errors,$proc.'() could not bind parameter: '.$e->getMessage());
                \Swef\Bespoke\Swef::statusHeader (SWEF_HTTP_STATUS_CODE_555);
                return SWEF_BOOL_FALSE;
            }
        }
        try {
            $stmt->execute ();
        }
        catch (\PDOException $e) {
            array_push ($this->errors,$proc.'() could not execute query: '.$e->getMessage());
            \Swef\Bespoke\Swef::statusHeader (SWEF_HTTP_STATUS_CODE_555);
            return SWEF_BOOL_FALSE;
        }
        // Execution was successful
        try {
            $data           =  $stmt->fetchAll (\PDO::FETCH_ASSOC);
            $stmt->closeCursor ();
        }
        catch (\PDOException $e) {
            // Successful execution but no data was fetched
            return SWEF_BOOL_TRUE;
        }
        return $data;
    }

    public function dbCallNoticeLast ( ) {
        if (count($this->notices)==0) {
            return null;
        }
        return $this->notices[count($this->notices)-1];
    }

    public function dbCalls ( ) {
        return $this->calls;
    }

    public function dbClose ( ) {
        if ($this->type==SWEF_SUPPORTED_DATABASE_POSTGRESQL) {
            try {
                $this->PDO->query ('SELECT pg_terminate_backend(pg_backend_pid());');
            }
            catch (\PDOException $e) {
                array_push ($this->errors,'PostgreSQL error: '.$e->getMessage());
                $this->PDO      = null;
                \Swef\Bespoke\Swef::statusHeader (SWEF_HTTP_STATUS_CODE_555);
                return SWEF_BOOL_FALSE;
            }
        }
        $this->PDO              = null;
        return SWEF_BOOL_TRUE;
    }

    private function dbConnect ($dsn,$usr,$pwd) {
        if (is_object($this->PDO)) {
            return SWEF_BOOL_TRUE;
        }
        if ($dsn==null) {
            if (!is_readable(SWEF_FILE_CONFIG_DB)) {
                array_push ($this->errors,'Database config file '.SWEF_FILE_CONFIG_DB.'is not readable');
                return SWEF_BOOL_FALSE;
            }
            try {
                $var = @require_once SWEF_FILE_CONFIG_DB;
            }
            catch (ParseError $e) {
                array_push ($this->errors,'Config file '.SWEF_FILE_CONFIG_DB.' could not be parsed - syntax error');
                \Swef\Bespoke\Swef::statusHeader (SWEF_HTTP_STATUS_CODE_555);
                return SWEF_BOOL_FALSE;
            }
            if (!is_array($var)) {
                array_push ($this->errors,'Config file '.SWEF_FILE_CONFIG_DB.' did not return an array');
                \Swef\Bespoke\Swef::statusHeader (SWEF_HTTP_STATUS_CODE_555);
                return SWEF_BOOL_FALSE;
            }
            $dsn            = $var['SWEF_DB_PDO_DSN'];
            $usr            = $var['SWEF_DB_PDO_USR'];
            $pwd            = $var['SWEF_DB_PDO_PWD'];
        }
        $type               = explode (SWEF_STR__COLON,$dsn);
        $type               = array_shift ($type);
        if (!in_array($type,$this->types)) {
            array_push ($this->errors,'Database type "'.$type.'" is not supported');
            \Swef\Bespoke\Swef::statusHeader (SWEF_HTTP_STATUS_CODE_555);
            return SWEF_BOOL_FALSE;
        }
        try {
            $this->PDO      = new \PDO ($dsn,$usr,$pwd,array(\PDO::ATTR_ERRMODE=>SWEF_DB_PDO_E_MODE));
        }
        catch (\PDOException $e) {
            array_push ($this->errors,'Database not connected: '.$e->getMessage());
            \Swef\Bespoke\Swef::statusHeader (SWEF_HTTP_STATUS_CODE_555);
            return SWEF_BOOL_FALSE;
        }
        $this->DSN          = $dsn;
        $this->type         = $type;
        return SWEF_BOOL_TRUE;
    }

    public function dbErrorLast ( ) {
        if (count($this->errors)==0) {
            return null;
        }
        return $this->errors[count($this->errors)-1];
    }

    public function dbInputFilter ($proc,$argnum,$arg) {
        if (!$this->inputs) {
            array_push ($this->errors,'dbInputFilter(): input argument data not available');
            return SWEF_BOOL_FALSE;
        }
        if (!strlen($proc) || $argnum<SWEF_INT_1) {
            array_push ($this->errors,'dbInputFilter(): not called correctly');
            return SWEF_BOOL_FALSE;
        }
        foreach ($this->inputs as $i) {
            if ($i[SWEF_COL_PROCEDURE]!=$proc || $i[SWEF_COL_ARG]!=$argnum) {
                continue;
            }
            $log            = $i[SWEF_COL_PROCEDURE].'(['.$i[SWEF_COL_ARG].']='.$arg.') ';
            if (strlen($i[SWEF_COL_FILTER_VAR])) {
                if (!strlen(filter_var($arg,constant($i[SWEF_COL_FILTER_VAR])))) {
                    $log .= 'validation '.SWEF_COL_FILTER_VAR.' ('.$i[SWEF_COL_FILTER_VAR].')';
                    array_push ($this->notices,$log);
                    return SWEF_BOOL_FALSE;
                }
            }
            if (strlen($i[SWEF_COL_PREG_MATCH])) {
                if (!preg_match($i[SWEF_COL_PREG_MATCH],$arg)) {
                    $log .= 'validation '.SWEF_COL_PREG_MATCH.' ('.$i[SWEF_COL_PREG_MATCH].')';
                    array_push ($this->notices,$log);
                    return SWEF_BOOL_FALSE;
                }
            }
            if (strlen($i[SWEF_COL_VALUE_MIN])) {
                if ($arg<$i[SWEF_COL_VALUE_MIN]) {
                    $log .= 'validation '.SWEF_COL_VALUE_MIN.' < '.$i[SWEF_COL_VALUE_MIN];
                    array_push ($log,SWEF_COL_VALUE_MIN,$i[SWEF_COL_VALUE_MIN]);
                    array_push ($this->notices,$log);
                    return SWEF_BOOL_FALSE;
                }
            }
            if (strlen($i[SWEF_COL_VALUE_MAX])) {
                if ($arg>$i[SWEF_COL_VALUE_MAX]) {
                    $log .= 'validation '.SWEF_COL_VALUE_MAX.' > '.$i[SWEF_COL_VALUE_MAX];
                    array_push ($this->notices,$log);
                    return SWEF_BOOL_FALSE;
                }
            }
            if ($i[SWEF_COL_LENGTH_MIN]>0) {
                if (strlen($arg)<$i[SWEF_COL_LENGTH_MIN]) {
                    $log .= 'validation '.SWEF_COL_LENGTH_MIN.' < '.$i[SWEF_COL_LENGTH_MIN];
                    array_push ($this->notices,$log);
                    return SWEF_BOOL_FALSE;
                }
            }
            if ($i[SWEF_COL_LENGTH_MAX]>0) {
                if (strlen($arg)>$i[SWEF_COL_LENGTH_MAX]) {
                    $log .= 'validation '.SWEF_COL_LENGTH_MAX.' > '.$i[SWEF_COL_LENGTH_MAX];
                    array_push ($this->notices,$log);
                    return SWEF_BOOL_FALSE;
                }
            }
            return SWEF_BOOL_TRUE;
        }
        array_push ($this->notices,'('.$proc.','.$arg.'): argument not allowed');
        return SWEF_BOOL_FALSE;
    }

    public function dbInputFiltering ($set=null) {
        if (is_bool($set)) {
            $this->filter = $set;
        }
        return $this->filter;
    }

    public function dbName () {
        preg_match (SWEF_STR_PDO_DBNAME_PREG,$this->DSN,$m);
        return $m[2];
    }

}

?>
