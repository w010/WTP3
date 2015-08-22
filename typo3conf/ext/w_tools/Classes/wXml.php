<?php

/**
 * wXml
 * WTP XML little helper
 * version 0.1.1
 * wolo.pl TYPO3 pack
 *
 * Note that this can be a standalone class, so it doesn't fit into typo3 naming standards and doesn't use any typo3 functions
 */
class wXml	{

	protected $error = false;


	/**
	 * @param string $url
	 * @param bool $sslBypass
	 * @return bool|SimpleXMLElement
	 */
	public function getXmlFromUrl($url, $sslBypass = true)	{
		ob_start();

		$ch = curl_init($url);

		//curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		if ($sslBypass)	{
			// nie sprawdza certyfikatu
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}

		$xml = curl_exec($ch);

		if (curl_errno($ch)) {
			$this->error = true;
			ob_clean();
			return false;
		}

		curl_close($ch);
		$rawXml = ob_get_contents();
		ob_clean();

		return simplexml_load_string($rawXml);
	}


	/**
	 * import xml from file
	 *
	 * @param string $path
	 * @return SimpleXMLElement
	 */
	public function getXmlFromFile($path)	{

		$XML = simplexml_load_file($path);

		return $XML;
	}
}


?>