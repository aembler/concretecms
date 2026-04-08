<?php

declare(strict_types=1);

namespace Concrete\Tests\Block\Manifest;

use Concrete\Core\Block\Manifest\BlockManifestParser;
use Concrete\Core\Block\Manifest\Exception\MalformedManifestException;
use Concrete\Core\Block\Manifest\Field\FieldManager;
use Concrete\Core\Block\Manifest\Field\Type\ColorFieldType;
use Concrete\Core\Block\Manifest\Field\Type\TextareaFieldType;
use Concrete\Core\Block\Manifest\Field\Type\TextFieldType;
use Concrete\Core\Block\Manifest\FormLayout\FieldReference;
use Concrete\Core\Block\Manifest\FormLayout\Tab;
use Concrete\Tests\TestCase;

final class BlockManifestParserTest extends TestCase
{
    protected function getParser(): BlockManifestParser
    {
        $manager = new FieldManager();
        $manager->register(new TextFieldType());
        $manager->register(new TextareaFieldType());
        $manager->register(new ColorFieldType());

        return new BlockManifestParser($manager);
    }

    public function testParsesManifestWithKnownFieldTypes(): void
    {
        $manifest = $this->getParser()->parseString(<<<XML
<concrete-bdf version="1.0">
    <blocktype handle="sample" name="Sample" description="Example" package="">
        <icon>
            <svg viewBox="0 0 10 10"><rect width="10" height="10" /></svg>
        </icon>
        <fields>
            <field id="headline" type="text" label="Headline" default="Hello" />
            <field id="body" type="textarea" label="Body" rows="4" />
            <field id="accent" type="color" label="Accent" default="#ffaa00" />
        </fields>
        <formlayout>
            <tab id="content" name="Content">
                <field id="headline" />
                <field id="body" />
            </tab>
            <tab id="design" name="Design">
                <field id="accent" />
            </tab>
        </formlayout>
    </blocktype>
</concrete-bdf>
XML);

        $this->assertSame('sample', $manifest->getHandle());
        $this->assertSame('Sample', $manifest->getName());
        $this->assertSame('Example', $manifest->getDescription());
        $this->assertCount(3, $manifest->getFields());
        $this->assertFalse($manifest->hasErrors());
        $this->assertStringContainsString('<svg', $manifest->getIcon());
        $this->assertInstanceOf(TextFieldType::class, $manifest->getField('headline')?->getFieldType());
        $this->assertSame('#FFAA00', $manifest->getField('accent')?->getDefinition()['default']);
        $this->assertSame(4, $manifest->getField('body')?->getDefinition()['rows']);

        $layout = $manifest->getLayout();
        $this->assertCount(2, $layout);
        $this->assertInstanceOf(Tab::class, $layout[0]);
        $this->assertSame('Content', $layout[0]->getName());
        $this->assertCount(2, $layout[0]->getChildren());
        $this->assertInstanceOf(Tab::class, $layout[1]);
        $this->assertSame('Design', $layout[1]->getName());
        $this->assertCount(1, $layout[1]->getChildren());
        $this->assertInstanceOf(FieldReference::class, $layout[1]->getChildren()[0]);
        $this->assertSame('accent', $layout[1]->getChildren()[0]->getFieldId());
    }

    public function testDuplicateFieldIdsAreFatal(): void
    {
        $this->expectException(MalformedManifestException::class);
        $this->expectExceptionMessage('Duplicate field id "headline"');

        $this->getParser()->parseString(<<<XML
<concrete-bdf version="1.0">
    <blocktype handle="sample" name="Sample">
        <fields>
            <field id="headline" type="text" />
            <field id="headline" type="textarea" />
        </fields>
    </blocktype>
</concrete-bdf>
XML);
    }

    public function testDuplicateLayoutFieldReferencesAreFatal(): void
    {
        $this->expectException(MalformedManifestException::class);
        $this->expectExceptionMessage('Field "headline" may only appear once');

        $this->getParser()->parseString(<<<XML
<concrete-bdf version="1.0">
    <blocktype handle="sample" name="Sample">
        <fields>
            <field id="headline" type="text" />
        </fields>
        <formlayout>
            <tab id="content" name="Content">
                <field id="headline" />
                <field id="headline" />
            </tab>
        </formlayout>
    </blocktype>
</concrete-bdf>
XML);
    }

    public function testUnknownFieldTypesBecomeRecoverableManifestErrors(): void
    {
        $manifest = $this->getParser()->parseString(<<<XML
<concrete-bdf version="1.0">
    <blocktype handle="sample" name="Sample">
        <fields>
            <field id="headline" type="mystery" label="Headline" />
        </fields>
        <formlayout>
            <tab id="content" name="Content">
                <field id="headline" />
            </tab>
        </formlayout>
    </blocktype>
</concrete-bdf>
XML);

        $this->assertTrue($manifest->hasErrors());
        $this->assertSame('unknown_field_type', $manifest->getErrors()[0]->getCode());
        $this->assertFalse($manifest->getField('headline')->hasKnownFieldType());
        $this->assertSame('mystery', $manifest->getField('headline')->getType());
    }
}
