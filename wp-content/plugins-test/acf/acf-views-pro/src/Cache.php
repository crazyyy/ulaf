<?php

declare(strict_types=1);

namespace org\wplake\acf_views;

use org\wplake\acf_views\AcfGroups\AcfCardData;
use org\wplake\acf_views\AcfGroups\AcfViewData;
use org\wplake\acf_views\vendors\LightSource\AcfGroups\Interfaces\AcfGroupInterface;

defined('ABSPATH') || exit;

/**
 * Avoid querying and parsing View/Card's fields multiple times
 * (e.g. one Card can call View's shortcode 10 times, it's better to save than create objects every time
 * (parsing json + objects (its fields) creation))
 *
 * There are more internal cache in the plugin:
 * 1. FieldsMeta (AcfViewData class; avoid calling 'get_field_object()' for every field multiple times)
 * 2. ViewMarkup (ViewMarkup class; save time for processing)
 */
class Cache
{
    private AcfViewData $acfViewData;
    private AcfCardData $acfCardData;

    /**
     * @var AcfGroupInterface[]
     */
    private array $posts;

    public function __construct(AcfViewData $acfViewData, AcfCardData $acfCardData)
    {
        $this->acfViewData = $acfViewData->getDeepClone();
        $this->acfCardData = $acfCardData->getDeepClone();

        $this->posts = [];
    }

    public function getAcfViewData(int $viewId): AcfViewData
    {
        if (key_exists($viewId, $this->posts)) {
            return $this->posts[$viewId];
        }

        $acfViewData = $this->acfViewData->getDeepClone();
        $acfViewData->loadFromPostContent($viewId);

        $this->posts[$viewId] = $acfViewData;

        return $acfViewData;
    }

    public function getAcfCardData(int $cardId): AcfCardData
    {
        if (key_exists($cardId, $this->posts)) {
            return $this->posts[$cardId];
        }

        $acfCardData = $this->acfCardData->getDeepClone();
        $acfCardData->loadFromPostContent($cardId);

        $this->posts[$cardId] = $acfCardData;

        return $acfCardData;
    }
}
