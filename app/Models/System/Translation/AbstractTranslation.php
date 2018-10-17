<?php declare(strict_types = 1);

namespace App\Models\System\Translation;

use App\Events\ModelUpdating;
use App\Models\System\Language;
use App\Traits\HasModelChangelogTrait as ModelChangelog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Base Translation Class which holds Language Query Scopes
 */
abstract class AbstractTranslation extends Model
{
    use ModelChangelog;

    const ATTR_LOCALE_CODE = '.locale_code';

    protected $dispatchesEvents = [
        'updating' => ModelUpdating::class,
        'created' => ModelUpdating::class,
        'deleting' => ModelUpdating::class,
    ];

    /**
     * Language Relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function language()
    {
        return $this->belongsTo('App\Models\System\Language');
    }

    /**
     * English Translations
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnglish(Builder $query)
    {
        return $query->where($this->getTable().self::ATTR_LOCALE_CODE, config('language.english'));
    }

    /**
     * German Translations
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGerman(Builder $query)
    {
        return $query->where($this->getTable().self::ATTR_LOCALE_CODE, config('language.german'));
    }
}
