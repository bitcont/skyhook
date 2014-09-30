<?php

namespace Coinee\Sales;

use Exception,
	Config,
	DB,
	BitcoinAddress,
	Coinee\ApiClient\Transfer,
	Purchase as SkyhookPurchase;


class Purchase extends SkyhookPurchase
{
	
	public static function completeCoineeTransaction(Config $cfg, DB $db, SkyhookPurchase $p, Transfer $transfer)
	{
		$w = $cfg->getWalletProvider();
		if ($p->isCompleted()) {
			throw new Exception("completeTransaction() was attempted twice on purchase ticket: " . $p->getId());
		}

		$address = '1Ha6ev2dfTw6RARYiQETbS4MvurBsLysET';
		static::updateCustomerAddress($db, $p, $address);


		return parent::completeTransaction($cfg, $db, $p);
	}


	protected static function updateCustomerAddress(DB $db, SkyhookPurchase $p, $address) {
		$stmt = $db->prepare('
			UPDATE `purchases`
			SET `customer_address` = :address
			WHERE `id` = :id
		');

		$result = $stmt->execute(array(
			':id' => $p->getId(),
			':address' => $address
		));

		$p->setCustomerAddress(new BitcoinAddress($address));

		return $result;
	}
}





