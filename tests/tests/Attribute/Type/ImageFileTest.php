<?php

namespace Concrete\Tests\Attribute\Type;

use Concrete\TestHelpers\Attribute\AttributeTypeTestCase;
use Concrete\Core\Error\ErrorList\Error\FieldNotPresentError;

class ImageFileTest extends AttributeTypeTestCase
{
    protected $atHandle = 'image_file';

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->metadatas[] = 'Concrete\Core\Entity\Attribute\Key\Settings\ImageFileSettings';
    }

    public function testValidateFormEmptyArray(): void
    {
        $this->assertInstanceOf(FieldNotPresentError::class, $this->ctrl->validateForm(null));
    }
}
