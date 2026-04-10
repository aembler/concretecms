<?php

declare(strict_types=1);

namespace Concrete\Tests\Block;

use Concrete\Core\Block\Manifest\BlockManifestParser;
use Concrete\Core\Block\Manifest\Field\FieldManager;
use Concrete\Core\Block\Manifest\FieldDefinitionParser;
use Concrete\Core\Block\Manifest\GlobalFieldRegistry;
use Concrete\Core\Block\Manifest\FormLayout\Fieldset;
use Concrete\Core\Block\Manifest\FormLayout\FieldReference;
use Concrete\Core\Block\Manifest\FormLayout\Tab;
use Concrete\Core\Block\Manifest\FormLayout\TabReference;
use Concrete\Core\Block\Manifest\TabDefinitionParser;
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
    <blocktype handle="sample" name="Sample" description="Example" set="basics">
        <fields>
            <field id="headline" type="text" label="Headline" />
            <field id="body" type="textarea" label="Body" rows="3" />
            <field id="accent" type="color" label="Accent" default="#abc123" />
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
        $this->assertSame('basics', $manifest->getSet());
        $this->assertCount(5, $manifest->getFields());
        $this->assertFalse($manifest->hasErrors());
        $this->assertNotNull($manifest->getField('core.styles.text_color'));
        $this->assertNotNull($manifest->getField('core.styles.background_color'));

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

    public function testGlobalFieldRegistryLoadsDefaultStylesSource(): void
    {
        $registry = new GlobalFieldRegistry(
            app(FieldDefinitionParser::class),
            app(TabDefinitionParser::class)
        );
        $registry->addSource(DIR_BASE . '/tests/fixtures/Block/Manifest/styles.xml');
        $registry->addSource(DIR_BASE . '/tests/fixtures/Block/Manifest/tabs.xml');

        $this->assertTrue($registry->has('core.styles.text_color'));
        $this->assertTrue($registry->has('core.styles.background_color'));
        $this->assertFalse($registry->has('text_color'));
        $this->assertCount(2, $registry->getFields());
        $this->assertTrue($registry->hasTab('core.design.colors_only'));
        $this->assertCount(1, $registry->getTabs());
        $this->assertSame('core.styles.text_color', $registry->getTab('core.design.colors_only')->getChildren()[0]->getFieldId());
        $this->assertSame('core.styles.background_color', $registry->getTab('core.design.colors_only')->getChildren()[1]->getFieldId());
        $this->assertCount(0, $registry->getErrors());
    }

    public function testParserCanReferenceGlobalFieldsFromFormLayout(): void
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
                <fieldref field="headline" />
                <fieldref field="body" />
            </tab>
            <tab id="design" name="Design">
                <fieldref field="core.styles.background_color" />
                <fieldref field="accent" />
            </tab>
        </formlayout>
    </blocktype>
