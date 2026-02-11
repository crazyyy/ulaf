<?php

declare(strict_types=1);

namespace org\wplake\acf_views\AcfCard;

use org\wplake\acf_views\AcfGroups\AcfCardData;
use org\wplake\acf_views\AcfGroups\CardLayoutData;

defined('ABSPATH') || exit;

class CardMarkup
{
    protected QueryBuilder $queryBuilder;
    /**
     * @var AcfCardData[]
     */
    protected array $renderedCards;

    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
        $this->renderedCards = [];
    }

    protected function getExtraMarkup(AcfCardData $acfCardData): string
    {
        return '';
    }

    protected function markCardAsRendered(AcfCardData $acfCardData): void
    {
        if (!key_exists($acfCardData->getSource(), $this->renderedCards)) {
            $this->renderedCards[$acfCardData->getSource()] = $acfCardData;
        }
    }

    public function getMarkup(
        AcfCardData $acfCardData,
        bool $isLoadMore = false,
        bool $isIgnoreCustomMarkup = false
    ): string {
        $markup = '';

        if (!$isLoadMore) {
            $classes = sprintf('acf-card acf-card--id--%s', $acfCardData->getSource());

            $extraClasses = trim($acfCardData->cssClasses);
            $extraClasses = $extraClasses ?
                ' ' . $extraClasses :
                '';

            $classes .= $extraClasses;

            $markup .= sprintf('<div class="%s">' . "\r\n", $classes);
        }

        $markup .= !$isLoadMore ?
            "\r\n\t" . '<div class="acf-card__items">' . "\r\n" :
            '';

        $markup .= "\t\t" . '$items$' . "\r\n";

        $markup .= !$isLoadMore ?
            "\t" . '</div>' . "\r\n" :
            '';
        $markup .= !$isLoadMore ?
            $this->getExtraMarkup($acfCardData) :
            '';
        $markup .= !$isLoadMore ?
            "\r\n" . '</div>' . "\r\n" :
            '';

        $this->markCardAsRendered($acfCardData);

        return $markup;
    }

    public function getRenderedCards(): array
    {
        return $this->renderedCards;
    }

    public function getLayoutCSS(AcfCardData $acfCardData): string
    {
        if (!$acfCardData->isUseLayoutCss) {
            return '';
        }

        $message = __(
            "Manually edit these rules by disabling Layout Rules, otherwise these rules are updated every time you press the 'Update' button",
            'acf-views'
        );

        $css = "/*BEGIN LAYOUT_RULES*/\n";
        $css .= sprintf("/*%s*/\n", $message);

        $rules = [];

        foreach ($acfCardData->layoutRules as $layoutRule) {
            $screen = 0;
            switch ($layoutRule->screen) {
                case CardLayoutData::SCREEN_TABLET:
                    $screen = 576;
                    break;
                case CardLayoutData::SCREEN_DESKTOP:
                    $screen = 992;
                    break;
                case CardLayoutData::SCREEN_LARGE_DESKTOP:
                    $screen = 1400;
                    break;
            }

            $rule = [];

            $rule[] = ' display:grid;';

            switch ($layoutRule->layout) {
                case CardLayoutData::LAYOUT_ROW:
                    $rule[] = ' grid-auto-flow:column;';
                    $rule[] = sprintf(' grid-column-gap:%s;', $layoutRule->horizontalGap);
                    break;
                case CardLayoutData::LAYOUT_COLUMN:
                    // the right way is 1fr, but use "1fr" because CodeMirror doesn't recognize it, "1fr" should be replaced with 1fr on the output
                    $rule[] = ' grid-template-columns:"1fr";';
                    $rule[] = sprintf(' grid-row-gap:%s;', $layoutRule->verticalGap);
                    break;
                case CardLayoutData::LAYOUT_GRID:
                    $rule[] = sprintf(' grid-template-columns:repeat(%s, "1fr");', $layoutRule->amountOfColumns);
                    $rule[] = sprintf(' grid-column-gap:%s;', $layoutRule->horizontalGap);
                    $rule[] = sprintf(' grid-row-gap:%s;', $layoutRule->verticalGap);
                    break;
            }

            $rules[$screen] = $rule;
        }

        // order is important in media rules
        ksort($rules);

        foreach ($rules as $screen => $rule) {
            if ($screen) {
                $css .= sprintf("\n@media screen and (min-width:%spx) {", $screen);
            }

            $css .= "\n#card .acf-card__items {\n";
            $css .= join("\n", $rule);
            $css .= "\n}\n";

            if ($screen) {
                $css .= "}\n";
            }
        }

        $css .= "\n/*END LAYOUT_RULES*/";

        return $css;
    }
}
