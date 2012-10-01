<?php

namespace Neblion\ScrumBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class NeblionScrumBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
