<?php

declare(strict_types=1);

namespace org\wplake\acf_views\pro\AcfCard;

use org\wplake\acf_views\AcfGroups\AcfCardData;

defined('ABSPATH') || exit;

class AcfCards extends \org\wplake\acf_views\AcfCard\AcfCards
{
    const COLUMN_MOUNT_POINTS = self::NAME . '_mountPoints';

    protected function addProBannerMetabox(): void
    {
        // nothing
    }

    protected function getAcfCard(AcfCardData $acfCardData): AcfCard
    {
        return new AcfCard($acfCardData, $this->queryBuilder, $this->cardMarkup);
    }

    public function getColumns(array $columns): array
    {
        $columns = parent::getColumns($columns);

        return $this->insertIntoArrayAfterKey($columns, self::COLUMN_SHORTCODE, [
            self::COLUMN_MOUNT_POINTS => __('Mount points', 'acf-views'),
        ]);
    }

    public function printColumn(string $column, int $postId): void
    {
        parent::printColumn($column, $postId);

        switch ($column) {
            case self::COLUMN_MOUNT_POINTS:
                $this->printMountPoints($this->cache->getAcfCardData($postId));
                break;
        }
    }

    public function setHooks(): void
    {
        parent::setHooks();

        add_action('wp_ajax_' . self::NAME, [$this, 'loadMoreAjax']);
        add_action('wp_ajax_nopriv_' . self::NAME, [$this, 'loadMoreAjax']);
    }

    public function loadMoreAjax(): void
    {
        $response = [
            'error' => '',
            'html' => '',
        ];

        $acfCardId = sanitize_text_field($_POST['_acfCardId'] ?? '0');
        $acfCardId = intval($acfCardId);

        $pageNumber = sanitize_text_field($_POST['_pageNumber'] ?? '0');
        $pageNumber = intval($pageNumber);

        if (!$acfCardId ||
            !$pageNumber) {
            $response['error'] .= __('Request is wrong, required fields are missing.', 'acf-views');
            echo json_encode($response);

            exit;
        }

        $acfCardPost = get_post($acfCardId);

        if (!$acfCardPost ||
            AcfCards::NAME !== $acfCardPost->post_type ||
            'publish' !== $acfCardPost->post_status) {
            $response['error'] .= __('Request is wrong, ACF Card is missing.', 'acf-views');
            echo json_encode($response);

            exit;
        }

        $acfCardData = $this->cache->getAcfCardData($acfCardPost->ID);

        if (!$acfCardData->isWithPagination) {
            $response['error'] .= __('Request is wrong, ACF Card is unavailable.', 'acf-views');
            echo json_encode($response);

            exit;
        }

        $acfCard = $this->getAcfCard($acfCardData);
        $acfCard->queryPostsAndInsertData($pageNumber, true, true);

        $response['html'] = $acfCard->getHTML();

        echo json_encode($response);

        exit;
    }
}
