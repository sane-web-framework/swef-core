<?php

namespace Swef\Base;

class SwefExecutive {

    public static function execute ( ) {

        SwefExecutive::requires ();

        $swef = new \Swef\Bespoke\Swef ();

        if ($swef->error()) {
            header (SWEF_HTTP_STATUS_MSG_555,SWEF_BOOL_TRUE,SWEF_HTTP_STATUS_CODE_555);
            echo 'Application error [2]: Construction error';
            exit;
        }

        if (!$swef->_db()) {
            header (SWEF_HTTP_STATUS_MSG_555,SWEF_BOOL_TRUE,SWEF_HTTP_STATUS_CODE_555);
            echo 'Application error [3]: Connection error';
            exit;
        }

        if (!$swef->_run()) {
            header (SWEF_HTTP_STATUS_MSG_555,SWEF_BOOL_TRUE,SWEF_HTTP_STATUS_CODE_555);
            echo 'Application error [4]: Request error';
            exit;
        }

    }

    public static function requires ( ) {
        foreach (scandir(SWEF_CONFIG_PATH.'/static') as $dir) {
            if (!is_dir(SWEF_CONFIG_PATH.'/static/'.$dir)) {
                continue;
            }
            foreach (scandir(SWEF_CONFIG_PATH.'/static/'.$dir) as $f) {
                if (!preg_match(SWEF_PREG_REQUIRES,$f)) {
                    continue;
                }
                require_once SWEF_CONFIG_PATH.'/static/'.$dir.'/'.$f;
                break;
            }
        }
    }

}

?>
