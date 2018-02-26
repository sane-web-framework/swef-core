<?php

namespace Swef\Base;

class SwefSwef {

    // WHEN ADDING CODE below, use constants defined in config.php

    public  $apiError;                                  // Last API error
    public  $apiNotice;                                 // API stored procedure notice
    public  $apiResponse;                               // API response object
    public  $apiStatus;                                 // Last API Status code (HTTP convention)
    public  $apis;                                      // Look-up table - API stored procedures
    private $buffer;                                    // Output buffer
    private $baseDir;                                   // Path from docroot
    public  $cli;                                       // Is this constructed from CLI?
    public  $endpointsPulled        = 0;
    public  $context                = array ();         // Context for SWEF execution
    public  $contexts               = array ();         // Look-up table - contexts
    public  $db;                                        // Database object
    public  $diagnostic             = array ();
    private $err                    = SWEF_STR__EMPTY;  // Last error message
    public  $moment;                                    // Moment object
    public  $notification;                              // Notification object
    public  $page;                                      // The page endpoint
    private $phrases;                                   // Look-up array of translated phrases
    private $plugins                = array ();         // Look-up table - plugins
    private $plugin                 = array ();         // Array of plugin objects for SWEF event capture
    public  $routers;                                   // Look-up table - routers
    public  $temp;                                      // Public temporary variable
    public  $templates;                                 // Look-up table - templates
    private $untr;                                      // XML element containing unstranslated phrases
    public  $user                   = null;             // The current user
    public  $usergroups             = array ();         // Look-up table - usergroups

    public function __construct ( ) {
        if (php_sapi_name()=='cli') {
            $this->cli          = SWEF_BOOL_TRUE;
        }
        else {
            if (SWEF_SSL_ENFORCE && !$this->isHTTPS()) {
                $this->err      = 'SSL is enforced but not SSL host';
                return;
            }
            error_reporting (SWEF_PHP_ERROR_LEVEL);
        }
        $this->baseDir          = dirname ($_SERVER[SWEF_STR_PHP_SELF]);
    }

    public function __destruct ( ) {
    }

    public function _db ( ) {
        $this->db               = new \Swef\Bespoke\Database ();
        if ($this->err=$this->db->dbErrorLast()) {
            $this->diagnosticPush ('SWEF ERROR: '.$this->err);
            return SWEF_BOOL_FALSE;
        }
        return SWEF_BOOL_TRUE;
    }

    public function _run ( ) {
        $this->diagnosticPush ('Memory usage [1] = '.memory_get_usage());
        $this->session ();
        $this->contextsLoad ();
        $this->context          = $this->contextIdentify ();
        $this->phrasesLoad ();  // From language file
        $this->sessionCount ();
        $this->lookupsLoad ();
        $this->cookie ();
        $this->moment           = new \Swef\Bespoke\Moment ();
        $this->diagnosticPush ('Memory usage [2] = '.memory_get_usage());
        $this->user             = new \Swef\Bespoke\User ($this);
        $this->notificationInit ();
        $this->page             = new \Swef\Bespoke\Page ($this);
        foreach ($this->diagnostic as $d) {
            $this->diagnosticPush ($d);
        }
        $this->diagnosticPush ('Memory usage [3] = '.memory_get_usage());
        $this->diagnosticPush ('Input filtering = '.$this->db->dbInputFiltering());
        $this->diagnosticPush ('SWEF running, version: '.$this->version());
        $this->diagnosticPush ('swefMoment constructed at: '.$this->moment->server(SWEF_DIAGNOSTIC_DATETIME_FORMAT).' (server time)');
        $this->diagnosticPush ('email: '.$this->user->email);
        $this->diagnosticPush ('Starting plugins');
        $this->user->membershipsLoad ();
        $this->pluginsStart ();
        $this->diagnosticPush ('Memory usage [4] = '.memory_get_usage());
        $this->diagnosticPush ('Started '.count($this->plugin).' plugins');
        $this->page->pluginSet ($this->plugin);
        $this->diagnosticPush ('Memory usage [5] = '.memory_get_usage());
        $this->page->pluginsRun (SWEF__ON_PLUGINSSETAFTER);
        $this->page->identifyPage ($_SERVER[SWEF_STR_REQUEST_URI]);
        $this->diagnosticPush ('Memory usage [6] = '.memory_get_usage());
        $this->db->dbInputFiltering (SWEF_BOOL_TRUE);
        $this->page->push ();
        $this->db->dbClose ();
        $this->cumulativeLog ();
        $this->errorLog ();
        return strlen($this->err)==0;
    }

    public function alertAdministrators ($data) {
        $body = print_r ($data,SWEF_BOOL_TRUE);
    }

