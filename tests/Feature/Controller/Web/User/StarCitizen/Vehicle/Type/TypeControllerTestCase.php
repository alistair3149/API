<?php

declare(strict_types=1);

namespace Tests\Feature\Controller\Web\User\StarCitizen\Vehicle\Type;

use App\Http\Controllers\Web\User\StarCitizen\Vehicle\Type\TypeController;
use App\Models\StarCitizen\Vehicle\Type\Type;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Tests\Feature\Controller\Web\User\StarCitizen\StarCitizenTestCase;

/**
 * Admin Vehicle Type Controller Test Case
 */
class TypeControllerTestCase extends StarCitizenTestCase
{
    /**
     * Index Tests
     */

    /**
     * Test Index
     *
     * @covers \App\Http\Controllers\Web\User\StarCitizen\Vehicle\Type\TypeController::index
     */
    public function testIndex()
    {
        $response = $this->actingAs($this->user)->get(route('web.user.starcitizen.vehicles.types.index'));
        $response->assertStatus(static::RESPONSE_STATUSES['index']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('user.starcitizen.vehicles.types.index')
                ->assertDontSee(__('Keine Übersetzungen vorhanden'))
                ->assertSee(__('Fahrzeugtypen'))
                ->assertSee(__('en_EN'));
        }
    }

    /**
     * Edit Tests
     */

    /**
     * Test Edit
     *
     * @covers \App\Http\Controllers\Web\User\StarCitizen\Vehicle\Type\TypeController::edit
     */
    public function testEdit()
    {
        /** @var \App\Models\StarCitizen\Vehicle\Type\Type $vehicleType */
        $vehicleType = Type::factory()->create();

        $response = $this->actingAs($this->user)->get(
            route('web.user.starcitizen.vehicles.types.edit', $vehicleType->getRouteKey())
        );
        $response->assertStatus(static::RESPONSE_STATUSES['edit']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('user.starcitizen.vehicles.types.edit')
                ->assertSee(__('Übersetzungen'))
                ->assertSee(__('Speichern'));
        }
    }

    /**
     * Test Edit
     *
     * @covers \App\Http\Controllers\Web\User\StarCitizen\Vehicle\Type\TypeController::edit
     */
    public function testEditNotFound()
    {
        $response = $this->actingAs($this->user)->get(
            route('web.user.starcitizen.vehicles.types.edit', static::MODEL_ID_NOT_EXISTENT)
        );
        $response->assertStatus(static::RESPONSE_STATUSES['edit_not_found']);
    }


    /**
     * Update Tests
     */

    /**
     * Test Update
     *
     * @covers \App\Http\Controllers\Web\User\StarCitizen\Vehicle\Type\TypeController::update
     *
     * @covers \App\Http\Requests\System\TranslationRequest
     *
     * @covers \App\Models\System\ModelChangelog
     */
    public function testUpdate()
    {
        /** @var \App\Models\StarCitizen\Vehicle\Type\Type $vehicleType */
        $vehicleType = Type::factory()->create();

        $response = $this->actingAs($this->user)->patch(
            route('web.user.starcitizen.vehicles.types.update', $vehicleType->getRouteKey()),
            [
                'en_EN' => 'Vehicle Type translation',
                'de_DE' => 'Translation Deutsch',
            ]
        );

        self::assertNotEquals(ValidationException::class, get_class($response->exception ?? new \stdClass()));

        $response->assertStatus(static::RESPONSE_STATUSES['update']);
    }

    /**
     * Test Update
     *
     * @covers \App\Http\Controllers\Web\User\StarCitizen\Vehicle\Type\TypeController::update
     */
    public function testUpdateNotFound()
    {
        $response = $this->actingAs($this->user)->patch(
            route('web.user.starcitizen.vehicles.types.update', static::MODEL_ID_NOT_EXISTENT),
            [
                'en_EN' => 'Vehicle Type translation',
                'de_DE' => 'Translation Deutsch',
            ]
        );

        self::assertNotEquals(ValidationException::class, get_class($response->exception ?? new \stdClass()));

        $response->assertStatus(static::RESPONSE_STATUSES['update_not_found']);
    }

    /**
     * {@inheritdoc}
     * Creates needed Vehicle types
     */
    protected function setUp(): void
    {
        parent::setUp();
        Type::factory()->count(10)->create();
    }
}
