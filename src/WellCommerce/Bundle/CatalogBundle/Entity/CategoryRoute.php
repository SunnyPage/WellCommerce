<?php
/*
 * WellCommerce Open-Source E-Commerce Platform
 *
 * This file is part of the WellCommerce package.
 *
 * (c) Adam Piotrowski <adam@wellcommerce.org>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace WellCommerce\Bundle\CatalogBundle\Entity;

use WellCommerce\Bundle\CommonBundle\Entity\Route;
use WellCommerce\Bundle\CommonBundle\Entity\RouteInterface;

/**
 * Class CategoryRoute
 *
 * @author  Adam Piotrowski <adam@wellcommerce.org>
 */
class CategoryRoute extends Route implements RouteInterface
{
    /**
     * @var CategoryInterface
     */
    protected $identifier;

    public function getType()
    {
        return 'category';
    }
}
