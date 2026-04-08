<?php

declare(strict_types=1);

namespace Concrete\Tests\Block;

use Concrete\Core\Block\Manifest\BlockManifestParser;
use Concrete\Core\Block\Manifest\Field\FieldManager;
use Concrete\Core\Block\Manifest\FormLayout\FieldReference;
use Concrete\Core\Block\Manifest\FormLayout\Tab;
use Concrete\Tests\TestCase;

final class ManifestTest extends TestCase
{
    public function testCoreManifestFieldManagerRegistersBasicFieldTypes(): void
    {
        $manager = app(FieldManager::class);

        $this->assertTrue($manager->has('text'));
        $this->assertTrue($manager->has('textarea'));
        $this->assertTrue($manager->has('color'));
    }

    public function testParserCanParseBasicManifestFromContainer(): void
    {
        $parser = app(BlockManifestParser::class);

        $manifest = $parser->parseString(<<<XML
<concrete-bdf version="1.0">
    <blocktype handle="sample" name="Sample" description="Example">
        <fields>
            <field id="headline" type="text" label="Headline" />
            <field id="body" type="textarea" label="Body" rows="3" />
            <field id="accent" type="color" label="Accent" default="#abc123" />
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
        $this->assertCount(3, $manifest->getFields());
        $this->assertFalse($manifest->hasErrors());

        $layout = $manifest->getLayout();
        $this->assertCount(2, $layout);
        $this->assertInstanceOf(Tab::class, $layout[0]);
        $this->assertInstanceOf(Tab::class, $layout[1]);
        $this->assertSame('content', $layout[0]->getId());
        $this->assertSame('Content', $layout[0]->getName());
        $this->assertCount(2, $layout[0]->getChildren());
        $this->assertInstanceOf(FieldReference::class, $layout[0]->getChildren()[0]);
        $this->assertSame('headline', $layout[0]->getChildren()[0]->getFieldId());
        $this->assertInstanceOf(FieldReference::class, $layout[0]->getChildren()[1]);
        $this->assertSame('body', $layout[0]->getChildren()[1]->getFieldId());
        $this->assertSame('design', $layout[1]->getId());
        $this->assertSame('Design', $layout[1]->getName());
        $this->assertCount(1, $layout[1]->getChildren());
        $this->assertInstanceOf(FieldReference::class, $layout[1]->getChildren()[0]);
        $this->assertSame('accent', $layout[1]->getChildren()[0]->getFieldId());
    }
}
