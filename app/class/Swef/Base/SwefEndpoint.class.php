<?php

namespace Swef\Base;

class SwefEndpoint extends \DOMDocument {

    public   $endpoint;
    public   $diagnostic                = array ();
    private  $e;
    public   $nestedEndpoint;
    public   $plugin                    = array ();     // Plugin objects for running event handlers
    public   $router;
    public   $swef;
    public   $template                  = array ();     // Most specific template match

    public function __construct ($swef,$endpoint=SWEF_STR__EMPTY) {
        parent::__construct ();
        $this->swef                     = $swef;
        $this->endpoint                = $endpoint;
    }

    public function __destruct () {
    }

    public function _run ($_ARGS=array()) {
        if (SWEF_DIAGNOSTIC) {
            $this->diagnosticAdd ('Received these arguments:');
            foreach ($_ARGS as $k => $v) {
                $this->diagnosticAdd ($k,$v);
            }
            unset ($k,$v);
        }
        if ($this->pluginsRun(SWEF__ON_ENDPOINTSCRIPTBEFORE)) {
            if (is_readable(SWEF_DIR_ENDPOINT.'/'.$this->endpoint.SWEF_STR_EXT_PHP)) {
                $this->diagnosticAdd ('Including script');
                include SWEF_DIR_ENDPOINT.'/'.$this->endpoint.SWEF_STR_EXT_PHP;
            }
            else {
                $this->diagnosticAdd ('Script '.SWEF_DIR_ENDPOINT.'/'.$this->endpoint.SWEF_STR_EXT_PHP.' is not readable');
            }
        }
        if ($this->template[SWEF_COL_TEMPLATE]) {
            if ($this->pluginsRun (SWEF__ON_ENDPOINTTEMPLATEBEFORE)) {
                $this->diagnosticAdd ('Including template');
                $this->diagnosticAdd (SWEF_DIR_TEMPLATE.'/'.$this->template[SWEF_COL_TEMPLATE]);
                include SWEF_DIR_TEMPLATE.'/'.$this->template[SWEF_COL_TEMPLATE];
            }
        }
    }

    public function args ($array) {
        $args           = array ();
        if (!$count=count($array)) {
            return $args;
        }
        $count --;
        $args[SWEF_COL_ENDPOINT] = array_shift ($array);
        while ($count) {
            $key        = array_shift ($array);
            $count --;
            if ($count) {
                $value  = array_shift ($array);
                $count--;
            }
            else {
                $value  = null;
            }
            $args[$key] = $value;
        }
        return $args;
    }

