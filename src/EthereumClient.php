<?php
/**
 * @file
 * Contains \Ethereum\Client.
 */

namespace Ethereum;

use Drupal\Core\TypedData\Plugin\DataType\IntegerData;
use Graze\GuzzleHttp\JsonRpc\Client as RpcClient;


/*
 * PHP implementation of JSON RPC API.
 *
 * See:
 * https://github.com/ethereum/wiki/wiki/JSON-RPC
 */
class EthereumClient {

  protected $id = 0;

  public function __construct($url) {

    $this->client = RpcClient::factory($url, array(
      'debug' => FALSE,
    ));
  }

  public function request($method, array $params = []) {
    $this->id++;
    return $this->client->send($this->client->request($this->id, $method, $params))->getRpcResult();
  }

  private function ether_request($method, $params = array()) {

    try {
      return $this->request($method, $params);
    }
    catch (RequestException $e) {
      throw $e;
    }
  }

  private function decode_hex($input) {

    if (substr($input, 0, 2) == '0x') {
      $input = substr($input, 2);
    }
    if (preg_match('/[a-f0-9]+/', $input)) {
      return hexdec($input);
    }
    return $input;
  }

  /**
   * web3_clientVersion().
   *
   * Returns the current client version.
   *
   * @return String - The current client version.
   */
  public function web3_clientVersion() {
    return $this->ether_request(__FUNCTION__);
  }

  /**
   * web3_sha3().
   *
   * Returns Keccak-256 (not the standardized SHA3-256) of the given data.
   *
   * @param $input string - the data to convert into a SHA3 hash.
   *
   * @return String -  The SHA3 result of the given string.
   */
  public function web3_sha3($input) {
    return $this->ether_request(__FUNCTION__, array($input));
  }

  /**
   * net_version().
   *
   * Returns the current network protocol version.
   *
   * @return String - The current network protocol version.
   */
  public function net_version() {

    // TODO TEST.

    return $this->ether_request(__FUNCTION__);
  }

  /**
   * net_listening().
   *
   * Returns true if client is actively listening for network connections.
   *
   * @return Boolean - TRUE when listening, otherwise FALSE.
   */
  public function net_listening() {
    return $this->ether_request(__FUNCTION__);
  }

  /**
   * net_peerCount().
   *
   * Returns number of peers currenly connected to the client.
   *
   * @return Int - number of connected peers.
   */
  function net_peerCount() {
    return $this->decode_hex($this->ether_request(__FUNCTION__));
  }

  /**
   * eth_protocolVersion().
   *
   * Returns the current ethereum protocol version.
   *
   * @return String - The current ethereum protocol version.
   */
  function eth_protocolVersion() {
    return $this->ether_request(__FUNCTION__);
  }

  /**
   * eth_syncing().
   *
   * Returns an object with data about the sync status or false.
   *
   * startingBlock: QUANTITY - The block at which the import started (will only be reset, after the sync reached his head)
   * currentBlock: QUANTITY - The current block, same as eth_blockNumber
   * highestBlock: QUANTITY - The estimated highest block
   *
   * @return Object|Boolean, An object with sync status data or FALSE, when not syncing.
   */
  function eth_syncing() {

    // Todo Validate Return of object.
    // Quantity must be $this->decode_hex().

    $return = $this->ether_request(__FUNCTION__);
    return $return;
  }

  /**
   * eth_coinbase().
   *
   * Returns the client coinbase address.
   *
   * @return String - DATA, 20 bytes - the current coinbase address.
   */
  function eth_coinbase() {

    // TODO
    // In Infura not allowed:
    // curl -X POST --data '{"jsonrpc":"2.0","method":"eth_coinbase","params":[],"id":64}' https://mainnet.infura.io/IvqXzecwpDZ8jpPaRV9C --> Method Not Allowed
    // Drupal
    // InvalidArgumentException: Unable to parse JSON data: JSON_ERROR_SYNTAX - Syntax error, malformed JSON --> WOD: "The website encountered an unexpected error. Please try again later."
    //
    // Will be fixed by Maurycy Pietrzak https://consensys.slack.com/archives/team-infura/p1484603604000159
    //
    return $this->ether_request(__FUNCTION__);
  }

