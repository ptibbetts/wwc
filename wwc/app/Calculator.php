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

    /**
     * Packs required to fulfil order
     *
     * @var array
     */
    protected $packs = [];

    /**
     * Total number of cards required to fulfil order
     *
     * @var integer
     */
    protected $total = 0;

    /**
     * @param array $pack_sizes
     */
    public function __construct(array $pack_sizes = [])
    {
        if ($pack_sizes) {
            $this->setPackSizes($pack_sizes);
        }
    }

    /**
     * Sets pack_sizes property
     *
     * @param array $sizes
     * @return void
     */
    public function setPackSizes(array $sizes)
    {
        $this->pack_sizes = $sizes;
    }

    /**
     * Gets Pack sizes in ASC or DESC order
     *
     * @param boolean $asc = true
     * @return array
     */
    public function getPackSizes($asc = true)
    {
        if ($asc) {
            $ascending = $this->pack_sizes;
            sort($ascending);
            return $ascending;
        }

        $descending = $this->pack_sizes;
        arsort($descending);

        return $descending;
    }

    /**
     * Clears the order
     *
     * @return void
     */
    private function reset()
    {
        $this->packs = [];
        $this->total = 0;
    }

    /**
     * Given a target, find packs required to fulfil order
     *
     * @param integer $input
     * @return array
     */
    public function calculate($input)
    {
        $this->reset();

        // if there is a pack with the desired amount
        if (in_array($input, $this->getPackSizes())) {
            $this->addPack($input);

            return $this->getResult();
        }

        // if the desired amount is less than the smallest pack size
        $smallest_size = $this->getSmallestPack();
        if ($input < $smallest_size) {
            $this->addPack($smallest_size);

            return $this->getResult();
        }

        // if not find a combination of packs
        $remaining = $input;
        $i = 0;

        while ($remaining >= 1) {
            ${'possibles_'.$i} = [];
            $all = [];

            if ($remaining < $smallest_size) {
                $this->addPack($smallest_size);
                return $this->getResult();
            }

            // work out which packs would fulfil the order
            foreach ($this->getPackSizes($asc = false) as $size) {
                $ratio = $remaining / $size;

                if ($ratio >= 1) {
                    ${'possibles_'.$i}[$size] = $ratio;
                }
            }

            if (${'possibles_'.$i}) { // there are packs that would fulfil the order
                asort(${'possibles_'.$i}); // sort ASC
                $old_remaining = $remaining;

                $smaller_size = array_key_first(${'possibles_'.$i});
                $smaller_quantity = floor(array_values(${'possibles_'.$i})[0]);

                $remaining = $remaining - ($smaller_quantity * $smaller_size);

                if ($remaining >= 1) {
                    foreach ($this->getPackSizes($asc = false) as $size) {
                        $ratio = $remaining / $size;
                        $all_ratios[$size] = $ratio;
                    }

                    arsort($all_ratios);

                    $bigger_size = array_keys($all_ratios)[1];
                    $ceiled = ceil(array_values($all_ratios)[1]);

                    $bigger_amount = $bigger_size * $ceiled;
                    $bigger_wasted = (($bigger_amount * 2) - $old_remaining);

                    $smaller_amount = $smaller_size * $smaller_quantity;
                    $smaller_wasted = ($old_remaining - $smaller_amount);

                    if (($bigger_amount * 2) > $old_remaining && // if 2 of the bigger size would cover it
                        $bigger_wasted < $smaller_wasted) { // and there are less wasted than using the smaller pack
                        $remaining = $remaining - (($ceiled * 2) * $bigger_size);
                        $this->addPack($bigger_size, ($ceiled * 2)); // use 2x the bigger pack
                    } elseif ($bigger_amount > $old_remaining) {
                        $this->addPack($bigger_size, $ceiled); // use the bigger pack
                        $remaining = $remaining - $bigger_amount;
                    } else {
                        $this->addPack($smaller_size, $smaller_quantity); // use the smaller pack
                    }
                    $i++; // proceed to a smaller pack size
                } else {
                    $this->addPack($smaller_size, $smaller_quantity); // use the smaller pack
                }
            } else {
                // the remaining is smaller than the smallest pack
                $this->addSmallestPack();
            }
        }

        return $this->getResult();
    }

    /**
     * Returns the result
     *
     * @return array
     */
    private function getResult()
    {
        return [
            'total' => $this->total,
            'packs' => $this->packs
        ];
    }

    /**
     * Adds a pack to the order
     *
     * @param integer $amount
     * @param integer $quantity
     * @return void
     */
    private function addPack($amount, $quantity = 1)
    {
        $total = $amount * $quantity;
        $this->total = $this->total + $total;
        array_push($this->packs, [
                'contains' => $amount,
                'quantity' => $quantity,
                'total' => $total
            ]);
    }

    /**
     * Returns the smallest pack size
     *
     * @return integer
     */
    private function getSmallestPack()
    {
        return array_values($this->getPackSizes($asc = true))[0];
    }

    /**
     * Adds the smallest pack size to the order
     *
     * @return void
     */
    private function addSmallestPack()
    {
        $this->addPack($this->getSmallestPack());
    }
}
