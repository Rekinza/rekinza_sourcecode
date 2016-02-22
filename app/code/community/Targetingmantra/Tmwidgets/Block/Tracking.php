<?php
/**
 * 
 * @author mani
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * 
 * This block class contains set of helper methods required for Tracking pixel 
 * integration in magento stores. 
 */
class Targetingmantra_Tmwidgets_Block_Tracking extends Mage_Core_Block_Template
{
    protected $_request;
    protected $_helper;
    protected $_paramsArray = array ();
    protected $_mid;
    /**
     * This map has entries for all tracking pixel pages
     *
     * @var hashArray
     */
    protected $_pixelEnabledMap = array (
            "cms_index_index" => true,
            "catalog_product_view" => true,
            "catalog_category_view" => true,
            "checkout_onepage_success" => true,
            "checkout_multishipping_success" => true 
    );
    protected $_multiDomainSupport = False;
    protected $_tmRegion = "na";
    public function __construct()
    {
        $this->_request = Mage::app ()->getRequest ();
        $this->_helper = Mage::helper ( 'tmwidgets' );
        $this->_mid = $this->_helper->getMID ();
        $this->_multiDomainSupport = $this->_helper->isMultiDomainEnable ();
        $this->_tmRegion = $this->_helper->getTmRegion();
    }
    
    /**
     * Converts tracking pixel key value parameters into url arguments format
     *
     * @return string
     */
    protected function getParamsData()
    {
        $trackingParamsData = '';
        foreach ( $this->_paramsArray as $paramKey => $paramValue ) {
            $trackingParamsData = $trackingParamsData . $paramKey . '=' . $paramValue . '&';
        }
        $trackingParamsData = substr ( $trackingParamsData, 0, - 1 );
        return $trackingParamsData;
    }
    protected function insertParam($key, $value)
    {
        $this->_paramsArray [$key] = $value;
    }

    public function getTmRegion(){
        return $this->_tmRegion;
    }
    
    /**
     * Fetches unique page identifier from url router controller
     * It is a combination of route + contoller + action name
     *
     * @return String
     */
    protected function getPageInfo()
    {
        $separator = "_";
        $pageInfoString = $this->_request->getRequestedRouteName () . $separator;
        $pageInfoString .= $this->_request->getRequestedControllerName () . $separator;
        $pageInfoString .= $this->_request->getRequestedActionName ();
        return $pageInfoString;
    }
    /**
     * Generates required tracking parameters for Product page view
     * @tracking_params: price, inStock, productId, eventId
     */
    protected function setProductViewParams()
    {
        $product = Mage::registry ( 'product' );
        $productId = $product->getId ();
        if($this->_multiDomainSupport){
          $productId = $productId . "_" . Mage::app()->getStore()->getStoreId();
        }
        $price = $product->getPrice ();
        $specialPrice = $product->getFinalPrice();
        $isInStock = 0;
        $stockItem = $product->getStockItem ();
        if ($stockItem->getIsInStock ()) {
            $isInStock = 1;
        }
        if( $specialPrice != "" ){
            $price = $specialPrice;
        }
        
        $this->insertParam ( 'prc', $price );
        $this->insertParam ( 'stk', $isInStock );
        $this->insertParam ( 'pid', $productId );
        $this->insertParam ( 'eid', 1 );
    }
    
    /**
     * Generates params for Home page view
     * @tracking_params eventId
     */
    protected function setHomeViewParams()
    {
        $this->insertParam ( 'pid', 'homepage' );
        $this->insertParam ( 'eid', 1 );
    }
    
    /**
     * Generates params for Category page view
     * @tracking_params eventId, categoryId
     */
    protected function setCategoryViewParams()
    {
        $layer = Mage::getSingleton ( 'catalog/layer' );
        $category = $layer->getCurrentCategory ();
        if($this->_multiDomainSupport){
            $categoryId = "s". Mage::app()->getStore()->getStoreId(). "_" . $category->getId ();
        } else{
            $categoryId = $category->getId ();
        }
        $this->insertParam ( 'pid', 'c' . $categoryId );
        $this->insertParam ( 'eid', 1 );
    }
    