    public function apiResult ($args) {
        $this->diagnosticPush ('apiResult(): procedure = '.$args[0]);
        $this->apiStatus                            = SWEF_HTTP_STATUS_CODE_200;
        $this->apiError                             = SWEF_STR__EMPTY;
        $this->apiNotice                            = SWEF_STR__EMPTY;
        if (!is_array($this->apis)) {
            $this->diagnosticPush ('apiResult(): API procedures were not available');
            $this->apiStatus                        = SWEF_HTTP_STATUS_CODE_555;
            $this->apiError                         = SWEF_HTTP_STATUS_MSG_555.SWEF_STR__SPACE.$this->translate (
                'API procedures were not available'
            );
            return null;
        }
        $proc                                       = $args[0];
        foreach ($this->apis as $a) {
            $this->diagnosticPush ('apiResult(): comparing '.$a[SWEF_COL_PROCEDURE].' with '.$proc);
            if ($a[SWEF_COL_PROCEDURE]!=$proc) {
                continue;
            }
            $this->diagnosticPush ('apiResult(): matching '.$this->context[SWEF_COL_CONTEXT].' with '.$a[SWEF_COL_CONTEXT_PREG]);
            if (!preg_match($a[SWEF_COL_CONTEXT_PREG],$this->context[SWEF_COL_CONTEXT])) {
                continue;
            }
            $this->diagnosticPush ('apiResult(): checking arguments for '.$proc);
            if (count($args)!=($a[SWEF_COL_NUM_ARGS]+1)) {
                $this->apiStatus                    = SWEF_HTTP_STATUS_CODE_444;
                $this->apiError                     = SWEF_HTTP_STATUS_MSG_444.SWEF_STR__SPACE;
                $this->apiError                    .= $this->translate ('Incorrect argument count');
                $this->apiError                    .= SWEF_STR__SPACE.$a[SWEF_COL_NUM_ARGS];
                $this->apiError                    .= SWEF_STR__SPACE;
                $this->apiError                    .= $this->translate ('expected');
                $this->apiError                    .= count($args) - 1;
                $this->apiError                    .= SWEF_STR__SPACE;
                $this->apiError                    .= $this->translate ('received');
                return null;
            }
            foreach ($this->user->memberships as $m) {
                if (preg_match($a[SWEF_COL_USERGROUP_PREG],$m[SWEF_COL_USERGROUP])) {
                    $this->diagnosticPush ('apiResult(): user allowed with membership '.$m[SWEF_COL_USERGROUP]);
                    if (strpos($args[SWEF_INT_0],SWEF_API_CLASS_METHOD_SEP)) {
                        $this->diagnosticPush ('apiResult(): calling '.$args[0].'() with '.$args[0].'('.(count($args)-1).' args)');
                        $method                     = explode (SWEF_API_CLASS_METHOD_SEP,$args[0]);
                        $classname                  = $method[0];
                        $method                     = $method[1];
                        foreach ($this->plugin as $plugin) {
                            if ($plugin->extensionName!=$classname) {
                                continue;
                            }
                            if (!method_exists($plugin,$method)) {
                                $this->apiStatus    = SWEF_HTTP_STATUS_CODE_404;
                                $this->apiError     = SWEF_HTTP_STATUS_CODE_404.SWEF_STR__SPACE.$this->translate ('Process not identified (missing method)');
                                return SWEF_BOOL_FALSE;
                            }
                            array_shift ($args);
                            $data                   = $plugin->$method ($args);
                            if (!is_array($data)) {
                                $this->apiStatus    = $data;
                                $this->apiError     = $this->apiStatus.SWEF_STR__SPACE;
                                $this->apiError    .= $this->translate ('Data was not  returned by');
                                $this->apiError    .= SWEF_STR__SPACE.$classname;
                                $this->apiError    .= SWEF_API_CLASS_METHOD_SEP.$method.'()';
                                $this->apiNotice    = null;
                                return SWEF_BOOL_FALSE;
                            }
                            return $data;
                        }
                        $this->apiStatus            = SWEF_HTTP_STATUS_CODE_404;
                        $this->apiError             = SWEF_HTTP_STATUS_CODE_404.SWEF_STR__SPACE.$this->translate ('Process not identified (missing class)');
                        $this->apiNotice            = $this->translate ($this->db->dbCallNoticeLast());
                        return SWEF_BOOL_FALSE;
                    }
                    else {
                        $this->diagnosticPush ('apiResult(): calling database with '.$args[0].'('.(count($args)-1).' args)');
                        $data                       = call_user_func_array (array($this->db,'dbCall'),$args);
                        if ($data===SWEF_BOOL_FALSE) {
                            $this->apiStatus        = SWEF_HTTP_STATUS_CODE_444;
                            $this->apiError         = SWEF_HTTP_STATUS_CODE_444.SWEF_STR__SPACE.$this->translate ('Process failed');
                            $this->apiNotice        = $this->translate ($this->db->dbCallNoticeLast());
                            return SWEF_BOOL_FALSE;
                        }
                        return $data;
                    }
                }
            }
            $this->apiStatus                        = SWEF_HTTP_STATUS_CODE_403;
            $this->apiError                         = SWEF_HTTP_STATUS_CODE_403.SWEF_STR__SPACE.$this->translate ('Data was not permitted');
            return null;
        }
        $this->apiStatus                            = SWEF_HTTP_STATUS_CODE_404;
        $this->apiError                             = SWEF_HTTP_STATUS_CODE_404.SWEF_STR__SPACE.$this->translate ('This data was not identified');
        return null;
    }

