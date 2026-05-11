<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard.index');
    }

    public function ask(Request $request)
    {
        $response = Http::post(env('RAG_API_URL') . '/ask', [
            'question' => $request->input('question'),
        ]);

        return $response->json();
    }
}
