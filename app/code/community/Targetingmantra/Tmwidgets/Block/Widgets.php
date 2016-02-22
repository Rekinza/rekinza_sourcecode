<?php
/**
 * 
 * @author mani
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * 
 * This block class contains set of helper methods required for all widgets
 * integration in magento stores.
 */
class Targetingmantra_Tmwidgets_Block_Widgets extends Mage_Core_Block_Template
{
    protected $_request;
    protected $_helper;
    protected $_mid;
    protected $_paramsArray = array ();
    /**
     * This hash array contains mappings between page identifiers
     * to tm page id for widgets generation
     *
     * @var hashArray
     */
    protected $_pageIDs = array (
            "cms_index_index" => "hp",
            "catalog_product_view" => "pp",
            "catalog_category_view" => "cp",
            "checkout_cart_index" => "ct",
            "checkout_onepage_success" => "tp",
            "checkout_multishipping_success" => "tp" 
    );
    /**
     * This hash array contains list of widgets availaible for
     * each page types
     *
     * @var hashArray
     */
    protected $_widgetsEnabledMap = array (
            "cms_index_index" => array (
                    "hp-hban",
                    "hp-dbar",
                    "hp-rp",
                    "hp-bs",
                    "hp-na",
                    "hp-rhf",
                    "hp-rvi"
            ),
            "catalog_product_view" => array (
                    "pp-vsims",
                    "pp-psims",
                    "pp-csims",
                    "pp-rhf",
                    "pp-rvi" 
            ),
            "catalog_category_view" => array (
                    "cp-bs",
                    "cp-na",
                    "cp-cr" 
            ),
            "checkout_cart_index" => array (
                    "ct-csc" 
            ),
            "checkout_onepage_success" => array (
                    "tp-ppr",
                    "tp-pr"
            ),
            "checkout_multishipping_success" => array (
                    "tp-ppr",
                    "tp-pr" 
            ) 
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
     * Returns unique page id for widget generation
     *
     * @return string
     */
    protected function getPageId()
    {
        $currentPageInfo = $this->getPageInfo ();
        if (! array_key_exists ( $currentPageInfo, $this->_widgetsEnabledMap ))
            return "ot";
        return $this->_pageIDs [$currentPageInfo];
    }

    public function getCurrentCurrencySymbol(){
        $currencySymbol = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
        if($currencySymbol == ""){
            $currencySymbol = Mage::app()->getStore()->getCurrentCurrencyCode();
        }
        return $currencySymbol;
    }

    public function getTmRegion(){
        return $this->_tmRegion;
    }

    public function isCurrencyChanged(){
        $baseCurrencyCode = Mage::app()->getBaseCurrencyCode();
        $currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        if ($baseCurrencyCode != $currentCurrencyCode)
            return true;
        return false;
    }

    public function getCurrencyRate(){
        $baseCode = Mage::app()->getBaseCurrencyCode();      
        $currentCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        $currencies = array();
        array_push($currencies, $currentCode);

        $allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies(); 
        $rates = Mage::getModel('directory/currency')->getCurrencyRates($baseCode, array_values($currencies));
        return $rates[$currentCode];
    }

    /**
     * Returns type of widgets enabled for current page
     *
     * @return string|hashArray
     */
    public function getWidgetTypes()
    {
        $currentPageInfo = $this->getPageInfo ();
        if (! array_key_exists ( $currentPageInfo, $this->_widgetsEnabledMap ))
            return "";
        return $this->_widgetsEnabledMap [$currentPageInfo];
    }
    public function getMid()
    {
        return $this->_mid;
    }
    /**
     * Converts all widget parameters in hash array into
     * url friendly arguments format
     *
     * @return string
     */
    protected function getParamsData()
    {
        $trackingParamsData = '';
        foreach ( $this->_paramsArray as $paramKey => $paramValue ) {
            $trackingParamsData = $trackingParamsData . $paramKey . ':\'' . $paramValue . '\',';
        }
        $trackingParamsData = substr ( $trackingParamsData, 0, - 1 );
        return $trackingParamsData;
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
     * Inserts key value pair of parameters for widgets
     *
     * @param string $key            
     * @param string $value            
     */
    protected function insertParam($key, $value)
    {
        $this->_paramsArray [$key] = $value;
    }
    /**
     * Checks if widgets were enabled for current page
     *
     * @return boolean
     */
    public function isPageWidgetsEnabled()
    {
        if (strlen ( $this->_mid ) != 6 || ! $this->_helper->isWidgetsEnabled ())
            return false;
        $currentPage = $this->getPageInfo ();
        if (array_key_exists ( $currentPage, $this->_widgetsEnabledMap ))
            return true;
        return false;
    }
    /**
     * Generates params for Post purchase thank you page widgets
     * @widget_params productIds, eventId
     */
    protected function setCheckoutWidgetParams()
    {
        $lastOrderId = Mage::getSingleton ( 'checkout/session' )->getLastOrderId ();
        $lastOrder = Mage::getModel ( 'sales/order' )->load ( $lastOrderId );
        $orderItems = $lastOrder->getAllVisibleItems ();
        $orderItemIdsString = '';
        foreach ( $orderItems as $orderItem ) {
            $orderItemQty = $orderItem->getQtyOrdered ();
            $orderItemId = $orderItem->getProductId ();
            if ($this->_multiDomainSupport) {
                $orderItemId = $orderItemId . "_" . Mage::app()->getStore()->getStoreId();
            }
            for ($appendTime = 0; $appendTime < $orderItemQty; $appendTime++) {
                $orderItemIdsString = $orderItemIdsString . $orderItemId . ',';
            }
        }
        $orderItemIdsString = substr ( $orderItemIdsString, 0, - 1 );
        $this->insertParam ( "es", $orderItemIdsString );
    }
    /**
     * Generates params for Shopping cart page widgets
     *
     * @widget_params productIds
     */
    protected function setCartWidgetParams()
    {
        $items = Mage::getSingleton ( 'checkout/session' )->getQuote ()->getAllVisibleItems ();
        $cartItemIdsString = '';
        foreach ( $items as $cartItem ) {
            $cartItemQty = $cartItem->getQty ();
            $cartItemId = $cartItem->getProductId ();
            if ($this->_multiDomainSupport) {
               $cartItemId = $cartItemId . "_" . Mage::app()->getStore()->getStoreId();
            }
            $cartItemIdsString = $cartItemIdsString . $cartItemId . ',';
        }
        $cartItemIdsString = substr ( $cartItemIdsString, 0, - 1 );
        $this->insertParam ( "es", $cartItemIdsString );
    }

    protected function leafCategories($category, $storeId)
    {
        if($category->hasChildren()){
            $childs = $category->getChildren();
            $ret = "";
            foreach(explode(',',$childs) as $c){
                $c = Mage::getModel('catalog/category')->load($c);
                $ret = $ret . "," . $this->leafCategories($c, $storeId);                
            }
            return $ret;
        }
        if($storeId != "-1"){
            return "s". $storeId . "_" . $category->getId();
        }
        return $category->getId();
    }

    protected function setCategoryWidgetParams()
    {
        $layer = Mage::getSingleton ( 'catalog/layer' );
        $category = $layer->getCurrentCategory ();
        $categoryId = $category->getId ();
        $cache = Mage::app()->getCache();
        $leafCategoryIds = $cache->load($categoryId);
        if($leafCategoryIds == False){
            if($this->_multiDomainSupport){
                $storeId = Mage::app()->getStore()->getStoreId() . "";
            } else {
                $storeId = "-1";
            }
            $leafCategoryIds = trim( $this->leafCategories($category, $storeId),',' );
            $cache->save( $leafCategoryIds,$categoryId,array("tm_cache"), 518400 );
        }
        $this->insertParam ( 'catid', $leafCategoryIds );
    }
    /**
     * Sets params for type of widgets to be rendered in current page
     */
    protected function setWidgetTypeParams()
    {
        $widgetTypeString = '';
        $currentPageInfo = $this->getPageInfo ();
        foreach ( $this->_widgetsEnabledMap [$currentPageInfo] as $widgetCode ) {
            $widgetTypeString = $widgetTypeString . $widgetCode . ',';
        }
        $widgetTypeString = substr ( $widgetTypeString, 0, - 1 );
        $this->insertParam ( "w", $widgetTypeString );
    }
    /**
     * Method for recording widget params for each type of page and
     * returns param data to tracker
     * @tracking_params_default customer_id, marketplaceId
     *
     * @return string
     */
    public function getWidgetParams()
    {
        $userId = $this->_helper->getUserId ();
        $mid = $this->getMid ();
        $pageId = $this->getPageId ();
        if($userId != ""){ 
            $this->insertParam ( 'cid', $userId );
        }
        $this->insertParam ( 'pg', $pageId );
        if ($this->_multiDomainSupport) {
            $storeId = "s" . Mage::app()->getStore()->getStoreId();
            $this->insertParam ( 'catid', $storeId );
        }
        // $this->setWidgetTypeParams ();
        $currentPageInfo = $this->getPageInfo ();
        switch ($currentPageInfo) {
            case 'cms_index_index' :
                break;
            case 'catalog_product_view' :
                $product = Mage::registry ( 'product' );
                $productId = $product->getId ();
                if($this->_multiDomainSupport){
                    $productId = $productId . "_" . Mage::app()->getStore()->getStoreId();
                }
                $this->insertParam ( 'pid', $productId );
                break;
            case 'catalog_category_view' :
                $this->setCategoryWidgetParams ();
                break;
            case 'checkout_onepage_success' :
                $this->setCheckoutWidgetParams ();
                break;
            case 'checkout_multishipping_success' :
                $this->setCheckoutWidgetParams ();
                break;
            case 'checkout_cart_index' :
                $this->setCartWidgetParams ();
                break;
            default :
                break;
        }
        $this->insertParam ( 'limit', $this->_helper->getNumberOfProducts() );
        $widgetParams = $this->getParamsData ();
        return $widgetParams;
    }



}