    public function diagnosticAdd ( ) {
        if (!SWEF_DIAGNOSTIC) {
            return;
        }
        $caller         = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,SWEF_INT_2)[SWEF_INT_1];
        array_push ($this->diagnostic,$this->swef->diagnosticString($caller,func_get_args()));
    }

    public function diagnosticGet ( ) {
        return $this->diagnostic;
    }

    public function error ( ) {
        return $this->e;
    }

    public function identify ( ) {
        $this->router           = $this->identifyRouter ($this->endpoint);
        $this->template         = $this->identifyTemplate ();
    }

    public function identifyRouter ( ) {
        foreach ($this->swef->user->memberships as $m) {
            $m              = $m[SWEF_COL_USERGROUP];
            foreach ($this->swef->routers as $r) {
                $m_match    = $r[SWEF_COL_USERGROUP_PREG];
                $e_match    = $r[SWEF_COL_ENDPOINT_PREG];
                $this->diagnosticAdd         (' preg_match('.$m_match.','.$m.')');
                if (!preg_match($m_match,$m)) {
                    continue;
                }
                $this->diagnosticAdd         ('     matched');
                $this->diagnosticAdd         ('     preg_match('.$e_match.','.$this->endpoint.')');
                if (!preg_match($e_match,$this->endpoint)) {
                    continue;
                }
                $this->diagnosticAdd         ('         MATCHED');
                return $r;
            }
        }
        $this->diagnosticAdd ('router: NO ROUTER');
        return SWEF_BOOL_FALSE;
    }

    public function identifyTemplate ( ) {
        foreach ($this->swef->templates as $i=>$t) {
            $this->diagnosticAdd             ('endpoint: '.$this->endpoint);
            $this->diagnosticAdd             ('    regexp: '.$t[SWEF_COL_ENDPOINT_PREG]);
            if (preg_match ($t[SWEF_COL_ENDPOINT_PREG],$this->endpoint,$m)) {
                $this->diagnosticAdd         ('        MATCHED');
                $t[SWEF_COL_TEMPLATE] = $t[SWEF_COL_TEMPLATE_BACKREFERENCED];
                $this->diagnosticAdd         ('            template: '.$t[SWEF_COL_TEMPLATE]);
                for ($i=1;array_key_exists($i,$m);$i++) {
                    $t[SWEF_COL_TEMPLATE] = str_replace ('$'.$i,$m[$i],$t[SWEF_COL_TEMPLATE]);
                    $this->diagnosticAdd     ('                template: '.$t[SWEF_COL_TEMPLATE]);
                }
                $this->diagnosticAdd ('template: '.SWEF_DIR_TEMPLATE.'/'.$t[SWEF_COL_TEMPLATE]);
                if (!is_readable(SWEF_DIR_TEMPLATE.'/'.$t[SWEF_COL_TEMPLATE])) {
                    $this->diagnosticAdd     ('                FILE NOT READABLE - continuing');
                    continue;
                }
                return $t;
            }
        }
        $this->diagnosticAdd ('template: NO TEMPLATE');
        return SWEF_BOOL_FALSE;
    }

    public function notes ( ) {
        return $this->swef->notifications ();
    }

    public function notify ($msg) {
        $this->swef->notify ($msg);
    }

    public function pluginSet ($plugin_objects) {
        $this->diagnosticAdd ('Setting '.count($plugin_objects).' plugin objects');
        $this->plugin = $plugin_objects;
    }

    public function pluginsRun ($method,$_ARGS=null) {
        // Any plugin can force return false
        $return = SWEF_BOOL_TRUE;
        if ($method==SWEF_STR__EMPTY) {
            $this->diagnosticAdd ('Method was not given');
            return $return;
        }
        $count = count ($this->plugin);
        $this->diagnosticAdd ('Handling "'.$method.'" for '.$count.' plugins');
        for ($i=0;$i<$count;$i++) {
            if (!is_object($this->plugin[$i])) {
                $this->diagnosticAdd ('plugin['.$i.'] is not an object');
                continue;
            }
            $c  = get_class($this->plugin[$i]);
            if (!method_exists($this->plugin[$i],$method)) {
                $this->diagnosticAdd ('method '.$c.'::'.$method.'() does not exist');
                continue;
            }
            $rtn    = $this->plugin[$i]->$method ($_ARGS);
            $return = $return && $rtn;
        }
        return $return;
    }

    public function pull ($endpoint) {
        if ($this->swef->pullStop()) {
            return;
        }
        $this->pluginsRun (SWEF__ON_PULLBEFORE);
        // Recursive pulling of nested endpoints
        // $_ARGS only exists within the scope of this recursion of this method
        $_ARGS                              = $this->args (func_get_args());
        if (SWEF_DIAGNOSTIC) {
            $this->diagnosticAdd ('Received these arguments:');
            foreach ($_ARGS as $k => $v) {
                $this->diagnosticAdd ($k,$v);
            }
            unset ($k,$v);
        }
        if ($endpoint==SWEF_STR__EMPTY) {
            $this->e                        =  'swefEndpoint::pull(): no endpoint was given<br/>'.SWEF_STR__CRLF;
            return SWEF_BOOL_FALSE;
        }
        $this->nestedEndpoint  = new \Swef\Bespoke\Endpoint (
            $this->swef,
            $endpoint
        );
        $this->nestedEndpoint->pluginSet ($this->plugin);
        if ($this->pluginsRun(SWEF__ON_ENDPOINTIDENTIFYBEFORE)) {
            $this->nestedEndpoint->identify ();
        }
        $this->pluginsRun (SWEF__ON_ENDPOINTIDENTIFYAFTER);
        $this->nestedEndpoint->_run ($_ARGS);
        array_push ($this->swef->diagnostic,$this->nestedEndpoint->diagnosticGet());
        $this->nestedEndpoint = null;
        $this->pluginsRun (SWEF__ON_PULLAFTER);
    }

    private function readable ($path) {
        if (is_dir($path)) {
            return SWEF_BOOL_FALSE;
        }
        if (is_readable($path)) {
            return SWEF_BOOL_TRUE;
        }
        return SWEF_BOOL_FALSE;
    }

}

?>