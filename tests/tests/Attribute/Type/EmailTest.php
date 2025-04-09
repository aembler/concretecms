<?php

namespace Concrete\Tests\Attribute\Type;

use Concrete\TestHelpers\Attribute\AttributeTypeTestCase;
use Concrete\Core\Error\ErrorList\Error\FieldNotPresentError;

class EmailTest extends AttributeTypeTestCase
{
    protected $atHandle = 'email';

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->metadatas[] = 'Concrete\Core\Entity\Attribute\Key\Settings\TextSettings';
    }

    public function testValidateFormEmptyArray(): void
    {
        $this->assertInstanceOf(FieldNotPresentError::class, $this->ctrl->validateForm(null));
    }
}
