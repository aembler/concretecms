<?php

namespace Concrete\StartingPointPackage\Atomik;

use Concrete\Core\Install\StartingPoint\Controller\AbstractController;
use Concrete\Core\Install\StartingPoint\StartingPointPreset;

class Controller extends AbstractController
{

    public function getHandle(): string
    {
        return 'atomik';
    }

    public function getName(): string
    {
        return t('Atomik');
    }

    public function getThumbnail(): ?string
    {
        return ASSETS_URL . '/' . DIRNAME_THEMES . '/atomik/thumbnail.png';
    }

    public function getDescription()
    {
        return [
            t('Creative Services'),
            t('Company Websites'),
            t('Marketing & Products'),
            t('Corporate Blogs'),
            t('General purpose websites'),
        ];
    }

    public function getPresets(): array
    {
        return [
            new StartingPointPreset('content.xml', t('Full Services Website'), t('A full creative services website with a blog, support for a calendar and more.')),
            new StartingPointPreset('empty.xml', T('Empty Site'), t('An empty website featuring the Atomik theme.')),
        ];
    }

}
