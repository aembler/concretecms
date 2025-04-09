<?php

namespace Concrete\Tests\Attribute\Type;

use Concrete\TestHelpers\Attribute\AttributeTypeTestCase;

class DateTimeTest extends AttributeTypeTestCase
{
    protected $atHandle = 'date_time';

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->metadatas[] = 'Concrete\Core\Entity\Attribute\Key\Settings\DateTimeSettings';
    }

    public function testValidateFormEmptyArray(): void
    {
        $this->assertFalse($this->ctrl->validateForm(null));
    }
}
