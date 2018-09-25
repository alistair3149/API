<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\Manufacturer;

use App\Events\ModelUpdating;
use App\Models\System\Translation\AbstractHasTranslations as HasTranslations;
use App\Traits\HasModelChangelogTrait as ModelChangelog;
use App\Traits\HasVehicleRelationsTrait as VehicleRelations;

/**
 * Manufacturer Model
 */
class Manufacturer extends HasTranslations
{
    use VehicleRelations;
    use ModelChangelog;

    protected $dispatchesEvents = [
        'updating' => ModelUpdating::class,
        'created' => ModelUpdating::class,
        'deleting' => ModelUpdating::class,

    ];

    protected $fillable = [
        'cig_id',
        'name',
        'name_short',
    ];

    protected $with = [
        'translations',
    ];

    protected $perPage = 10;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany(ManufacturerTranslation::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteKeyName()
    {
        return 'name_short';
    }
}
