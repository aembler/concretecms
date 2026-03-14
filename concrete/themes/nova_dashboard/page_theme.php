<?php
namespace Concrete\Theme\NovaDashboard;

use Concrete\Core\Area\Layout\Preset\Provider\ThemeProviderInterface;
use Concrete\Core\Feature\Features;
use Concrete\Core\Page\Theme\BedrockThemeTrait;

class PageTheme extends \Concrete\Core\Page\Theme\Theme
{

    use BedrockThemeTrait {
        registerAssets as bedrockRegisterAssets;
    }

    public function getThemeSupportedFeatures()
    {
        return [
        ];
    }

    public function registerAssets()
    {
    }

}