    /**
     * Generates params for Post purchase thank you page
     * @tracking_params productIds, eventId
     */
    protected function setCheckoutViewParams()
    {
        $lastOrderId = Mage::getSingleton ( 'checkout/session' )->getLastOrderId ();
        $lastOrder = Mage::getModel ( 'sales/order' )->load ( $lastOrderId );
        
        $orderItems = $lastOrder->getAllVisibleItems ();
        $orderItemIdsString = '';
        foreach ( $orderItems as $orderItem ) {
            $orderItemQty = $orderItem->getQtyOrdered ();
            $orderItemId = $orderItem->getProductId ();
            if($this->_multiDomainSupport){
                $orderItemId = $orderItemId . "_" . Mage::app()->getStore()->getStoreId();
            }
            for($appendTime = 0; $appendTime < $orderItemQty; $appendTime ++) {
                $orderItemIdsString = $orderItemIdsString . $orderItemId . ',';
            }
        }
        $orderItemIdsString = substr ( $orderItemIdsString, 0, - 1 );
        
        $this->insertParam ( 'pid', $orderItemIdsString );
        $this->insertParam ( 'eid', 2 );
    }
    
    /**
     * Method for recording tracking params for each type of page and
     * returns param data to tracker
     * @tracking_params_default customer_id, marketplaceId
     *
     * @return string
     */
    public function getTrackingParams()
    {
        $userId = $this->_helper->getUserId ();
        if($userId != ""){ 
            $this->insertParam ( 'cid', $userId );
        }
        $currentPageInfo = $this->getPageInfo ();
        switch ($currentPageInfo) {
            case 'cms_index_index' :
                $this->setHomeViewParams ();
                break;
            case 'catalog_product_view' :
                $this->setProductViewParams ();
                break;
            case 'catalog_category_view' :
                $this->setCategoryViewParams ();
                break;
            case 'checkout_onepage_success' :
                $this->setCheckoutViewParams ();
                break;
            case 'checkout_multishipping_success' :
                $this->setCheckoutViewParams ();
                break;
            default :
                break;
        }
        $trackingParams = $this->getParamsData ();
        return $trackingParams;
    }
    /**
     * This method invokes when add to cart button is clicked
     * and returns tracking pixel params
     * @tracking_params productId, price, customerId, eventId
     *
     * @return string
     */
    public function getTrackingAddCartParams()
    {
        $userId = $this->_helper->getUserId ();
        $mid = $this->getMid ();
        $productCartSession = Mage::getModel ( 'core/session' )->getProductToShoppingCart ();
        
        $this->insertParam ( 'cid', $userId );
        if ($productCartSession) {
            $productId = $productCartSession->getId ();
            if($this->_multiDomainSupport){
                $productId = $productId . "_" . Mage::app()->getStore()->getStoreId();
            }
            $this->insertParam ( 'pid', $productId );
            $this->insertParam ( 'eid', 4 );
        }
        $trackingParams = $this->getParamsData ();
        Mage::getModel ( 'core/session' )->unsProductToShoppingCart ();
        return $trackingParams;
    }
    public function getMid()
    {
        return $this->_mid;
    }
    /**
     * Returns if add to cart action is called
     *
     * @return boolean
     */
    public function isAddCartAction()
    {
        if (6 != strlen ( $this->_mid ) || ! $this->_helper->isTrackingPixelEnabled ())
            return false;
        $productCartSession = Mage::getModel ( 'core/session' )->getProductToShoppingCart ();
        if ($productCartSession) {
            return true;
        }
        return false;
    }
    /**
     * Returns if behaviour tracking pixel is enabled for
     * this page
     *
     * @return boolean
     */
    public function isPixelEnabled()
    {
        if (6 != strlen ( $this->_mid ) || ! $this->_helper->isTrackingPixelEnabled ())
            return false;
        $currentPage = $this->getPageInfo ();
        if (array_key_exists ( $currentPage, $this->_pixelEnabledMap )) {
            return true;
        }
        return false;
    }
}
