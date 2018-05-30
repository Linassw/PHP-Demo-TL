<?php
use PHPUnit\Framework\TestCase;
use \Demo\Book;
use Demo\StorageSqlLite;

class BookTest extends TestCase
{ 
    public function testCase1()
    {
        $storage = new StorageSqlLite(__DIR__ . '/../storage/demo.db');
        $expected = '0.06';
        $actual = Book::getCommission('2016-01-05', 1, 'natural', 'cash_in', 200.00, 'EUR', $storage);
        $this->assertSame($expected, $actual);
    }

    public function testCase2()
    {
        $storage = new StorageSqlLite(__DIR__ . '/../storage/demo.db');
        $expected = '0.90';
        $actual = Book::getCommission('2016-01-06', 2, 'juridical', 'cash_out', 300.00, 'EUR', $storage);
        $this->assertSame($expected, $actual);
    }

    public function testCase4()
    {
        $storage = new StorageSqlLite(__DIR__ . '/../storage/demo.db');
        $expected = '0.70';
        $actual = Book::getCommission('2016-01-07', 1, 'natural', 'cash_out', 1000.00, 'EUR', $storage);
        $this->assertSame($expected, $actual);
    }
}
