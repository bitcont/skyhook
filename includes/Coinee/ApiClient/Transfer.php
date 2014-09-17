<?php

namespace Coinee\ApiClient;

use Exception;
use Kdyby\Curl\Request;


class Transfer {

	/**
	 * Dummy address to mimic default skyhook behavior.
	 * @var string
	 */
	const BTC_ADDRESS = '1NLCwZzzds3QJd941W1XvXdWVZpMWJo78p';


	/**
	 * JSON decoded transfer info.
	 * @var stdClass
	 */
	protected $transferInfo;


	/**
	 * Class constructor.
	 *
	 * @param string $url
	 */
	public function __construct($url)
	{
		$request = new Request($url);
		$response = $request->get();
		$result = json_decode($response->getResponse());

		if (!$result->deliveryMethod) throw new Exception('Delivery method not provided');

		$this->transferInfo = $result;
	}


	/**
	 * Returns transfer specified by API resource URL.
	 *
	 * @param string $url
	 * @return self
	 */
	public static function findByResourceUrl($url)
	{
		return new static($url);
	}


	/**
	 * Returns JSON decoded transfer info.
	 *
	 * @return stdClass
	 */
	public function getInfo()
	{
		return $this->transferInfo;
	}
}

