<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedPersonalWeaponAmmunitionDamagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_personal_weapon_ammunition_damages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ammunition_id');
            $table->string('type');
            $table->string('name');

            $table->unsignedDouble('damage');

            $table->timestamps();

            $table->foreign('ammunition_id', 'ammunition_id_damage_id')
                ->references('id')
                ->on('star_citizen_unpacked_personal_weapon_ammunitions')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('star_citizen_unpacked_personal_weapon_ammunition_damages');
    }
}
