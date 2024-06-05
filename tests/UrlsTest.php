<?php

namespace PageAnalyzer\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use App\Database\DB;
use App\Database\Urls;

class UrlsTest extends TestCase
{
    private Urls $object;
    private MockObject $database;
    public function setUp(): void
    {
        $this->object = new Urls();
        $this->database = $this->createMock(DB::class);
    }
    
    public function testGettingUrlById(): void
    {
        $id = 0;
        $pdo = $this->createMock(\PDO::class);
        $pdoStmt = $this->createMock(\PDOStatement::class);
        $expectedResult = [];
        
        $this->database->expects($this->any())
            ->method('connect')
            ->willReturn($pdo);

        $pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($pdoStmt);
        
        $pdoStmt->expects($this->once())
            ->method('execute');
        
        $pdoStmt->expects($this->once())
            ->method('fetch')
            ->willReturn($expectedResult);
        
        $result = $this->object->getUrlById($id);
        $this->assertEmpty($result);
    }
}