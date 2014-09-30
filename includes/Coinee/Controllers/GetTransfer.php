<?php

namespace Coinee\Controllers;

//use Exception;
use Controllers\Controller;
use AddressUtility;
use JSON;
use Coinee\ApiClient\Transfer;


class GetTransfer implements Controller {

	public function execute(array $matches, $url, $rest) {

		$address = parse_url($matches['address'], PHP_URL_PATH);
		$address = base64_decode($address);

		try {
			Transfer::findByResourceUrl($address)->validate();

			echo JSON::encode([
				'valid' => true
			]);

		} catch (Exception $e) {

			echo JSON::encode([
				'valid' => false,
				'error' => $e->getMessage()
			]);
		}

		return true;
	}
}

