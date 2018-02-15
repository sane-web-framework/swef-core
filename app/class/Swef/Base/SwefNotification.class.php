<?php

namespace Swef\Base;

class SwefNotification {

    private $n      = array ();

    public function __construct ( ) {
        $this->n    = array ();
    }

    public function __destruct () {
    }

    public function notify ($msg) {
        array_push ($this->n,$msg);
    }

    public function notes ( ) {
        return $this->n;
    }

    public function purge ( ) {
        $this->n     = array ();
    }

}

?>
