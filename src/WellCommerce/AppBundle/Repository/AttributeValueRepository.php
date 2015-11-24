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
namespace WellCommerce\AppBundle\Repository;

use Doctrine\Common\Collections\Criteria;
use WellCommerce\AppBundle\Entity\AttributeInterface;
use WellCommerce\CoreBundle\Repository\AbstractEntityRepository;

/**
 * Class AttributeValueRepository
 *
 * @author  Adam Piotrowski <adam@wellcommerce.org>
 */
class AttributeValueRepository extends AbstractEntityRepository implements AttributeValueRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCollectionByAttribute(AttributeInterface $attribute)
    {
        $criteria = new Criteria();
        $criteria->where($criteria->expr()->eq('attribute', $attribute));

        return $this->matching($criteria);
    }
}
