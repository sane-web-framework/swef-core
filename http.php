<?php

namespace Swef;
error_reporting (-1);
function swef ( ) {
    if (version_compare(PHP_VERSION,'7.0.0','<')) {
        die ('Application error [1]: PHP minimum requirement is 7.0.0');
    }
    if (php_sapi_name()=='cli') {
        die ('Application error [2]: '.basename(__FILE__).' is not intended for CLI use');
    }
    require_once './app/config/static/requires.php';
    foreach (scandir(SWEF_DIR_CONFIG.'/static') as $dir) {
        if (!is_dir(SWEF_DIR_CONFIG.'/static/'.$dir)) {
            continue;
        }
        foreach (scandir(SWEF_DIR_CONFIG.'/static/'.$dir) as $f) {
            if (!preg_match(SWEF_FILE_REQUIRES_PREG,$f)) {
                continue;
            }
            require_once SWEF_DIR_CONFIG.'/static/'.$dir.'/'.$f;
        }
    }
    \Swef\Bespoke\Executive::execute ();
}

\Swef\swef ();

?>
