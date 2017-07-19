<?php

namespace App\Http\Controllers;

use App\Traits\ProfilesMethodsTrait;
use Illuminate\Contracts\View\View;

/**
 * Class APIPageController
 *
 * @package App\Http\Controllers
 */
class APIPageController extends Controller
{
    use ProfilesMethodsTrait;

    /**
     * Returns the API Index View
     *
     * @return View
     */
    public function showAPIView() : View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('api.index');
    }

    /**
     * Returns the API FAQ View
     *
     * @return View
     */
    public function showFAQView() : View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('api.faq');
    }
}
