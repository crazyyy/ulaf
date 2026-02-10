<?php

declare(strict_types=1);

namespace org\wplake\acf_views\AcfView;

use org\wplake\acf_views\Acf;

defined('ABSPATH') || exit;

class Post
{
    // all fields have ids like 'field_x', so no conflicts possible
    const FIELD_TITLE = '_post_title';
    const FIELD_TITLE_LINK = '_post_title_link';
    const FIELD_THUMBNAIL = '_thumbnail_id';
    const FIELD_THUMBNAIL_LINK = '_thumbnail_id_link';
    const FIELD_AUTHOR = '_post_author';
    const FIELD_DATE = '_post_date';
    const FIELD_MODIFIED = '_post_modified';
    const FIELD_EXCERPT = '_post_excerpt';
    const FIELD_LINK = '_post_link';

    /**
     * @var int|string Can be string in case with 'options' or 'user_x'
     */
    private $id;
    private array $fieldsCache;
    private bool $isBlock;

    /**
     * @param int|string $id
     * @param array $fieldsCache
     * @param bool $isBlock
     */
    public function __construct($id, array $fieldsCache = [], bool $isBlock = false)
    {
        $this->id = $id;
        $this->fieldsCache = $fieldsCache;
        $this->isBlock = $isBlock;
    }

    public static function getFields(): array
    {
        return [
            self::FIELD_TITLE,
            self::FIELD_THUMBNAIL,
            self::FIELD_THUMBNAIL_LINK,
            self::FIELD_AUTHOR,
            self::FIELD_DATE,
            self::FIELD_MODIFIED,
            self::FIELD_EXCERPT,
            self::FIELD_TITLE_LINK,
            self::FIELD_LINK,
        ];
    }

    public function isOptions(): bool
    {
        return 'options' === $this->id;
    }

    public function getTitle(): string
    {
        if ($this->isOptions()) {
            return '';
        }

        return get_the_title($this->id);
    }

    public function getExcerpt(): string
    {
        if ($this->isOptions()) {
            return '';
        }

        return get_the_excerpt($this->id);
    }

    public function getLink(): string
    {
        if ($this->isOptions()) {
            return '';
        }

        return (string)get_the_permalink($this->id);
    }

    public function getTitleLink(): array
    {
        // array according to the ACF 'link' with 'array' return format
        return [
            'url' => $this->getLink(),
            'title' => $this->getTitle(),
        ];
    }

    public function getThumbnailLink(): array
    {
        $info = [
            'image_id' => 0,
            'permalink' => 0,
        ];

        if ($this->isOptions()) {
            return $info;
        }

        $info['image_id'] = (int)get_post_thumbnail_id($this->id);
        $info['permalink'] = (string)get_the_permalink($this->id);

        return $info;
    }

    public function getThumbnailId(): int
    {
        if ($this->isOptions()) {
            return 0;
        }

        return (int)get_post_thumbnail_id($this->id);
    }

    public function getAuthor(): array
    {
        if ($this->isOptions()) {
            return [
                'url' => '',
                'title' => '',
            ];
        }

        $authorId = get_post_field('post_author', $this->id);
        $authorUser = get_user_by('ID', $authorId);

        // array according to the ACF 'link' with 'array' return format
        return [
            'url' => get_author_posts_url($authorUser->ID ?? 0),
            'title' => $authorUser->display_name ?? '',
        ];
    }

    public function getDate(): string
    {
        if ($this->isOptions()) {
            return '';
        }

        return (string)get_the_date('', $this->id);
    }

    public function getModifiedDate(): string
    {
        if ($this->isOptions()) {
            return '';
        }

        return (string)get_the_modified_date('', $this->id);
    }

    /**
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    protected function getPostField(string $fieldName)
    {
        $value = '';

        switch ($fieldName) {
            case self::FIELD_TITLE:
                $value = $this->getTitle();
                break;
            case self::FIELD_THUMBNAIL:
                $value = $this->getThumbnailId();
                break;
            case self::FIELD_THUMBNAIL_LINK:
                $value = $this->getThumbnailLink();
                break;
            case self::FIELD_AUTHOR:
                $value = $this->getAuthor();
                break;
            case self::FIELD_DATE:
                $value = $this->getDate();
                break;
            case self::FIELD_MODIFIED:
                $value = $this->getModifiedDate();
                break;
            case self::FIELD_EXCERPT:
                $value = $this->getExcerpt();
                break;
            case self::FIELD_TITLE_LINK:
                $value = $this->getTitleLink();
                break;
            case self::FIELD_LINK:
                $value = $this->getLink();
                break;
        }

        return $value;
    }

    protected function getTermIds(string $fieldName): array
    {
        if ($this->isOptions()) {
            return [];
        }

        $taxonomyName = substr($fieldName, strlen(Acf::TAXONOMY_PREFIX));
        $postTerms = get_the_terms($this->id, $taxonomyName);

        if (false === $postTerms ||
            is_wp_error($postTerms)) {
            return [];
        }

        return array_column($postTerms, 'term_id');
    }

    public function getFieldValue(string $fieldName, bool $isWithoutFormatting = false, bool $isSkipCache = false)
    {
        if (isset($this->fieldsCache[$fieldName]) &&
            !$isSkipCache) {
            return $this->fieldsCache[$fieldName];
        }

        $value = '';

        if (in_array($fieldName, $this->getFields(), true)) {
            $value = $this->getPostField($fieldName);
        } elseif (0 === strpos($fieldName, Acf::TAXONOMY_PREFIX)) {
            $value = $this->getTermIds($fieldName);
        } else {
            if (function_exists('get_field')) {
                $value = !$this->isBlock ?
                    get_field($fieldName, $this->id, !$isWithoutFormatting) :
                    get_field($fieldName, false, !$isWithoutFormatting);
            }
        }

        $this->fieldsCache[$fieldName] = $value;

        return $value;
    }
}
