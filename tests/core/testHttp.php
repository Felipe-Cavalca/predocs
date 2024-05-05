<?php

namespace predocsTestCore;

use predocsTestCore\requestHttp;

trait testHttp
{
    public requestHttp $requestHttp;

    protected function setUp(): void
    {
        $this->requestHttp = new requestHttp();
        $this->requestHttp->setUrl("http://api/index/index");
    }

    public function testRequestGet(): void
    {
        $response = $this->requestHttp->request("GET");
        $this->validateStatusCode405($response);
    }

    public function testRequestPost(array $data = []): void
    {
        $response = $this->requestHttp->request("POST", $data);
        $this->validateStatusCode405($response);
    }

    public function testRequestPut(array $data = []): void
    {
        $response = $this->requestHttp->request("PUT", $data);
        $this->validateStatusCode405($response);
    }

    public function testRequestPatch(array $data = []): void
    {
        $response = $this->requestHttp->request("PATCH", $data);
        $this->validateStatusCode405($response);
    }

    public function testRequestDelete(array $data = []): void
    {
        $response = $this->requestHttp->request("DELETE", $data);
        $this->validateStatusCode405($response);
    }

    public function validateStatusCode405($response)
    {
        $response = json_decode($response, true);
        $this->assertEquals($response["statusCode"], 405);
    }
}