    public function contextGet ( ) {
        return $this->context;
    }

    private function contextIdentify ( ) {
        if ($this->cli) {
            return array (SWEF_COL_CONTEXT=>'cli');
        }
        if (!is_array($this->contexts)) {
            return SWEF_BOOL_FALSE;
        }
        foreach ($this->contexts as $c) {
            if (preg_match($c[SWEF_COL_SERVER_VALUE_PREG],$_SERVER[$c[SWEF_COL_SERVER_KEY]])) {
                return $c;
            }
        }
        return SWEF_BOOL_FALSE;
    }

    public function contextsGet ( ) {
        return $this->contexts;
    }

    private function contextsLoad ($force=null) {
        if (SWEF_CONFIG_STORE_LOOKUP_FILES && !$force) {
            $file           = SWEF_STR_CONTEXTS.SWEF_STR_EXT_VAR;
            $this->contexts = $this->lookupFileGet ($file);
            if (is_array($this->contexts) && count($this->contexts)) {
                $this->diagnosticPush ('contextsLoad(): Got contexts from file inclusion');
                return SWEF_BOOL_TRUE;
            }
        }
        $this->diagnosticPush ('contextsLoad(): Getting API details from database');
        $this->contexts         = $this->db->dbCall (SWEF_CALL_CONTEXTSLOAD);
        if (!is_array($this->contexts) || !count($this->contexts)) {
            $this->diagnosticPush ('contextsLoad(): could not load contexts: '.$this->db->dbErrorLast());
            return SWEF_BOOL_FALSE;
        }
        if (SWEF_CONFIG_STORE_LOOKUP_FILES) {
            $this->lookupFileSet ($file,$this->contexts);
        }
        if ($this->cli) {
            return SWEF_BOOL_TRUE;
        }
    }

    public function cookie ( ) {
        if ($this->cli) {
            return SWEF_BOOL_FALSE;
        }
        if (!$this->sessionCheck()) {
            $this->diagnosticPush ('cookie(): session must be started before cookies can be saved to it');
            header (SWEF_HTTP_STATUS_MSG_555);
            return SWEF_BOOL_FALSE;
        }
        // Timezone offset
        if (array_key_exists(SWEF_COOKIE_TZOM,$_COOKIE)) {
            return $this->sessionSet (SWEF_COOKIE_TZOM,$_COOKIE[SWEF_COOKIE_TZOM]);
        }
    }

    public function cumulativeLog ( ) {
        if (!defined('SWEF_DIAGNOSTIC_START')) {
            return;
        }
        $string         = $this->moment->server (SWEF_DIAGNOSTIC_DATETIME_FORMAT);
        $string        .= SWEF_STR__SPACE;
        $string        .= intval(1000*microtime(SWEF_BOOL_TRUE)) - SWEF_DIAGNOSTIC_START;
        $string        .= SWEF_STR__SPACE;
        $string        .= 'ms';
        $string        .= SWEF_STR__SPACE;
        $string        .= memory_get_peak_usage ();
        $string        .= SWEF_STR__SPACE;
        $string        .= 'B';
        $string        .= SWEF_STR__SPACE;
        $string        .= $_SERVER['SERVER_PORT'];
        $string        .= SWEF_STR__SPACE;
        $string        .= $_SERVER['HTTP_HOST'].$this->baseDir;
        $string        .= SWEF_STR__SPACE;
        $string        .= $this->page->swef->context[SWEF_COL_CONTEXT];
        $string        .= SWEF_STR__SPACE;
        $string        .= $this->page->endpoint;
        if ($this->err) {
            $string    .= SWEF_STR__SPACE;
            $string    .= $this->err;
        }
        $string        .= SWEF_STR__CRLF;
        $fp = @fopen (SWEF_DIAGNOSTIC_FILE_CUMULATIVE,SWEF_F_APPEND);
        if (!$fp) {
            return;
        }
        @fwrite ($fp,$string);
        @fclose ($fp);
        $path           = realpath (SWEF_DIAGNOSTIC_FILE_CUMULATIVE);
        $cmd            = 'truncate -s "'.intval(SWEF_DIAGNOSTIC_FILE_CUM_SIZE).' '.escapeshellarg($path);
        exec ($cmd);
    }

