<?php declare(strict_types=1);

namespace Tests\Feature\Controller\Web\User\Rsi\CommLink\Category;

use App\Http\Controllers\Web\User\Rsi\CommLink\Category\CategoryController;
use App\Models\Rsi\CommLink\Category\Category;
use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Http\Response;
use Tests\Feature\Controller\Web\User\UserTestCase;

/**
 * Class Category Controller Test Case
 */
class CategoryControllerTestCase extends UserTestCase
{
    /**
     * @var \App\Models\Rsi\CommLink\Category\Category
     */
    protected $category;

    /**
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $commLinks;

    /**
     * @covers \App\Http\Controllers\Web\User\Rsi\CommLink\Category\CategoryController::index
     */
    public function testIndex()
    {
        $response = $this->actingAs($this->user)->get(route('web.user.rsi.comm-links.categories.index'));

        $response->assertStatus(static::RESPONSE_STATUSES['index']);
        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('user.rsi.comm_links.categories.index')->assertSee($this->category->name);
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\User\Rsi\CommLink\Category\CategoryController::show
     * @covers \App\Models\Rsi\CommLink\Category\Category
     */
    public function testShow()
    {
        $response = $this->actingAs($this->user)->get(
            route('web.user.rsi.comm-links.categories.show', $this->category)
        );

        $response->assertStatus(static::RESPONSE_STATUSES['show']);
        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('user.rsi.comm_links.index')->assertSee(
                $this->commLinks->first()->title
            );
        }
    }

    /**
     * {@inheritdoc}
     * Creates needed Comm-Link Category
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->createSystemLanguages();

        $this->category = Category::factory()->create();

        $this->commLinks = CommLink::factory()->count(5)->create(['category_id' => $this->category->id]);
    }
}
