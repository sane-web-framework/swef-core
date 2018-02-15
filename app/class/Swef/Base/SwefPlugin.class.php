<?php

namespace Swef\Base;

class SwefPlugin {

    public  $config             = array ();     // Context-specific configuration
    public  $extensionName;                     // \NameSpace\ClassName of child extension class
    public  $page;                              // Page reference
    public  $properties;                        // Property array by context

    public function __construct ($page,$ext) {
        $this->page                 = $page;
        $this->extensionName        = $ext;
    }


/*
    When deployed, these event methods should be overridden by the plugin extension class
    Note that _on_*Before() MUST return true UNLESS you want to inhibit the relevant
    framework behaviours - the plugin hooks are in:
        * swefSwef::_run()
        * swefPage::push()
        * swefPage::headers()
        * swefEndpoint::pull()
        * swefEndpoint::_run()
*/


    public function _on_pluginsSetAfter ( ) {
    }

    public function _on_pushBefore ( ) {
        return SWEF_BOOL_TRUE;
    }

    public function _on_headersBefore ( ) {
        return SWEF_BOOL_TRUE;
    }

    public function _on_pageIdentifyBefore ( ) {
        return SWEF_BOOL_TRUE;
    }

    public function _on_pageIdentifyAfter ( ) {
    }

    public function _on_pageScriptBefore ( ) {
        return SWEF_BOOL_TRUE;
    }

    public function _on_pageTemplateBefore ( ) {
        return SWEF_BOOL_TRUE;
    }

    public function _on_endpointIdentifyBefore ( ) {
        return SWEF_BOOL_TRUE;
    }

    public function _on_endpointIdentifyAfter ( ) {
    }

    public function _on_pullBefore ( ) {
        return SWEF_BOOL_TRUE;
    }

    public function _on_endpointScriptBefore ( ) {
        return SWEF_BOOL_TRUE;
    }

    public function _on_endpointTemplateBefore ( ) {
        return SWEF_BOOL_TRUE;
    }

    public function _on_pullAfter ( ) {
    }

    public function _on_flushBefore ( ) {
        return SWEF_BOOL_TRUE;
    }

    public function _on_flushAfter ( ) {
    }

    public function _on_pushAfter ( ) {
    }

    public function _on_diagnosticAfter ( ) {
    }

    public function __destruct ( ) {
    }

    public function configure ($configs) {
        $configs    = explode (SWEF_STR_PLUGINS_CONFIG_SEP_O,$configs);
        foreach ($configs as $c) {
            if (!strlen(trim($c))) {
                continue;
            }
            $c      = explode (SWEF_STR_PLUGINS_CONFIG_SEP_I,$c);
            $k      = trim (array_shift($c));
            $v      = trim (implode(SWEF_STR_PLUGINS_CONFIG_SEP_I,$c));
            $this->config[$k] = $v;
        }
        return SWEF_BOOL_TRUE;
    }

    public function getLiveEndpoint ($obj=null) {
        if (!is_object($obj)) {
            $obj    = $this->page;
        }
        $next       = $obj->nestedEndpoint;
        if (!is_object($next)) {
            return $obj;
        }
        $obj        = $this->getLiveEndpoint ($next);
        return $obj;
    }

    public function notify ($msg) {
        $this->page->swef->notify ($msg);
    }

    public function propertiesLoad ( ) {
        $this->properties = $this->page->swef->db->dbCall (
            SWEF_CALL_PLUGINFETCH
           ,$this->extensionName
        );
    }

}

?>