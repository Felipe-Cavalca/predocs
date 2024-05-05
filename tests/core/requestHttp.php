<?php

namespace predocsTestCore;

use CurlHandle;

class requestHttp
{
    public CurlHandle $curl;

    public function __construct()
    {
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
    }

    public function __destruct()
    {
        curl_close($this->curl);
    }

    public function setUrl(string $url): void
    {
        curl_setopt($this->curl, CURLOPT_URL, $url);
    }

    public function request(string $method, array $data = []): string
    {
        switch ($method) {
            case "GET":
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "GET");
                break;
            case "POST":
            case "PUT":
            case "PATCH":
            case "DELETE":
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $method);
                curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);
                break;
        }
        return $this->execute();
    }

    public function execute(): string
    {
        $response = curl_exec($this->curl);
        return $response;
    }
}
