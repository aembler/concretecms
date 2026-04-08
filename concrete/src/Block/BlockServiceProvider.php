<?php
namespace Concrete\Core\Block;

use Concrete\Core\Block\Manifest\Field\FieldManager;
use Concrete\Core\Block\Manifest\Field\Type\ColorFieldType;
use Concrete\Core\Block\Manifest\Field\Type\TextareaFieldType;
use Concrete\Core\Block\Manifest\Field\Type\TextFieldType;
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
    }
}
