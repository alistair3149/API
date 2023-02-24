<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizenUnpacked\Import;

use App\Models\StarCitizenUnpacked\CharArmor\CharArmorAttachment;
use App\Models\StarCitizenUnpacked\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CharArmor implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $weapons = new \App\Services\Parser\StarCitizenUnpacked\CharArmor\CharArmor();
        } catch (\JsonException | FileNotFoundException $e) {
            $this->fail($e->getMessage());

            return;
        }

        $weapons->getData()
            ->each(function ($armor) {
                if (!Item::query()->where('uuid', $armor['uuid'])->exists()) {
                    return;
                }

                /** @var \App\Models\StarCitizenUnpacked\CharArmor\CharArmor $model */
                $model = \App\Models\StarCitizenUnpacked\CharArmor\CharArmor::updateOrCreate([
                    'uuid' => $armor['uuid'],
                ], [
                    'armor_type' => $armor['type'],
                    'carrying_capacity' => $armor['carrying_capacity'],
                    'damage_reduction' => $armor['damage_reduction'],
                    'temp_resistance_min' => $armor['temp_resistance_min'],
                    'temp_resistance_max' => $armor['temp_resistance_max'],
                    'version' => config('api.sc_data_version'),
                ]);

                $model->translations()->updateOrCreate([
                    'locale_code' => 'en_EN',
                ], [
                    'translation' => $armor['description'] ?? '',
                ]);

                $ids = [];

                foreach ($armor['attachments'] as $attachment) {
                    $ids[] = (CharArmorAttachment::updateOrCreate([
                        'name' => $attachment['name'],
                        'min_size' => $attachment['min_size'],
                        'max_size' => $attachment['max_size'],
                    ]))->id;
                }

                if (isset($armor['resistances'])) {
                    foreach ($armor['resistances'] as $type => $resistance) {
                        $model->resistances()->updateOrCreate([
                            'type' => $type,
                        ], [
                            'multiplier' => $resistance['multiplier'],
                            'threshold' => $resistance['threshold'] ?? 0,
                        ]);
                    }
                }

                $model->attachments()->sync($ids);
            });
    }
}
