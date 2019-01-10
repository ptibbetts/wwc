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
        $calculator = new Calculator();
        $sizes = [1,2,3];
        $calculator->setPackSizes($sizes);

        $calculation = $calculator->calculate(1);

        // $calculation = [
        //     [
        //         'contains' => 1,
        //         'quantity' => 1,
        //         'total' => 1
        //     ]
        // ];

        $this->assertEquals($calculation[0]['contains'], min($sizes));
        $this->assertEquals($calculation[0]['total'], min($sizes));
        $this->assertEquals($calculation[0]['quantity'], 1);
    }
}