  /**
   * eth_mining().
   *
   * Returns true if client is actively mining new blocks.
   *
   * @return Boolean - returns true of the client is mining, otherwise false.
   */
  function eth_mining() {
    return $this->ether_request(__FUNCTION__);
  }

  /**
   * eth_hashrate().
   *
   * Returns the number of hashes per second that the node is mining with.
   *
   * @return Integer - Number of hashes per second.
   */
  function eth_hashrate() {
    return $this->decode_hex($this->ether_request(__FUNCTION__));
  }

  /**
   * eth_gasPrice().
   *
   * Returns the current price per gas in wei.
   *
   * @return Integer - QUANTITY of the current gas price in wei.
   */
  function eth_gasPrice() {
    return $this->decode_hex($this->ether_request(__FUNCTION__));
  }

  /**
   * eth_accounts().
   *
   * Returns a list of addresses owned by client.
   *
   * @return array - of DATA, 20 Bytes - addresses owned by the client.
   */
  function eth_accounts() {

    // TODO Fix? Infura dosn't support.
    return $this->ether_request(__FUNCTION__);
  }

  /**
   * eth_blockNumber().
   *
   * Returns the number of most recent block.
   *
   * @param $decode_hex Set FALSE to get Integer.
   *
   * @return String/Integer - Current block number the client is on.
   */
  function eth_blockNumber($decode_hex = FALSE) {
    $block = $this->ether_request(__FUNCTION__);
    if ($decode_hex) {
      $block = $this->decode_hex($block);
    }
    return $block;
  }

  /**
   * eth_getBalance().
   *
   * Returns the balance of the account of given address.
   *
   * @param $address - DATA, 20 Bytes - address to check for balance.
   *
   * @param $block - QUANTITY|TAG
   *   Integer block number, or the string "latest", "earliest" or "pending"
   *   See: https://github.com/ethereum/wiki/wiki/JSON-RPC#the-default-block-parameter
   *
   * @param $decode_hex - Set FALSE to get Integer value.
   *
   * @return Integer - QUANTITY - integer of the current balance in wei.
   */
  function eth_getBalance($address, $block='latest', $decode_hex=TRUE) {

    $balance = $this->ether_request(__FUNCTION__, array($address, $block));

    if($decode_hex) {
      $balance = $this->decode_hex($balance);
    }
    return $balance;
  }

  /**
   * eth_getStorageAt().
   *
   * Returns the value from a storage position at a given address.
   *
   * @param $address - DATA, 20 Bytes - address to check for balance.
   *
   * @param $at - QUANTITY integer of the position in the storage.
   *
   * @param $block - QUANTITY|TAG
   *   Integer block number, or the string "latest", "earliest" or "pending"
   *   See: https://github.com/ethereum/wiki/wiki/JSON-RPC#the-default-block-parameter
   *
   * @return ??? - DATA the value at this storage position.
   */
  function eth_getStorageAt($address, $at, $block='latest') {

    // TODO How to test that?

    return $this->ether_request(__FUNCTION__, array($address, $at, $block));
  }

  // UNCLEAR: Compare 0x26dd6b7a2fff271aa7c5fe8cfb5ba0ab33f47408
  // Here (43TX) VS Etherscan (46TX)
  // Why there is a difference?
  function eth_getTransactionCount($address, $block='latest', $decode_hex=FALSE) {
    $count = $this->ether_request(__FUNCTION__, array($address, $block));

        if($decode_hex)
            $count = $this->decode_hex($count);

        return $count;
  }

  function eth_getBlockTransactionCountByHash($tx_hash) {
    return $this->ether_request(__FUNCTION__, array($tx_hash));
  }

  function eth_getBlockTransactionCountByNumber($tx='latest') {
    return $this->ether_request(__FUNCTION__, array($tx));
  }

  function eth_getUncleCountByBlockHash($block_hash) {
    return $this->ether_request(__FUNCTION__, array($block_hash));
  }

  function eth_getUncleCountByBlockNumber($block='latest') {
    return $this->ether_request(__FUNCTION__, array($block));
  }

