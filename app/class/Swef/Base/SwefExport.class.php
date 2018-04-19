<?php

namespace Swef\Base;

class SwefExport {

    private $calls          = array ();

    public function __construct ( ) {
    }

    public function __destruct ( ) {
    }

    public static function callNew ($args) {
        // Check if this procedure ($args[0]) is exportable
        // using table swef_config_export
        // If not, return. Else:
        // Push $args on to $this->calls
    }

    public static function callExport ($args) {
        // Check if this procedure ($args[0]) is exportable
        // If not, return
        // Write $this->calls JSON file to a per-procedure
        // export directory ensuring paths are created
    }

    public static function export ( ) {
        // Move export directory to buffer
        // Per procedure: merge from JSON into single array
        //                export as JSON to output log:
        //    ./app/export/mySP/mySP-2018-02-19-19:07:00.json
    }

}

?>
