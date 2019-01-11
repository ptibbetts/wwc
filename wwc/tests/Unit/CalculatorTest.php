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

            $this->assertEquals(min($test['sizes']), $calculation['packs'][0]['contains']);
            $this->assertEquals(min($test['sizes']), $calculation['packs'][0]['total']);
            $this->assertEquals(1, $calculation['packs'][0]['quantity']);
        }
    }

    /** @test */
    public function it_uses_the_lowest_quantity_of_packs()
    {
        $sizes = [1,10];

        $calculator = new Calculator($sizes);

        $calculation = $calculator->calculate(10);

        $this->assertEquals(10, $calculation['packs'][0]['contains']);
        $this->assertEquals(1, $calculation['packs'][0]['quantity']);

        $this->assertNotEquals(1, $calculation['packs'][0]['contains']);
        $this->assertNotEquals(10, $calculation['packs'][0]['quantity']);
    }

    /** @test */
    public function it_works_with_multiple_quantities()
    {
        $sizes = [1,10];

        $calculator = new Calculator($sizes);

        $calculation = $calculator->calculate(20);

        $this->assertEquals(10, $calculation['packs'][0]['contains']);
        $this->assertEquals(2, $calculation['packs'][0]['quantity']);
    }

    /** @test */
    public function it_uses_the_least_amount_of_stickers()
    {
        // two digits
        $sizes = [1,10];
        $test_value = 11;

        $calculator = new Calculator($sizes);
        $calculation = $calculator->calculate($test_value);

        $this->assertEquals($test_value, $calculation['total']);

        $this->assertEquals(10, $calculation['packs'][0]['contains']);
        $this->assertEquals(1, $calculation['packs'][1]['contains']);

        // a different two digits
        $sizes = [1,10];
        $test_value = 22;

        $calculator = new Calculator($sizes);
        $calculation = $calculator->calculate($test_value);

        $this->assertEquals($test_value, $calculation['total']);

        $this->assertEquals(10, $calculation['packs'][0]['contains']);
        $this->assertEquals(2, $calculation['packs'][0]['quantity']);

        $this->assertEquals(1, $calculation['packs'][1]['contains']);
        $this->assertEquals(2, $calculation['packs'][1]['quantity']);

        // three digits
        $sizes = [10, 100, 1000];
        $calculator = new Calculator($sizes);
        $calculation = $calculator->calculate(195);

        $this->assertEquals(200, $calculation['total']);

        $this->assertEquals(100, $calculation['packs'][0]['contains']);
        $this->assertEquals(2, $calculation['packs'][0]['quantity']);
    }

    /** @test */
    public function it_passes_the_rules_set_in_the_brief()
    {
        $sizes = [250, 500, 1000, 2000, 5000];

        $calculator = new Calculator($sizes);

        $first = $calculator->calculate(1);
        $this->assertEquals(250, $first['total']);

        $second = $calculator->calculate(250);
        $this->assertEquals(250, $second['packs'][0]['contains']);

        $third = $calculator->calculate(251);
        $this->assertEquals(500, $third['total']);

        $fourth = $calculator->calculate(501);
        $this->assertEquals(750, $fourth['total']);

        $fifth = $calculator->calculate(12001);
        $this->assertEquals(12250, $fifth['total']);
    }
}
