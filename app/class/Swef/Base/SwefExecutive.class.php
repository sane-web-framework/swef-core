<?php

namespace Swef\Base;

class SwefExecutive {

    public static function execute () {

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

}

?>
