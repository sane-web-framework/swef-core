<?php

namespace Swef\Base;

class SwefPage extends \Swef\Bespoke\Endpoint {

    // The root SWEF endpoint - typically a markup/plain text web page with back-end processes
    // Any page may instead send different content headers and serve different content types
    // as does the plugin swefContent
    private  $baseDir;
    public   $buffer;
    public   $httpE;
    public   $httpHeader;
    public   $requestURI;
    public   $scriptBuffer;
    public   $pageTitle                 = 'Untitled';

    public function __construct ($swef) {
        parent::__construct ($swef);
        $this->baseDir                  = dirname ($_SERVER[SWEF_STR_PHP_SELF]);
        if ($this->baseDir==DIRECTORY_SEPARATOR) {
            $this->baseDir              = SWEF_STR__EMPTY;
        }
        $this->requestURI               = substr ($_SERVER[SWEF_STR_REQUEST_URI],strlen($this->baseDir));
    }

    public function __destruct () {
    }

    public function _COOKIE ($k) {
        if (!array_key_exists($k,$_COOKIE)) {
            return SWEF_STR__EMPTY;
        }
        return $_COOKIE[$k];
    }

    public function _GET ($k) {
        if (!array_key_exists($k,$_GET)) {
            return SWEF_STR__EMPTY;
        }
        return $_GET[$k];
    }

    public function _POST ($k) {
        if (!array_key_exists($k,$_POST)) {
            return SWEF_STR__EMPTY;
        }
        return $_POST[$k];
    }

    public function _SESSION ($k) {
        return $this->swef->sessionGet ($k);
    }

    public function diagnosticOutput ( ) {
        $a_or_w             = SWEF_F_WRITE;
        if (($d=$this->_SESSION(SWEF_STR_DIAGNOSTIC))) {
            $a_or_w         = SWEF_F_APPEND;
            $this->swef->sessionSet (SWEF_STR_DIAGNOSTIC,SWEF_BOOL_FALSE);
        }
// -----
    ob_start ();
// <<---
        include SWEF_DIR_TEMPLATE.'/'.SWEF_DIAGNOSTIC_TEMPLATE;
// -----
    $diagnostic  = ob_get_contents ();
    ob_end_clean ();
// <<---
        $this->diagnosticAdd ('this->swef->diagnosticWrite('.$a_or_w.',diagnostic)');
        $this->swef->diagnosticWrite ($a_or_w,$diagnostic);
    }

    private function headers ( ) {
        if (!$this->pluginsRun(SWEF__ON_HEADERSBEFORE)) {
            // At least one plugin has cancelled this method
            return SWEF_BOOL_TRUE;
        }
        if ($this->template[SWEF_COL_CONTENTTYPE]) {
            header (SWEF_STR_CONTENTTYPE.$this->template[SWEF_COL_CONTENTTYPE]);
        }
        else {
            header (SWEF_STR_CONTENTTYPE.SWEF_HTTP_HEADER_CONTENTTYPE);
        }
        if (SWEF_SEND_HEADER_X_POWERED_BY) {
            header (SWEF_STR_X_POWERED_BY.SWEF_HEADER_X_POWERED_BY);
        }
    }

    public function identifyPage ( ) {
        // Purposed for root endpoint, called "page" but does not have to be HTML (or even mark-up)
        if ($this->swef->error()) {
            return SWEF_BOOL_FALSE;
        }
        if (!$this->pluginsRun(SWEF__ON_PAGEIDENTIFYBEFORE)) {
            return;
        }
        // Convert shortcut request_uri to endpoint request_uri
        $sc_valid                       = null;
        $sc_tried                       = null;
        if (preg_match(SWEF_URI_SLUG_PREG_MATCH,substr($this->requestURI,1))) {
            $sc_valid                   = SWEF_BOOL_TRUE;
            $this->diagnosticAdd ('request_uri is a valid shortcut: '.$this->requestURI);
        }
        if ($sc_valid && $r=$this->identifyShortcut($this->requestURI)) {
            $sc_got                     = $r;
            $this->diagnosticAdd ('Got shortcut: '.$sc_got);
            $this->requestURI           = $r;
            $this->diagnosticAdd ('request_uri changed');
        }
        // Discard query string and tidy
        $sc                             = explode (SWEF_STR__QMARK,$this->requestURI);
        $sc                             = array_shift ($sc);
        $this->diagnosticAdd ('shortcut: '.$sc);
        while (strstr($sc,'//')!==SWEF_BOOL_FALSE) {
            $sc                         = str_replace ('//','/',$sc);
        }
        $this->diagnosticAdd ('shortcut tidied: '.$sc);
        // Convert shortcut to endpoint (if not already converted)
        $endpoint                       = SWEF_STR__EMPTY;
        $this->diagnosticAdd ($sc.'!=='.$sc_tried.' && '.$sc_valid.' && this->identifyShortcut('.$sc.') ...');
        if ($sc!==$sc_tried && $sc_valid && $e=$this->identifyShortcut($sc)) {
            $this->diagnosticAdd ('... = TRUE - using endpoint found');
            $endpoint                   = $e;
        }
        else {
            $this->diagnosticAdd ('... = FALSE - using the shortcut as the endpoint');
            $endpoint                   = $sc;
        }
        // Trim directory separators
        $this->diagnosticAdd ('endpoint: '.$endpoint);
        $endpoint                       = trim ($endpoint,'/');
        $this->diagnosticAdd ('    trimmed: '.$endpoint);
        // Home endpoint
        if (!strlen($endpoint)) {
            $this->diagnosticAdd ('context['.SWEF_COL_HOME.']: '.$this->swef->context[SWEF_COL_HOME]);
            $endpoint                   = $this->swef->context[SWEF_COL_HOME];
            $this->diagnosticAdd ('empty endpoint set to '.$endpoint);
        }
        // Identify the endpoint script and/or template
        $this->endpoint                 = $endpoint;
        $this->diagnosticAdd ('this->endpoint: '.$this->endpoint);
        $this->identify ();
        $this->diagnosticAdd ('endpoint = '.$this->endpoint);
        $this->diagnosticAdd ('template = '.$this->template[SWEF_COL_TEMPLATE]);
        $this->pluginsRun (SWEF__ON_PAGEIDENTIFYAFTER);
    }

