<?php

namespace Coinee\Controllers;

use Template;
use Admin as AdminConfig;
use Controllers\Controller;
use Controllers\ScannerStopper;
use Controllers\BalanceCacheUpdater;


class Account implements Controller {
	use ScannerStopper;
	use BalanceCacheUpdater;
	
	public function execute(array $matches, $url, $rest) {
		$this->stopScanner();
		$this->notifyBalanceChange();
		$admin = AdminConfig::volatileLoad();
		$contactInfo = $admin->getConfig()->getContactInformation();

		$tmpl = new Template('coinee/account');
		$tmpl->render([
			'contactInfo' => $contactInfo,
		]);
		return true;
	}
}

