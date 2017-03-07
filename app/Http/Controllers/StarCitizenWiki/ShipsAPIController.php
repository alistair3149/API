<?php

namespace App\Http\Controllers\StarCitizenWiki;

use App\Http\Controllers\Controller;
use App\Repositories\StarCitizenWiki\APIv1\Ships\ShipsRepository;
use Illuminate\Http\Request;

class ShipsAPIController extends Controller
{
    /** @var ShipsRepository */
    private $_api;

    public function __construct(ShipsRepository $api)
    {
        $this->_api = $api;
    }

    public function getShipList()
    {
        return response()->json($this->_api->getShipList()->asArray(),200,[],JSON_PRETTY_PRINT);
    }
}
