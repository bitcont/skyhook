<?php

namespace Coinee\Traits;


trait Config
{

	/**
	 * Returns coinee settings
	 *
	 * @return array
	 */
	public function getCoinee() {
		return $this->data['coinee'];
	}
}

