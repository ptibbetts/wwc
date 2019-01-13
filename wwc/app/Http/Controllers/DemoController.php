<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Calculator;

class DemoController extends Controller
{
    public function index()
    {
        $calculator = new Calculator;

        $pack_sizes = $calculator->getPackSizes($asc = false);

        return view('demo', compact('pack_sizes'));
    }

    public function calculate(Request $request)
    {
        $pack_sizes = [];
        if ($request->has('packSizes')) {
            $pack_sizes = $request->packSizes;
        }
        $calculator = new Calculator($pack_sizes);

        $result = $calculator->calculate($request->input);
        return $result;
    }
}
