<?php

declare(strict_types=1);

namespace Concrete\Core\Updater\Migrations\Migrations;

use Concrete\Core\Entity\Block\BlockType\BlockType;
use Concrete\Core\Updater\Migrations\AbstractMigration;

final class Version20260225120000 extends AbstractMigration
{
    public function upgradeDatabase()
    {
        $this->refreshEntities([BlockType::class]);
        $this->refreshDatabaseTables(['Blocks']);
    }
}
