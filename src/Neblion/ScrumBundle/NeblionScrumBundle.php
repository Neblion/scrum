<?php

/*
 * This file is part of the Neblion/scrum app.
 *
 * Copyright (c) Thomas BIBARD
 *
 * Neblion/scrum is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Foobar is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neblion\ScrumBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class NeblionScrumBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
