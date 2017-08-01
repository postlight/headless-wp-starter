<?php

class WPMDBPro_Media_Files_CLI_Bar extends \cli\progress\Bar {

    public function setMessage( $message ) {
        $this->_message = $message;
    }
}
