<?php

namespace App\Jobs;

use App\Exceptions\InvalidDataException;
use App\Transformers\StarCitizenDB\Ships\ShipsTransformer;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Fractal\Fractal;

/**
 * Class SplitShipFiles
 * @package App\Jobs
 */
class SplitShipFiles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const WIKI_SHIP_NAMES = [
        'RSI_Bengal' => 'RSI_Bengal_Carrier',
        'ORIG_85X_Limited' => 'ORIG_85X_Runabout',
        'ANVL_Hornet_F7C' => 'ANVL_F7C_Hornet',
        'ANVL_Hornet_F7C_Wildfire' => 'ANVL_F7C_Hornet_Wildfire',
        'ANVL_Hornet_F7CM_Super' => 'ANVL_F7C-M_Super_Hornet',
        'ANVL_Hornet_F7CR_Tracker' => 'ANVL_F7C-R_Hornet_Tracker',
        'ANVL_Hornet_F7CS_Ghost' => 'ANVL_F7C-S_Hornet_Ghost',
    ];

    /**
     * @var array
     */
    private $content;

    /**
     * @var array
     */
    private $baseVersion;

    /**
     * @var \Spatie\Fractalistic\Fractal
     */
    private $fractalManager;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->fractalManager = Fractal::create();
        $this->fractalManager->transformWith(ShipsTransformer::class);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() : void
    {
        App::make('Log')::info('Starting Split Ship Files Job');
        foreach (File::allFiles(config('filesystems.disks.scdb_ships_base.root')) as $ship) {
            $this->setContent((String) $ship);

            try {
                $this->checkIfShipHasNameField();
                $this->getDataForBaseVersion();
                if ($this->checkIfShipHasModifications()) {
                    App::make('Log')::debug('Ship has Modifications');
                    foreach ($this->content['Modifications'] as $modification) {
                        if ($this->checkIfModificationIsValid($modification)) {
                            $this->content = $modification;
                            $this->prepareModificationArray();
                            App::make('Log')::debug('Mod: '.$this->content['@name']);
                            $this->getDataForModifications();

                        }
                    }
                }
            } catch (InvalidDataException $e) {
                App::make('Log')::warning($e->getMessage(), [
                    'file' => (String) $ship,
                ]);
            }
        }
        App::make('Log')::info('Finished Split Ship Files Job');
    }

    /**
     * Gets the ships file content and parses it
     *
     * @param String $data
     */
    private function setContent(String $data) : void
    {
        $this->content = (String) File::get($data);
        $this->content = json_decode($this->content, true);
    }

    /**
     * @throws InvalidDataException
     */
    private function checkIfShipHasNameField() : void
    {
        if (!isset($this->content['@name']) || empty($this->content['@name'])) {
            throw new InvalidDataException('Name field is missing or empty');
        }
    }

    /**
     * Transformes the data and sets a base version
     */
    private function getDataForBaseVersion() : void
    {
        $this->getShipNameForBaseVersion();
        App::make('Log')::info('Processing '.$this->content['processedName']);

        $baseVersion = $this->content;
        unset($baseVersion['Modifications']);

        $collectedData = $this->fractalManager->item($baseVersion)->toArray()['data'];

        $this->saveDataToDisk($collectedData);

        $this->baseVersion = $collectedData;
    }

    private function getShipNameForBaseVersion() : void
    {
        $name = $this->content['@name'];

        if (isset($this->content['@displayname']) &&
            !empty($this->content['@displayname'])) {
            $displayName = $this->assembleFilename($this->content['@displayname']);
        }

        if (isset($this->content['@local']) &&
            !empty($this->content['@local'])) {
            $localName = $this->assembleFilename($this->content['@local']);
        }

        if (isset($displayName) &&
            isset($localName)) {
            if ($displayName !== $localName) {
                $name = $localName;
            }
        }

        if (isset($displayName)) {
            if ($name !== $displayName) {
                $name = $displayName;
            }
        }

        if (isset($localName)) {
            if ($name !== $localName) {
                $name = $localName;
            }
        }
        $this->content['processedName'] = $name;
    }

    /**
     * @param String $name
     *
     * @return String
     */
    private function assembleFilename(String $name) : String
    {
        $manufacturerID = explode('_', $this->content['@name'])[0];

        $name = str_replace(' ', '_', $name);
        $name = explode('_', $name);
        $name = array_filter($name);
        $name[0] = $manufacturerID;

        return implode('_', $name);
    }

    /**
     * Checks if a Modifications key is present in the content array
     *
     * @return bool
     */
    private function checkIfShipHasModifications() : bool
    {
        return isset($this->content['Modifications']);
    }

    /**
     * @param array $modification
     *
     * @return bool
     */
    private function checkIfModificationIsValid(array $modification) : bool
    {
        return isset($modification['@patchFile']) &&
               isset($modification['mod']) &&
               isset($modification['@name']) &&
               !empty($modification['@name']);
    }

    /**
     * Transformes the Modification Data and merges it with the base version
     */
    private function getDataForModifications() : void
    {
        $this->getShipNameForModification();
        App::make('Log')::info('Processing Modification '.$this->content['processedName']);

        $collectedData = $this->fractalManager->item($this->content)->toArray()['data'];

        $collectedData = $this->filterModificationArray($collectedData);

        $collectedData = array_replace_recursive($this->baseVersion, $collectedData);

        $this->saveDataToDisk($collectedData);
    }

    private function getShipNameForModification() : void
    {
        $patchFileName = $this->content['@patchFile'];
        $name = str_replace(
            'Modifications/',
            '',
            $patchFileName
        );

        if (last(explode('_', $name)) !== $this->content['@name']) {
            $name .= '_'.$this->content['@name'];
        }

        if (isset($this->content['@local']) &&
            !empty($this->content['@local'])) {
            $manufacturerID = explode('_', $name)[0];
            $localName = explode(' ', $this->content['@local']);
            $localName = array_filter($localName);
            $localName[0] = $manufacturerID;
            $localName = implode('_', $localName);

            if ($name !== $localName) {
                $name = $localName;
            }
        }

        $this->content['processedName'] = $name;
    }

    /**
     * Flattens the Modification array
     */
    private function prepareModificationArray() : void
    {
        // Content des MOD arrays eine Ebene höherstufen, damit nur ein ShipsTransformer benötigt wird
        $mod = $this->content['mod'];
        unset($this->content['mod']);
        $this->content = array_replace_recursive($this->content, $mod);

        // Transformer benötigt ifcs array
        if (!isset($this->content['ifcs'])) {
            $this->content['ifcs'] = [];
        }
    }

    /**
     * Entfernen leerer Keys
     *
     * @param $array
     *
     * @return array
     */
    private function filterModificationArray(&$array) : array
    {
        foreach ($array as $key => $item) {
            if (is_array($item)) {
                $array[$key] = $this->filterModificationArray($item);
            }
            if (empty($array[$key])) {
                unset($array[$key]);
            }
        }

        return $array;
    }

    /**
     * @param array $content
     */
    private function saveDataToDisk(array $content) : void
    {
        $this->prepareFilename($content);

        App::make('Log')::info('Saving '.$content['name']);
        Storage::disk('scdb_ships_splitted')->put(
            $content['filename'],
            json_encode($content, JSON_PRETTY_PRINT)
        );
    }

    /**
     * Adjusts the Filename to Match the Wiki Site Name
     *
     * @param array $data
     */
    private function prepareFilename(array &$data) : void
    {
        $manufacturerID = strtoupper(
            explode('_', $data['name'])[0]
        );

        $nameSplitted = explode('_', $data['name']);
        $nameSplitted[0] = $manufacturerID;

        $data['name'] = implode('_', $nameSplitted);
        $data['name'] = self::WIKI_SHIP_NAMES[$data['name']] ?? $data['name'];
        $data['filename'] = strtolower($data['name'].'.json');
    }
}
