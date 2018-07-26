<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\ProductionNote;

use App\Models\Api\Translation\AbstractHasTranslations as HasTranslations;
use App\Traits\HasVehicleRelationsTrait as VehicleRelations;

/**
 * Production Note Model
 */
class ProductionNote extends HasTranslations
{
    use VehicleRelations;

    public $timestamps = false;

    protected $with = [
        'translations',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany(ProductionNoteTranslation::class);
    }
}