    public function dataSort ($rows,$field,$asc=SWEF_BOOL_TRUE,$nat=SWEF_BOOL_FALSE) {
        if (!is_array($rows)) {
            return SWEF_BOOL_FALSE;
        }
        if (!is_array($rows[SWEF_INT_0])) {
            return SWEF_BOOL_FALSE;
        }
        if (count($rows)<SWEF_INT_2) {
            return $rows;
        }
        // Transform rows with column arrays into columns with row arrays
        $columns = array ();
        foreach ($rows as $i=>$row) {
            foreach ($row as $column=>$value) {
                if (!array_key_exists($column,$columns)) {
                    // New field
                    $columns[$column] = array ();
                }
                $columns[$column][$i] = $value;
            }
        }
        if (!array_key_exists($field,$columns)) {
            // Ordering field not found in columns
            return $rows;
        }
        // Fill missing values
        $count = count ($rows);
        $out   = array ();
        for ($i=0;$i++;$i<count) {
            array_push ($out,array());
            foreach ($columns as $column=>$array) {
                if (!array_key_exists($i,$array)) {
                    $columns[$column][$i] = null;
                }
            }
        }
        // Sort the target column
        if ($asc) {
            if ($nat) {
                asort ($columns[$field],SORT_NATURAL);
            }
            else {
                asort ($columns[$field]);
            }
        }
        else {
            if ($nat) {
                arsort ($columns[$field],SORT_NATURAL);
            }
            else {
                arsort ($columns[$field]);
            }
        }
        // Reconstruct in rows
        foreach ($columns as $column=>$array) {
            foreach ($columns[$field] as $i=>$v) {
                $out[$i][$column] = $array[$i];
            }
        }
        return $rows;
    }

    public function dateTime ( ) {
        return $this->moment;
    }

    public function diagnosticPush ($string) {
        if (!SWEF_DIAGNOSTIC) {
            return;
        }
        if (is_object($this->page)) {
            $this->page->diagnosticAdd ($string);
            return;
        }
        array_push ($this->diagnostic,$string);
    }

    public function diagnosticString ($caller,$args) {
        $str                 = SWEF_STR__EMPTY;
        if (array_key_exists(SWEF_STR_CLASS,$caller)) {
            $str            .= $caller[SWEF_STR_CLASS];
            $str            .= SWEF_STR__DCOLON;
            $str            .= $caller[SWEF_COL_FUNCTION];
            $str            .= SWEF_STR__DBRACKET;
            $str            .= SWEF_STR__COLON;
            $str            .= SWEF_STR__EMPTY;
        }
        $i                   = count ($args);
        while ($i) {
            $str            .= array_shift ($args);
            $i--;
            if ($i) {
               $v            = array_shift ($args);
               if (is_array($v)) {
                   $v        = print_r ($v,SWEF_BOOL_TRUE);
               }
               $str         .= '='.$v.', ';
               $i--;
            }
        }
        return $str;
    }

    public function diagnosticWrite ($o,$d) {
        if (is_writable(SWEF_DIAGNOSTIC_FILE_UNTRANSLATED)) {
            $fp                 = fopen (SWEF_DIAGNOSTIC_FILE_UNTRANSLATED,SWEF_F_WRITE);
            if ($fp) {
                $xml  = $this->untr->asXML ();
                $xml  = str_replace (SWEF_STR__GT.SWEF_STR__LT,SWEF_STR__GT.SWEF_STR__LF.SWEF_STR__LT,$xml); // HACK
                fwrite ($fp,$xml);
                fclose ($fp);
            }
            else {
                $d .= 'fopen('.SWEF_DIAGNOSTIC_FILE_UNTRANSLATED.','.SWEF_F_WRITE.') DID NOT WORK'.SWEF_STR__CRLF;
            }
        }
        else {
            $d .= 'CANNOT WRITE TO '.SWEF_DIAGNOSTIC_FILE_UNTRANSLATED.SWEF_STR__CRLF;
        }
        if (!is_writable(SWEF_DIAGNOSTIC_FILE)) {
            echo 'DIAGNOSTIC FILE "'.SWEF_DIAGNOSTIC_FILE.'" IS NOT WRITABLE<br/>'.SWEF_STR__CRLF;
            return;
        }
        $fp                 = fopen (SWEF_DIAGNOSTIC_FILE,$o);
        if (!$fp) {
            echo 'fopen('.SWEF_DIAGNOSTIC_FILE.','.$o.') DID NOT WORK<br/>'.SWEF_STR__CRLF;
            return;
        }
        fwrite ($fp,$d);
        fclose ($fp);
    }

    public function error ( ) {
        return $this->err;
    }

    public function errorLog ( ) {
        if (!$this->err) {
            return;
        }
        $fp = @fopen (SWEF_DIAGNOSTIC_FILE_ERROR,SWEF_F_WRITE);
        if (!$fp) {
            return;
        }
        @fwrite ($fp,$this->moment->server(SWEF_DIAGNOSTIC_DATETIME_FORMAT).SWEF_STR__SPACE.$this->err);
        @fclose ($fp);
    }

    public function getDefinedConstants ( ) {
        $c = get_defined_constants (SWEF_BOOL_TRUE);
        $c = $c[SWEF_STR_USER];
        ksort ($c);
        return $c;
    }

