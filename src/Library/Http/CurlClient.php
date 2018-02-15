<?php

namespace Library\Http;

class CurlClient implements ClientInterface {

	/**
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return mixed
     * @throws HttpException
     * @internal param array $body
     * @internal param array $headers
     */
	public function request($method, $uri, array $options)
	{
		$headers = $options['headers'];
		$body = $options['body'];

		$processed_headers = array();

		if (!empty($headers)) {
			foreach($headers as $key => $value) {
				$processed_headers[] = $key . ': ' . $value;
			}
		}

		$postData = json_decode($body, true);

		if (isset($postData['file'])) {
			$boundary = uniqid();
			$delimiter = '-------------' . $boundary;
			$files[$postData['file']] = file_get_contents($postData['file']);
			$body = $this->build_data_files($boundary, $postData, $files);
			$processed_headers[0] = "Content-Type: multipart/form-data; boundary=" . $delimiter;
		}

		$processed_headers[] = "Content-Length: " . strlen($body);

		$uri = trim($uri,'/');
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $processed_headers);
		curl_setopt($ch, CURLOPT_TIMEOUT, 120);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 500);

		$response = curl_exec($ch);
		$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ($responseCode != 200) {
			curl_close($ch);
			throw new HttpException('Error: Unexpected HTTP code - '. $responseCode);
		}

		curl_close($ch);
		return $response;
	}

	private function build_data_files($boundary, $fields, $files)
	{
	    $data = '';
	    $eol = "\r\n";
	    $delimiter = '-------------' . $boundary;
		foreach ($fields as $name => $content) {
			$data .= "--" . $delimiter . $eol
	            . 'Content-Disposition: form-data; name="' . $name . "\"".$eol.$eol
	            . $content . $eol;
	    }

	    foreach ($files as $name => $content) {
	        $data .= "--" . $delimiter . $eol
	            . 'Content-Disposition: form-data; name="file"; filename="' . $name . '"' . $eol
	            . 'Content-Transfer-Encoding: binary'.$eol;

	        $data .= $eol;
	        $data .= $content . $eol;
	    }
	    $data .= "--" . $delimiter . "--".$eol;

	    return $data;
	}

}
