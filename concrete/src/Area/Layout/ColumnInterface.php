<?php
namespace Concrete\Core\Area\Layout;

interface ColumnInterface
{
    public function getColumnHtmlObject(bool $disableControls = false);
    public function getColumnHtmlObjectEditMode();
}
