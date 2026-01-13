<?php

namespace App\Http\Controllers\Api;

use App\Models\Donation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\DonationRequest;
use Illuminate\Support\Facades\Notification;

class DonationController extends Controller
{
    public function store(DonationRequest $request)
    {
        $donation = Donation::create($request->validated());
     Notification::route('mail', env('ADMIN_EMAIL'))
                    ->notify(new \App\Notifications\NewDonationNotification($donation));
        return response()->json([
            'message' => 'تم إرسال التبرع بنجاح',
            'data' => $donation
        ], 201);
    }
}
