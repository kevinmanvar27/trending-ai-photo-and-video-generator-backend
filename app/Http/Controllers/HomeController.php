<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Show home page - redirect to templates
     */
    public function index()
    {
        // Redirect directly to the templates page
        return redirect()->route('image-submission.index');
    }
}
