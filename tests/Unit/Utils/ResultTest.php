<?php
namespace Tests\Unit\Utils;

use PHPUnit\Framework\TestCase;
use Ferrox\Utils\Types\Result;

class ResultTest extends TestCase
{
    public function test_ok_result_can_be_unwrapped()
    {
        $result = Result::ok('ferrox_data');
        
        $this->assertTrue($result->isOk());
        $this->assertFalse($result->isErr());
        $this->assertEquals('ferrox_data', $result->unwrap());
    }

    public function test_err_result_throws_on_unwrap()
    {
        $this->expectException(\RuntimeException::class);
        
        $result = Result::err('Database offline');
        $result->unwrap(); // This should panic
    }

    public function test_err_result_returns_default()
    {
        $result = Result::err('Failed');
        $this->assertEquals('fallback', $result->unwrapOr('fallback'));
    }
}
