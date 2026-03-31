<?php

namespace Concrete\Core\Application\UserInterface\Dashboard\Component;

use Concrete\Core\Application\UserInterface\Dashboard\Navigation\FullNavigationFactory;
use Concrete\Core\Html\Service\Navigation as NavigationService;
use Concrete\Core\Navigation\Modifier\ActivateParentPagesModifier;
use Concrete\Core\Navigation\Modifier\TopLevelOnlyModifier;
use Concrete\Core\Navigation\NavigationInterface;
use Concrete\Core\Navigation\NavigationModifier;

class Navigation implements \JsonSerializable
{

    public NavigationInterface $navigation;

    public function __construct(FullNavigationFactory $factory, NavigationService $navigationService)
    {
        $navigation = $factory->createNavigation();
        $modifier = new NavigationModifier();
        $modifier->addModifier(new ActivateParentPagesModifier($navigationService));
        $modifier->addModifier(new TopLevelOnlyModifier());
        $this->navigation = $modifier->process($navigation);
    }

    public function jsonSerialize(): array
    {
        return [
            'navigation' => $this->navigation,
        ];
    }
}
