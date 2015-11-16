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

namespace WellCommerce\Bundle\SalesBundle\Controller\Front;

use WellCommerce\Bundle\CatalogBundle\Entity\ProductAttributeInterface;
use WellCommerce\Bundle\CatalogBundle\Entity\ProductInterface;
use WellCommerce\Bundle\CoreBundle\Controller\Front\AbstractFrontController;
use WellCommerce\Bundle\CoreBundle\Controller\Front\FrontControllerInterface;
use WellCommerce\Bundle\SalesBundle\Entity\CartProductInterface;
use WellCommerce\Bundle\SalesBundle\Exception\AddCartItemException;
use WellCommerce\Bundle\SalesBundle\Exception\DeleteCartItemException;
use WellCommerce\Bundle\CommonBundle\Breadcrumb\BreadcrumbItem;

/**
 * Class CartController
 *
 * @author  Adam Piotrowski <adam@wellcommerce.org>
 */
class CartController extends AbstractFrontController implements FrontControllerInterface
{
    /**
     * @var \WellCommerce\Bundle\SalesBundle\Manager\Front\CartManagerInterface
     */
    protected $manager;

    /**
     * {@inheritdoc}
     */
    public function indexAction()
    {
        $this->addBreadCrumbItem(new BreadcrumbItem([
            'name' => $this->trans('cart.heading.index')
        ]));

        $cart = $this->manager->getCartContext()->getCurrentCart();
        $form = $this->manager->getForm($cart, [
            'validation_groups' => ['cart']
        ]);

        if ($form->handleRequest()->isSubmitted()) {
            if ($form->isValid()) {
                $this->manager->updateResource($cart);

                return $this->getRouterHelper()->redirectTo('front.cart.index');
            }

            if (count($form->getError())) {
                $this->getFlashHelper()->addError('client.form.error.registration');
            }
        }

        return $this->displayTemplate('index', [
            'form'         => $form,
            'elements'     => $form->getChildren(),
            'shippingCost' => (null !== $cart->getShippingMethodCost()) ? $cart->getShippingMethodCost()->getCost() : null,
            'summary'      => $this->get('cart_summary.collector')->collect($cart)
        ]);
    }

    /**
     * Adds item to cart or redirects to quick-view
     *
     * @param ProductInterface               $product
     * @param ProductAttributeInterface|null $attribute
     * @param int                            $quantity
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addAction(ProductInterface $product, ProductAttributeInterface $attribute = null, $quantity = 1)
    {
        if ($product->getAttributes()->count() && !$product->getAttributes()->contains($attribute)) {
            return $this->redirectToRoute('front.product.view', ['id' => $product->getId()]);
        }

        try {
            $this->manager->addProductToCart($product, $attribute, $quantity);
        } catch (AddCartItemException $exception) {
            return $this->jsonResponse([
                'error'         => $exception->getMessage(),
                'previousError' => $exception->getPrevious()->getMessage(),
            ]);
        }

        $category        = $product->getCategories()->first();
        $recommendations = $this->get('product.helper')->getProductRecommendationsForCategory($category);

        $basketModalContent = $this->renderView('WellCommerceSalesBundle:Front/Cart:add.html.twig', [
            'product'         => $product,
            'recommendations' => $recommendations
        ]);

        $cartPreviewContent = $this->renderView('WellCommerceSalesBundle:Front/Common:preview.html.twig');

        return $this->jsonResponse([
            'basketModalContent' => $basketModalContent,
            'cartPreviewContent' => $cartPreviewContent
        ]);
    }

    public function editAction(CartProductInterface $cartProduct, $quantity)
    {
        $message = null;

        try {
            $this->manager->changeCartProductQuantity($cartProduct, $quantity);
            $success = true;
        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
        }

        return $this->jsonResponse([
            'success' => $success,
            'message' => $message,
        ]);
    }

    public function deleteAction(CartProductInterface $cartProduct)
    {
        try {
            $this->manager->deleteCartProduct($cartProduct);
        } catch (DeleteCartItemException $exception) {
            $this->getFlashHelper()->addError($exception->getMessage());
        }

        return $this->redirectToAction('index');
    }
}
