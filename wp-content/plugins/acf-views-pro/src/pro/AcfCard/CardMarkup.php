<?php

declare(strict_types=1);

namespace org\wplake\acf_views\pro\AcfCard;

use org\wplake\acf_views\AcfGroups\AcfCardData;

defined('ABSPATH') || exit;

class CardMarkup extends \org\wplake\acf_views\AcfCard\CardMarkup
{
    protected bool $isWithPagination;

    public function __construct(QueryBuilder $queryBuilder)
    {
        parent::__construct($queryBuilder);

        $this->isWithPagination = false;
    }

    protected function getExtraMarkup(AcfCardData $acfCardData): string
    {
        $extraMarkup = parent::getExtraMarkup($acfCardData);

        // pagination always print, to make permanent copy-pasting from using Custom Markup (regardless of the pagination option)
        $extraMarkup .= "\r\n\t" . '$pagination$' . "\r\n";

        return $extraMarkup;
    }

    public function getMarkup(
        AcfCardData $acfCardData,
        bool $isLoadMore = false,
        bool $isIgnoreCustomMarkup = false
    ): string {
        if ($acfCardData->isWithPagination) {
            $this->isWithPagination = true;
        }

        if (! $isIgnoreCustomMarkup &&
            $acfCardData->customMarkup &&
            ! $isLoadMore) {
            $customMarkup = trim($acfCardData->customMarkup);

            if ($customMarkup) {
                $this->markCardAsRendered($acfCardData);

                return $customMarkup;
            }
        }

        return parent::getMarkup($acfCardData, $isLoadMore);
    }

    public function isWithPagination(): bool
    {
        return $this->isWithPagination;
    }
}
