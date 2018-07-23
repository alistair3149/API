<?php declare(strict_types = 1);

use Illuminate\Database\Seeder;

class ProductionStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = \Carbon\Carbon::now();

        DB::table('production_statuses')->insert(
            [
                'id' => 1,
            ]
        );
        DB::table('production_status_translations')->insert(
            [
                'locale_code' => 'en_EN',
                'production_status_id' => 1,
                'translation' => 'undefined',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
        DB::table('production_status_translations')->insert(
            [
                'locale_code' => 'de_DE',
                'production_status_id' => 1,
                'translation' => 'Undefiniert',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}
