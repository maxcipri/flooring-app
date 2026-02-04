<?php
class ShopifyClient {
	public $shop_domain;
	private $token;
	private $api_key;
	private $secret;
	private $last_response_headers = null;

	public function __construct($shop_domain, $token, $api_key, $secret) {
		$this->shop_domain = $shop_domain;
		$this->token = $token;
		$this->api_key = $api_key;
		$this->secret = $secret;
	}

	public function getAuthorizeUrl($scope, $redirect_url='') {
		$url = "https://{$this->shop_domain}/admin/oauth/authorize?client_id={$this->api_key}&scope=" . urlencode($scope);
		if ($redirect_url != '') {
			$url .= "&redirect_uri=" . urlencode($redirect_url);
		}
		return $url;
	}

	public function getAccessToken($code) {
		$url = "https://{$this->shop_domain}/admin/oauth/access_token";
		$payload = "client_id={$this->api_key}&client_secret={$this->secret}&code=$code";
		$response = $this->curlHttpApiRequest('POST', $url, '', $payload, array());
		$response = json_decode($response, true);
		if (isset($response['access_token']))
			return $response['access_token'];
		return '';
	}

	public function callsMade() {
		return $this->shopApiCallLimitParam(0);
	}

	public function callLimit() {
		return $this->shopApiCallLimitParam(1);
	}

	public function callsLeft($response_headers) {
		return $this->callLimit() - $this->callsMade();
	}

	public function call($method, $path, $params=array()) {
		$baseurl = "https://{$this->shop_domain}/";
		$url = $baseurl.ltrim($path, '/');
		$query = in_array($method, array('GET','DELETE')) ? $params : array();
		$payload = in_array($method, array('POST','PUT')) ? json_encode($params, JSON_UNESCAPED_SLASHES) : array();
		$request_headers = in_array($method, array('POST','PUT')) ? array("Content-Type: application/json; charset=utf-8", 'Expect:') : array();
		$request_headers[] = 'X-Shopify-Access-Token: ' . $this->token;
		$response = $this->curlHttpApiRequest($method, $url, $query, $payload, $request_headers);
		$response = json_decode($response, true);
		if (isset($response['errors']) or ($this->last_response_headers['http_status_code'] >= 400))
			throw new ShopifyApiException($method, $path, $params, $this->last_response_headers, $response);
		return (is_array($response) and (count($response) > 0)) ? array_shift($response) : $response;
	}

	private function curlHttpApiRequest($method, $url, $query='', $payload='', $request_headers=array()) {
		$url = $this->curlAppendQuery($url, $query);
		$ch = curl_init($url);
		$this->curlSetopts($ch, $method, $payload, $request_headers);
		$response = curl_exec($ch);
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$errno = curl_errno($ch);
		$error = curl_error($ch);
		curl_close($ch);
		if ($errno) throw new ShopifyCurlException($error, $errno);
		$message_headers = substr($response, 0, (int)$header_size);
		$message_body = substr($response, (int)$header_size);
		$this->last_response_headers = $this->curlParseHeaders($message_headers);
		return $message_body;
	}

	private function curlAppendQuery($url, $query) {
		if (empty($query)) return $url;
		if (is_array($query)) return "$url?".http_build_query($query);
		else return "$url?$query";
	}

	private function curlSetopts($ch, $method, $payload, $request_headers) {
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_USERAGENT, 'ohShopify-php-api-client');
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		if (!empty($request_headers)) curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
		if ($method != 'GET' && !empty($payload)) {
			if (is_array($payload)) $payload = http_build_query($payload);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		}
	}

	private function curlParseHeaders($message_headers) {
		$header_lines = preg_split("/\r\n|\n|\r/", $message_headers);
		$headers = array();
		if (count($header_lines) > 0) {
			$status_line = trim(array_shift($header_lines));
			$status_parts = explode(' ', $status_line, 3);
			$headers['http_status_code'] = isset($status_parts[1]) ? $status_parts[1] : '200';
			$headers['http_status_message'] = isset($status_parts[2]) ? $status_parts[2] : 'OK';
		}
		foreach ($header_lines as $header_line) {
			if (strpos($header_line, ':') === false) continue;
			list($name, $value) = explode(':', $header_line, 2);
			$name = strtolower($name);
			$headers[$name] = trim($value);
		}
		return $headers;
	}

	private function shopApiCallLimitParam($index) {
		if ($this->last_response_headers == null) {
			throw new Exception('Cannot be called before an API call.');
		}
		if (!isset($this->last_response_headers['http_x_shopify_shop_api_call_limit'])) {
			return 0;
		}
		$params = explode('/', $this->last_response_headers['http_x_shopify_shop_api_call_limit']);
		return isset($params[$index]) ? (int) $params[$index] : 0;
	}
}

class ShopifyCurlException extends Exception { }

class ShopifyApiException extends Exception {
	protected $method;
	protected $path;
	protected $params;
	protected $response_headers;
	protected $response;

	function __construct($method, $path, $params, $response_headers, $response) {
		$this->method = $method;
		$this->path = $path;
		$this->params = $params;
		$this->response_headers = $response_headers;
		$this->response = $response;
		parent::__construct($response_headers['http_status_message'], $response_headers['http_status_code']);
	}

	function getMethod() { return $this->method; }
	function getPath() { return $this->path; }
	function getParams() { return $this->params; }
	function getResponseHeaders() { return $this->response_headers; }
	function getResponse() { return $this->response; }
}
