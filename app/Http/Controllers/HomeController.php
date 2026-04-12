<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HomeController extends Controller
{
    public function ask(Request $request)
    {
        $response = Http::post(env('RAG_API_URL') . '/ask', [
            'question' => $request->input('question'),
        ]);

        return $response->json();
    }
}
