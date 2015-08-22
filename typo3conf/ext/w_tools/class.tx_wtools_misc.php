<?php

// move to Classes

class tx_wtools_misc {
	
	/*
	 * Process/modify top menu in page header
	 */
	function topMenuIProcFunc($I,$conf)	{
		switch ($I['uid']) {
			case 64: // Twoje konto
				$split = t3lib_div::trimExplode('|', $I['parts']['title'] );
				if ($GLOBALS['TSFE']->fe_user->user)    {
					$I['parts']['title'] = $split[1]. ' <b><!--###USERNAME###--></b>';
				} else  {
					$I['parts']['title'] = $split[0];
				}
				break;
			default:
				break;
		}
		return $I;
	}

	/**
	 * to z jakiegos powodu nie dziala, nie wywoluje sie
	 */
	function rekrutacjaMenuIProcFunc($I,$conf)	{
		switch ($I['uid']) {

			case 205: // menu/rejestracja usera
			case 206: // menu/rejestracja firmy

				$split = t3lib_div::trimExplode('|', $I['parts']['title'] );
				if ($GLOBALS['TSFE']->fe_user->user)    {
					$I['parts']['title'] = $split[1]. ' <b><!--###USERNAME###--></b>';
				} else  {
					$I['parts']['title'] = $split[0];
				}
				break;
			default:
				break;
		}
		return $I;
	}
}
