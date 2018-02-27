<?php

return array (
    // Chmod in the following order
    // 0 = default
    0 => array (
        // 1 = SWEF development, 2 = Bespoke development, 3 = Production
        // Permission is: array([dir perm],[file perm])
        // A null permission is interpreted as "use equivalent from this default"
        1=>array(0755,0644), 2=>array(0755,0644), 3=>array(0555,0444)
    )
   ,SWEF_DIR_CONFIG.'/console-class' => array (
        1=>array(0700,0600), 2=>array(0500,0400), 3=>array(0500,0400)
    )
   ,SWEF_DIR_CONFIG.'/console-option' => array (
        1=>array(0700,0600), 2=>array(0500,0400), 3=>array(0500,0400)
    )
   ,SWEF_DIR_CLASS.'/base' => array (
        1=>null,             2=>array(0555,0444), 3=>array(0555,0444)
    )
   ,SWEF_DIR_LOG => array (
        1=>array(0777,0666), 2=>array(0777,0666), 3=>array(0777,0666)
    )
   ,SWEF_DIR_LOOKUP => array (
        1=>array(0777,0444), 2=>array(0777,0444), 3=>array(0777,0444)
    )
   ,SWEF_DIR_PHRASES => array (
        1=>array(0777,0666), 2=>array(0777,0666), 3=>array(0777,0444)
    )
);

?>
