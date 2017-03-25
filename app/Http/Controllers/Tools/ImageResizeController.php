<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class ImageResizeController
 *
 * @package App\Http\Controllers\Tools
 */
class ImageResizeController extends Controller
{
    /**
     * Returns the Image Resize View
     *
     * @return View
     */
    public function showImageResizeView() : View
    {
        $imageResizeSettings = [
            'default' => [
                'outputWidth' => 1920,
                'outputHeight' => 250,
                'displayWidth' => 960,
                'displayHeight' => 125,
                'selectionRectangleColor' => '#ff0000',
            ],
        ];

        return view('tools.imageresizer', compact('imageResizeSettings'));
    }
}
