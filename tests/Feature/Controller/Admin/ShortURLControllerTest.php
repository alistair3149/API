<?php

namespace Tests\Feature\Controller\Admin;

use App\Models\ShortURL\ShortURLWhitelist;
use App\Models\User;
use App\Models\ShortURL\ShortURL;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * Class ShortURLControllerTest
 * @package Tests\Feature\Controller\Admin
 */
class ShortURLControllerTest extends TestCase
{
    use DatabaseTransactions;

    private $user;

    protected function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->user = User::find(1);
    }

    /**
     * @covers \App\Http\Controllers\Auth\Admin\ShortURLController::showURLsListView()
     */
    public function testURLsView()
    {
        $response = $this->actingAs($this->user)->get('admin/urls');
        $response->assertStatus(200);
    }

    /**
     * @covers \App\Http\Controllers\Auth\Admin\ShortURLController::showURLWhitelistView()
     */
    public function testURLsWhitelistView()
    {
        $response = $this->actingAs($this->user)->get('admin/urls/whitelist');
        $response->assertStatus(200);
    }

    /**
     * @covers \App\Http\Controllers\Auth\Admin\ShortURLController::showAddURLWhitelistView()
     */
    public function testAddURLsWhitelistView()
    {
        $response = $this->actingAs($this->user)->get('admin/urls/whitelist/add');
        $response->assertStatus(200);
    }

    /**
     * @covers \App\Http\Controllers\Auth\Admin\ShortURLController::showEditURLView()
     */
    public function testEditURLView()
    {
        $url = ShortURL::create([
            'url' => 'https://star-citizen.wiki/'.str_random(6),
            'hash_name' => str_random(5),
            'user_id' => 1,
        ]);
        $response = $this->actingAs($this->user)->get('admin/urls/'.$url->id);
        $response->assertStatus(200);
    }

    /**
     * @covers \App\Http\Controllers\Auth\Admin\ShortURLController::showEditURLView()
     */
    public function testEditURLViewException()
    {
        $response = $this->actingAs($this->user)->get('admin/urls/-1');
        $response->assertStatus(302);
    }

    /**
     * @covers \App\Http\Controllers\Auth\Admin\ShortURLController::deleteURL()
     */
    public function testDeleteURL()
    {
        $url = ShortURL::create([
            'url' => 'https://star-citizen.wiki/'.str_random(6),
            'hash_name' => str_random(5),
            'user_id' => 1,
        ]);
        $response = $this->actingAs($this->user)->delete('admin/urls', [
            'id' => $url->id,
        ]);
        $response->assertStatus(302);
    }

    /**
     * @covers \App\Http\Controllers\Auth\Admin\ShortURLController::deleteURL()
     */
    public function testDeleteURLException()
    {
        $response = $this->actingAs($this->user)->delete('admin/urls', [
            'id' => 999,
        ]);
        $response->assertStatus(302);
    }

    /**
     * @covers \App\Http\Controllers\Auth\Admin\ShortURLController::deleteWhitelistURL()
     */
    public function testDeleteWhitelistURL()
    {
        $url = ShortURLWhitelist::all()->first();
        $response = $this->actingAs($this->user)->delete('admin/urls/whitelist', [
            'id' => $url->id,
        ]);
        $response->assertStatus(302);
    }

    /**
     * @covers \App\Http\Controllers\Auth\Admin\ShortURLController::deleteWhitelistURL()
     */
    public function testDeleteWhitelistURLException()
    {
        $response = $this->actingAs($this->user)->delete('admin/urls/whitelist', [
            'id' => 999,
        ]);
        $response->assertStatus(302);
    }

    /**
     * @covers \App\Http\Controllers\Auth\Admin\ShortURLController::addWhitelistURL()
     */
    public function testAddWhitelistURL()
    {
        $response = $this->actingAs($this->user)->post('admin/urls/whitelist', [
            'url' => 'https://url.com',
            'internal' => false,
        ]);
        $response->assertStatus(302);
    }

    /**
     * @covers \App\Http\Controllers\Auth\Admin\ShortURLController::updateURL()
     * @covers \App\Models\ShortURL\ShortURL::updateShortURL()
     */
    public function testUpdateURL()
    {
        $url = ShortURL::create([
            'url' => 'https://star-citizen.wiki/'.str_random(6),
            'hash_name' => str_random(5),
            'user_id' => 1,
        ]);
        $response = $this->actingAs($this->user)->patch('admin/urls', [
            'id' => $url->id,
            'url' => 'https://url.com',
            'hash_name' => str_random(5),
            'user_id' => 1,
        ]);
        $response->assertStatus(302);
    }
}
