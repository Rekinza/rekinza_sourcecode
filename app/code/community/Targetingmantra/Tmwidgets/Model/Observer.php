<?php
/**
 * 
 * @author mani
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 *  Observer class for montoring add to cart events
 */
class Targetingmantra_Tmwidgets_Model_Observer
{
    /**
     *
     * @param model $observer
     *            Save add to cart event data in session, so that that data can be fetched
     *            by tracking pixel
     */
    public function addCartTrackingAction($observer)
    {
        $product = Mage::getModel ( 'catalog/product' )->load ( Mage::app ()->getRequest ()->getParam ( 'product', 0 ) );
        if (! $product->getId ()) {
            return;
        }
        Mage::getModel ( 'core/session' )->setProductToShoppingCart ( new Varien_Object ( array (
                'id' => $product->getId ()
        ) ) );
    }
}

