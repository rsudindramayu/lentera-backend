<?php

namespace App\Modules\BPJSVclaim\Services;

use \LZCompressor\LZString;

class RequestService
{
    protected $client;

    public function __construct()
    {
        $this->client = service('curlrequest', ['timeout' => env('REQUEST_TIMEOUT')]);
    }
    public function sendRequest($method, $url, $body = null)
    {
        $signature = $this->generateSignature();
        $options = [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => $method != 'GET' ? 'application/x-www-form-urlencoded' : 'application/json',
                'x-cons-id' => env('CONS_ID'),
                'x-timestamp' => $signature['t'],
                'x-signature' => $signature['s'],
                'user_key' => env('USER_KEY')
            ]
        ];
        if (isset($body)) {
            $data = [
                'body' => $body
            ];
            $options = array_merge($options, $data);
        }

        try {
            $response = $this->client->request($method, $url, $options);
            $statusCode = $response->getStatusCode();
            if ($statusCode == 200) {
                $body = $response->getBody();
                $result = json_decode($body);
                if (isset($result->metaData)) {
                    if ($result->metaData->code == "200") {
                        $key = env('CONS_ID') . env('SECRET_KEY') . $signature['t'];
                        if ($result->response != null) $data = $this->stringDecrypt($key, $result->response);
                        return [
                            'status' => true,
                            'message' => $result->metaData->message,
                            'data' => $data ?? null,
                        ];
                    } else {
                        return [
                            'status' => false,
                            'message' => $result->metaData->message,
                        ];
                    }
                }
            }

            return [
                'status' => false,
                'message' => 'Request Failed',
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e
            ];
        }
    }

    public function sendAntreanRequest($method, $url, $body = null)
    {
        $signature = $this->generateSignature();
        $options = [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => $method != 'GET' ? 'application/x-www-form-urlencoded' : 'application/json',
                'x-cons-id' => env('CONS_ID'),
                'x-timestamp' => $signature['t'],
                'x-signature' => $signature['s'],
                'user_key' => env('USER_KEY_ANTROL')
            ]
        ];
        if (isset($body)) {
            $data = [
                'body' => $body
            ];
            $options = array_merge($options, $data);
        }

        try {
            $response = $this->client->request($method, $url, $options);
            $statusCode = $response->getStatusCode();
            if ($statusCode == 200) {
                $body = $response->getBody();
                $result = json_decode($body);
                if (isset($result->metadata)) {
                    if ($result->metadata->code == "200") {
                        $key = env('CONS_ID') . env('SECRET_KEY') . $signature['t'];
                        if (isset($result->response) && $result->response != null) $data = $this->stringDecrypt($key, $result->response);
                        return [
                            'status' => true,
                            'message' => $result->metadata->message,
                            'data' => $data ?? null,
                        ];
                    } else {
                        return [
                            'status' => false,
                            'message' => $result->metadata->message,
                        ];
                    }
                }
            }

            return [
                'status' => false,
                'message' => 'Request Failed',
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e
            ];
        }
    }

    public function sendAntreanRSRequest($method, $url, $body = null, $token = null)
    {
        try {
            $options = [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'x-token' => $token,
                    'x-username' => env('X_USERNAME_ANTROL_SIMGOS'),
                    'x-password' => env('X_PASSWORD_ANTROL_SIMGOS')
                ]
            ];

            if (isset($body)) {
                $data = ['body' => $body];
                $options = array_merge($options, $data);
            }
            $response = $this->client->request($method, $url, $options);
            $statusCode = $response->getStatusCode();
            if ($statusCode == 200) {
                $json = $response->getBody();
                $result = json_decode($json, true);
                if ((isset($result['status']) && $result['status'] == '200') || (isset($result['metadata']) && $result['metadata']['code'] == '200')) {
                    return [
                        'status' => true,
                        'result' => $result
                    ];
                } else {
                    return [
                        'status' => false,
                        'result' => $result
                    ];
                }
            }

            return [
                'status' => false,
                'message' => 'tidak terhubung dengan Server',
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e
            ];
        }
    }

    private function generateSignature()
    {
        date_default_timezone_set('UTC');
        $tStamp = strval(time() - strtotime('1970-01-01 00:00:00'));
        $signature = hash_hmac('sha256', env('CONS_ID') . "&" . $tStamp, env('SECRET_KEY'), true);
        $encodedSignature = base64_encode($signature);
        return [
            't' => $tStamp,
            's' => $encodedSignature
        ];
    }

    private function stringDecrypt($key, $ciphertext)
    {
        $encrypt_method = 'AES-256-CBC';
        $key_hash = hex2bin(hash('sha256', $key));
        $iv = substr(hex2bin(hash('sha256', $key)), 0, 16);
        $output = openssl_decrypt(base64_decode($ciphertext), $encrypt_method, $key_hash, OPENSSL_RAW_DATA, $iv);
        $result = LZString::decompressFromEncodedURIComponent($output);
        return json_decode($result);
    }
}
