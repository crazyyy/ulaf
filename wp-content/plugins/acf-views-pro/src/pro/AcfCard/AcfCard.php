<?php

declare(strict_types=1);

namespace org\wplake\acf_views\pro\AcfCard;

use org\wplake\acf_views\AcfGroups\AcfCardData;

defined('ABSPATH') || exit;

class AcfCard extends \org\wplake\acf_views\AcfCard\AcfCard
{
    protected function renderPaginationMarkup(): string
    {
        if ($this->pagesAmount < 2) {
            return '';
        }

        $paginationMarkup = sprintf(
            '<div class="acf-card__pagination acf-card__pagination--type--%s" data-pages-amount="%s" data-card-id="%s">',
            $this->acfCardData->paginationType,
            $this->pagesAmount,
            $this->acfCardData->getSource()
        );

        switch ($this->acfCardData->paginationType) {
            case AcfCardData::PAGINATION_TYPE_LOAD_MORE_BUTTON:
                $paginationMarkup .= sprintf(
                    '<button class="acf-card__load-more">%s</button>',
                    $this->acfCardData->loadMoreButtonLabel
                );
                break;
            case AcfCardData::PAGINATION_TYPE_PAGE_NUMBERS:
                for ($i = 1; $i <= $this->pagesAmount; $i++) {
                    $activeClass = 1 === $i ?
                        ' acf-card__page--active' :
                        '';
                    $paginationMarkup .= sprintf(
                        '<a class="acf-card__page%s" data-page-number="%s" href="#">%s</a>',
                        $activeClass,
                        $i,
                        $i
                    );
                }
                break;
            case AcfCardData::PAGINATION_TYPE_INFINITY:
                // nothing
                break;
        }

        $paginationMarkup .= '</div>';

        return $paginationMarkup;
    }

    public function queryPostsAndInsertData(int $pageNumber, bool $isMinifyMarkup = true, bool $isLoadMore = false): void
    {
        parent::queryPostsAndInsertData($pageNumber, $isMinifyMarkup, $isLoadMore);

        if ($isLoadMore) {
            return;
        }

        $paginationHTML = $this->acfCardData->isWithPagination ?
            $this->renderPaginationMarkup() :
            '';

        $this->html = str_replace('$pagination$', $paginationHTML, $this->html);

        $cardId = $this->acfCardData->getSource();

        $customVariables = [];
        $customVariables = (array)apply_filters(
            'acf_views/card/custom_variables',
            $customVariables,
            $cardId
        );
        $customVariables = (array)apply_filters(
            'acf_views/card/custom_variables/card_id=' . $cardId,
            $customVariables,
            $cardId
        );

        foreach ($customVariables as $name => $value) {
            $this->html = str_replace('{' . $name . '}', $value, $this->html);
        }
    }
}