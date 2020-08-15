<?php

namespace Hussaini\WebServices;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

abstract class BaseWebService
{
    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const PATCH = 'PATCH';
    const DELETE = 'DELETE';
    const HEAD = 'HEAD';

    protected Client $client;

    public function __construct($base_uri)
    {
        $this->client = new Client([
            'base_uri' => $base_uri,
        ]);
    }

    public function get(string $uri, array $options = []): array
    {
        return $this->request(self::GET, $uri, $options);
    }

    public function post(string $uri, array $options = []): array
    {
        return $this->request(self::POST, $uri, $options);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return array
     */
    public function request(string $method, string $uri, array $options = []): array
    {
        try {
            $response = $this->client->request($method, $uri, $options);
        } catch (RequestException $exception) {
            $response = $exception->getResponse();
        } catch (GuzzleException $e) {
            return [
                'body' => $e->getMessage(),
                'status_code' => 504,
                'headers' => [],
            ];
        }

        $headers = $this->parseHeaders($response->getHeaders());
        $status_code = $response->getStatusCode();
        $body = $this->parseContent($response->getBody()->getContents(), $headers['Content-Type']);

        return [
            'body' => $body,
            'status_code' => $status_code,
            'headers' => $headers,
        ];
    }

    private function parseHeaders(array $headers): array
    {
        $parsed_headers = [];

        foreach ($headers as $key => $value) {
            $parsed_headers[$key] = implode(', ', $value);
        }

        return $parsed_headers;
    }

    private function parseContent(string $body, string $content_type)
    {
        switch ($content_type) {
            case 'application/json; charset=utf-8':
                return json_decode($body, true);
            default:
                return $body;
        }
    }
}