<?php namespace CharUtils;

class UTF8StringTest extends \PHPUnit_Framework_TestCase {
    public function testToString() {
        $this->assertEquals((string)(new UTF8String('stȈrĉ')), 'stȈrĉ');
    }

    public function testIterator() {
        $testStr = 'stȈrĉ';

        $str = new UTF8String($testStr);
        $compStr = '';
        foreach($str as $ch) {
            $compStr .= $ch;
        }

        $this->assertEquals($compStr, $testStr);
    }

    public function testArrayAccess() {
        $testStr = 'stȈrĉ';

        $str = new UTF8String($testStr);
        $this->assertEquals('Ȉ', $str[2]);
        $this->assertEquals('ĉ', $str[4]);

        $compStr = '';
        for($i = 0; $i < count($str); $i++) {
            $compStr .= $str[$i];
        }

        $this->assertEquals($compStr, $testStr);
    }

    public function testArrayImplode() {
        $str = new UTF8String('stȈrĉ');
        $this->assertEquals(implode(' ', iterator_to_array($str)), 's t Ȉ r ĉ');
    }

    public function testJsonSerialize() {
        $testStr = 'stȈrĉ';
        $encoded = json_encode($testStr);

        $str = new UTF8String($testStr);
        $this->assertEquals(json_encode($str), $encoded);
    }

    public function testReadChar() {
        $this->assertEquals(UTF8String::readChar('stȈr', 2, 2), 'Ȉ');
    }

    /**
     * @expectedException       CharUtils\UTF8Exception
     * @expectedExceptionCode   1
     */
    public function testCharValidationFB() {
        UTF8String::readChar('stȈr', 2, 3);
    }

    /**
     * @expectedException       CharUtils\UTF8Exception
     * @expectedExceptionCode   2
     */
    public function testCharValidationChop() {
        UTF8String::readChar(substr('Ȉ', 0, 1), 0, 0);
    }

    /**
     * @expectedException       CharUtils\UTF8Exception
     * @expectedExceptionCode   3
     */
    public function testCharValidationCont() {
        UTF8String::readChar(substr('Ȉ', 0, 1) . 't', 0, 0);
    }

    /**
     * @expectedException       InvalidArgumentException
     */
    public function testOrd() {
        $str = new UTF8String('stȈ');
        $this->assertEquals(0x208, $str->ord(2));

        $this->assertEquals(0x208, $str->ord('Ȉ'));
        $this->assertEquals(0x208, UTF8String::ord('Ȉ'));

        $this->assertEquals(0x208, UTF8String::ord(3));
    }
}