<?php

declare(strict_types=1);

namespace App\Services\Parser\SC;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use JsonException;

final class Manufacturers
{
    private Collection $manufacturers;

    /**
     * Manufacturers contain all available manufacturers
     * Manufacturers are mapped by UUID
     *
     * @throws FileNotFoundException
     * @throws JsonException
     */
    public function __construct()
    {
        $items = File::get(storage_path('app/api/scunpacked-data/manufacturers.json'));
        $this->manufacturers = collect(json_decode($items, true, 512, JSON_THROW_ON_ERROR));
    }

    public function getData(): Collection
    {
        return $this->manufacturers
            ->mapWithKeys(function (array $manufacturer) {
                return [
                    $manufacturer['reference'] => [
                        'name' => $manufacturer['name'] ?? 'Unknown Manufacturer',
                        'code' => $manufacturer['code'] ?? 'UNKN',
                        'uuid' => $manufacturer['reference'] ?? '00000000-0000-0000-0000-000000000000',
                    ]
                ];
            });
    }
}
