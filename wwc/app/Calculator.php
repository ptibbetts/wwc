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
     * Total number of cards remaining to fulfil order
     *
     * @var integer
     */
    protected $remaining = 0;

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

        // if a developer is trying to break it
        if ($input <= 0) {
            $this->addPack(0, 0);
            return $this->getResult();
        }

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
        $this->remaining = $input;
        $i = 0;

        while ($this->remaining >= 1) {
            ${'possibles_'.$i} = [];
            $all = [];

            if ($this->remaining < $smallest_size) {
                $this->addPack($smallest_size);
                return $this->getResult();
            }

            // work out which packs would fulfil the order
            foreach ($this->getPackSizes($asc = false) as $size) {
                $ratio = $this->remaining / $size;

                if ($ratio >= 1) {
                    ${'possibles_'.$i}[$size] = $ratio;
                }
            }

            if (${'possibles_'.$i}) { // there are packs that would fulfil the order
                asort(${'possibles_'.$i}); // sort ASC

                if ($this->remaining >= 1) {
                    foreach ($this->getPackSizes($asc = false) as $size) {
                        $ratio = $this->remaining / $size;
                        $all_ratios[$size] = $ratio;
                    }

                    asort($all_ratios);

                    $i = 0;
                    foreach ($all_ratios as $size => $ratio) {
                        if ($ratio >= 1) {
                            $contains = $size;
                            $quantity = ceil($ratio);

                            if (ceil($ratio) >= 2) {
                                if (! is_int($ratio)) {
                                    if (array_key_exists($i - 1, array_keys($all_ratios))) {
                                        $wasted = $this->remaining - ($contains * $quantity);
                                        $bigger_wasted = $this->remaining - array_keys($all_ratios)[$i - 1];
                                        if (array_keys($all_ratios)[$i - 1] >= $this->remaining && abs($bigger_wasted) <= abs($wasted)) {
                                            if (array_key_exists($i + 1, array_keys($all_ratios))) {
                                                $smaller = array_keys($all_ratios)[$i+1];
                                                if (($this->remaining - ($contains + $smaller)) < abs($wasted)) {
                                                    $this->addPack($smaller, 1);
                                                    break;
                                                } else {
                                                    $contains = array_keys($all_ratios)[$i - 1];
                                                    $quantity = ceil(array_values($all_ratios)[$i - 1]);
                                                }
                                            } else {
                                                if (array_keys($all_ratios)[$i - 1] > $this->remaining) {
                                                    $larger = array_keys($all_ratios)[$i - 1];
                                                    $this->addPack($larger, 1);
                                                    break;
                                                }
                                            }
                                        } else {
                                            if (($contains * $quantity) >= $this->remaining) {
                                                $quantity = $quantity - 1;
                                            }
                                        }
                                    } else {
                                        $quantity = $quantity - 1;
                                    }
                                }
                            }
                            $this->addPack($contains, $quantity);
                            break;
                        }
                        $i++;
                    }
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
     * and sets the quantity required for each size
     *
     * @return array
     */
    private function getResult()
    {
        $packs = [];
        foreach ($this->packs as $pack) {
            $size = $pack['contains'];
            if (array_key_exists($size, $packs)) {
                $packs[$size]['quantity'] = $packs[$size]['quantity'] + $pack['quantity'];
                $packs[$size]['total'] = $packs[$size]['total'] + $pack['total'];
            } else {
                $packs[$size]['quantity'] = $pack['quantity'];
                $packs[$size]['total'] = $pack['total'];
            }
            $packs[$size]['contains'] = $pack['contains'];
        }

        return [
            'total' => $this->total,
            'packs' => array_values($packs)
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
        if ($amount <= 0) {
            return;
        }
        $total = $amount * $quantity;
        $this->remaining = $this->remaining - $total;
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
