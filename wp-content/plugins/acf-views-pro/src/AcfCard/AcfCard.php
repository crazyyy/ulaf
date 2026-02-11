<?php

declare(strict_types=1);

namespace org\wplake\acf_views\AcfCard;

use org\wplake\acf_views\AcfGroups\AcfCardData;
use org\wplake\acf_views\Plugin;

defined('ABSPATH') || exit;

class AcfCard
{
    protected string $html;
    protected AcfCardData $acfCardData;
    protected QueryBuilder $queryBuilder;
    protected CardMarkup $cardMarkup;
    protected int $pagesAmount;
    protected array $postIds;

    public function __construct(AcfCardData $acfCardData, QueryBuilder $queryBuilder, CardMarkup $cardMarkup)
    {
        $this->html = '';
        $this->acfCardData = $acfCardData;
        $this->queryBuilder = $queryBuilder;
        $this->cardMarkup = $cardMarkup;
        $this->pagesAmount = 0;
        $this->postIds = [];
    }

    protected function renderPosts(): string
    {
        $markup = '';

        foreach ($this->postIds as $postId) {
            $shortcode = sprintf(
                "[%s view-id='%s' object-id='%s']",
                Plugin::SHORTCODE,
                $this->acfCardData->acfViewId,
                $postId
            );
            $markup .= do_shortcode($shortcode);
        }

        return $markup;
    }

    public function queryPostsAndInsertData(int $pageNumber, bool $isMinifyMarkup = true, bool $isLoadMore = false): void
    {
        if ($isMinifyMarkup) {
            // remove special symbols that used in the markup for a preview
            // exactly here, before the fields are inserted, to avoid affecting them
            $this->html = str_replace(["\t", "\n", "\r"], '', $this->html);
        }

        $postsData = $this->queryBuilder->getPostsData($this->acfCardData, $pageNumber);
        $this->pagesAmount = $postsData['pagesAmount'];
        $this->postIds = $postsData['postIds'];

        $itemsMarkup = $this->renderPosts();

        if (!$itemsMarkup &&
            $this->acfCardData->noPostsFoundMessage) {
            $itemsMarkup = sprintf(
                '<div class="acf-card__no-posts-message">%s</div>',
                $this->acfCardData->noPostsFoundMessage
            );
        }

        // don't use the 'AcfCardData->markup' field, as user can override it (and it shouldn't be supported)
        $this->html = $this->cardMarkup->getMarkup($this->acfCardData, $isLoadMore);
        $this->html = str_replace('$items$', $itemsMarkup, $this->html);
    }

    public function getHTML(): string
    {
        return $this->html;
    }
}