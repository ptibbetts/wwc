<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Calculator;

class CalculatorTest extends TestCase
{
    /**
     * Pack Sizs from the brief
     *
     * @var array
     */
    protected $brief_sizes = [250, 500, 1000, 2000, 5000];

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

        $this->assertEquals(20, $calculation['total']);

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
        $calculator = new Calculator($this->brief_sizes);

        $first = $calculator->calculate(1);
        $this->assertEquals(250, $first['total']);
        $this->assertEquals(1, $first['packs'][0]['quantity']);
        $this->assertEquals(250, $first['packs'][0]['contains']);

        $second = $calculator->calculate(250);
        $this->assertEquals(250, $second['packs'][0]['contains']);
        $this->assertEquals(1, $second['packs'][0]['quantity']);
        $this->assertEquals(250, $second['packs'][0]['contains']);

        $third = $calculator->calculate(251);
        $this->assertEquals(500, $third['total']);
        $this->assertEquals(1, $third['packs'][0]['quantity']);
        $this->assertEquals(500, $third['packs'][0]['contains']);

        $fourth = $calculator->calculate(501);
        $this->assertEquals(750, $fourth['total']);
        $this->assertEquals(500, $fourth['packs'][0]['contains']);
        $this->assertEquals(250, $fourth['packs'][1]['contains']);

        $fifth = $calculator->calculate(12001);
        $this->assertEquals(12250, $fifth['total']);
        $this->assertEquals(5000, $fifth['packs'][0]['contains']);
        $this->assertEquals(2, $fifth['packs'][0]['quantity']);
        $this->assertEquals(1, $fifth['packs'][1]['quantity']);
        $this->assertEquals(2000, $fifth['packs'][1]['contains']);
        $this->assertEquals(1, $fifth['packs'][2]['quantity']);
        $this->assertEquals(250, $fifth['packs'][2]['contains']);
    }

    /** @test */
    public function it_doesnt_send_2_500_boxes_for_999()
    {
        $calculator = new Calculator($this->brief_sizes);

        $result = $calculator->calculate(999);
        $this->assertEquals(1000, $result['packs'][0]['contains']);
    }

    /** @test */
    public function it_doesnt_send_20_500_boxes_for_9999()
    {
        $calculator = new Calculator($this->brief_sizes);

        $result = $calculator->calculate(9999);
        $this->assertEquals(5000, $result['packs'][0]['contains']);
        $this->assertEquals(2, $result['packs'][0]['quantity']);
        $this->assertEquals(10000, $result['total']);
    }
}
