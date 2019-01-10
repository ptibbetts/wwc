<?php

namespace App;

class Calculator
{
    /**
     * Default Pack Sizes
     *
     * @var array
     */
    protected $pack_sizes = [
        250,
        500,
        1000,
        2000,
        5000
    ];

    public function __construct(array $pack_sizes = [])
    {
        if ($pack_sizes) {
            $this->setPackSizes($pack_sizes);
        }
    }

    public function setPackSizes(array $sizes)
    {
        $this->pack_sizes = $sizes;
    }

    public function getPackSizes()
    {
        return $this->pack_sizes;
    }

    public function calculate($input)
    {
        // using the $input
        // find the smallest amount
        // of the smallest pack size
        // that is either $input or 1 over it

        sort($this->pack_sizes);

        foreach ($this->pack_sizes as $size) {
            if ($size === $input) {
                return [
                    'total' => $size,
                    'packs' => [
                        'contains' => $size,
                        'quantity' => 1,
                        'total' => $size
                    ]
                ];
            }
        }
    }
}
