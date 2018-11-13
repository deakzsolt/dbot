<?php

namespace App\Http\Controllers;

use App\Trades;
use Illuminate\Http\Request;

class TradesController extends Controller
{
    /*
     *  Display the trader data
     */
    public function index()
    {
        $data = Trades::all();
        return view('trades.index', compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     */
    public function store(Request $request)
    {
        $trade = new Trades();
        $trade->fill($request->all());
        $trade->save();
    }
}
