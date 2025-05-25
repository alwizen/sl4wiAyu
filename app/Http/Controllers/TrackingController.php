<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function showForm()
    {
        return view('tracking.form');
    }

    public function check(Request $request)
    {
        $delivery = Delivery::where('delivery_number', $request->delivery_number)->first();

        if (!$delivery) {
            return back()->withErrors(['delivery_number' => 'Nomor pengiriman tidak ditemukan.']);
        }

        return view('tracking.result', compact('delivery'));
    }
}
