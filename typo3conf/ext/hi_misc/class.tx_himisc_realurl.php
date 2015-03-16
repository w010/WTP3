<?php

/**
 * Misc function used in realurl config
 */
class tx_himisc_realurl {
	
	/**
	 * Universal function to add UID to RealURL processed title
	 * @param <type> $params
	 * @return string
	 */
	public function uidToTitle($params) {
		$values = $params['pObj']->orig_paramKeyValues;

		foreach ($values as $k=>$v) {
			if (in_array($k, array('id','cHash'))) continue;
			$uid = $v; break;
		}

		return $uid.'-'.$params['processedTitle'];
	}

	/**
	 * @param object $params
	 * @param object $ref
	 * @return integer
	 */
	public function recalculatePageNumber($params, $ref) {
		return $params['decodeAlias']
			? ($params['value'] ? $params['value']-1 : $params['value'])
			: ($params['value'] ? $params['value']+1 : $params['value']);
	}
}




if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hi_misc/class.tx_himisc_realurl.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hi_misc/class.tx_himisc_realurl.php']);
}