<?php

namespace Misery\Component\Common\Client;

use Exception;

class BasicAuthApiClient {
    private $baseUri;
    private $username;
    private $password;

    public function __construct($baseUri, $username, $password) {
        $this->baseUri = rtrim($baseUri, '/');
        $this->username = $username;
        $this->password = $password;
    }

    public function sendRequest($method, $endpoint, $data = null, $headers = []) {
        $url = $this->baseUri . '/' . ltrim($endpoint, '/');

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $authHeader = 'Authorization: Basic ' . base64_encode($this->username . ':' . $this->password);
        $headers[] = $authHeader;

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            $headers[] = 'Content-Type: application/json';
        }

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            throw new Exception('Curl error: ' . curl_error($curl));
        }

        curl_close($curl);

        return $response;
    }

    public function get($endpoint, $headers = []) {
        return $this->sendRequest('GET', $endpoint, null, $headers);
    }

    public function post($endpoint, $data = null, $headers = []) {
        return $this->sendRequest('POST', $endpoint, $data, $headers);
    }

    public function put($endpoint, $data = null, $headers = []) {
        return $this->sendRequest('PUT', $endpoint, $data, $headers);
    }

    public function delete($endpoint, $headers = []) {
        return $this->sendRequest('DELETE', $endpoint, null, $headers);
    }
}