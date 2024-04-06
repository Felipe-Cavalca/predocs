<?php

use PHPUnit\Framework\TestCase;
use Predocs\Core\Database;

class DatabaseTest extends TestCase
{
    private $database;

    protected function setUp(): void
    {
        $this->database = new Database();
    }

    public function testWhere(): void
    {
        $conditions = ['field1' => 'value1', 'field2' => 'value2'];
        $expectedResult = 'field1 = :field1 AND field2 = :field2';
        $this->assertEquals($expectedResult, $this->database->where($conditions));
    }

    public function testInicializeTransaction(): void
    {
        $result = $this->database->inicializeTransaction();
        $this->assertTrue($result);
    }
}
