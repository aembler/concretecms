<?php
namespace Concrete\Controller\SinglePage;

use Concrete\Controller\PageType\CoreStackCategory;
use Page;

class Stacks extends CoreStackCategory
{

    public function view()
    {
        return $this->buildRedirect('/dashboard/blocks/stacks');
    }

}
