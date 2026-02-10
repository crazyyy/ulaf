<?php

declare(strict_types=1);

namespace org\wplake\acf_views\AcfCard;

use org\wplake\acf_views\AcfGroups\AcfCardData;
use org\wplake\acf_views\AcfView\FieldMeta;
use WP_Query;

defined('ABSPATH') || exit;

class QueryBuilder
{
    public function getQueryArgs(AcfCardData $acfCardData, int $pageNumber): array
    {
        $args = [
            'fields' => 'ids',
            'post_type' => $acfCardData->postTypes,
            'post_status' => $acfCardData->postStatuses,
            'posts_per_page' => $acfCardData->limit,
            'order' => $acfCardData->order,
            'orderby' => $acfCardData->orderBy,
            'ignore_sticky_posts' => $acfCardData->isIgnoreStickyPosts,
        ];

        if ($acfCardData->postIn) {
            $args['post__in'] = $acfCardData->postIn;
        }

        if ($acfCardData->postNotIn) {
            $args['post__not_in'] = $acfCardData->postNotIn;
        }

        if (in_array($acfCardData->orderBy, ['meta_value', 'meta_value_num',], true)) {
            $fieldMeta = new FieldMeta($acfCardData->getOrderByMetaAcfFieldId());

            if ($fieldMeta->isFieldExist()) {
                $args['meta_key'] = $fieldMeta->getName();
            }
        }

        return $args;
    }

    public function getPostsData(AcfCardData $acfCardData, int $pageNumber = 1): array
    {
        // stub for tests
        if (!class_exists('WP_Query')) {
            return [
                'pagesAmount' => 0,
                'postIds' => [],
            ];
        }

        $queryArgs = $this->getQueryArgs($acfCardData, $pageNumber);
        $query = new WP_Query($queryArgs);

        $postsPerPage = $queryArgs['posts_per_page'] ?? 0;

        // otherwise, can be DivisionByZero error
        $pagesAmount = $postsPerPage ?
            (int)ceil($query->found_posts / $postsPerPage) :
            0;

        return [
            'pagesAmount' => $pagesAmount,
            // only ids, as the 'fields' argument is set
            'postIds' => $query->get_posts(),
        ];
    }
}