</concrete-bdf>
XML);

        $layout = $manifest->getLayout();
        $this->assertCount(2, $layout);
        $this->assertSame('core.styles.background_color', $layout[1]->getChildren()[0]->getFieldId());
        $this->assertSame('accent', $layout[1]->getChildren()[1]->getFieldId());
        $this->assertNotNull($manifest->getField('core.styles.background_color'));
        $this->assertSame('Background Color', $manifest->getField('core.styles.background_color')->getLabel());
    }

    public function testParserCanResolveGlobalTabReferencesFromFormLayout(): void
    {
        $registry = new GlobalFieldRegistry(
            app(FieldDefinitionParser::class),
            app(TabDefinitionParser::class)
        );
        $registry->addSource(DIR_BASE . '/tests/fixtures/Block/Manifest/styles.xml');
        $registry->addSource(DIR_BASE . '/tests/fixtures/Block/Manifest/tabs.xml');
        $parser = new BlockManifestParser(
            app(FieldDefinitionParser::class),
            app(TabDefinitionParser::class),
            $registry
        );

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
                <fieldref field="headline" />
                <fieldref field="body" />
            </tab>
            <tabref tab="core.design.colors_only" />
            <tab id="details" name="Details">
                <fieldref field="accent" />
            </tab>
        </formlayout>
    </blocktype>
</concrete-bdf>
XML);

        $layout = $manifest->getLayout();
        $this->assertCount(3, $layout);
        $this->assertInstanceOf(Tab::class, $layout[1]);
        $this->assertSame('core.design.colors_only', $layout[1]->getId());
        $this->assertSame('core.styles.text_color', $layout[1]->getChildren()[0]->getFieldId());
        $this->assertSame('core.styles.background_color', $layout[1]->getChildren()[1]->getFieldId());
        $this->assertSame('accent', $layout[2]->getChildren()[0]->getFieldId());
    }

    public function testParserCanResolveGlobalTabReferencesWithFieldsets(): void
    {
        $registry = new GlobalFieldRegistry(
            app(FieldDefinitionParser::class),
            app(TabDefinitionParser::class)
        );
        $registry->addSource(DIR_BASE . '/tests/fixtures/Block/Manifest/styles.xml');
        $registry->addSource(DIR_BASE . '/tests/fixtures/Block/Manifest/tabs_with_fieldset.xml');
        $parser = new BlockManifestParser(
            app(FieldDefinitionParser::class),
            app(TabDefinitionParser::class),
            $registry
        );

        $manifest = $parser->parseString(<<<XML
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
            <tabref tab="core.design" />
        </formlayout>
    </blocktype>
</concrete-bdf>
XML);

        $layout = $manifest->getLayout();
        $this->assertCount(2, $layout);
        $this->assertInstanceOf(Tab::class, $layout[0]);
        $this->assertInstanceOf(Fieldset::class, $layout[0]->getChildren()[0]);
        $this->assertSame('Headline', $layout[0]->getChildren()[0]->getLegend());
        $this->assertSame('headline', $layout[0]->getChildren()[0]->getChildren()[0]->getFieldId());
        $this->assertSame('accent', $layout[0]->getChildren()[0]->getChildren()[1]->getFieldId());
        $this->assertInstanceOf(Fieldset::class, $layout[0]->getChildren()[1]);
        $this->assertSame('', $layout[0]->getChildren()[1]->getLegend());
        $this->assertSame('body', $layout[0]->getChildren()[1]->getChildren()[0]->getFieldId());
        $this->assertInstanceOf(Tab::class, $layout[1]);
        $this->assertSame('core.design', $layout[1]->getId());
        $this->assertInstanceOf(Fieldset::class, $layout[1]->getChildren()[0]);
        $this->assertSame('Colors', $layout[1]->getChildren()[0]->getLegend());
        $this->assertSame('core.styles.text_color', $layout[1]->getChildren()[0]->getChildren()[0]->getFieldId());
        $this->assertSame('core.styles.background_color', $layout[1]->getChildren()[0]->getChildren()[1]->getFieldId());
    }

    public function testParserCanExcludeGlobalFieldsFromReferencedGlobalTabs(): void
    {
        $registry = new GlobalFieldRegistry(
            app(FieldDefinitionParser::class),
            app(TabDefinitionParser::class)
        );
        $registry->addSource(DIR_BASE . '/tests/fixtures/Block/Manifest/styles.xml');
        $registry->addSource(DIR_BASE . '/tests/fixtures/Block/Manifest/tabs_with_fieldset.xml');
        $parser = new BlockManifestParser(
            app(FieldDefinitionParser::class),
            app(TabDefinitionParser::class),
            $registry
        );

        $manifest = $parser->parseString(<<<XML
<concrete-bdf version="1.0">
    <blocktype handle="sample" name="Sample" description="Example">
        <fields>
            <field id="headline" type="text" label="Headline" />
        </fields>
        <formlayout>
            <tab id="content" name="Content">
                <fieldref field="headline" />
            </tab>
            <tabref tab="core.design">
                <excludefield field="core.styles.background_color" />
            </tabref>
        </formlayout>
    </blocktype>
</concrete-bdf>
XML);

        $layout = $manifest->getLayout();
        $this->assertCount(2, $layout);
        $this->assertInstanceOf(Tab::class, $layout[1]);
        $this->assertInstanceOf(Fieldset::class, $layout[1]->getChildren()[0]);
        $this->assertCount(1, $layout[1]->getChildren()[0]->getChildren());
        $this->assertSame('core.styles.text_color', $layout[1]->getChildren()[0]->getChildren()[0]->getFieldId());
    }

    public function testParserCanResolveGlobalFieldsInsideLocalFieldsets(): void
    {
        $parser = app(BlockManifestParser::class);

        $manifest = $parser->parseString(<<<XML
<concrete-bdf version="1.0">
    <blocktype handle="sample" name="Sample" description="Example">
        <fields>
            <field id="headline" type="text" label="Headline" />
            <field id="body" type="textarea" label="Body" rows="3" />
        </fields>
        <formlayout>
            <tab id="content" name="Content">
                <fieldset legend="Design">
                    <fieldref field="core.styles.background_color" />
                </fieldset>
                <fieldset legend="Content">
                    <fieldref field="headline" />
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
        $this->assertSame('Design', $layout[0]->getChildren()[0]->getLegend());
        $this->assertSame('core.styles.background_color', $layout[0]->getChildren()[0]->getChildren()[0]->getFieldId());
        $this->assertSame('Background Color', $manifest->getField('core.styles.background_color')->getLabel());
        $this->assertInstanceOf(Fieldset::class, $layout[0]->getChildren()[1]);
        $this->assertSame('headline', $layout[0]->getChildren()[1]->getChildren()[0]->getFieldId());
        $this->assertSame('body', $layout[0]->getChildren()[1]->getChildren()[1]->getFieldId());
    }
}
