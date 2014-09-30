<?php

namespace Coinee\Controllers;


use Container;
use Config;
use DB;
use Admin as AdminConfig;
use BitcoinAddress;
use Purchase;
use JobHandlers\MachineStatusEmail;
use Localization;
use Math;
use Controllers\Controller;
use Coinee\ApiClient\Transfer;


class StartPurchase implements Controller {


	public function execute(array $matches, $url, $rest) {

		$resourceBase64 = $matches['address'];
		Transfer::findByResourceUrl(base64_decode($resourceBase64))->validate();


//		$addr = new BitcoinAddress($matches['address']);
		$addr = new BitcoinAddress(Transfer::COINEE_DEFAULT_BTC_ADDRESS);
		$admin = AdminConfig::volatileLoad();
		$cfg = $admin->getConfig();
		$db = Container::dispense('DB');
		
		$ticket = Purchase::create(
			$cfg,
			$db,
			$addr
		);
		
		$wallet = $cfg->getWalletProvider();
		
		$i18n = Localization::getTranslator();
		$btcBalance = $wallet->getBalance();
		$balance = $btcBalance->multiplyBy($ticket->getBitcoinPrice());
		
		$threshhold = Math::max([
			$cfg->getMaxTransactionValue(),
			Math::max($cfg->getCurrencyMeta()->getDenominations())
		]);
		
		if ($balance->isLessThan($threshhold)) {
			MachineStatusEmail::reportError(
				$cfg,
				$db,
				$i18n->_('Low balance: ') . $btcBalance->get() . ' ' . $i18n->_('bitcoin')
			);
		}
		
		header('HTTP/1.1 303 See Other');
//		header('Location: /purchase/' . $addr->get() . '/' . $ticket->getId());
		header('Location: /coinee/purchase/' . $resourceBase64 . '/' . $ticket->getId());
		header('Cache-Control: no-cache, no-store, must-revalidate');
		header('Pragma: no-cache');
		header('Expires: 0');
		
		return true;
	}
}

