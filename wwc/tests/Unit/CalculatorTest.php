<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Calculator;

class CalculatorTest extends TestCase
{
    /** @test */
    public function it_uses_the_smallest_pack_size()
    {
        $tests = [
            ['answer' => 1, 'sizes' => [1,2,3]],
            ['answer' => 4, 'sizes' => [4,5,6]],
            ['answer' => 1000, 'sizes' => [1000,5000,10000]]
        ];

        foreach ($tests as $test) {
            $calculator = new Calculator($test['sizes']);

            $calculation = $calculator->calculate($test['answer']);

            $this->assertEquals($calculation['packs'][0]['contains'], min($test['sizes']));
            $this->assertEquals($calculation['packs'][0]['total'], min($test['sizes']));
            $this->assertEquals($calculation['packs'][0]['quantity'], 1);
        }
    }
}
