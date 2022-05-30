<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\ShipItems;

use App\Services\Parser\StarCitizenUnpacked\AbstractCommodityItem;
use App\Services\Parser\StarCitizenUnpacked\Labels;
use App\Services\Parser\StarCitizenUnpacked\Manufacturers;
use App\Services\Parser\StarCitizenUnpacked\PersonalInventory;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

final class ShipItem extends AbstractCommodityItem
{
    private Collection $items;

    private $labels;
    private $manufacturers;

    public function __construct()
    {
        $this->labels = (new Labels())->getData();
        $this->manufacturers = (new Manufacturers())->getData();
    }

    /**
     * @throws FileNotFoundException
     * @throws \JsonException
     */
    public function loadFromShipItems(): void
    {
        $items = File::get(storage_path('app/api/scunpacked-data/ship-items.json'));
        $this->items = collect(json_decode($items, true, 512, JSON_THROW_ON_ERROR));
    }

    public function setItems(Collection $items): void
    {
        $this->items = $items;
    }

    public function getData(bool $onlyBaseVersions = false, bool $excludeToy = true): Collection
    {
        return $this->items
            ->filter(function (array $entry) {
                return isset($entry['reference']) || isset($entry['__ref']);
            })
            ->filter(function (array $entry) {
                return !empty($entry['ClassName'] ?? $entry['className'] ?? '');
            })
            ->filter(function (array $entry) {
                $type = $entry['Components']['SAttachableComponentParams']['AttachDef']['Type'] ?? $entry['type'] ?? '';

                return !empty($type) &&
                    $type !== 'Armor' &&
                    $type !== 'Ping' &&
                    $type !== 'Paints';
            })
            ->map(function (array $entry) {
                $out = $entry['stdItem'] ?? $entry;
                $out['reference'] = $entry['reference'] ?? $entry['__ref'] ?? null;
                $out['itemName'] = $entry['itemName'] ?? $entry['ClassName'] ?? null;

                return $out;
            })
            ->map(function (array $entry) {
                try {
                    $item = File::get(
                        storage_path(
                            sprintf(
                                'app/api/scunpacked-data/v2/items/%s-raw.json',
                                strtolower($entry['itemName'])
                            )
                        )
                    );

                    $rawData = collect(json_decode($item, true, 512, JSON_THROW_ON_ERROR));
                    if (!isset($entry['Description']) || empty($entry['Description'])) {
                        // phpcs:ignore
                        $entry['Description'] = $this->labels->get(substr($rawData['Components']['SAttachableComponentParams']['AttachDef']['Localization']['Description'], 1));
                    }

                    return $this->map($entry, $rawData);
                } catch (\JsonException | FileNotFoundException $e) {
                    return null;
                }
            })
            ->filter(function ($entry) {
                return $entry !== null;
            });
    }

