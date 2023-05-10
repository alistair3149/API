<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2\SC\Char;

use App\Http\Controllers\Api\V2\AbstractApiV2Controller;
use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\Char\ClothingResource;
use App\Http\Resources\SC\Item\ItemLinkResource;
use App\Http\Resources\SC\Item\ItemResource;
use App\Models\SC\Item\Item;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[OA\Parameter(
    parameter: 'clothing_includes_v2',
    name: 'include',
    in: 'query',
    schema: new OA\Schema(
        schema: 'include',
        description: 'Available Armor Item includes',
        collectionFormat: 'csv',
        enum: [
            'shops',
            'shops.items',
            'ports',
            'resistances',
        ]
    ),
    allowReserved: true
)]
#[OA\Parameter(
    parameter: 'clothing_filter_v2',
    name: 'filter',
    in: 'query',
    schema: new OA\Schema(
        schema: 'filter[type]',
        description: 'Filter list based on type',
        type: 'string',
    ),
    allowReserved: true
)]
#[OA\Parameter(
    parameter: 'commodity_includes_v2',
    name: 'include',
    in: 'query',
    schema: new OA\Schema(
        schema: 'include',
        description: 'Available Commodity Item includes',
        collectionFormat: 'csv',
        enum: [
            'shops',
            'shops.items',
        ]
    ),
    allowReserved: true
)]
class ArmorController extends AbstractApiV2Controller
{
    #[OA\Get(
        path: '/api/v2/armor',
        tags: ['Armor', 'In-Game'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(ref: '#/components/parameters/clothing_filter_v2'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Armors',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/item_link_v2')
                )
            )
        ]
    )]
    public function index(): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(Item::class)
            ->where('type', 'LIKE', 'Char_Armor%')
            ->allowedFilters(['type'])
            ->paginate($this->limit)
            ->appends(request()->query());

        return ItemLinkResource::collection($query);
    }

    #[OA\Get(
        path: '/api/v2/armor/{armor}',
        tags: ['Armor', 'In-Game'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(ref: '#/components/parameters/clothing_includes_v2'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'An Armor Item',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/item_v2')
                )
            )
        ]
    )]
    public function show(Request $request): AbstractBaseResource
    {
        ['clothing' => $identifier] = Validator::validate(
            [
                'clothing' => $request->clothing,
            ],
            [
                'clothing' => 'required|string|min:1|max:255',
            ]
        );

        $identifier = $this->cleanQueryName($identifier);

        try {
            $identifier = QueryBuilder::for(Item::class)
                ->where('type', 'LIKE', 'Char_Armor%')
                ->where(function (Builder $query) use ($identifier) {
                    $query->where('uuid', $identifier)
                        ->orWhere('name', 'LIKE', sprintf('%%%s%%', $identifier));
                })
                ->allowedIncludes(ClothingResource::validIncludes())
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException('No Armor with specified UUID or Name found.');
        }

        return new ItemResource($identifier);
    }
}
