<?php

namespace Ethereum;
use Ethereum\EthereumDataType;

/**
 *
 */
class EthereumDataTypeTest extends \PHPUnit_Framework_TestCase {

  public function testBoolean()
  {
    $booleanTrue = new EthereumDataType('B', TRUE);
    $booleanFalse = new EthereumDataType('boolean', FALSE);

    $this->assertEquals($booleanTrue->hexVal(), '0x00000001');
    $this->assertEquals($booleanFalse->hexVal(), '0x00000000');

    $this->assertEquals($booleanTrue->val(), TRUE);
    $this->assertEquals($booleanFalse->val(), FALSE);

    $this->assertEquals($booleanTrue->getType(), 'bool');

    $this->assertEquals($booleanTrue->val(), TRUE);
  }

  public function testAddress() {

    $address = new EthereumDataType('address', '0xf4c875ee7a70fae078c9a4b07dc4f6970a804f6f');
    $this->assertEquals($address->val(), 1397464170408132752794213135410209510987340468079);
    $this->assertEquals($address->hexVal(), '0xf4c875ee7a70fae078c9a4b07dc4f6970a804f6f');

    $address2 = new EthereumDataType('address', 1397464170408132752794213135410209510987340468079);

    var_dump($address2->val());

    $this->assertEquals($address2->val(), doubleval(1397464170408132752794213135410209510987340468079));
    $this->assertEquals($address2->val(), 1397464170408132752794213135410209510987340468079);

    $this->assertEquals($address2->hexVal(), '0xf4c875ee7a70fae078c9a4b07dc4f6970a804f6f');
  }

  public function testHash() {

    $hash = new EthereumDataType('hash', '0x3d28f358c11302b9cccbb1ce2458f22ebbd199c3801b159fc27c0f549a5bad2c');
    $this->assertEquals($hash->val(), 27663437163341054699053052081317933555832365863079071775406979935620126453036);
    $this->assertEquals($hash->hexVal(), '0x3d28f358c11302b9cccbb1ce2458f22ebbd199c3801b159fc27c0f549a5bad2c');

    $hash2 = new EthereumDataType('hash', 27663437163341054699053052081317933555832365863079071775406979935620126453036);
    $this->assertEquals($hash2->val(), '0x3d28f358c11302b9cccbb1ce2458f22ebbd199c3801b159fc27c0f549a5bad2c');
  }

  public function testInteger() {

    $int = new EthereumDataType('integer', '0x3d28f358c11302b9cccbb1ce2458f22ebbd199c3801b159fc27c0f549a5bad2c');
    $this->assertEquals($int->val(), 27663437163341054699053052081317933555832365863079071775406979935620126453036);
    $this->assertEquals($int->hexVal(), '0x3d28f358c11302b9cccbb1ce2458f22ebbd199c3801b159fc27c0f549a5bad2c');

    $int3 = new EthereumDataType('integer', '0x64');
    $this->assertEquals($int3->val(), 100);

    $int4 = new EthereumDataType('integer', '0x00000000064');
    $this->assertEquals($int4->val(), 100);

    // Integer constructor.
    $int2 = new EthereumDataType('integer', 100);
    $this->assertEquals($int2->hexVal(), '0x64');
    $this->assertEquals($int2->val(), 100);
  }


}
