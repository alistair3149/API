<?php declare(strict_types = 1);

namespace App\Http\Controllers\Auth\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\DownloadStarmapData;
use App\Models\CelestialObject;
use App\Models\Starsystem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Class AdminStarmapController
 *
 * @package App\Http\Controllers\Auth\Admin
 */
class StarmapController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function showStarmapSystemsView(): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('admin.starmap.systems.index')->with(
            'systems',
            Starsystem::orderBy('code')->get()
        );
    }

    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function showStarmapCelestialObjectView(): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('admin.starmap.celestialobjects.index')->with(
            'celestialobjects',
            CelestialObject::orderBy('code')->get()
        );
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function downloadStarmap(): RedirectResponse
    {
        $this->dispatch(new DownloadStarmapData());

        return redirect()->back()->with(
            'success',
            ['Starmap Download Queued']
        );
    }
}
