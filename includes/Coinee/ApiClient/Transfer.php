<?php

namespace Coinee\ApiClient;

use Exception;
use Kdyby\Curl\Request;
use Kdyby\Curl\BadStatusException;


class Transfer {

	/**
	 * Dummy address to mimic default skyhook behavior.
	 *
	 * @var string
	 */
	const COINEE_DEFAULT_BTC_ADDRESS = '1NLCwZzzds3QJd941W1XvXdWVZpMWJo78p';

	/**
	 * Exception code.
	 *
	 * @var int
	 */
	const EXCEPTION_ZERO_AMOUNT = 1;

	/**
	 * Exception code.
	 *
	 * @var int
	 */
	const EXCEPTION_METHOD_NOT_AVAILABLE = 2;


//	/**
//	 * JSON decoded transfer info.
//	 *
//	 * @var array
//	 */
//	protected $transferInfo;

	/**
	 * resource URL.
	 *
	 * @var string
	 */
	protected $url;


	/**
	 * Class constructor.
	 *
	 * @param string $url
	 */
	protected function __construct($url)
	{
		$this->url = $url;
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


//	/**
//	 * Validates the transfer for given amount of fiat.
//	 *
//	 * @param string $entryAmount
//	 */
//	public function getInfo($entryAmount = NULL)
//	{
//		$url = $this->url;
//		if ($entryAmount !== NULL) $url .= '?entryAmount=' . $entryAmount;
//
//		$request = new Request($url);
//
//		try {
//			$response = $request->get();
//			$result = json_decode($response->getResponse(), TRUE);
////			$this->transferInfo = $result;
//			if (!$result['deliveryMethod']) throw new Exception('Delivery method not provided');
//			return $result;
//
//		} catch (BadStatusException $e) {
//			$result = json_decode($e->getResponse()->getResponse(), TRUE);
//
//			if ($result['message'] === 'Parameter amount must be a number greater than zero') {
//				throw new Exception('Zero amount', static::EXCEPTION_ZERO_AMOUNT);
//
//			} elseif ($result['message'] === 'Selected method is not available') {
//				throw new Exception('Zero amount', static::EXCEPTION_METHOD_NOT_AVAILABLE);
//			}
//
//			throw $e;
//		}
//	}


	/**
	 * Returns the transfer info for given amount of btc.
	 *
	 * @param string $entryAmount
	 */
	public function getInfo($btcAmount = NULL)
	{
		$url = $this->url;
		if ($btcAmount !== NULL) $url .= '?btcAmount=' . $btcAmount;

		$request = new Request($url);

//		print_r($url);

		try {
			$response = $request->get();
			$result = json_decode($response->getResponse(), TRUE);
//			$this->transferInfo = $result;
			if (!$result['deliveryMethod']) throw new Exception('Delivery method not provided');
			return $result;

		} catch (BadStatusException $e) {
			$result = json_decode($e->getResponse()->getResponse(), TRUE);

			if ($result['message'] === 'Parameter amount must be a number greater than zero') {
				throw new Exception('Zero amount', static::EXCEPTION_ZERO_AMOUNT);

			} elseif ($result['message'] === 'Selected method is not available') {
				throw new Exception('Selected delivery method not available', static::EXCEPTION_METHOD_NOT_AVAILABLE);
			}

			throw $e;
		}
	}


	/**
	 * Validates the transfer.
	 */
	public function validate()
	{
		$this->getInfo();
	}


//	/**
//	 * Returns JSON decoded transfer info.
//	 *
//	 * @return array
//	 */
//	public function getInfo()
//	{
//		return $this->transferInfo;
//	}


//	/**
//	 * Returns Coins.ph quotes.
//	 *
//	 * @param int $amount
//	 * @param bool $forceReload
//	 * @return array
//	 */
//	protected function getQuotes($amount = NULL, $forceReload = FALSE)
//	{
//		if ($this->quotes && !$forceReload) return $this->quotes;
//
//		$quoteUrl = $this->transferInfo->quoteUrl;
//		$request = new Request($quoteUrl);
//		$response = $request->get();
//		$this->quotes = json_decode($response->getResponse(), TRUE);
//		return $this->quotes;
//	}
//
//
//	/**
//	 * Returns Coins.ph quote.
//	 *
//	 * @param int $amount
//	 * @param bool $forceReload
//	 * @return array
//	 */
//	public function getQuote($amount = NULL, $forceReload = FALSE)
//	{
//		$quotes = $this->getQuotes($amount, $forceReload);
//
//		foreach ($quotes as $quote) {
//			if ($quote['id'] !== $this->getInfo()->deliveryMethod) {
//				$quote = NULL;
//
//			} else {
//				break;
//			}
//		}
//
//		if (!$quote) throw new Exception('Quote not found');
//
//
//
//
//		return $quote;
//
//	}
}

