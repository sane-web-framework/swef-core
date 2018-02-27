<?php

namespace Swef\Base;

class SwefExecutive {

    public static function execute ( ) {

        $swef = new \Swef\Bespoke\Swef ();

        if ($swef->error()) {
            header (SWEF_HTTP_STATUS_MSG_555,SWEF_BOOL_TRUE,SWEF_HTTP_STATUS_CODE_555);
            echo 'Application error [3]: Framework construction error';
            exit;
        }

        if (!$swef->_db()) {
            header (SWEF_HTTP_STATUS_MSG_555,SWEF_BOOL_TRUE,SWEF_HTTP_STATUS_CODE_555);
            echo 'Application error [4]: Database connection error';
            exit;
        }

        if (!$swef->_run()) {
            header (SWEF_HTTP_STATUS_MSG_555,SWEF_BOOL_TRUE,SWEF_HTTP_STATUS_CODE_555);
            echo 'Application error [5]: Framework run error';
            exit;
        }

    }

}

?>
