<?php

declare(strict_types=1);

namespace Concrete\Tests\Block;

use Concrete\Core\Block\BlockType\BlockType;
use Concrete\Core\Block\BlockType\Set;
use Concrete\Core\Entity\Block\BlockType\BlockType as BlockTypeEntity;
use Concrete\TestHelpers\Database\ConcreteDatabaseTestCase;

final class ManifestInstallTest extends ConcreteDatabaseTestCase
{
    protected $tables = [
        'BlockTypeSets',
        'BlockTypeSetBlockTypes',
        'Blocks',
    ];

    protected $entityClassNames = [
        BlockTypeEntity::class,
    ];

    public function testManifestBlockTypeSetIsAppliedDuringInstall(): void
    {
        $set = Set::add('basics', 'Basics');
        $this->assertNotNull($set);

        $bt = BlockType::installBlockType('nova_hello_world');

        $this->assertSame('nova_hello_world', $bt->getBlockTypeHandle());

        $blockTypeSets = $bt->getBlockTypeSets();
        $this->assertCount(1, $blockTypeSets);
        $this->assertSame('basics', $blockTypeSets[0]->getBlockTypeSetHandle());
    }
}
