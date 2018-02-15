<?php

// System definitions - NOT userland
// Application file and directory paths
// Defined from a variable because they are conditional on HTTP/CLI execution
return array (
    'SWEF_FILE_CONFIG_MODE' => './app/config/statis/Swef.var.chmod.php'
   ,'SWEF_FILE_CONFIG_DB'   => './app/config/Swef/Swef.var.db.php'
   ,'SWEF_DIR_CLASS'        => './app/class'
   ,'SWEF_DIR_FUNCTION'     => './app/function'
   ,'SWEF_DIR_LOG'          => './app/log'
   ,'SWEF_DIR_LOOKUP'       => './app/lookup'
   ,'SWEF_DIR_PHRASES'      => './app/phrases'
   ,'SWEF_DIR_PLUGIN'       => './app/plugin'
   ,'SWEF_DIR_ENDPOINT'     => './app/endpoint'
   ,'SWEF_DIR_TEMPLATE'     => './app/template'
);

?>
