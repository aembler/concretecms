<?php

declare(strict_types=1);

namespace Concrete\Core\Block\Manifest\Field;

use Concrete\Core\Block\Manifest\FieldDefinition;

final class EnvelopeSectionResolver
{
    public static function resolve(FieldDefinition $field): string
    {
        // @todo - we're keeping this here in case we need to eventually
        // support different sections of the envelope.
        return 'fields';
    }
}
