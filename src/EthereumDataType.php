<?php
/**
 * @file
 * Contains \Ethereum\Client.
 */

namespace Ethereum;

use Alchemy\Zippy\Exception\InvalidArgumentException;
use Behat\Mink\Exception\Exception;
use Drupal\views\Plugin\views\field\Boolean;
use Ethereum\EthereumStatic;
use PhpParser\Node\Expr\Cast\String_;

/*
 * Ethereum JsonRPC Data Types
 *
 * Implements Ethereum JsonRPC API DATATYPES for PHP
 *   https://github.com/ethereum/wiki/wiki/JSON-RPC
 *
 * This is part of the Guzzle implementation of ethereum-php-lib.
 * https://github.com/digitaldonkey/ethereum-php-lib
 *
 * For Schema check
 *   https://github.com/ethjs/ethjs-schema
 */
class EthereumDataType {

  /**
   * @var String $type
   * @var EthereumData$value
   */
  private $type, $value;

  /**
   * Available data types and their aliases.
   *
   * Usage:
   *   $int = new EthereumDataType('integer', 100);
   * or equavalent:
   *   $int = new EthereumDataType('integer', '0x64');
   *   $int = new EthereumDataType('integer', '0x00000000064');
   */
  const TYPES = array(
    'bool'=> array ('bool', 'boolean', 'B'),
    'hash' => array('hash', 'tx_hash', 'D32'),
    'address' => array('address','D20'),
    'integer' => array('integer', 'int', 'quantity', 'Q'),
    'string' => array ('string', 'S'),
//    'bytes' => array('bytes', 'D', 'data'),
//    'array' => array('array', 'ARRAY|DATA'),
  );


  /**
   * Constructing a Ethereum data type.
   *
   * Any EthereumDataType
   *
   * @param String $type_str
   *   Type or alias. See TYPES above.
   *
   * @param String|Integer $value
   *   Number or Hex value of given type. Hex values must be 0x prefixed.
   *
   * @throws InvalidArgumentException
   *   If type is unknown or value won't validate against given type.
   */
  public function __construct($type_str, $value) {
    if ($this->setType($type_str)) {
      $this->setValue($value);
    }
  }
  /**
   * Get PHP style value of any type.
   */
  public function val() {
    return $this->value->val();
  }
  /**
   * Get Ethereum JsonRPC style value of any type.
   */
  public function hexVal() {
    return $this->value->hexVal();
  }
  /**
   * Get type string.
   */
  public function getType() {
    return $this->type;
  }

  /**
   * Validate and set $value.
   *
   * @param String $value - Input value.
   */
  private function setValue($value) {

    // Getters and validation is implemented in EthereumDataTYPE.
    // Which are descendants of EthereumData.
    $class = 'Ethereum\EthereumDataType' . ucfirst($this->type);
    $value_obj = new $class($value);
    if (is_object($value_obj)) {
      $this->value = $value_obj;
    }
    else {
      throw new \InvalidArgumentException("Couldn't validate type: " . $this->type);
    }
  }

  /**
   * Returns validates and the default type name.
   *
   * @param string $type_input - string Type or alias.
   * @throws InvalidArgumentException if type not known.
   * @return Boolean
   */
  private function setType($type_input) {
    $map = array();
    foreach (EthereumDataType::TYPES as $type_name => $aliases) {
      foreach ($aliases as $a) {
        $map[$a] = $type_name;
      }
    }
    if (isset($map[$type_input])) {
      $this->type = $map[$type_input];
      return TRUE;
    }
    else {
      throw new \InvalidArgumentException('Invalid type: ' . $type_input);
    }
  }
}


class EthereumData {

  public $value;

  public function __construct($value) {
    $this->value = $this->validate($value);
    if (!is_null($this->value)) {
      return $this;
    }
    return NULL;
  }

  // PHP Value
  public function val() {
    return $this->value;
  }

  public function validate($value) {
    // must return NULL or value.
    return NULL;
    throw new \InvalidArgumentException('No validation implemented yet.');
  }

  public function getType() {
    return substr(__CLASS__, strlen('EthereumDataType'));
  }

  public function hexVal() {
    throw new \InvalidArgumentException('Not implemented yet.');
  }


  // TODO MOVE TO STATIC.
  /**
   * hexPaddingLeft().
   *
   * @param String $hex - Hexadecimal value.
   * @param Integer $length - Desired length of string excluding prefix.
   * @param Bool $prefix - Prefix string with "0x".
   *
   * @return String - padded to given length.
   */
  public function hexPaddingLeft($hex, $length, $prefix = TRUE) {
    $ret = sprintf('%0' . $length . 'd', $hex);
    return $prefix ? '0x' . $ret : $ret;
  }
}


/**
 * Data Type implementations below.
 */

class EthereumDataTypeBool extends EthereumData {

  public function validate($value) {
    return (bool) $value;
  }

  public function hexVal() {
    return $this->hexPaddingLeft((int)$this->value, 8);
  }
}

class EthereumDataTypeHash extends EthereumData {

  public function validate($value) {
    $return = NULL;
    if (is_numeric($value) && substr($value, 0, 2 ) !== '0x') {
      $return = $value;
    }
    elseif (is_string($value) && substr($value, 0, 2 ) === '0x') {
      $return = EthereumStatic::decode_hex_number($value);
    }
    return $return;
  }

  public function hexVal() {

    return EthereumStatic::encode_hex($this->value);
  }
}

class EthereumDataTypeAddress extends EthereumDataTypeHash {

}

class EthereumDataTypeInteger extends EthereumDataTypeHash {
}