  function eth_getCode($address, $block='latest') {
    return $this->ether_request(__FUNCTION__, array($address, $block));
  }

  function eth_sign($address, $input) {
    return $this->ether_request(__FUNCTION__, array($address, $input));
  }

  function eth_sendTransaction($transaction) {
    if(!is_a($transaction, 'Ethereum_Transaction'))
    {
      throw new ErrorException('Transaction object expected');
    }
    else
    {
      return $this->ether_request(__FUNCTION__, $transaction->toArray());
    }
  }

  function eth_call($message, $block) {
    if(!is_a($message, 'Ethereum_Message'))
    {
      throw new ErrorException('Message object expected');
    }
    else
    {
      return $this->ether_request(__FUNCTION__, $message->toArray());
    }
  }

  function eth_estimateGas($message, $block) {
    if(!is_a($message, 'Ethereum_Message'))
    {
      throw new ErrorException('Message object expected');
    }
    else
    {
      return $this->ether_request(__FUNCTION__, $message->toArray());
    }
  }



  function eth_getBlockByHash($hash, $full_tx=TRUE) {
    return $this->ether_request(__FUNCTION__, array($hash, $full_tx));
  }

  function eth_getBlockByNumber($block='latest', $full_tx=TRUE) {
    return $this->ether_request(__FUNCTION__, array($block, $full_tx));
  }

  function eth_getTransactionByHash($hash) {
    return $this->ether_request(__FUNCTION__, array($hash));
  }

  function eth_getTransactionByBlockHashAndIndex($hash, $index) {
    return $this->ether_request(__FUNCTION__, array($hash, $index));
  }

  function eth_getTransactionByBlockNumberAndIndex($block, $index) {
    return $this->ether_request(__FUNCTION__, array($block, $index));
  }

  function eth_getTransactionReceipt($tx_hash) {
    return $this->ether_request(__FUNCTION__, array($tx_hash));
  }

  function eth_getUncleByBlockHashAndIndex($hash, $index) {
    return $this->ether_request(__FUNCTION__, array($hash, $index));
  }

  function eth_getUncleByBlockNumberAndIndex($block, $index) {
    return $this->ether_request(__FUNCTION__, array($block, $index));
  }

  function eth_getCompilers() {
    return $this->ether_request(__FUNCTION__);
  }

  function eth_compileSolidity($code) {
    return $this->ether_request(__FUNCTION__, array($code));
  }

  function eth_compileLLL($code) {
    return $this->ether_request(__FUNCTION__, array($code));
  }

  function eth_compileSerpent($code) {
    return $this->ether_request(__FUNCTION__, array($code));
  }

  function eth_newFilter($filter, $decode_hex=FALSE) {
    if(!is_a($filter, 'Ethereum_Filter'))
    {
      throw new ErrorException('Expected a Filter object');
    }
    else
    {
      $id = $this->ether_request(__FUNCTION__, $filter->toArray());

      if($decode_hex)
        $id = $this->decode_hex($id);

      return $id;
    }
  }

  function eth_newBlockFilter($decode_hex=FALSE) {
    $id = $this->ether_request(__FUNCTION__);

    if($decode_hex)
      $id = $this->decode_hex($id);

    return $id;
  }

  function eth_newPendingTransactionFilter($decode_hex=FALSE) {
    $id = $this->ether_request(__FUNCTION__);

    if($decode_hex)
      $id = $this->decode_hex($id);

    return $id;
  }

  function eth_uninstallFilter($id) {
    return $this->ether_request(__FUNCTION__, array($id));
  }

  function eth_getFilterChanges($id) {
    return $this->ether_request(__FUNCTION__, array($id));
  }

  function eth_getFilterLogs($id) {
    return $this->ether_request(__FUNCTION__, array($id));
  }

  function eth_getLogs($filter) {
    if(!is_a($filter, 'Ethereum_Filter'))
    {
      throw new ErrorException('Expected a Filter object');
    }
    else
    {
      return $this->ether_request(__FUNCTION__, $filter->toArray());
    }
  }

  function eth_getWork() {
    return $this->ether_request(__FUNCTION__);
  }

  function eth_submitWork($nonce, $pow_hash, $mix_digest) {
    return $this->ether_request(__FUNCTION__, array($nonce, $pow_hash, $mix_digest));
  }

