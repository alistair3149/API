<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 25.09.2018
 * Time: 12:51
 */

namespace App\Jobs\Api\StarCitizen\Vehicle\Parser\Element;

use App\Jobs\Api\StarCitizen\Vehicle\Parser\Element\AbstractBaseElement as BaseElement;
use App\Models\Api\StarCitizen\ProductionNote\ProductionNote as ProductionNoteModel;
use App\Models\Api\StarCitizen\ProductionNote\ProductionNoteTranslation;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class ProductionNote
 */
class ProductionNote extends BaseElement
{
    protected const PRODUCTION_NOTE = 'production_note';

    /**
     * @return \App\Models\Api\StarCitizen\ProductionNote\ProductionNote
     */
    public function getProductionNote(): ProductionNoteModel
    {
        app('Log')::debug('Getting Production Note');

        $note = $this->rawData->get(self::PRODUCTION_NOTE);
        if (null === $note) {
            app('Log')::debug('Production Note not set in Matrix, returning default (None)');

            return ProductionNoteModel::find(1);
        }

        try {
            /** @var \App\Models\Api\StarCitizen\ProductionNote\ProductionNoteTranslation $productionNoteTranslation */
            $productionNoteTranslation = ProductionNoteTranslation::query()->where(
                'translation',
                $note
            )->where(
                'locale_code',
                config('language.english')
            )->firstOrFail();
        } catch (ModelNotFoundException $e) {
            app('Log')::debug('Production Note not found in DB');

            return $this->createNewProductionNote();
        }

        return $productionNoteTranslation->productionNote;
    }

    /**
     * @return \App\Models\Api\StarCitizen\ProductionNote\ProductionNote
     */
    private function createNewProductionNote(): ProductionNoteModel
    {
        app('Log')::debug('Creating new Production Note');

        /** @var \App\Models\Api\StarCitizen\ProductionNote\ProductionNote $productionNote */
        $productionNote = ProductionNoteModel::create();

        $productionNote->translations()->create(
            [
                'locale_code' => config('language.english'),
                'translation' => $this->rawData->get(self::PRODUCTION_NOTE),
            ]
        );

        app('Log')::debug('Production Note created');

        return $productionNote;
    }
}
