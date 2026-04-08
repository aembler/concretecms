<?php
namespace Concrete\Core\Block;

use Concrete\Core\Block\Manifest\BlockManifestParser;
use Concrete\Core\Block\Manifest\FieldDefinitionParser;
use Concrete\Core\Block\Manifest\Field\FieldManager;
use Concrete\Core\Block\Manifest\Field\Type\ColorFieldType;
use Concrete\Core\Block\Manifest\Field\Type\TextareaFieldType;
use Concrete\Core\Block\Manifest\Field\Type\TextFieldType;
use Concrete\Core\Block\Manifest\GlobalFieldRegistry;
use Concrete\Core\Block\Manifest\GroupDefinitionParser;
use Concrete\Core\Cache\Level\RequestCache;
use Concrete\Core\Foundation\Service\Provider as ServiceProvider;

class BlockServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(CustomStyleRepository::class);
        $this->app->singleton(FieldManager::class, static function () {
            $manager = new FieldManager();
            $manager->register(new TextFieldType());
            $manager->register(new TextareaFieldType());
            $manager->register(new ColorFieldType());

            return $manager;
        });
        $this->app->singleton(FieldDefinitionParser::class);
        $this->app->singleton(GroupDefinitionParser::class);
        $this->app->singleton(GlobalFieldRegistry::class, function () {
            $registry = new GlobalFieldRegistry(
                $this->app->make(FieldDefinitionParser::class),
                $this->app->make(GroupDefinitionParser::class),
                $this->app->make(RequestCache::class)
            );
            $registry->addSource(DIR_BASE_CORE . '/config/blocks/styles.xml');

            return $registry;
        });
        $this->app->singleton(BlockManifestParser::class);
    }
}
