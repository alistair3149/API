<?php declare(strict_types=1);

namespace Tests\Feature\Controller\Web\Api;

use App\Models\Account\User\User;
use App\Models\Account\User\UserGroup;
use Tests\TestCase;

/**
 * Class Page Controller Test
 */
class PageControllerTest extends TestCase
{
    private $user;

    /**
     * @covers \App\Http\Controllers\Web\Api\PageController::index
     */
    public function testIndexView()
    {
        $response = $this->actingAs($this->user)->get(route('web.api.index'));
        $response->assertOk()
            ->assertViewIs('api.pages.index')
            ->assertSee($this->user->name);
    }

    /**
     * @covers \App\Http\Controllers\Web\Api\PageController::index
     */
    public function testIndexBlockedView()
    {
        $user = User::factory()->blocked()->create();

        $response = $this->actingAs($user)->get(route('web.api.index'));
        $response->assertStatus(403);
    }

    /**
     * @covers \App\Http\Controllers\Web\Api\PageController::showFaqView
     */
    public function testFaqView()
    {
        $response = $this->actingAs($this->user)->get(route('web.api.faq'));
        $response->assertOk()
            ->assertViewIs('api.pages.faq');
    }

    /**
     * Creates a User in the DB
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->createUserGroups();
        $this->user = User::factory()->create();
        $this->user->groups()->sync(UserGroup::where('name', 'user')->first()->id);
    }
}
