<?php namespace CharUtils;

class UTF8Exception extends \Exception {
    public $charNum;
    public $offset;

    public function __construct($charNum, $offset, $addMsg = null, $code = 0) {
        $this->charNum = $charNum;
        $this->offset = $offset;

        $message = "Encountered invalid UTF-8 character as position " . $charNum . ", offset " . $offset;
        parent::__construct($message . ($addMsg ? ': ' . $addMsg : ''), $code);
    }
}