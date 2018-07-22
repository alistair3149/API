<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\Vehicle\Size;

use App\Traits\HasTranslationsTrait as Translations;
use App\Traits\HasVehicleRelationsTrait as VehicleRelations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Vehicle Size Model
 */
class VehicleSize extends Model
{
    use VehicleRelations;
    use Translations;

    public $timestamps = false;
    protected $with = [
        'translations',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany(VehicleSizeTranslation::class)->join(
            'languages',
            'vehicle_size_translations.language_id',
            '=',
            'languages.id'
        );
    }

    /**
     * Translations Joined with Languages
     *
     * @return \Illuminate\Support\Collection
     */
    public function translationsCollection(): Collection
    {
        $collection = DB::table('vehicle_size_translations')->select('*')->rightJoin(
            'languages',
            function ($join) {
                /** @var $join \Illuminate\Database\Query\JoinClause */
                $join->on(
                    'vehicle_size_translations.language_id',
                    '=',
                    'languages.id'
                )->where('vehicle_size_translations.vehicle_size_id', '=', $this->getKey());
            }
        )->get();

        return $collection->keyBy('locale_code');
    }
}