    public function isHTTPS ( ) {
        if (!array_key_exists('HTTPS',$_SERVER)) {
            return SWEF_BOOL_FALSE;
        }
        if (empty($_SERVER['HTTPS'])) {
            return SWEF_BOOL_FALSE;
        }
        if ($_SERVER['HTTPS']=='off') {
            return SWEF_BOOL_FALSE;
        }
        return SWEF_BOOL_TRUE;
    }

    public function lookupFileGet ($file) {
        if (!is_readable(SWEF_DIR_LOOKUP.'/'.$file)) {
            return SWEF_BOOL_FALSE;
        }
        $file       = SWEF_DIR_LOOKUP.'/'.$file;
        $var        = SWEF_BOOL_FALSE;
        $var        = @require_once $file;
        return $var;
    }

    public function lookupFileSet ($file,$var) {
        $file       = SWEF_DIR_LOOKUP.'/'.$file;
        $str        = var_export ($var,SWEF_BOOL_TRUE);
        $str        = var_export ($var,SWEF_BOOL_TRUE);
        $fp         = @fopen ($file,SWEF_F_WRITE);
        if ($fp) {
            $write  = @fwrite ($fp,'<?php'.SWEF_STR__CRLF.'return '.$str.';'.SWEF_STR__CRLF.' ?>');
            @fclose ($fp);
        }
        if ($write) {
            return SWEF_BOOL_TRUE;
        }
        return SWEF_BOOL_FALSE;
    }

    public function lookupLoad ($handle,$proc,$arg=null,$force=null) {
        if (!strlen($handle)) {
            $this->diagnosticPush ('lookupLoad(): requires a handle');
            return SWEF_BOOL_FALSE;
        }
        $this->diagnosticPush ('lookupLoad('.$handle.')');
        if (SWEF_CONFIG_STORE_LOOKUP_FILES && !$force) {
            if ($arg) {
                $file       = $arg.SWEF_STR__DASH.$handle.SWEF_STR_EXT_VAR;
            }
            else {
                $file       = $handle.SWEF_STR_EXT_VAR;
            }
            $array          = $this->lookupFileGet ($file);
            if (is_array($array) && count($array)) {
                $this->diagnosticPush ('lookupLoad(): Got '.$handle.' from file inclusion');
                return $array;
            }
        }
        $this->diagnosticPush ('lookupLoad(): Getting '.$handle.' from database');
        if ($arg) {
            $array          = $this->db->dbCall ($proc,$arg);
        }
        else {
            $array          = $this->db->dbCall ($proc);
        }
        if (!is_array($array)) {
            $this->diagnosticPush ('lookupLoad(): could not load '.$handle.': '.$this->db->dbErrorLast());
            return SWEF_BOOL_FALSE;
        }
        if (SWEF_CONFIG_STORE_LOOKUP_FILES && count($array)) {
            $this->lookupFileSet ($file,$array);
        }
        return $array;
    }

    private function lookupsLoad ( ) {
        // From database (or session or include file where configured)
        $c                  = $this->context[SWEF_COL_CONTEXT];
        $this->usergroups   = $this->lookupLoad ( SWEF_COL_USERGROUPS, SWEF_CALL_USERGROUPSLOAD    );
        if (!is_array($this->usergroups) || !count($this->usergroups)) {
            $this->err      = 'Could not load usergroup data (or there was no data)';
        }
        $this->plugins      = $this->lookupLoad ( SWEF_COL_PLUGINS,    SWEF_CALL_PLUGINSLOAD,   $c );
        if (!is_array($this->plugins)) {
            $this->err      = 'Could not load plugin data';
        }
        $this->routers      = $this->lookupLoad ( SWEF_COL_ROUTERS,    SWEF_CALL_ROUTERSLOAD,   $c );
        if (!is_array($this->routers) || !count($this->routers)) {
            $this->err      = 'Could not load router data (or there was no data)';
        }
        $this->templates    = $this->lookupLoad ( SWEF_COL_TEMPLATES,  SWEF_CALL_TEMPLATESLOAD, $c );
        if (!is_array($this->templates) || !count($this->templates)) {
            $this->err      = 'Could not load template data (or there was no data)';
        }
        $this->apis         = $this->lookupLoad ( SWEF_COL_APIS,       SWEF_CALL_APISLOAD          );
        if (!is_array($this->apis) || !count($this->apis)) {
            $this->err      = 'Could not load API data (or there was no data)';
        }
        $this->db->inputs   = $this->lookupLoad ( SWEF_COL_INPUTS,     SWEF_CALL_INPUTSLOAD        );
        if (!is_array($this->db->inputs) || !count($this->db->inputs)) {
            $this->err      = 'Could not load input data (or there was no data)';
        }
    }

    public function notify ($msg) {
        $this->notification->notify ($this->translate($msg));
    }

    private function notificationInit ( ) {
        $this->notification     = new \Swef\Bespoke\Notification ($this->cli);
    }

    public function notificationPurge ( ) {
        $this->notification->purge ();
    }