    private function map(array $item, Collection $rawData): ?array
    {
        /**
         * BASE ALL
         *  Durability
         *      Health
         *      Lifetime
         *  PowerConnection
         *      PowerBase
         *      PowerDraw
         *  HeatConnection
         *      ThermalEnergyBase
         *      ThermalEnergyDraw
         *      CoolingRate
         *
         *
         * Cooler:
         *  Cooler
         *      Rate
         *
         *
         * PowerPlanet
         *  PowerPlant
         *      Output
         *
         *
         * QuantumDrive
         *  QuantumDrive
         *      FuelRate
         *      JumpRange
         *      StandardJump
         *          Speed
         *          Cooldown
         *          Stage1AccelerationRate
         *          Stage2AccelerationRate
         *          SpoolTime
         *      SplineJump
         *          Speed
         *          Cooldown
         *          Stage1AccelerationRate
         *          Stage2AccelerationRate
         *          SpoolTime
         *
         *
         * QuantumInterdictionGenerator
         *  QuantumInterdictionGenerator
         *      JammingRange
         *      JumpRange
         *      InterdictionRange
         *
         * Shield
         *  Shield
         *      Health
         *      Regeneration
         *      DownedDelay
         *      DamageDelay
         *      Absorption
         *          Physical
         *              Min
         *              Max
         *          Energy
         *              Min
         *              Max
         *          Distortion
         *              Min
         *              Max
         *          Thermal
         *              Min
         *              Max
         *          Biochemical
         *              Min
         *              Max
         *          Stun
         *              Min
         *              Max
         *
         * Weapon
         *  Weapon
         *      Ammunition
         *          Speed
         *          Range
         *          Size
         *          Capacity
         *          ImpactDamage
         *              Energy
         *              Physical
         *              Distortion
         *      Modes
         *          Name
         *          RoundsPerMinute
         *          FireType
         *          AmmoPerShot
         *          PelletsPerShot
         *          DamagePerShot
         *              Energy
         *              Physical
         *              Distortion
         *          DamagePerSecond
         *              Energy
         *              Physical
         *              Distortion
         *  Ammunition
         *      Speed
         *      Range
         *      Size
         *      Capacity
         *      ImpactDamage
         *          Energy
         *      DetonationDamage
         *
         *
         * MissileLauncher
         */
        if (!isset($item['Description']) || empty($item['Description'])) {
            $item['Description'] = '';
        }

        $item['Description'] = str_replace(["\n", '\n'], "\n", $item['Description']);

        $data = $this->tryExtractDataFromDescription($item['Description'], [
            'Item Type' => 'item_type',
            'Manufacturer' => 'manufacturer',
            'Size' => 'size',
            'Grade' => 'grade',
            'Class' => 'item_class',
            'Attachment Point' => 'attachment_point',
            'Missiles' => 'misslies',
            'Rockets' => 'rockets',
            'Tracking Signal' => 'tracking_signal',
        ]);

        // phpcs:disable
        $mappedItem = [
            'uuid' => $item['__ref'] ?? $item['reference'],
            'size' => $data['size'] ?? $rawData['Components']['SAttachableComponentParams']['AttachDef']['Size'] ?? $item['Size'] ?? 0,
            'item_type' => $data['item_type'] ?? $rawData['Components']['SAttachableComponentParams']['AttachDef']['Type'] ?? $item['Type'] ?? 0,
            'item_class' => trim($item['Classification'] ?? $rawData['Components']['SAttachableComponentParams']['AttachDef']['Class'] ?? 'Unknown Class'),
            'item_grade' => $data['grade'] ?? $rawData['Components']['SAttachableComponentParams']['AttachDef']['Grade'] ?? $item['Grade'] ?? 0,
            'description' => $data['description'] ?? '',
            'name' => str_replace(
                [
                    '“',
                    '”',
                    '"',
                    '\'',
                ],
                '"',
                trim($item['Name'] ?? $this->labels->get(substr($item['Components']['SAttachableComponentParams']['AttachDef']['Localization']['Name'], 1)) ?? 'Unknown Ship Item')
            ),
            'manufacturer' => $data['manufacturer'] ?? $this->getManufacturer($item),
            'type' => trim($item['type'] ?? $data['item_type'] ?? 'Unknown Type'),
            'class' => trim($data['item_class'] ?? 'Unknown Class'),
            'grade' => $data['grade'] ?? null,
        ];

        if ($mappedItem['type'] === 'Unknown Type') {
            if (isset($rawData['Components']['SAttachableComponentParams']['AttachDef']['Type'])) {
                $mappedItem['type'] = trim(preg_replace('/([A-Z])/', ' $1', $rawData['Components']['SAttachableComponentParams']['AttachDef']['Type']));
            } else {
                $tmp = explode('.', $item['Type']);
                $mappedItem['type'] = trim(preg_replace('/([A-Z])/', ' $1', array_shift($tmp)));
            }
        }

        // Change Cargo type to 'PersonalInventory' if item is in fact not a cargo grid
        if ($mappedItem['type'] === 'Cargo' && isset($rawData['Components']['SInventoryParams']['capacity'])) {
            $capacity = $rawData['Components']['SInventoryParams']['capacity']['SCentiCargoUnit'] ?? $rawData['Components']['SInventoryParams']['capacity']['SMicroCargoUnit'] ?? [];
            $capacity = $capacity['centiSCU'] ?? $capacity['microSCU'] ?? 1;

            if ($capacity > 1) {
                $mappedItem['type'] = 'PersonalInventory';
                $mappedItem['item_type'] = 'PersonalInventory';
                $mappedItem['item_class'] = 'Ship.PersonalInventory';
            }
        }
        // phpcs:enable

        $this->addData($mappedItem, $item, $rawData);

        return $mappedItem;
    }

    private function addData(array &$mappedItem, array $item, Collection $rawData): void
    {
        $mappedItem = array_merge(
            $mappedItem,
            BaseData::getData($item, $rawData)
        );
        $mappedItem['cooler'] = Cooler::getData($item, $rawData);
        $mappedItem['power_plant'] = PowerPlant::getData($item, $rawData);
        $mappedItem['shield'] = Shield::getData($item, $rawData);
        $mappedItem['quantum_drive'] = QuantumDrive::getData($item, $rawData);
        $mappedItem['fuel_tank'] = FuelTank::getData($item, $rawData);
        $mappedItem['fuel_intake'] = FuelIntake::getData($item, $rawData);
        $mappedItem['weapon'] = Weapon::getData($item, $rawData);
        $mappedItem['missile_rack'] = MissileRack::getData($item, $rawData);
        $mappedItem['missile'] = Missile::getData($item, $rawData);
        $mappedItem['turret'] = Turret::getData($item, $rawData);
        $mappedItem['thruster'] = Thruster::getData($item, $rawData);
        $mappedItem['self_destruct'] = SelfDestruct::getData($item, $rawData);
        $mappedItem['counter_measure'] = CounterMeasure::getData($item, $rawData);
        $mappedItem['radar'] = Radar::getData($item, $rawData);
        $mappedItem['mining_laser'] = MiningLaser::getData($item, $rawData);
        $mappedItem['cargo_grid'] = CargoGrid::getData($item, $rawData);
        $mappedItem['personal_inventory'] = PersonalInventory::getData($item, $rawData);
    }
}
