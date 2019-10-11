<?php

declare(strict_types=1);
/**
 * User: Hannes
 * Date: 11.09.2018
 * Time: 17:38.
 */

namespace App\Jobs\Rsi\CommLink\Parser\Element;

use App\Jobs\Rsi\CommLink\Parser\Element\AbstractBaseElement as BaseElement;
use App\Jobs\Rsi\CommLink\Parser\ParseCommLink;
use App\Models\Rsi\CommLink\Image\Image as ImageModel;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Extracts and Creates Image Models from Comm-Link Contents.
 */
class Image extends BaseElement
{
    private const RSI_DOMAINS = [
        'robertsspaceindustries.com',
        'media.robertsspaceindustries.com',
    ];

    /**
     * Post Background CSS Selector.
     */
    private const POST_BACKGROUND = '#post-background';

    /**
     * @var \Symfony\Component\DomCrawler\Crawler
     */
    private $commLink;

    /**
     * @var array Image Data Array
     */
    private $images = [];

    /**
     * Image constructor.
     *
     * @param \Symfony\Component\DomCrawler\Crawler $commLinkDocument
     */
    public function __construct(Crawler $commLinkDocument)
    {
        $this->commLink = $commLinkDocument;
    }

    /**
     * Returns an array with image ids from the image table.
     *
     * @return array Image IDs
     */
    public function getImageIds(): array
    {
        $this->extractImages();
        $imageIDs = [];

        $contentImages = collect($this->images);
        $contentImages->filter(
            static function ($image) {
                $host = parse_url($image['src'], PHP_URL_HOST);

                return null === $host || in_array($host, self::RSI_DOMAINS, true);
            }
        )->each(
            function ($image) use (&$imageIDs) {
                $src = $this->cleanImgSource($image['src']);

                $imageIDs[] = ImageModel::query()->firstOrCreate(
                    [
                        'src' => $this->cleanText($src),
                        'alt' => $this->cleanText($image['alt']),
                        'dir' => $this->getDirHash($src),
                    ]
                )->id;
            }
        );

        return array_unique($imageIDs);
    }

    /**
     * Extracts all <img> Elements from the Crawler
     * Saves src and alt attributes.
     */
    private function extractImages(): void
    {
        $filter = ParseCommLink::POST_SELECTOR;
        if ($this->isSubscriberPage($this->commLink)) {
            $filter = '#subscribers .album-wrapper';
        }

        $this->commLink->filter($filter)->filterXPath('//img')->each(
            function (Crawler $crawler) {
                $src = $crawler->attr('src');

                if (null !== $src && !empty($src)) {
                    $this->images[] = [
                        'src' => trim($src),
                        'alt' => $crawler->attr('alt') ?? '',
                    ];
                }
            }
        );

        if ($this->commLink->filter(self::POST_BACKGROUND)->count() > 0) {
            $background = $this->commLink->filter(self::POST_BACKGROUND);
            $src = $background->attr('style');

            if (null !== $src && !empty($src)) {
                if (preg_match('/(\/media\/.*\.\w+)/', $src, $src)) {
                    $src = $src[1];
                }

                if (!empty($src)) {
                    $this->images[] = [
                        'src' => trim($src),
                        'alt' => self::POST_BACKGROUND,
                    ];
                }
            }
        }

        preg_match_all(
            "/source:\s?'(https:\/\/(?:media\.)?robertsspaceindustries\.com.*?)'/",
            $this->commLink->html(),
            $matches
        );

        if (!empty($matches[1])) {
            collect($matches[1])->each(
                function ($src) {
                    $this->images[] = [
                        'src' => trim($src),
                        'alt' => '',
                    ];
                }
            );
        }
    }

    /**
     * Cleans the IMG SRC.
     *
     * @param string $src IMG SRC
     *
     * @return string
     */
    private function cleanImgSource(string $src): string
    {
        $srcUrlPath = parse_url($src, PHP_URL_PATH);
        $srcUrlPath = str_replace(['%20', '%0A'], '', $srcUrlPath);

        // if host is media.robertsspaceindustries.com
        if (parse_url($src, PHP_URL_HOST) === self::RSI_DOMAINS[1]) {
            $pattern = '/(\w+)\/(?:\w+)\.(\w+)/';
            $replacement = '$1/source.$2';
        } else {
            $pattern = '/media\/(\w+)\/(\w+)\//';
            $replacement = 'media/$1/source/';
        }

        $srcUrlPath = preg_replace($pattern, $replacement, $srcUrlPath);

        $srcUrlPath = str_replace('//', '/', $srcUrlPath);
        $srcUrlPath = trim(ltrim($srcUrlPath, '/'));

        return "/{$srcUrlPath}";
    }

    /**
     * Try to get Original RSI Hash.
     *
     * @param string $src
     *
     * @return string|null
     */
    private function getDirHash(string $src): ?string
    {
        $src = substr($src, 1);
        $dir = str_replace('media/', '', $src);
        $dir = explode('/', $dir);

        return $dir[0] ?? null;
    }
}
