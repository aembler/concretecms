<?php

declare(strict_types=1);

namespace Concrete\Tests\Block\Manifest;

use Concrete\Core\Block\Block;
use Concrete\Core\Block\Controller\ManifestBlockController;
use Concrete\Core\Block\Manifest\BlockManifestParser;
use Concrete\Core\Block\Manifest\Serializer\Serializer;
use Concrete\Core\Entity\Block\CollectionVersionBlockData;
use Concrete\Core\Entity\Block\CollectionVersionBlockDataRepository;
use Concrete\Core\Page\Collection\Collection;
use Concrete\Core\Page\Collection\Version\Version;
use Concrete\Tests\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

final class SerializerTest extends TestCase
{
    public function testSerializerBuildsFullEnvelopeFromRequest(): void
    {
        $manifest = app(BlockManifestParser::class)->parseString(<<<XML
<concrete-bdf version="1.0">
    <blocktype handle="sample" name="Sample" description="Example">
        <fields>
            <field id="text" type="text" label="Text" />
            <field id="body" type="textarea" label="Body" default="Hello" />
            <field id="accent" type="color" label="Accent" default="#abc123" />
        </fields>
        <formlayout>
            <tab id="content" name="Content">
                <fieldref field="text" />
                <fieldref field="body" />
            </tab>
            <tab id="design" name="Design">
                <fieldref field="accent" />
                <fieldref field="core.styles.text_color" />
            </tab>
        </formlayout>
    </blocktype>
</concrete-bdf>
XML);

        $logger = $this->createMock(LoggerInterface::class);
        $serializer = new Serializer($logger);

        $json = $serializer->serializeFromRequest($manifest, [
            'text' => 'Headline',
            'body' => 'Longer body copy',
            'accent' => '#ef00aa',
            'core.styles.text_color' => '#f5f5f5',
        ]);

        $payload = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(
            [
                'version' => 1,
                'fields' => [
                    'text' => 'Headline',
                    'body' => 'Longer body copy',
                    'accent' => '#EF00AA',
                ],
                'design' => [
                    'core.styles.text_color' => '#F5F5F5',
                ],
                'meta' => [],
            ],
            $payload
        );
    }

    public function testSerializerLogsNoticeAndSkipsUnknownFieldTypes(): void
    {
        $manifest = app(BlockManifestParser::class)->parseString(<<<XML
<concrete-bdf version="1.0">
    <blocktype handle="sample" name="Sample" description="Example">
        <fields>
            <field id="headline" type="text" label="Headline" />
            <field id="mystery" type="unknown_type" label="Mystery" />
        </fields>
        <formlayout>
            <tab id="content" name="Content">
                <fieldref field="headline" />
                <fieldref field="mystery" />
            </tab>
        </formlayout>
    </blocktype>
</concrete-bdf>
XML);

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('notice')
            ->with(
                $this->stringContains('Skipping manifest field "{fieldId}"'),
                $this->callback(static function (array $context): bool {
                    return $context['fieldId'] === 'mystery'
                        && $context['fieldType'] === 'unknown_type'
                        && $context['blockType'] === 'sample';
                })
            );

        $serializer = new Serializer($logger);
        $json = $serializer->serializeFromRequest($manifest, [
            'headline' => 'Known value',
            'mystery' => 'Should be ignored',
        ]);

        $payload = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(['headline' => 'Known value'], $payload['fields']);
        $this->assertSame([], $payload['design']);
        $this->assertSame([], $payload['meta']);
    }

    public function testManifestBlockControllerSavePersistsSerializedEnvelope(): void
    {
        $manifest = app(BlockManifestParser::class)->parseString(<<<XML
<concrete-bdf version="1.0">
    <blocktype handle="sample" name="Sample" description="Example">
        <fields>
            <field id="text" type="text" label="Text" />
        </fields>
        <formlayout>
            <tab id="content" name="Content">
                <fieldref field="text" />
            </tab>
        </formlayout>
    </blocktype>
</concrete-bdf>
XML);

        $logger = $this->createMock(LoggerInterface::class);
        $serializer = new Serializer($manifest, $logger);

        $record = new CollectionVersionBlockData();

        /** @var CollectionVersionBlockDataRepository&MockObject $repository */
        $repository = $this->createMock(CollectionVersionBlockDataRepository::class);
        $repository
            ->expects($this->once())
            ->method('findOneByBlock')
            ->willReturn(null);

        /** @var EntityManagerInterface&MockObject $entityManager */
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(CollectionVersionBlockData::class)
            ->willReturn($repository);
        $entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(static function ($entity) use ($record): bool {
                return $entity instanceof CollectionVersionBlockData
                    && $entity->getBlockId() === 123
                    && $entity->getCollectionId() === 456
                    && $entity->getCollectionVersionId() === 789
                    && $entity->getData() === '{"version":1,"fields":{"text":"Saved text"},"design":[],"meta":[]}';
            }));
        $entityManager
            ->expects($this->once())
            ->method('flush');

