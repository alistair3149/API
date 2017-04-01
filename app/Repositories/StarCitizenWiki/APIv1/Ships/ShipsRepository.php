<?php
/**
 * User: Hannes
 * Date: 03.03.2017
 * Time: 18:16
 */

namespace App\Repositories\StarCitizenWiki\APIv1\Ships;

use App\Repositories\StarCitizenWiki\APIv1\BaseStarCitizenWikiAPI;
use App\Transformers\StarCitizenWiki\Ships\ShipsListTransformer;
use App\Transformers\StarCitizenWiki\Ships\ShipsSearchTransformer;
use App\Transformers\StarCitizenWiki\Ships\ShipsTransformer;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Storage;

/**
 * Class ShipsRepository
 * @package App\Repositories\StarCitizenWiki\APIv1\Ships
 */
class ShipsRepository extends BaseStarCitizenWikiAPI implements ShipsInterface
{
    /**
     * Returns Ship data
     *
     * @param String $shipName ShipName
     *
     * @return ShipsRepository
     */
    public function getShip(String $shipName) : ShipsRepository
    {
        $this->transformer = resolve(ShipsTransformer::class);
        $this->request(
            'GET',
            '?action=browsebysubject&format=json&subject='.$shipName,
            []
        );

        if (isset($this->dataToTransform['query']['subject'])) {
            $client = new Client([
                'base_uri' => 'http://starcitizendb.com/static/ships/specs/',
                'timeout' => 2.0,
            ]);

            $subject = explode('/', $this->dataToTransform['query']['subject']);
            if (count($subject) === 3) {
                $fileName = $subject[1].'_'.$shipName.'.json';

                if (Storage::disk('scdb_ships')->exists($fileName)) {
                    $content = Storage::disk('scdb_ships')->get($fileName);
                    $this->dataToTransform['scdb'] = json_decode($content, true);
                }
            }
        }

        return $this;
    }

    /**
     * Gets a ShipList
     *
     * @return ShipsRepository
     */
    public function getShipList() : ShipsRepository
    {
        $this->collection();
        $this->transformer = resolve(ShipsListTransformer::class);

        $offset = 0;
        $data = [];
        do {
            $response = (String) $this->request(
                'GET',
                '?action=askargs&format=json&conditions=Kategorie%3ARaumschiff%7CHersteller%3A%3A%2B&parameters=offset%3D'.$offset,
                []
            )->getBody();
            $response = json_decode($response, true);
            $data = array_merge($data, $response['query']['results']);
            if (array_key_exists('query-continue-offset', $response)) {
                $offset = $response['query-continue-offset'];
            }
        } while (array_key_exists('query-continue-offset', $response));

        $this->dataToTransform = $data;

        return $this;
    }

    /**
     * Seraches for a Ship
     *
     * @param String $shipName ShipName
     *
     * @return ShipsRepository
     */
    public function searchShips(String $shipName)
    {
        /**
         * TODO: Suche Gibt teils Mist zurück
         * Beispiel: Suche nach Aurora gibt zusätzlich Orion und Hull A zurück!?
         */
        $this->transformer = resolve(ShipsSearchTransformer::class);
        $this->collection()->request(
            'GET',
            '/api.php?action=query&format=json&list=search&continue=-%7C%7Ccategories%7Ccategoryinfo&srnamespace=0&srprop=&srsearch=-intitle:Hersteller+incategory%3ARaumschiff+'.$shipName,
            []
        );
        $this->dataToTransform = $this->dataToTransform['query']['search'];

        return $this;
    }
}