    public function identifyShortcut ($request_uri) {
        $s = $this->swef->db->dbCall (SWEF_CALL_SHORTCUTFETCH,$this->swef->context[SWEF_COL_CONTEXT],$request_uri);
        if (!is_array($s)) {
            $this->diagnosticAdd ('Could not fetch shortcut: '.print_r($this->swef->db->dbErrorLast(),SWEF_BOOL_TRUE));
        }
        if (count($s)) {
            return $s[0][SWEF_COL_ENDPOINT_URI];
        }
        $this->diagnosticAdd ($request_uri.' is not a shortcut');
    }

    public function output ( ) {
        // Output script buffer and clear
        echo $this->scriptBuffer;
        $this->diagnosticAdd ('echoed '.strlen($this->scriptBuffer).'B of script output');
        $this->scriptBuffer     = null;
    }

    public function push ( ) {
        if ($this->swef->error()) {
            if (SWEF_DIAGNOSTIC) {
                $this->diagnosticAdd ('MEMORY PEAK USAGE = '.memory_get_peak_usage());
                $this->diagnosticOutput ();
            }
            return SWEF_BOOL_FALSE;
        }
        $_ARGS                  = func_get_args ();
        $_ARGS                  = $this->args ($_ARGS);
        if (!$this->pluginsRun(SWEF__ON_PUSHBEFORE,$_ARGS)) {
            // At least one plugin has cancelled this method
            return SWEF_BOOL_TRUE;
        }
        // FRAMEWORK HEADERS MAY BE OVER-WRITTEN BY COMPONENTS - LAST HEADER WINS
        $this->headers ();
// >>---
        // BUFFER ALL OUTPUT
        ob_start ();
// -----
    // >>---
            // BUFFER SCRIPT OUTPUT
            ob_start ();
    // -----
        // RUN SCRIPT
        if ($this->pluginsRun(SWEF__ON_PAGESCRIPTBEFORE)) {
            // No plugin has cancelled the page script
            if (is_readable(SWEF_DIR_ENDPOINT.'/'.$this->endpoint.SWEF_STR_EXT_PHP)) {
                    require (SWEF_DIR_ENDPOINT.'/'.$this->endpoint.SWEF_STR_EXT_PHP);
            }
    // -----
        }
        $this->scriptBuffer                             = ob_get_contents ();
        ob_end_clean ();
    // <<---
        // RUN TEMPLATE
        if ($this->template) {
            if ($this->pluginsRun(SWEF__ON_PAGETEMPLATEBEFORE)) {
                // No plugin has cancelled the page template
                require (SWEF_DIR_TEMPLATE.'/'.$this->template[SWEF_COL_TEMPLATE]);
            }
        }
// -----
        // ADD TEMPLATE OUTPUT TO BUFFER
        $this->buffer                                       = ob_get_contents ();
        ob_end_clean ();
// <<---
        // ADD SCRIPT OUTPUT TO BUFFER
        if ($this->scriptBuffer) {
            // The template has not run swefPage::output() so prepend the main buffer
            $this->buffer                                   = ltrim ($this->scriptBuffer.$this->buffer);
        }
        // MULTINGUAL TRANSLATION OF <t xx>SOME PHRASE</t>
        $this->buffer       = $this->swef->translateBlock ($this->buffer);
        // OUTPUT FLUSH
        if ($this->pluginsRun(SWEF__ON_FLUSHBEFORE)) {
            // No plugin has cancelled output flush
            echo $this->buffer;
            $this->pluginsRun (SWEF__ON_FLUSHAFTER);
        }
        $this->pluginsRun (SWEF__ON_PUSHAFTER);
        // DIAGNOSTIC
        if (SWEF_DIAGNOSTIC) {
            $this->diagnosticAdd ('MEMORY PEAK USAGE = '.memory_get_peak_usage());
            $this->diagnosticOutput ();
            $this->pluginsRun (SWEF__ON_DIAGNOSTICAFTER);
        }
    }

    public function reload ($get=null) {
        if (!strlen($get)) {
            $get = $this->baseDir.$this->requestURI;
        }
        $this->swef->sessionSet (SWEF_STR_NOTIFICATIONS,$this->swef->notifications());
        if (SWEF_DIAGNOSTIC) {
            $this->diagnosticAdd ('Set notifications for reload');
            $this->diagnosticAdd ('About to output diagnostic, retain it for reload and send header:');
            $this->diagnosticAdd ('Location: '.$get);
            $this->diagnosticAdd ('BYE');
            $this->diagnosticOutput ();
            $this->swef->sessionSet (SWEF_STR_DIAGNOSTIC,SWEF_BOOL_TRUE);
        }
        header ('Location: '.$get);
        exit;
    }

    public function sessionIsNew ( ) {
        return $this->swef->sessionGet(SWEF_STR_REQUESTS)==SWEF_INT_1;
    }

    public function titleGet ( ) {
        return $this->pageTitle;
    }

    public function titleSet ($title) {
        $this->pageTitle = $title;
    }

}

?>
