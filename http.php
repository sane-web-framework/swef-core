<?php

namespace Swef;

// Log script start milliseconds (global scope)
define ('SWEF_DIAGNOSTIC_START',intval(1000*microtime(true)));

// Configuration for this executive script (global scope)
define ('SWEF_CONFIG_PATH','./app/config');

// This HTTP portal should not be included or run by CLI
if (str_replace('\\','/',__FILE__)!=$_SERVER['SCRIPT_FILENAME'] || php_sapi_name()=='cli') {
    header ('403 Access denied',true,403);
    die ('Application error [2]: Execution error');
}

// The executive function
function swef ( ) {
    $definitions = require_once SWEF_CONFIG_PATH.'/static/Swef/Swef.var.paths.php';
    foreach ($definitions AS $c=>$d) {
        // Define for HTTP (global scope)
        define ($c,$d);
    }
    // Define and include everything else
    require_once SWEF_CONFIG_PATH.'/static/Swef/Swef.require_once.php';
    // The executive method ( \Bespoke\Executive extends \Base\SwefExecutive )
    \Swef\Bespoke\Executive::execute ();
}

// Run the framework
\Swef\swef ();

?>
