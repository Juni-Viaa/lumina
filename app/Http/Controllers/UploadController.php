<?php

namespace App\Http\Controllers;

use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UploadController extends Controller
{
    public function index(): View
    {
        return view('upload.index');
    }
}
