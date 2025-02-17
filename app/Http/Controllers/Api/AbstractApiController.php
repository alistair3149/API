<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Events\ApiRouteCalled;
use App\Http\Controllers\Controller;
use App\Transformers\Api\LocalizableTransformerInterface;
use App\Transformers\Api\V1\AbstractV1Transformer as V1Transformer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Item;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Star Citizen API',
    contact: new OA\Contact(email: 'foxftw@star-citizen.wiki'),
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
)]
#[OA\Server(url: 'https://api.star-citizen.wiki')]
#[OA\Parameter(
    name: 'page',
    in: 'query',
    schema: new OA\Schema(
        schema: 'page',
        description: 'Page of pagination if any',
        type: 'integer',
        format: 'int64',
        minimum: 0,
    )
)]
#[OA\Parameter(
    name: 'limit',
    in: 'query',
    schema: new OA\Schema(
        schema: 'limit',
        description: 'Items per page, set to 0, to return all items',
        type: 'integer',
        format: 'int64',
        maximum: 1000,
        minimum: 0,
    ),
)]
#[OA\Parameter(
    name: 'locale',
    in: 'query',
    schema: new OA\Schema(
        schema: 'locale',
        description: 'Localization to use.',
        collectionFormat: 'csv',
        enum: [
            'de_DE',
            'en_EN',
        ]
    ),
)]
#[OA\Schema(
    schema: 'query',
    type: 'string',
)]
abstract class AbstractApiController extends Controller
{

    public const SC_DATA_KEY = 'api.sc_data_version';

    public const INVALID_LIMIT_STRING = 'Limit has to be greater than 0';

    public const INVALID_LOCALE_STRING = 'Locale Code \'%s\' is not valid';

    public const INVALID_RELATION_STRING = '\'%s\' does not exist';

    /**
     * Sprintf String which is used if no model was found
     */
    public const NOT_FOUND_STRING = 'No Results for Query \'%s\'';

    /**
     * Limit Get Parameter
     */
    private const LIMIT = 'limit';

    /**
     * Locale Get Parameter
     */
    private const LOCALE = 'locale';

    /**
     * @var Request The API Request
     */
    protected Request $request;

    /**
     * @var V1Transformer The Default Transformer for index and show
     */
    protected V1Transformer $transformer;

    /**
     * @var array Parameter Errors
     */
    protected array $errors = [];

    /**
     * @var ?int Pagination Limit, 0 = no pagination
     */
    protected $limit;

    /**
     * @var string Locale Code, set if Transformer implements LocaleAwareTransformerInterface
     */
    protected string $localeCode;

    /**
     * @var array Extra Metadata to include
     */
    protected array $extraMeta = [];

    protected Manager $manager;

    /**
     * AbstractApiController constructor.
     *
     * @param Request $request API Request
     */
    public function __construct(Request $request)
    {
        parent::__construct();
        $this->request = $request;
        $this->manager = new Manager();
        $this->manager->parseIncludes($request->get('include', ''));

        $this->processRequestParams();
    }

    /**
     * Processes all possible Request Parameters
     */
    protected function processRequestParams(): void
    {
        $this->processLimit();
        //$this->processIncludes();
        $this->processLocale();
    }

    /**
     * Processes the 'limit' Request-Parameter
     */
    private function processLimit(): void
    {
        if ($this->request->has(self::LIMIT) && null !== $this->request->get(self::LIMIT, null)) {
            $itemLimit = (int)$this->request->get(self::LIMIT);

            if ($itemLimit > 0) {
                $this->limit = $itemLimit;
            } elseif (0 === $itemLimit) {
                $this->limit = 0;
            } else {
                $this->errors[self::LIMIT] = static::INVALID_LIMIT_STRING;
            }
        }
    }

    /**
     * Processes the 'locale' Request-Parameter
     */
    private function processLocale(): void
    {
        if ($this->request->has(self::LOCALE) && null !== $this->request->get(self::LOCALE, null)) {
            $this->setLocale($this->request->get(self::LOCALE));
        }
    }

