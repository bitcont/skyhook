<?php

namespace Coinee\Controllers;


use Exception;
use Exceptions\InsufficientFundsException;
use JobManager;
use Controllers\Controller;
use Container;
use Admin;
use Amount;
use Config;
use JSON;
use Template;
use DB;
use BitcoinAddress;
use Purchase;
use BillScannerDriver;
use Controllers\BalanceCacheUpdater;
use Coinee\Sales\Purchase as CoineePurchase;
use Coinee\ApiClient\Transfer;


class FinalizePurchase implements Controller {

	use BalanceCacheUpdater;


	public function execute(array $matches, $url, $rest) {
		$admin = Admin::volatileLoad();
		$cfg = $admin->getConfig();
		$db = Container::dispense('DB');
		$scanner = new BillScannerDriver();
		
		$ticket = Purchase::load($cfg, $db, intval($matches['ticket']));
		$response = [];
		
		$currencyEntered = $ticket->getCurrencyAmount();
		$zero = new Amount("0");
		
		if ($currencyEntered->isEqualTo($zero)
		|| $currencyEntered->isLessThan($zero)) {
			echo JSON::encode(['cancelTransaction' => true]);
			return true;
		}
		
		if ($ticket->getStatus() !== Purchase::PENDING) {
			header('Cache-Control: no-cache, no-store, must-revalidate');
			header('Pragma: no-cache');
			header('Expires: 0');
			echo JSON::encode(['redirect' => '/start']);
			return true;
		}
		
		$scanner->stop();
		
		try {

			$coineeConfig = $cfg->getCoinee();
			$userEmail = $coineeConfig['email'];
			$apiKey = $coineeConfig['apiKey'];


			// get current btc amount
			$btcAmount = $ticket->recalculateBitcoinAmount()->get();

			// get address for given btc amount
			$transferResourceBase64 = $matches['transfer'];
			$transfer = Transfer::findByResourceUrl(base64_decode($transferResourceBase64));
			$order = $transfer->createOrder($btcAmount, $userEmail, $apiKey);

			// finish the transaction
			CoineePurchase::completeCoineeTransaction($cfg, $db, $ticket, $order['payToAddress'], $order['btcAmount']);
			$response['orderId'] = $order['id'];


			$this->notifyBalanceChange();
			$response['proceed'] = true;

		} catch (Exception $e) {


//			throw $e;


			if ($e instanceof InsufficientFundsException) {
				$response['insufficient'] = true;
			}
			file_put_contents(
				'/home/pi/phplog/purchase_errors.' . intval($matches['ticket']) . '.log',
				$e,
				FILE_APPEND
			);
			$response['error'] = true;
			JobManager::enqueue(
				$db,
				'PurchaseError',
				['purchase_id' => $ticket->getId()]
			);
			$this->notifyBalanceChange();
		}
		
		echo JSON::encode($response);
		
		return true;
	}
}
