<?php namespace CharUtils;

use CharUtils;

class UTF8String implements \ArrayAccess, \Iterator, \Countable, \JsonSerializable {
    private $offset = 0;
    private $charNum = 0;
    private $curChar;
    private $charMap = array();
    private $charMapOffset = 0;
    private $charMapComplete = false;

    private $str;
    private $byteLen;
    private $_count;

    // Used as a performance enhancement to get the last read character width so we don't have to count it each time.
    private static $_lastReadCharWidth;

    public function __construct($str) {
        $enc = mb_detect_encoding($str);
        if($enc && $enc != 'UTF-8') {
            $str = mb_convert_encoding($str, 'UTF-8', $enc);
        }

        $this->str = $str;
        $this->byteLen = $this->strBytes($str);
    }

    public function __toString() {
        return $this->str;
    }

    public function jsonSerialize() {
        return $this->str;
    }

    public static function strBytes($str) {
        return ini_get('mbstring.func_overload') ? mb_strlen($str, '8bit') : strlen($str);
    }

    public static function readChar($str, $charNum, $offset) {
        $firstByte = ord($str[$offset]);

        // Validate the first byte starts with either '01' or '11'. '10' is definitely an invalid start byte
        if(($firstByte & 0xC0) == 0x80) {
            throw new UTF8Exception($charNum, $offset, 'Invalid first byte of character', Consts::ERR_UTF8_FIRST_BYTE);
        }

        $charWidth = 1;
        if($firstByte & (1 << 7)) {
            for($shift = 6, $i = 1; $shift > 0; $shift--, $i++) {
                if((1 << $shift) & $firstByte) {
                    $charpos = $offset + $i;
                    if(!isset($str[$charpos])) {
                        throw new UTF8Exception($charNum, $charpos, 'Unexpected end of string. Character incomplete.', Consts::ERR_UTF8_CHAR_INCOMPLETE);
                    }

                    // Check the byte to make sure it's a valid continuation byte before including it as part of the character
                    if((ord($str[$charpos]) & 0xC0) != 0x80) {
                        throw new UTF8Exception($charNum, $charpos, 'Invalid continuation byte', Consts::ERR_UTF8_CHAR_CONT);
                    }

                    $charWidth++;
                } else {
                    break;
                }
            }
        }

        self::$_lastReadCharWidth = $charWidth;

        return substr($str, $offset, $charWidth);
    }

    protected function mapStrToCharNum($charNum = null) {
        if($this->charMapComplete) return;

        for($charPos = count($this->charMap); $this->charMapOffset < $this->byteLen; $charPos++) {
            if($charNum && $charNum < $charPos) break;

            $char = $this->readChar($this->str, $charPos, $this->charMapOffset);
            $this->charMap[$charPos] = $char;
            $this->charMapOffset += self::$_lastReadCharWidth;
        }

        if($this->charMapOffset >= $this->byteLen) {
            $this->charMapComplete = true;
        }
    }


    //
    // Countable Interface
    //

    public function count($mode = COUNT_NORMAL) {
        if($this->_count) return $this->_count;

        $this->mapStrToCharNum();
        return $this->_count = count($this->charMap);
    }


    //
    // ArrayAccess Interface
    //

    public function offsetExists($offset) {
        $this->mapStrToCharNum($offset);
        return isset($this->charMap[$offset]);
    }

    public function offsetGet($offset) {
        $this->mapStrToCharNum($offset);

        if(isset($this->charMap[$offset])) {
            return $this->charMap[$offset];
        } else {
            return null;
        }
    }

    public function offsetSet($offset, $value) {}

    public function offsetUnset($offset) {}


    //
    // Iterator Interface
    //

    public function rewind() {
        $this->charNum = 0;
    }

    public function current() {
        return $this->offsetGet($this->charNum);
    }

    public function next() {
        $this->charNum++;
    }

    public function key() {
        return $this->charNum;
    }

    public function valid() {
        return $this->offsetExists($this->charNum);
    }


    //
    // Utility Functions
    //

    public function __call($name, $arguments) {
        if($name == 'ord') {
            return call_user_func(array($this, '_ord'), $arguments[0], $this);
        }
    }

    public static function __callStatic($name, $arguments) {
        if($name == 'ord') {
            return call_user_func(array('CharUtils\UTF8String', '_ord'), $arguments[0]);
        }
    }

    public static function _ord($char, $obj = null) {
        if(isset($this)) $obj = this;

        if(gettype($char) == 'integer') {
            if($obj) {
                $char = $obj[$char];
            } else {
                throw new \InvalidArgumentException("You cannot pass an offset in a static call");
            }
        } else {
            // `readChar` will perform encoding validation so we can make assumptions below
            $char = self::readChar($char, 0, 0);
        }

        // First byte must be handled differently from the rest of the bytes
        $ordbin = decbin(ord($char[0]) & ((1 << (8 - self::strBytes($char))) - 1));

        $cbitMask = 0x3F;
        for($i = 1; $i < self::strBytes($char); $i++) {
            $ordbin .= str_pad(decbin(ord($char[$i]) & $cbitMask), 6, '0', STR_PAD_LEFT);
        }

        return bindec($ordbin);
    }
}