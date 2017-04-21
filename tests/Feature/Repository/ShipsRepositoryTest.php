<?php

namespace Tests\Feature\Repository;

use App\Exceptions\InvalidDataException;
use App\Repositories\StarCitizenWiki\APIv1\ShipsRepository;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Class ShipsTest
 * @package Tests\Feature\Repository
 * @covers \App\Repositories\BaseAPITrait
 * @covers \App\Repositories\StarCitizenWiki\BaseStarCitizenWikiAPI
 */
class ShipsRepositoryTest extends TestCase
{
    /**
     * @var ShipsRepository
     */
    private $repository;

    protected function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->repository = resolve(ShipsRepository::class);
    }

    /**
     * Get Ship from Repository
     *
     * @covers \App\Repositories\StarCitizenWiki\APIv1\ShipsRepository::getShip()
     */
    public function testShipRetrieval()
    {
        $this->repository->getShip(new Request(), '300i');
        $this->assertContains('300i', $this->repository->asJSON());
    }

    /**
     * @covers \App\Repositories\StarCitizenWiki\APIv1\ShipsRepository::getShipList()
     */
    public function testShipList()
    {
        $this->repository->getShipList();
        $this->assertContains('300i', $this->repository->asJSON());
    }

    /**
     * @covers \App\Repositories\StarCitizenWiki\APIv1\ShipsRepository::searchShips()
     */
    public function testShipSearch()
    {
        $this->repository->searchShips('300i');
        $this->assertContains('300i', $this->repository->asJSON());
    }

    /**
     * Test if Filter is working
     *
     * @covers \App\Repositories\StarCitizenWiki\APIv1\ShipsRepository::getShipList()
     */
    public function testFilter()
    {
         $this->repository->getShipList();
         $this->repository->transformer->addFilterArray(['api_url']);
         $this->assertNotContains('"wiki_url":', $this->repository->asJSON());
    }

    /**
     * Test if InvalidDataException is thrown if unknown field is filtered
     *
     * @covers \App\Traits\FiltersDataTrait::filterData()
     */
    public function testFilterException()
    {
        $this->repository->getShipList();
        $this->expectException(InvalidDataException::class);
        $this->repository->transformer->addFilterArray(['notexists']);
        $this->repository->asJSON();
    }
}
