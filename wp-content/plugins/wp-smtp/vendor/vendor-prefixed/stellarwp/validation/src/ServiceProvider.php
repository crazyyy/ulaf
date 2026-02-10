<?php
/**
 * @license GPL-2.0-or-later
 *
 * Modified using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace SolidWP\Mail\StellarWP\Validation;

use SolidWP\Mail\StellarWP\Validation\Rules\Boolean;
use SolidWP\Mail\StellarWP\Validation\Rules\Currency;
use SolidWP\Mail\StellarWP\Validation\Rules\DateTime;
use SolidWP\Mail\StellarWP\Validation\Rules\Email;
use SolidWP\Mail\StellarWP\Validation\Rules\Exclude;
use SolidWP\Mail\StellarWP\Validation\Rules\ExcludeIf;
use SolidWP\Mail\StellarWP\Validation\Rules\ExcludeUnless;
use SolidWP\Mail\StellarWP\Validation\Rules\In;
use SolidWP\Mail\StellarWP\Validation\Rules\InStrict;
use SolidWP\Mail\StellarWP\Validation\Rules\Integer;
use SolidWP\Mail\StellarWP\Validation\Rules\Max;
use SolidWP\Mail\StellarWP\Validation\Rules\Min;
use SolidWP\Mail\StellarWP\Validation\Rules\Nullable;
use SolidWP\Mail\StellarWP\Validation\Rules\NullableIf;
use SolidWP\Mail\StellarWP\Validation\Rules\NullableUnless;
use SolidWP\Mail\StellarWP\Validation\Rules\Numeric;
use SolidWP\Mail\StellarWP\Validation\Rules\Optional;
use SolidWP\Mail\StellarWP\Validation\Rules\OptionalIf;
use SolidWP\Mail\StellarWP\Validation\Rules\OptionalUnless;
use SolidWP\Mail\StellarWP\Validation\Rules\Required;
use SolidWP\Mail\StellarWP\Validation\Rules\Size;

class ServiceProvider
{
    private $validationRules = [
        Required::class,
        Min::class,
        Max::class,
        Size::class,
        Numeric::class,
        In::class,
        InStrict::class,
        Integer::class,
        Email::class,
        Currency::class,
        Exclude::class,
        ExcludeIf::class,
        ExcludeUnless::class,
        Nullable::class,
        NullableIf::class,
        NullableUnless::class,
        Optional::class,
        OptionalIf::class,
        OptionalUnless::class,
        DateTime::class,
        Boolean::class,
    ];

    /**
     * Registers the validation rules registrar with the container
     */
    public function register()
    {
        Config::getServiceContainer()->singleton(ValidationRulesRegistrar::class, function () {
            $register = new ValidationRulesRegistrar();

            foreach ($this->validationRules as $rule) {
                $register->register($rule);
            }

            do_action(Config::getHookPrefix() . 'register_validation_rules', $register);

            return $register;
        });
    }
}
