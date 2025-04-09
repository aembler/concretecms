<?php

namespace Concrete\Tests\Attribute\Type;

use Concrete\TestHelpers\Attribute\AttributeTypeTestCase;

class SelectTest extends AttributeTypeTestCase
{
    protected $atHandle = 'select';

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->metadatas[] = 'Concrete\Core\Entity\Attribute\Key\Settings\SelectSettings';
        $this->metadatas[] = 'Concrete\Core\Entity\Attribute\Value\Value\SelectValueOptionList';
    }

    public function testValidateFormEmptyArray(): void
    {
        $this->assertFalse($this->ctrl->validateForm(null));
    }
}