    /**
     * Set the Locale
     *
     * @param string $localeCode
     */
    protected function setLocale(string $localeCode): void
    {
        if (in_array($localeCode, config('language.codes'), true)) {
            $this->localeCode = $localeCode;

            if ($this->transformer instanceof LocalizableTransformerInterface) {
                $this->transformer->setLocale($localeCode);
            }
        } else {
            $this->errors[self::LOCALE] = sprintf(static::INVALID_LOCALE_STRING, $localeCode);
        }
    }

    /**
     * Disables the pagination by setting the limit to 0
     *
     * @return $this
     */
    protected function disablePagination(): self
    {
        $this->limit = 0;

        return $this;
    }

    /**
     * Creates the API Response, Collection if no pagination, Paginator if a limit is set
     * Item if a single model is given
     *
     * @param Builder|Model|Collection $query
     *
     * @return Response
     */
    protected function getResponse($query): Response
    {
        if ($query === null) {
            $query = collect();
        }

        if ($query instanceof Model) {
            $resource = new Item($query, $this->transformer);
            $resource->setMeta($this->getMeta());

            $datum = $this->manager->createData($resource);

            return new Response($datum, 200);
        }

        if ($this->limit === 0 || $query instanceof Collection) {
            if ($query instanceof Builder) {
                $query = $query->get();
            }

            $resource = new \League\Fractal\Resource\Collection($query, $this->transformer);
            $resource->setMeta($this->getMeta());

            return new Response($this->manager->createData($resource), 200);
        }

        $paginate = $query->paginate($this->limit);

        ApiRouteCalled::dispatch([
            'url' => $this->request->fullUrl(),
            'user-agent' => $this->request->userAgent() ?? 'Star Citizen Wiki API',
            'forwarded-for' => $this->request->header('X-Forwarded-For', '127.0.0.1'),
        ]);


        $resource = new \League\Fractal\Resource\Collection(
            $query->get(),
            $this->transformer
        );
        $resource->setMeta($this->getMeta());
        $resource->setPaginator(new IlluminatePaginatorAdapter($paginate));

        return new Response($this->manager->createData($resource), 200);
    }

    /**
     * Generates the Meta Array
     *
     * @return array Meta Array
     */
    protected function getMeta(): array
    {
        $meta = [
            'processed_at' => Carbon::now()->toDateTimeString(),
        ];

        if (!empty($this->errors)) {
            $meta['errors'] = $this->errors;
        }

        if (!empty($this->transformer->getAvailableIncludes())) {
            $meta['valid_relations'] = array_map(
                'Illuminate\Support\Str::snake',
                $this->transformer->getAvailableIncludes()
            );
        }

        return array_merge($meta, $this->extraMeta);
    }

    /**
     * Processes the 'include' Model Relations Request-Parameter
     */
    private function processIncludes(): void
    {
        if ($this->request->has('include') && null !== $this->request->get('include', null)) {
            $this->checkIncludes($this->request->get('include', []));
        }
    }

    /**
     * Processes the given 'include' model relation key
     *
     * @param string|array $relations
     */
    protected function checkIncludes($relations): void
    {
        if (!is_array($relations)) {
            $relations = explode(',', $relations);
        }

        collect($relations)->transform(
            static function ($relation) {
                return trim($relation);
            }
        )
            ->transform(
                static function ($relation) {
                    return Str::camel($relation);
                }
            )
            ->each(
                function ($relation) {
                    if (!in_array($relation, $this->transformer->getAvailableIncludes(), true)) {
                        $this->errors['include'][] = sprintf(static::INVALID_RELATION_STRING, Str::snake($relation));
                    }
                }
            );
    }

    /**
     * Cleans the name for query use
     *
     * @param string $name
     * @return string
     */
    protected function cleanQueryName(string $name): string
    {
        return str_replace('_', ' ', urldecode($name));
    }
}
