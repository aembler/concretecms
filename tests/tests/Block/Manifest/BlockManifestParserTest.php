<?php

declare(strict_types=1);

namespace Concrete\Tests\Block\Manifest;

use Concrete\Core\Block\Manifest\BlockManifestParser;
use Concrete\Core\Block\Manifest\FieldDefinitionParser;
use Concrete\Core\Block\Manifest\GlobalFieldRegistry;
use Concrete\Core\Block\Manifest\Exception\MalformedManifestException;
use Concrete\Core\Block\Manifest\Field\FieldManager;
use Concrete\Core\Block\Manifest\Field\Type\ColorFieldType;
use Concrete\Core\Block\Manifest\Field\Type\TextareaFieldType;
use Concrete\Core\Block\Manifest\Field\Type\TextFieldType;
use Concrete\Core\Block\Manifest\FormLayout\Fieldset;
use Concrete\Core\Block\Manifest\FormLayout\FieldReference;
use Concrete\Core\Block\Manifest\FormLayout\Tab;
use Concrete\Core\Block\Manifest\FormLayout\TabReference;
use Concrete\Core\Block\Manifest\TabDefinitionParser;
use Concrete\Tests\TestCase;

final class BlockManifestParserTest extends TestCase
{
    protected function getParser(): BlockManifestParser
    {
        $manager = new FieldManager();
        $manager->register(new TextFieldType());
        $manager->register(new TextareaFieldType());
        $manager->register(new ColorFieldType());

        return new BlockManifestParser(
            new FieldDefinitionParser($manager),
            new TabDefinitionParser(),
            new GlobalFieldRegistry(
                new FieldDefinitionParser($manager),
                new TabDefinitionParser(),
                app('cache/request')
            )
        );
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
                <fieldref field="headline" />
                <fieldref field="body" />
            </tab>
            <tab id="design" name="Design">
                <fieldref field="accent" />
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
                <fieldref field="headline" />
                <fieldref field="headline" />
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
                <fieldref field="headline" />
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

    public function testAppliesOptionalFieldsPrefixToFieldIdentifiers(): void
    {
        $manifest = $this->getParser()->parseString(<<<XML
<concrete-bdf version="1.0">
    <blocktype handle="sample" name="Sample">
        <fields prefix="design">
            <field id="accent" type="color" label="Accent" />
        </fields>
        <formlayout>
            <tab id="design" name="Design">
                <fieldref field="design.accent" />
            </tab>
        </formlayout>
    </blocktype>
</concrete-bdf>
XML);

        $this->assertNotNull($manifest->getField('design.accent'));
        $this->assertNull($manifest->getField('accent'));
        $this->assertSame('design.accent', $manifest->getLayout()[0]->getChildren()[0]->getFieldId());
    }

    public function testMissingFieldReferenceBecomesRecoverableManifestError(): void
    {
        $manifest = $this->getParser()->parseString(<<<XML
<concrete-bdf version="1.0">
    <blocktype handle="sample" name="Sample">
        <fields>
            <field id="headline" type="text" label="Headline" />
        </fields>
        <formlayout>
            <tab id="content" name="Content">
                <fieldref field="missing" />
                <fieldref field="headline" />
            </tab>
        </formlayout>
    </blocktype>
</concrete-bdf>
XML);

        $this->assertTrue($manifest->hasErrors());
        $this->assertSame('unknown_field_reference', $manifest->getErrors()[0]->getCode());
        $this->assertSame('missing', $manifest->getLayout()[0]->getChildren()[0]->getFieldId());
        $this->assertSame('headline', $manifest->getLayout()[0]->getChildren()[1]->getFieldId());
    }

    public function testParserCanResolveLocalTabReferencesFromFormLayout(): void
    {
        $manifest = $this->getParser()->parseString(<<<XML
<concrete-bdf version="1.0">
    <blocktype handle="sample" name="Sample">
        <fields>
            <field id="headline" type="text" label="Headline" />
            <field id="accent" type="color" label="Accent" />
        </fields>
        <tabs>
            <tab id="sample.design" name="Design">
                <fieldref field="accent" />
            </tab>
        </tabs>
        <formlayout>
            <tab id="content" name="Content">
                <fieldref field="headline" />
            </tab>
            <tabref tab="sample.design" />
        </formlayout>
    </blocktype>
</concrete-bdf>
XML);

        $layout = $manifest->getLayout();
        $this->assertCount(2, $layout);
        $this->assertInstanceOf(Tab::class, $layout[1]);
        $this->assertSame('sample.design', $layout[1]->getId());
        $this->assertSame('accent', $layout[1]->getChildren()[0]->getFieldId());
    }

    public function testMissingTabReferenceBecomesRecoverableManifestError(): void
    {
        $manifest = $this->getParser()->parseString(<<<XML
<concrete-bdf version="1.0">
    <blocktype handle="sample" name="Sample">
        <fields>
            <field id="headline" type="text" label="Headline" />
        </fields>
        <formlayout>
            <tabref tab="missing.design" />
            <tab id="content" name="Content">
                <fieldref field="headline" />
            </tab>
        </formlayout>
    </blocktype>
</concrete-bdf>
XML);

        $this->assertTrue($manifest->hasErrors());
        $this->assertSame('unknown_tab_reference', $manifest->getErrors()[0]->getCode());
        $this->assertInstanceOf(TabReference::class, $manifest->getLayout()[0]);
        $this->assertSame('missing.design', $manifest->getLayout()[0]->getTabId());
    }

    public function testDuplicateGlobalTabIdsAreFatal(): void
    {
        $manager = new FieldManager();
        $manager->register(new TextFieldType());
        $manager->register(new TextareaFieldType());
        $manager->register(new ColorFieldType());

        $duplicateSource = tempnam(sys_get_temp_dir(), 'manifest-tabs-');
        $this->assertNotFalse($duplicateSource);
        file_put_contents($duplicateSource, <<<'XML'
<concrete-bdf version="1.0">
    <tabs>
        <tab id="core.design.colors_only" name="Design Duplicate">
            <fieldref field="core.styles.text_color" />
        </tab>
    </tabs>
</concrete-bdf>
XML);

        $registry = new GlobalFieldRegistry(
            new FieldDefinitionParser($manager),
            new TabDefinitionParser()
        );
        $registry->addSource(DIR_BASE . '/tests/fixtures/Block/Manifest/tabs.xml');
        $registry->addSource($duplicateSource);

        $this->expectException(MalformedManifestException::class);
        $this->expectExceptionMessage('Duplicate global tab id "core.design.colors_only"');

        try {
            $registry->getTabs();
        } finally {
            @unlink($duplicateSource);
        }
    }

    public function testParserCanParseFieldsetsInsideTabs(): void
    {
        $manifest = $this->getParser()->parseString(<<<XML
<concrete-bdf version="1.0">
    <blocktype handle="sample" name="Sample" description="Example">
        <fields>
            <field id="headline" type="text" label="Headline" />
            <field id="accent" type="color" label="Headline Color" />
            <field id="body" type="textarea" label="Body" rows="3" />
        </fields>
        <formlayout>
            <tab id="content" name="Content">
                <fieldset legend="Headline">
                    <fieldref field="headline" />
                    <fieldref field="accent" />
                </fieldset>
                <fieldset>
                    <fieldref field="body" />
                </fieldset>
            </tab>
        </formlayout>
    </blocktype>
</concrete-bdf>
XML);

        $layout = $manifest->getLayout();
        $this->assertCount(1, $layout);
        $this->assertInstanceOf(Tab::class, $layout[0]);
        $this->assertCount(2, $layout[0]->getChildren());
        $this->assertInstanceOf(Fieldset::class, $layout[0]->getChildren()[0]);
        $this->assertSame('Headline', $layout[0]->getChildren()[0]->getLegend());
        $this->assertSame('headline', $layout[0]->getChildren()[0]->getChildren()[0]->getFieldId());
        $this->assertSame('accent', $layout[0]->getChildren()[0]->getChildren()[1]->getFieldId());
        $this->assertInstanceOf(Fieldset::class, $layout[0]->getChildren()[1]);
        $this->assertSame('', $layout[0]->getChildren()[1]->getLegend());
        $this->assertSame('body', $layout[0]->getChildren()[1]->getChildren()[0]->getFieldId());
    }

    public function testTabReferenceCanExcludeDirectChildFields(): void
    {
        $manifest = $this->getParser()->parseString(<<<XML
<concrete-bdf version="1.0">
    <blocktype handle="sample" name="Sample">
        <fields>
            <field id="headline" type="text" label="Headline" />
            <field id="accent" type="color" label="Accent" />
        </fields>
        <tabs>
            <tab id="sample.design" name="Design">
                <fieldref field="headline" />
                <fieldref field="accent" />
            </tab>
        </tabs>
        <formlayout>
            <tabref tab="sample.design">
                <excludefield field="accent" />
            </tabref>
        </formlayout>
    </blocktype>
</concrete-bdf>
XML);

        $layout = $manifest->getLayout();
        $this->assertCount(1, $layout);
        $this->assertInstanceOf(Tab::class, $layout[0]);
        $this->assertCount(1, $layout[0]->getChildren());
        $this->assertSame('headline', $layout[0]->getChildren()[0]->getFieldId());
    }

    public function testTabReferenceCanExcludeFieldsInsideFieldsetsAndDropsEmptyFieldsets(): void
    {
        $manifest = $this->getParser()->parseString(<<<XML
<concrete-bdf version="1.0">
    <blocktype handle="sample" name="Sample">
        <fields>
            <field id="headline" type="text" label="Headline" />
            <field id="accent" type="color" label="Accent" />
            <field id="body" type="textarea" label="Body" />
        </fields>
        <tabs>
            <tab id="sample.design" name="Design">
                <fieldset legend="Headline">
                    <fieldref field="headline" />
                    <fieldref field="accent" />
                </fieldset>
                <fieldset legend="Body">
                    <fieldref field="body" />
                </fieldset>
            </tab>
        </tabs>
        <formlayout>
            <tabref tab="sample.design">
                <excludefield field="headline" />
                <excludefield field="accent" />
            </tabref>
        </formlayout>
    </blocktype>
</concrete-bdf>
XML);

        $layout = $manifest->getLayout();
        $this->assertCount(1, $layout);
        $this->assertInstanceOf(Tab::class, $layout[0]);
        $this->assertCount(1, $layout[0]->getChildren());
        $this->assertInstanceOf(Fieldset::class, $layout[0]->getChildren()[0]);
        $this->assertSame('Body', $layout[0]->getChildren()[0]->getLegend());
        $this->assertCount(1, $layout[0]->getChildren()[0]->getChildren());
        $this->assertSame('body', $layout[0]->getChildren()[0]->getChildren()[0]->getFieldId());
    }

    public function testTabReferenceKeepsEmptyTabsAfterExclusions(): void
    {
        $manifest = $this->getParser()->parseString(<<<XML
<concrete-bdf version="1.0">
    <blocktype handle="sample" name="Sample">
        <fields>
            <field id="headline" type="text" label="Headline" />
        </fields>
        <tabs>
            <tab id="sample.design" name="Design">
                <fieldref field="headline" />
            </tab>
        </tabs>
        <formlayout>
            <tabref tab="sample.design">
                <excludefield field="headline" />
            </tabref>
        </formlayout>
    </blocktype>
</concrete-bdf>
XML);

        $layout = $manifest->getLayout();
        $this->assertCount(1, $layout);
        $this->assertInstanceOf(Tab::class, $layout[0]);
        $this->assertSame('sample.design', $layout[0]->getId());
        $this->assertCount(0, $layout[0]->getChildren());
    }
}
