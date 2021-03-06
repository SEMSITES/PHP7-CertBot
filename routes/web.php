<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('certbot');
});

Route::get('/monitor', function () {
    // Expired certs scanned/updated in the past 2 days
    $expired = \App\Monitor\Certificate::whereDate('expires_at', '<', \Carbon\Carbon::today()->toDateString())
                                       ->whereDate('updated_at', '>', \Carbon\Carbon::today()->subDays(2)->toDateString())
                                       ->orderBy('expires_at')
                                       ->get();
    // JSONify the SANs
    foreach ($expired as $key => $value) {
        $expired[$key]->subjects = json_encode($value->san);
    }

    // Valid certs expiring in the next month scanned/updated in the past 2 days
    $expiring = \App\Monitor\Certificate::whereDate('expires_at', '>', \Carbon\Carbon::today()->toDateString())
                                        ->whereDate('expires_at', '<', \Carbon\Carbon::today()->addMonth()->toDateString())
                                        ->whereDate('updated_at', '>', \Carbon\Carbon::today()->subDays(2)->toDateString())
                                        ->orderBy('expires_at')
                                        ->get();
    // JSONify the SANs
    foreach ($expiring as $key => $value) {
        $expiring[$key]->subjects = json_encode($value->san);
    }

    // build the views data
    $data = [
            'expired'  => $expired,
            'expiring' => $expiring,
            ];

    return view('monitor', $data);
});
