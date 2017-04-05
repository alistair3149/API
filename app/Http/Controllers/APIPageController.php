<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;

/**
 * Class APIPageController
 *
 * @package App\Http\Controllers
 */
class APIPageController extends Controller
{
    /**
     * Returns the API Index View
     *
     * @return View
     */
    public function showAPIView() : View
    {
        Log::debug('API Index requested');

        return view('api.index');
    }
}
