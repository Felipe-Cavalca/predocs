<?php

use PHPUnit\Framework\TestCase;
use predocsTestCore\testHttpInterface;
use predocsTestCore\testHttp;
use predocsTestCore\requestHttp;

final class indexTest extends TestCase implements testHttpInterface
{
    use testHttp;

    protected function setUp(): void
    {
        $this->requestHttp = new requestHttp();
        $this->requestHttp->setUrl("http://api/index/index");
    }

    public function testRequestGet(): void
    {
        $response = $this->requestHttp->request("GET");
        $this->assertEquals($response, "Index");
    }
}
