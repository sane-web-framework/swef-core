<?php

namespace Swef\Base;

class SwefExecutive {

    public static function execute ( ) {

        $swef = new \Swef\Bespoke\Swef ();

        if ($swef->error()) {
            \Swef\Bespoke\Swef::statusHeader (SWEF_HTTP_STATUS_CODE_555);
            echo 'Application error [3]: Framework construction error';
            exit;
        }

        if (!$swef->_db()) {
            \Swef\Bespoke\Swef::statusHeader (SWEF_HTTP_STATUS_CODE_555);
            echo 'Application error [4]: Database connection error';
            exit;
        }

        if (!$swef->_run()) {
            \Swef\Bespoke\Swef::statusHeader (SWEF_HTTP_STATUS_CODE_555);
            echo 'Application error [5]: Framework run error';
            exit;
        }

    }

}

?>