    public function notifications ( ) {
        // Return notifications
        return $this->notification->notes ();
    }

    public function paths ($path,$paths=array()) {
        if (!is_readable($path)) {
            return $paths;
        }
        array_push ($paths,$path);
        if (!is_dir($path)) {
            return $paths;
        }
        $children = scandir ($path);
        foreach ($children as $child) {
            if ($child=='.' || $child=='..') {
                continue;
            }
            $paths = find ($path.'/'.$child,$paths);
        }
        return $paths;
    }


    public function phrasesLoad ( ) {
        if (SWEF_DIAGNOSTIC) {
            if (!is_readable(SWEF_DIAGNOSTIC_FILE_UNTRANSLATED)) {
                touch (SWEF_DIAGNOSTIC_FILE_UNTRANSLATED);
                if (!is_readable(SWEF_DIAGNOSTIC_FILE_UNTRANSLATED)) {
                    $this->diagnosticPush ('phrasesLoad(): could not touch '.SWEF_DIAGNOSTIC_FILE_UNTRANSLATED);
                    return;
                }
            }
            $xml = trim (file_get_contents(SWEF_DIAGNOSTIC_FILE_UNTRANSLATED));
            if (strpos($xml,SWEF_XML_TAG_VERSION)!==SWEF_INT_0) {
                $xml    = SWEF_XML_TAG_VERSION.SWEF_STR__CRLF;
                $xml   .= SWEF_XML_TAG_ROOT_OPEN.SWEF_STR__CRLF;
                $xml   .= SWEF_XML_TAG_ROOT_CLOSE.SWEF_STR__CRLF;
            }
            ob_start ();
            $this->untr = new \SimpleXMLElement ($xml);
            ob_end_clean ();
            if ($e=libxml_get_last_error()) {
                $this->diagnosticPush ('phrasesLoad(): XML parse error - libxml error code = '.$e);
                return;
            }
        }
        $lang           = $this->context[SWEF_COL_LANGUAGE];
        if (array_key_exists(SWEF_COOKIE_LANG,$_COOKIE)) {
            if ($_COOKIE[SWEF_COOKIE_LANG]) {
                $lang   = $_COOKIE[SWEF_COOKIE_LANG];
            }
        }
        $file           = SWEF_DIR_PHRASES.SWEF_STR__FSLASH.SWEF_STR_PHRASES.SWEF_STR__DOT;
        while (!is_readable($file.$lang)) {
            $parts      = explode (SWEF_STR__DASH,$lang);
            array_pop ($parts);
            if (!count($parts)) {
                return;
            }
            $lang       = implode (SWEF_STR__DASH,$parts);
        }
        $this->phrases  = require_once $file.$lang;
    }

    public function pluginsList ( ) {
        $plugins    = array ();
        $ps         = $this->db->dbCall (SWEF_CALL_PLUGINSLIST,$this->context[SWEF_COL_CONTEXT]);
        if (!is_array($ps)) {
            $this->diagnosticPush ( 'pluginsList(): could not list plugins: '.$this->db->dbErrorLast());
            return SWEF_BOOL_FALSE;
        }
        $cns                                                                = array ();
        foreach ($ps as $p) {
            if (!class_exists($p[SWEF_COL_CLASSNAME])) {
                continue;
            }
            if (!array_key_exists($p[SWEF_COL_CLASSNAME],$plugins)) {
                $plugins[$p[SWEF_COL_CLASSNAME]]                            = array ();
            }
            $plugins[$p[SWEF_COL_CLASSNAME]][$p[SWEF_COL_CONTEXT_LIKE]]     = $p;
            $plugins[$p[SWEF_COL_CLASSNAME]][$p[SWEF_COL_CONTEXT_LIKE]][SWEF_STR_DASHBOARD] = null;
            if (!$p[SWEF_COL_CURRENT_CONTEXT]) {
                continue;
            }
            if (!$p[SWEF_COL_DASH_ALLOW]) {
                continue;
            }
            foreach ($this->user->memberships as $m) {
                if (preg_match($p[SWEF_COL_DASH_USERGROUP_PREG],$m[SWEF_COL_USERGROUP])) {
                    $plugins[$p[SWEF_COL_CLASSNAME]][$p[SWEF_COL_CONTEXT_LIKE]][SWEF_STR_DASHBOARD] = SWEF_BOOL_TRUE;
                    break;
                }
            }
        }
        return $plugins;
    }

    public function pluginsStart ( ) {
        if (!is_object($this->page)) {
            $this->diagnosticPush ('pluginsStart(): page must be constructed before starting plugins');
            header (SWEF_HTTP_STATUS_MSG_555);
            return SWEF_BOOL_FALSE;
        }
        $this->diagnosticPush ('Checking '.count($this->plugins).' plugins');
        foreach ($this->plugins as $p) {
            $c = $p[SWEF_COL_CLASSNAME];
            if (!$p[SWEF_COL_ENABLED]) {
                $this->diagnosticPush ('Plugin '.$p[SWEF_COL_CLASSNAME].' NOT ENABLED');
                continue;
            }
            if (!class_exists($c)) {
                $this->diagnosticPush ('Plugin class '.$c.' NOT INCLUDED');
                continue;
            }
            $this->diagnosticPush ('Constructing an object of class "'.$c.'"');
            $o = new $c ($this->page);
            $o->configure ($p[SWEF_COL_CONFIGS]);
            array_push ($this->plugin,$o);
            $this->diagnosticPush ('Plugin object added');
        }
    }

