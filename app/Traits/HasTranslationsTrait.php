<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 13.07.2018
 * Time: 13:34
 */

namespace App\Traits;

use Illuminate\Support\Collection;

/**
 * Trait HasTranslationsTrait
 */
trait HasTranslationsTrait
{
    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function english()
    {
        return $this->translations()->english()->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function german()
    {
        return $this->translations()->german()->first();
    }

    public function ofLanguage(string $localeCode)
    {
        return $this->translations()->ofLanguage($localeCode)->first();
    }

    /**
     * Group Translations by Locale Code
     *
     * @return \Illuminate\Support\Collection
     */
    public function getTranslationsAttribute()
    {
        /** @var \Illuminate\Support\Collection $col */
        $col = $this->getRelationValue('translations');

        return $col->keyBy('locale_code');
    }

    /**
     * Translations Joined with Languages
     *
     * @return \Illuminate\Support\Collection
     */
    public function translationsCollection(): Collection
    {
        $table = str_singular($this->getTable()).'_translations';

        $collection = DB::table($table)->select('*')->rightJoin(
            'languages',
            function ($join) use ($table) {
                /** @var $join \Illuminate\Database\Query\JoinClause */
                $join->on(
                    "{$table}.locale_code",
                    '=',
                    'languages.locale_code'
                )->where($table.'.'.str_singular($this->getTable()).'_id', '=', $this->getKey());
            }
        )->get();

        return $collection->keyBy('locale_code');
    }
}
