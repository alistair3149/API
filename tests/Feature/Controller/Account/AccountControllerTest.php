<?php

namespace Tests\Feature\Controller\Account;

use App\Models\ShortURL\ShortURL;
use App\Models\User;
use Illuminate\Foundation\Testing\WithoutEvents;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * Class AccountControllerTest
 * @package Tests\Feature\Controller\Account
 */
class AccountControllerTest extends TestCase
{
    use DatabaseTransactions;

    private $user;

    protected function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->user = factory(User::class)->create();
    }

    /**
     * @covers \App\Http\Controllers\Auth\Account\AccountController::showAccountView()
     * @covers \App\Http\Middleware\RedirectIfAuthenticated
     */
    public function testAccountView()
    {
        $response = $this->actingAs($this->user)->get('account');
        $response->assertStatus(200);
    }

    /**
     * @covers \App\Http\Controllers\Auth\Account\AccountController::delete()
     */
    public function testDeleteAccount()
    {
        $response = $this->actingAs($this->user)->delete('account', []);
        $response->assertStatus(302);
    }

    /**
     * @covers \App\Http\Controllers\Auth\Account\AccountController::showEditAccountView()
     */
    public function testAccountEditFormView()
    {
        $response = $this->actingAs($this->user)->get('account/edit');
        $response->assertStatus(200);
    }

    /**
     * @covers \App\Http\Controllers\Auth\Account\AccountController::updateAccount()
     * @covers \App\Http\Middleware\VerifyCsrfToken
     */
    public function testUpdateAccount()
    {
        $response = $this->actingAs($this->user)->patch('account', [
            'name' => 'UpdatedName',
            'email' => 'a'.str_random(5).'@star-citizen.wiki',
            'password' => null,
            'password_confirmed' => null,
        ]);

        $response->assertStatus(302);
    }
}