    public function print_r_pre ($var) {
        ?><pre>var = <?php print_r ($var); ?></pre><?php
    }

    public function pullStop () {
        $this->endpointsPulled++;
        if ($this->endpointsPulled<=SWEF_ENDPOINTS_MAX) {
            return SWEF_BOOL_FALSE;
        }
        $this->diagnosticPush ('pullStop(): too many levels of endpoint pulling - refusing to add more');
        header (SWEF_HTTP_STATUS_MSG_555);
        return SWEF_BOOL_TRUE;
    }

    private function session ( ) {
        if ($this->cli) {
            return SWEF_BOOL_FALSE;
        }
        session_start ();
        $_SESSION[SWEF_SESSION_KEY_TRIGGER] = SWEF_BOOL_TRUE;
        if (!array_key_exists(SWEF_SESSION_KEY_CONTEXTS,$_SESSION)) {
            $_SESSION[SWEF_SESSION_KEY_CONTEXTS] = array ();
        }
        if (!array_key_exists(SWEF_SESSION_KEY,$_SESSION)) {
            $_SESSION[SWEF_SESSION_KEY] = array ();
        }
    }

    private function sessionCheck ( ) {
        if ($this->cli) {
            return SWEF_BOOL_FALSE;
        }
        if (!$this->context) {
            return SWEF_BOOL_FALSE;
        }
        if (!array_key_exists(SWEF_SESSION_KEY,$_SESSION)) {
            return SWEF_BOOL_FALSE;
        }
        if (!array_key_exists($this->context[SWEF_COL_CONTEXT],$_SESSION[SWEF_SESSION_KEY])) {
            $_SESSION[SWEF_SESSION_KEY][$this->context[SWEF_COL_CONTEXT]] = array ();
        }
        return SWEF_BOOL_TRUE;
    }

    private function sessionCount ( ) {
        if (!$this->sessionCheck()) {
            return SWEF_BOOL_FALSE;
        }
        if (!array_key_exists(SWEF_STR_REQUESTS,$_SESSION[SWEF_SESSION_KEY][$this->context[SWEF_COL_CONTEXT]])) {
            $_SESSION[SWEF_SESSION_KEY][$this->context[SWEF_COL_CONTEXT]][SWEF_STR_START]     = time();
            $_SESSION[SWEF_SESSION_KEY][$this->context[SWEF_COL_CONTEXT]][SWEF_STR_REQUESTS]  = 0;
        }
        $_SESSION[SWEF_SESSION_KEY][$this->context[SWEF_COL_CONTEXT]][SWEF_STR_REQUESTS]++;
    }

    public function sessionGet ($k) {
        if (!$this->sessionCheck()) {
            return SWEF_BOOL_FALSE;
        }
        if (!array_key_exists($k,$_SESSION[SWEF_SESSION_KEY][$this->context[SWEF_COL_CONTEXT]])) {
            return SWEF_BOOL_FALSE;
        }
        return $_SESSION[SWEF_SESSION_KEY][$this->context[SWEF_COL_CONTEXT]][$k];
    }

    public function sessionSetIfNotSet ($k,$v) {
        if (!$this->sessionCheck()) {
            return SWEF_BOOL_FALSE;
        }
        if (array_key_exists($k,$_SESSION[SWEF_SESSION_KEY][$this->context[SWEF_COL_CONTEXT]])) {
            return SWEF_BOOL_TRUE;
        }
        return $this->sessionSet ($k,$v);
    }

    public function sessionSet ($k,$v) {
        if (!$this->sessionCheck()) {
            return SWEF_BOOL_FALSE;
        }
        $_SESSION[SWEF_SESSION_KEY][$this->context[SWEF_COL_CONTEXT]][$k] = $v;
        return SWEF_BOOL_TRUE;
    }

    public function setCookie ($key,$value,$expires) {
        setcookie ($key,$value,$expires,SWEF_COOKIE_PATH,SWEF_COOKIE_DOMAIN,$this->isHTTPS(),$this->isHTTPS());
    }

    public function siteTitleGet ( ) {
        return $this->siteTitle;
    }

    public function translate ($phrase,$lang=SWEF_LANG_SOURCE) {
        $this->diagnosticPush ('phrase in = '.$phrase);
        if (!array_key_exists($phrase,$this->phrases)) {
            $this->diagnosticPush ('untranslated');
            if (SWEF_DIAGNOSTIC) {
                $this->untranslated ($lang,$phrase);
            }
            return $phrase;
        }
        if (SWEF_DIAGNOSTIC) {
            $this->translated ($lang,$phrase);
        }
        $this->diagnosticPush ('phrase out = '.$this->phrases[$phrase]);
        return $this->phrases[$phrase];
    }

