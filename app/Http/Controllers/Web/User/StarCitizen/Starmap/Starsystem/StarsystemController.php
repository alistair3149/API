<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\StarCitizen\Starmap\Starsystem;

use App\Http\Controllers\Controller;
use App\Models\StarCitizen\Starmap\Starsystem\Starsystem;
use Illuminate\Contracts\View\View;

/**
 * Class AdminStarmapController
 */
class StarsystemController extends Controller
{
    public function index(): View
    {
        return view(
            'user.starcitizen.starmap.starsystems.index',
            [
                'systems' => Starsystem::query()->orderBy('code')->get(),
            ]
        );
    }

    public function show(Starsystem $starsystem): View
    {
        return view(
            'user.starcitizen.starmap.starsystems.show',
            [
                'system' => $starsystem,
            ]
        );
    }
}
