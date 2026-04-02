<?php
namespace Concrete\Theme\NovaDashboard;

class PageTheme extends \Concrete\Core\Page\Theme\Theme
{

    public function getThemeHandle()
    {
        return 'nova_dashboard';
    }

    public function getThemeSupportedFeatures()
    {
        return [
        ];
    }

    public function registerAssets()
    {
        $this->requireAsset('font-awesome');
        $this->requireAsset('moment');
    }

}
