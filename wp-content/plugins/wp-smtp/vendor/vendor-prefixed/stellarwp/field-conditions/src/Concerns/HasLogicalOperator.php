<?php
/**
 * @license GPL-2.0-or-later
 *
 * Modified using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace SolidWP\Mail\StellarWP\FieldConditions\Concerns;

use SolidWP\Mail\StellarWP\FieldConditions\Config;
use SolidWP\Mail\StellarWP\FieldConditions\Contracts\Condition;

trait HasLogicalOperator
{
    /**
     * @var 'and'|'or'
     */
    protected $logicalOperator;

    /**
     * @since 1.0.0
     *
     * @return void
     */
    public function setLogicalOperator(string $operator)
    {
        if ( ! in_array($operator, Condition::LOGICAL_OPERATORS, true)) {
            throw Config::throwInvalidArgumentException(
                "Invalid logical operator: $operator. Must be one of: " . implode(
                    ', ',
                    Condition::LOGICAL_OPERATORS
                )
            );
        }

        $this->logicalOperator = $operator;
    }

    /**
     * @since 1.0.0
     */
    public function getLogicalOperator(): string
    {
        return $this->logicalOperator;
    }
}