        $version = $this->createConfiguredMock(Version::class, [
            'getVersionID' => 789,
        ]);
        $collection = $this->createConfiguredMock(Collection::class, [
            'getCollectionID' => 456,
            'getVersionObject' => $version,
        ]);
        $block = $this->createConfiguredMock(Block::class, [
            'getBlockID' => 123,
            'getBlockCollectionObject' => $collection,
        ]);

        $controller = new ManifestBlockController(
            $manifest,
            $serializer,
            $entityManager,
            $logger,
            $block
        );

        $controller->save([
            'text' => 'Saved text',
        ]);
    }

    public function testManifestBlockControllerOnBeforeRenderHydratesFieldAndDesignValues(): void
    {
        $manifest = app(BlockManifestParser::class)->parseString(<<<XML
<concrete-bdf version="1.0">
    <blocktype handle="sample" name="Sample" description="Example">
        <fields>
            <field id="text" type="text" label="Text" default="Hello" />
        </fields>
        <formlayout>
            <tab id="content" name="Content">
                <fieldref field="text" />
                <fieldref field="core.styles.text_color" />
            </tab>
        </formlayout>
    </blocktype>
</concrete-bdf>
XML);

        $logger = $this->createMock(LoggerInterface::class);
        $serializer = new Serializer($manifest, $logger);

        $record = new CollectionVersionBlockData();
        $record->setData('{"version":1,"fields":{"text":"Saved text"},"design":{"core.styles.text_color":"#ABCDEF"},"meta":[]}');

        /** @var CollectionVersionBlockDataRepository&MockObject $repository */
        $repository = $this->createMock(CollectionVersionBlockDataRepository::class);
        $repository
            ->expects($this->once())
            ->method('findOneByBlock')
            ->willReturn($record);

        /** @var EntityManagerInterface&MockObject $entityManager */
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(CollectionVersionBlockData::class)
            ->willReturn($repository);

        $block = $this->createConfiguredMock(Block::class, [
            'getBlockID' => 123,
        ]);

        $controller = new ManifestBlockController(
            $manifest,
            $serializer,
            $entityManager,
            $logger,
            $block
        );

        $controller->on_before_render();
        $sets = $controller->getSets();

        $this->assertSame('Saved text', (string) $sets['fields']['text']);
        $this->assertSame('#ABCDEF', $sets['design']['core.styles.text_color']->getHex());
        $this->assertSame('#ABCDEF', $sets['manifestData']['design']['core.styles.text_color']);
    }

    public function testManifestBlockControllerOnBeforeRenderFallsBackToDefaultsWithoutStoredData(): void
    {
        $manifest = app(BlockManifestParser::class)->parseString(<<<XML
<concrete-bdf version="1.0">
    <blocktype handle="sample" name="Sample" description="Example">
        <fields>
            <field id="text" type="text" label="Text" default="Hello world" />
        </fields>
        <formlayout>
            <tab id="content" name="Content">
                <fieldref field="text" />
            </tab>
        </formlayout>
    </blocktype>
</concrete-bdf>
XML);

        $logger = $this->createMock(LoggerInterface::class);
        $serializer = new Serializer($manifest, $logger);

        /** @var CollectionVersionBlockDataRepository&MockObject $repository */
        $repository = $this->createMock(CollectionVersionBlockDataRepository::class);
        $repository
            ->expects($this->once())
            ->method('findOneByBlock')
            ->willReturn(null);

        /** @var EntityManagerInterface&MockObject $entityManager */
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(CollectionVersionBlockData::class)
            ->willReturn($repository);

        $block = $this->createConfiguredMock(Block::class, [
            'getBlockID' => 123,
        ]);

        $controller = new ManifestBlockController(
            $manifest,
            $serializer,
            $entityManager,
            $logger,
            $block
        );

        $controller->on_before_render();

        $this->assertSame('Hello world', (string) $controller->getSets()['fields']['text']);
    }
}
