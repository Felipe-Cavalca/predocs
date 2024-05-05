<?php

use PHPUnit\Framework\TestCase;
use predocsTestCore\testHttpInterface;
use predocsTestCore\testHttp;
use predocsTestCore\requestHttp;

final class dataTest extends TestCase implements testHttpInterface
{
    use testHttp;

    protected function setUp(): void
    {
        $this->requestHttp = new requestHttp();
        $this->requestHttp->setUrl("http://api/index/data");
    }

    public function testRequestPost(): void
    {
        $data = [
            "id" => 1,
            "email" => "felipe@email.com",
            "numero" => 123,
        ];
        $this->requestHttp->setUrl("http://api/index/data?id=".$data["id"]);
        $response = $this->requestHttp->request("POST", $data);
        $response = json_decode($response, true);
        $this->assertEquals($response, $data);
    }
}
