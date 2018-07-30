<?php
class ispayService {

	public $payId;
	public $payKey;

	function __construct($payId, $payKey) {
		$this -> payId = $payId;
		$this -> payKey = $payKey;
	}

	function callbackSignCheck($Array) {
		if ($this -> Sign($Array) == $Array['callbackSign']) {
			return true;
		} else {
			return false;
		}
	}

	function callbackRequestCheck($Array) {
		$Url = "https://pay.ispay.cn/core/api/request/query/";
		$postData = array("payId" => $this -> payId, "orderNumber" => $Array['orderNumber']);
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $Url);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
		$Data = curl_exec($curl);
		curl_close($curl);
		$Data = json_decode($Data, true);
		if ($Data['State'] == 'success') {
			if ($Data['payChannel'] == $Array['payChannel']) {
				if ($Data['Money'] == $Array['Money']) {
					if ($Data['attachData'] == $Array['attachData']) {
						return true;
					} else {
						return false;
					}
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	function Sign($Array) {
		ksort($Array);
		$stringA = "";
		foreach ($Array as $k => $v) {
			if ($k != "Sign" && $k != "callbackSign") {
				$stringA .= $v;
			}
		}
		$stringSignTemp = $stringA . $this -> payKey;
		$Sign = md5($stringSignTemp);
		return $Sign;
	}

}
?>