    public function translateBlock ($buffer) {
        $this->diagnosticPush ('translating block');
        $output             = SWEF_STR__EMPTY;
        $buffer             = explode (SWEF_LANG_TAG_CLOSE,$buffer);
        $tail               = array_pop ($buffer);
        foreach ($buffer as $chunk) {
            $this->diagnosticPush ('Another chunk');
            if (strpos($chunk,SWEF_LANG_TAG_OPEN)===SWEF_BOOL_FALSE) {
                $this->diagnosticPush ('no open tag');
                $output    .= $chunk;
                continue;
            }
            $chunk          = explode (SWEF_LANG_TAG_OPEN,$chunk);
            $output        .= $chunk[0];
            $chunk          = explode ('>',$chunk[1],SWEF_INT_2);
            $lang           = $chunk[0];
            $phrase         = $chunk[1];
            $output        .= $this->translate ($phrase,$lang);
        }
        return $output.$tail;
    }

    public function translated ($lang,$phrase) {
        $this->diagnosticPush ('translated(): removing "'.$phrase.'" from untranslated');
ob_start ();
        foreach ($this->untr->children() as $child) {
            $from = null;
            $to   = null;
            foreach ($child->attributes() as $k=>$v) {
                if ($k==SWEF_XML_ATTR_FROM) {
                    $from = $v;
                }
                elseif ($k==SWEF_XML_ATTR_TO) {
                    $to   = $v;
                }
            }
            if ($from==$lang && $to==$this->context[SWEF_COL_LANGUAGE]) {
                if ((string) $child==$phrase) {
                    unset ($child);
                    break;
                }
            }
        }
ob_end_clean ();
    }

    public function untranslated ($lang,$phrase) {
        if (!SWEF_DIAGNOSTIC) {
            return;
        }
        $got                = null;
        $this->diagnosticPush ('translated(): adding "'.$phrase.'" to untranslated');
ob_start ();
        foreach ($this->untr->children() as $child) {
            $from           = null;
            $to             = null;
            foreach ($child->attributes() as $k=>$v) {
                if ($k==SWEF_XML_ATTR_FROM) {
                    $from   = $v;
                }
                elseif ($k==SWEF_XML_ATTR_TO) {
                    $to     = $v;
                }
            }
            if ($from==$lang && $to==$this->context[SWEF_COL_LANGUAGE]) {
                if ((string) $child==$phrase) {
                    $got    = SWEF_BOOL_TRUE;
                    break;
                }
            }
        }
        if (!$got) {
            $child          = $this->untr->addChild (SWEF_XML_TAG_PHRASE,$phrase);
            $child->addAttribute (SWEF_XML_ATTR_FROM,$lang);
            $child->addAttribute (SWEF_XML_ATTR_TO,$this->context[SWEF_COL_LANGUAGE]);
        }
ob_end_clean ();
        if ($e=libxml_get_last_error()) {
            $this->diagnosticPush ('untranslated(): XML parse error - libxml error code = '.$e);
            return;
        }
    }

    public function userLoggedIn ( ) {
        return $this->user->isLoggedIn ();
    }

    public function userLogin ($email,$password) {
        $this->diagnosticPush ('userLogin(): email = '.$email);
        if ($this->userLoggedIn()) {
            $this->diagnosticPush ('userLogin(): already logged in as '.$this->user->email);
        }
        elseif ($this->user->login($email,$password,$this->context[SWEF_COL_MUST_BE_VERIFIED])) {
            if (!$this->user->verified) {
                $this->notify ('Account verification not complete');
            }
            if (!$this->user->email) {
                $this->diagnosticPush ('userLogin(): Login was OK but not verified');
                return SWEF_BOOL_FALSE;
            }
            $this->diagnosticPush ('userLogin(): login details were correct');
            if ($this->page->identifyRouter($this->page->endpoint)) {
                $this->diagnosticPush ('userLogin(): PASS - router identified for '.$email);
            }
            else {
                $this->diagnosticPush ('userLogin(): FAIL - router for this user in this context');
                $this->userLogout ();
            }
        }
        else {
            $this->diagnosticPush ('userLogin(): FAIL - login details were incorrect');
        }
        $this->diagnosticPush ('userLogin(): email = '.$this->user->email);
        return $this->user->email;
    }

    public function userLogout ( ) {
        $this->setCookie (session_name(),SWEF_STR__EMPTY,SWEF_INT_0);
        session_destroy ();
        $_SESSION = array ();
        $this->user->logout ();
        $this->notify ('You have been logged out');
    }

    public function version () {
        return trim (file_get_contents(dirname(__FILE__).'/'.SWEF_FILE_VERSION));
    }

}

?>
