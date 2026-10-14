<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Phpqrcode {
    public function __construct() {
        require_once(APPPATH . 'libraries/qrcode/qrlib.php');
    }
}