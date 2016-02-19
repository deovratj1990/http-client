<?php
class HttpClient {
	private $response;
	private $error;

	public function __construct() {
		if(function_exists('curl_init')) {
			$this->reset();
		} else {
			trigger_error('cURL not installed', E_USER_ERROR);
		}
	}

	private function reset() {
		$this->response = '';
		$this->error = array(
			'code' => '',
			'text' => ''
		);
	}

	public function request($url, $data = array(), $method = '', $headers = array()) {
		$this->reset();

		if(!empty($url)) {
			$allowed_methods = array(
				'GET',
				'POST',
				'PUT',
				'DELETE'
			);

			$method = strtoupper(trim($method));
			if(!in_array($method, $allowed_methods)) {
				$method = 'GET';
			}

			$data_str = '';
			if(!empty($data)) {
				if(is_array($data)) {
					$data_str = http_build_query($data);
				} else {
					$data_str = trim($data, ' ?&');
				}

				if($method == 'GET' && !empty($data_str)) {
					if(strpos($url, '?') === false) {
						$url = $url . '?' . $data_str;
					} else {
						$url = $url . '&' . $data_str;
					}
				}
			}

			$curl = curl_init();

			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
			curl_setopt($curl, CURLOPT_URL, $url);

			if(!empty($headers) && is_array($headers)) {
				curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			}

			if($method == 'POST' || $method == 'PUT') {
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data_str);
			}

			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HEADER, false);

			$this->response = curl_exec($curl);

			curl_close($curl);

			return true;
		} else {
			$this->error['code'] = E_USER_WARNING;
			$this->error['text'] = 'URL not specified';
		}

		return false;
	}

	public function getResponse($format = 'RAW') {
		if(!empty($this->response)) {
			if($format == 'JSON') {
				return json_decode($this->response, true);
			}
		}

		return $this->response;
	}

	public function getError() {
		return $this->error;
	}
}
