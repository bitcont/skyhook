<?php

namespace Coinee\Sales;

use Exception,
	Config,
	DB,
	BitcoinAddress,
	Amount,
	Purchase as SkyhookPurchase,
	Coinee\ApiClient\Transfer;


class Purchase extends SkyhookPurchase
{

	public static function completeCoineeTransaction(Config $cfg, DB $db, SkyhookPurchase $p, $address, $btcAmount)
	{
		$w = $cfg->getWalletProvider();
		if ($p->isCompleted()) {
			throw new Exception("completeTransaction() was attempted twice on purchase ticket: " . $p->getId());
		}

		try {



			static::updateCustomerAddress($db, $p, $address);




			$db->beginTransaction();

			$tx = $w->sendTransaction(
				$p->getCustomerAddress(),
//				new BitcoinAddress(Transfer::COINEE_DEFAULT_BTC_ADDRESS),
				new Amount($btcAmount)
			);

			$p->setTXID($tx->getId())
				->setNTXID(self::normalizeTXID($tx->getId()))
				->setStatus(self::COMPLETE)
				->setMessage($tx->getMessage())
				->setNotice($tx->getNotice());

			self::finalize($db, $p);
			self::save($db, $p);

			$db->commit();

		} catch (Exception $e) {
			$db->rollback();
			$erroredOut = self::load($cfg, $db, $p->getId());
			$erroredOut->setStatus(self::ERROR);
			self::save($db, $erroredOut);
			throw $e;
		}
	}


//	public static function completeCoineeTransaction(Config $cfg, DB $db, SkyhookPurchase $p, $address)
//	{
//		$w = $cfg->getWalletProvider();
//		if ($p->isCompleted()) {
//			throw new Exception("completeTransaction() was attempted twice on purchase ticket: " . $p->getId());
//		}
//
//		$address = '1Ha6ev2dfTw6RARYiQETbS4MvurBsLysET';
//		static::updateCustomerAddress($db, $p, $address);
//
//
//		return parent::completeTransaction($cfg, $db, $p);
//	}


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





