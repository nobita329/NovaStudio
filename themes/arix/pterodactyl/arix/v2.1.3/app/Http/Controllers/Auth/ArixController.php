<?php

namespace Pterodactyl\Http\Controllers\Auth;

use Illuminate\Support\Facades\Http;

class ArixController extends AbstractLoginController
{
    public function index(): object
    {

        $endpoint = 'https://arix.gg/internal/check';
    
        $response = Http::asForm()->post($endpoint, [
            'license' => 'ARIX-CHECK',
        ]);
    
        $responseData = $response->json();
    
        if (!$responseData['success']) {
            return response()->json([
                'status' => 'Not available'
            ]);
        }

        return response()->json([
            'NONCE' => '65f86e8726030eccf04bea9e4f7f4ba1',
            'ID' => '578839',
            'USERNAME' => 'anoop111',
            'TIMESTAMP' => '1786879177'
        ]);
    }
}