  function db_putString($db, $key, $value) {
    return $this->ether_request(__FUNCTION__, array($db, $key, $value));
  }

  function db_getString($db, $key) {
    return $this->ether_request(__FUNCTION__, array($db, $key));
  }

  function db_putHex($db, $key, $value) {
    return $this->ether_request(__FUNCTION__, array($db, $key, $value));
  }

  function db_getHex($db, $key) {
    return $this->ether_request(__FUNCTION__, array($db, $key));
  }

  function shh_version() {
    return $this->ether_request(__FUNCTION__);
  }

  function shh_post($post) {
    if(!is_a($post, 'Whisper_Post'))
    {
      throw new ErrorException('Expected a Whisper post');
    }
    else
    {
      return $this->ether_request(__FUNCTION__, $post->toArray());
    }
  }

  function shh_newIdentinty() {
    return $this->ether_request(__FUNCTION__);
  }

  function shh_hasIdentity($id) {
    return $this->ether_request(__FUNCTION__);
  }

  function shh_newFilter($to=NULL, $topics=array()) {
    return $this->ether_request(__FUNCTION__, array(array('to'=>$to, 'topics'=>$topics)));
  }

  function shh_uninstallFilter($id) {
    return $this->ether_request(__FUNCTION__, array($id));
  }

  function shh_getFilterChanges($id) {
    return $this->ether_request(__FUNCTION__, array($id));
  }

  function shh_getMessages($id) {
    return $this->ether_request(__FUNCTION__, array($id));
  }
}

//  /**
//  *	Ethereum transaction object
//  */
//  class Ethereum_Transaction
//  {
//  private $to, $from, $gas, $gasPrice, $value, $data, $nonce;
//
//  function __construct($from, $to, $gas, $gasPrice, $value, $data='', $nonce=NULL) {
//    $this->from = $from;
//    $this->to = $to;
//    $this->gas = $gas;
//    $this->gasPrice = $gasPrice;
//    $this->value = $value;
//    $this->data = $data;
//    $this->nonce = $nonce;
//  }
//
//  function toArray() {
//    return array(
//      array
//      (
//        'from'=>$this->from,
//        'to'=>$this->to,
//        'gas'=>$this->gas,
//        'gasPrice'=>$this->gasPrice,
//        'value'=>$this->value,
//        'data'=>$this->data,
//        'nonce'=>$this->nonce
//      )
//    );
//  }
//  }
//
//  /**
//  *	Ethereum message -- Same as a transaction, except using this won't
//  *  post the transaction to the blockchain.
//  */
//  class Ethereum_Message extends Ethereum_Transaction
//  {
//
//  }
//
//  /**
//  *	Ethereum transaction filter object
//  */
//  class Ethereum_Filter
//  {
//  private $fromBlock, $toBlock, $address, $topics;
//
//  function __construct($fromBlock, $toBlock, $address, $topics) {
//    $this->fromBlock = $fromBlock;
//    $this->toBlock = $toBlock;
//    $this->address = $address;
//    $this->topics = $topics;
//  }
//
//  function toArray() {
//    return array(
//      array
//      (
//        'fromBlock'=>$this->fromBlock,
//        'toBlock'=>$this->toBlock,
//        'address'=>$this->address,
//        'topics'=>$this->topics
//      )
//    );
//  }
//}
//
///**
// * 	Ethereum whisper post object
// */
//class Whisper_Post
//{
//	private $from, $to, $topics, $payload, $priority, $ttl;
//
//	function __construct($from, $to, $topics, $payload, $priority, $ttl)
//	{
//		$this->from = $from;
//		$this->to = $to;
//		$this->topics = $topics;
//		$this->payload = $payload;
//		$this->priority = $priority;
//		$this->ttl = $ttl;
//	}
//
//	function toArray()
//	{
//		return array(
//			array
//			(
//				'from'=>$this->from,
//				'to'=>$this->to,
//				'topics'=>$this->topics,
//				'payload'=>$this->payload,
//				'priority'=>$this->priority,
//				'ttl'=>$this->ttl
//			)
//		);
//	}
//}
