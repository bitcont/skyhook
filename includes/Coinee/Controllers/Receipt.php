<?php

namespace Coinee\Controllers;


use Template;
use Container;
use Purchase;
use Admin as AdminConfig;
use Controllers\Controller;
use Coinee\ApiClient\Transfer;


class Receipt implements Controller
{

	public function execute(array $matches, $url, $rest)
	{
		$get = Container::dispense('Environment\Get');
		$cfg = AdminConfig::volatileLoad()->getConfig();
		$db = Container::dispense('DB');
		
		$p = Purchase::load(
			$cfg,
			$db,
			$matches['ticket']
		);
		
		$scanner = Container::dispense('BillScannerDriver');
		$scanner->stop();
		
		$error = false;
		
		if (!empty($get['error'])) {
			$error = true;
		}
		
		$sent = false;
		
		if (!empty($get['sent'])) {
			$sent = true;
		}




		$transferResourceBase64 = $matches['transfer'];
		$transfer = Transfer::findByResourceUrl(base64_decode($transferResourceBase64));

		$coineeConfig = $cfg->getCoinee();
		$userEmail = $coineeConfig['email'];
		$apiKey = $coineeConfig['apiKey'];

		$orderId = $matches['order'];
		$transfer->orderPaid($orderId, $userEmail, $apiKey);




		
		$tmpl = new Template('coinee/receipt');
		$tmpl->render([
			'purchase' => $p,
			'config' => $cfg,
			'error' => $error,
			'sent' => $sent,
		]);
		return true;
	}
}

