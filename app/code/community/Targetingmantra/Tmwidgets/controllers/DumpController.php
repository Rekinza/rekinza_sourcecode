<?php
/**
 * 
 * @author mani
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * 
 * Controller class for calling catalog feed and past data export methods. Responsible for
 * dispatching export files as a download response.
 */
class Targetingmantra_Tmwidgets_DumpController extends Mage_Core_Controller_Front_Action
{
    protected $_ipAddress;
    protected $_authKey;
    /**
     * Check authentication of request, routes to 404 not found page if authentication
     * failed
     *
     * @return boolean
     */
    public function _checkAuth()
    {
        $helper = Mage::helper ( 'tmwidgets' );
        $this->_authKey = $helper->getSecretKey ();
        
        $_secretKey = Mage::App ()->getRequest ()->getParam ( 'code' );
        $this->_ipAddress = $_SERVER ['REMOTE_ADDR'];
        if ($_secretKey == '' or $_secretKey != $this->_authKey) {
            Mage::log ( "Unauthorized request from " . $this->_ipAddress );
            $this->_redirect ( 'noroute' );
            return false;
        }
        return true;
    }
    
    /**
     * Generate catalog data as a download response.
     * This method is expected to
     * call on daily basis
     */
    public function catalogAction()
    {
        if ($this->_checkAuth ()) {
            set_time_limit ( 0 );
            $helper = Mage::helper ( 'tmwidgets' );
            $helper->writeLog ( 'Catalog download request from ' . $this->_ipAddress );
            $filename = 'catalog.csv';
            $sendPages = false;
            $pageNum = 1;
            $pageLimit = 1000;
            $imgWidth = - 1;
            $imgHeight = - 1;
            $imgMode = false;
            $customFields = '0';
            $storeId = "-1";
            if ($this->getRequest ()->getParam ( 'custom' )) {
                $customFields = $this->getRequest ()->getParam ( 'custom' );
            }
            if ($this->getRequest ()->getParam ( 'store' )) {
                $storeId = $this->getRequest ()->getParam ( 'store' );
            }
            if ($this->getRequest ()->getParam ( 'width' )) {
                $imgWidth = $this->getRequest ()->getParam ( 'width' );
                $imgHeight = $this->getRequest ()->getParam ( 'height' );
            }
            if ($this->getRequest ()->getParam ( 'mode' )) {
                $imgMode = $this->getRequest ()->getParam ( 'mode' );
            }
            if ($this->getRequest ()->getParam ( 'page' )) {
                $sendPages = true;
                $pageNum = $this->getRequest ()->getParam ( 'page', 1 );
                $pageLimit = $this->getRequest ()->getParam ( 'limit', 1000 );
            }                                                                                        
            $content = Mage::getModel ( 'tmwidgets/exporter' )->generateCatalogPSV ( $sendPages, $pageNum, $pageLimit, $imgWidth, $imgHeight, $imgMode, $customFields, $storeId);
            $helper->writeLog ( 'Catalog csv rendered' );
            $this->_prepareDownloadResponse ( $filename, $content );
        }
    }

    /**
    * This will be called only if email notifications enabled
    */
    public function usersAction()
    {
        if ($this->_checkAuth ()) {
            set_time_limit ( 0 );
            $helper = Mage::helper ( 'tmwidgets' );
            $helper->writeLog ( 'users download request from ' . $this->_ipAddress );
            $filename = 'users.csv';
            $sendPages = false;
            $pageNum = 1;
            $pageLimit = 1000;
        
            if ($this->getRequest ()->getParam ( 'page' )) {
                $sendPages = true;
                $pageNum = $this->getRequest ()->getParam ( 'page', 1 );
                $pageLimit = $this->getRequest ()->getParam ( 'limit', 1000 );
            }                                                                                        
            $content = Mage::getModel ( 'tmwidgets/exporter' )->generateUsersCSV ( $sendPages, $pageNum, $pageLimit);
            $helper->writeLog ( 'users csv rendered' );
            $this->_prepareDownloadResponse ( $filename, $content );
        }
    }

    /**
     * Generate past orders data as a download response.
     * This method will be
     * only once just after sipognup.
     */
    public function ordersAction()
    {
        if ($this->_checkAuth ()) {
            @set_time_limit ( 0 );
            $helper = Mage::helper ( 'tmwidgets' );
            $helper->writeLog ( 'Orders download request from ' . $this->_ipAddress );
            $filename = 'orders.csv';
            $sendPages = false;
            $pageNum = 1;
            $pageLimit = 100;
            $numDays = 120;
            $storeId = -1;
            if ($this->getRequest ()->getParam ( 'days' )) {
                $numDays = $this->getRequest ()->getParam ( 'days' );
            }
            if ($this->getRequest ()->getParam ( 'page' )) {
                $sendPages = true;
                $pageNum = $this->getRequest ()->getParam ( 'page', 1 );
                $pageLimit = $this->getRequest ()->getParam ( 'limit', 1000 );
            }
            if ($this->getRequest ()->getParam ( 'store' )) {
                $storeId = $this->getRequest ()->getParam ( 'store' );
            }
            $content = Mage::getModel ( 'tmwidgets/exporter' )->generateOrdersCSV ( $sendPages, $pageNum, $pageLimit, $numDays, $storeId);
            $helper->writeLog ( 'Orders csv rendered' );
            $this->_prepareDownloadResponse ( $filename, $content );
        }
    }
    /**
     * Generate past product views data as a download response.
     * This method will be
     * only once just after signup.
     */
    public function viewsAction()
    {
        if ($this->_checkAuth ()) {
            $helper = Mage::helper ( 'tmwidgets' );
            $helper->writeLog ( 'Views download request from ' . $this->_ipAddress );
            $filename = 'views.csv';
            $numDays = 180;
            $storeId = -1;
            if ($this->getRequest ()->getParam ( 'days' )) {
                $numDays = $this->getRequest ()->getParam ( 'days' );
            }
            if ($this->getRequest ()->getParam ( 'store' )) {
                $storeId = $this->getRequest ()->getParam ( 'store' );
            }
            $content = Mage::getModel ( 'tmwidgets/exporter' )->generateViewsCSV ($numDays, $storeId);
            $helper->writeLog ( 'Views csv rendered' );
            $this->_prepareDownloadResponse ( $filename, $content );
        }
    }
    /**
     * Generate past add to cart data as a download response.
     * This method will be
     * only once just after signup.
     */
    public function cartAction()
    {
        if ($this->_checkAuth ()) {
            $helper = Mage::helper ( 'tmwidgets' );
            $helper->writeLog ( 'add to cart download request from ' . $this->_ipAddress );
            $filename = 'addCart.csv';
            $numDays = 180;
            $storeId = -1;
            if ($this->getRequest ()->getParam ( 'days' )) {
                $numDays = $this->getRequest ()->getParam ( 'days' );
            }
            if ($this->getRequest ()->getParam ( 'store' )) {
                $storeId = $this->getRequest ()->getParam ( 'store' );
            }
            $content = Mage::getModel ( 'tmwidgets/exporter' )->generateCartCSV ($numDays, $storeId);
            $helper->writeLog ( 'add to cart csv rendered' );
            $this->_prepareDownloadResponse ( $filename, $content );
        }
    }
    
    /**
     * 
     */
    public function categoriesAction(){
        if ($this->_checkAuth ()) {
            $helper = Mage::helper ( 'tmwidgets' );
            $helper->writeLog ( 'categories download request from ' . $this->_ipAddress );
            $filename = 'categories.csv';
            $content = Mage::getModel ( 'tmwidgets/exporter' )->generateCategories();
            $helper->writeLog ( 'categories csv rendered' );
            $this->_prepareDownloadResponse ( $filename, $content );
        }
    }
    /**
     * Prints extension status as a json response
     */
    public function statusAction()
    {
        $helper = Mage::helper ( 'tmwidgets' );
        $this->_authKey = $helper->getSecretKey ();
        $result = array ();
        $result ["installed"] = "OK";
        if (strlen ( $this->_authKey ) >= 4)
            $result ["secret_code"] = "OK";
        else
            $result ["secret_code"] = "FAIL";
        $this->getResponse ()->setHeader ( 'Content-type', 'application/json; charset=UTF-8' );
        $this->getResponse ()->setBody ( json_encode ( $result ) );
    }
    /**
     * Prints statistics of last 4 months as a json response
     * Authentication required
     */
    public function validateAction()
    {
        $helper = Mage::helper ( 'tmwidgets' );
        $this->_authKey = $helper->getSecretKey ();
        $secretKey = Mage::App ()->getRequest ()->getParam ( 'code' );
        $result = array ();
        $result ["installed"] = "OK";
        if ($this->_authKey == $secretKey) {
            $result ["secret_code"] = "OK";
            $result ["products_count"] = $helper->getProductCount ();
            $result ["views_count"] = $helper->getViewsCount ();
            $result ["purchases_count"] = $helper->getPurchaseCount ();
            $result ["add_cart_count"] = $helper->getCartCount ();
        } else {
            $result ["secret_code"] = "FAIL";
            $result ["products_count"] = 0;
            $result ["views_count"] = 0;
            $result ["purchases_count"] = 0;
            $result ["add_cart_count"] = 0;
        }
        $this->getResponse ()->setHeader ( 'Content-type', 'application/json; charset=UTF-8' );
        $this->getResponse ()->setBody ( json_encode ( $result ) );
    }
    /**
     * Prints catalog attributes names and corresponding data types as
     * a json response.
     * Authentication required
     */
    public function headerAction()
    {
        if ($this->_checkAuth ()) {
            $result = array ();
            $result ["defined"] = array ();
            $result ["default"] = array ();
            $attributes = Mage::getResourceModel ( 'catalog/product_attribute_collection' )->getItems ();
            foreach ( $attributes as $attribute ) {
                if ($attribute->getIsUserDefined ()) {
                    $attributeCode = $attribute->getAttributecode ();
                    $attributeType = $attribute->getBackendType ();
                    $result ["defined"] [$attributeCode] = $attributeType;
                } else {
                    $attributeCode = $attribute->getAttributecode ();
                    $attributeType = $attribute->getBackendType ();
                    $result ["default"] [$attributeCode] = $attributeType;
                }
            }
            $this->getResponse ()->setHeader ( 'Content-type', 'application/json; charset=UTF-8' );
            $this->getResponse ()->setBody ( json_encode ( $result ) );
        }
    }

    /**
    * Stores view controller
    * Authentication required
    */
    public function storesAction(){
        if ($this->_checkAuth ()) {
            $result = array();
            $storesResult = array();
            $cnt = 0;
            $stores = Mage::app()->getStores();
            $storeIdsStr = "";
            foreach($stores as $sId => $sVal){
                $storeId = Mage::app()->getStore($sId)->getId();
                $storeIdsStr .= $storeId . ",";
                $storeName = Mage::app()->getStore($sId)->getName();
                $storeCode = Mage::app()->getStore($sId)->getCode();
                $storeUrl = Mage::app()->getStore($sId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);;
                $resultString = $storeId . ";" . $storeName . ";" . $storeCode . ";" . $storeUrl;;
                $storesResult[$cnt] = $resultString;
                $cnt += 1;
            }
            $result['stores'] = $storesResult;
            $result['ids'] = trim($storeIdsStr,",") ;
            $result['multi'] = Mage::helper ( 'tmwidgets' )->isMultiDomainEnable ();
            $this->getResponse ()->setHeader ( 'Content-type', 'application/json; charset=UTF-8' );
            $this->getResponse ()->setBody ( json_encode ( $result ) );
        }
    }

    /**
     * Logs view controller
     * Authentication required
     */
    public function logAction()
    {
        if ($this->_checkAuth ()) {
            $lines = 50;
            if (Mage::App ()->getRequest ()->getParam ( 'lines' )) {
                $lines = Mage::App ()->getRequest ()->getParam ( 'lines' );
            }
            $filePath = Mage::getBaseDir ( 'var' ) . DS . 'log' . DS . 'tm_export.log';
            $cssUrl = Mage::getStoreConfig ( Mage_Core_Model_Url::XML_PATH_SECURE_URL ) . "skin/frontend/base/default/css/targetingmantra_tmwidgets/logs.css";
            $logLines = Mage::helper ( 'tmwidgets' )->tailLog ( $filePath, $lines );
            $this->getResponse ()->setHeader ( 'Content-type', 'text/html; charset=UTF-8' );
            $content = "<head><link rel='stylesheet' type='text/css' href='" . $cssUrl . "' media='all' /></head><body>" . "<div id='tmlogs' style='border:1px solid #ededeb; width: 90%; padding: 10px; height: 600px; overflow-y: scroll; margin:0px auto;'>" . "<table>" . $logLines . "</table></div></body></html>";
            
            $this->getResponse ()->setBody ( $content );
        }
    }


    public function cimageAction(){
        $result = array ();
        $postData = Mage::app()->getRequest()->getPost();
        if(! array_key_exists('pid', $postData)){
            $this->_redirect ( 'noroute' );
        } 
        $paramPid = $postData['pid'];
        if($paramPid == ""){
            $this->_redirect ( 'noroute' );
        }
        $multiDomainSupport = Mage::helper ( 'tmwidgets' )->isMultiDomainEnable ();
        $pids = explode(",", $paramPid);
        $width = Mage::helper ( 'tmwidgets' )->getImgWidth();
        $height = Mage::helper ( 'tmwidgets' )->getImgHeight();
        if(!$width) $width = 150;
        if(!$height) $height = 150;
        foreach($pids as $pid){
            $new_pid = $pid;
            if($multiDomainSupport){
                $store_pid = explode('_',$pid);
                if(count($store_pid) > 0){
                  $new_pid = $store_pid[0];
                }
            }
            try {
                $product = Mage::getModel('catalog/product')->load($new_pid);
                $path = Mage::helper('catalog/image')->init($product, 'image')->resize($width, $height);
            } catch (Exception $e) {
                $path = "http://d1gsqroy9pf3oi.cloudfront.net/images/140x150.jpeg";
            }
            $result [$pid] = (string) $path;
        }
        $this->getResponse ()->setHeader ( 'Content-type', 'application/json; charset=UTF-8' );
        $this->getResponse ()->setBody ( json_encode ( $result ) );
    }

    /*
    * Method to return a json string of the form - s.storeid -> String(s.storeid_leafcategoryids <,>) 
    */

    public function getLeafCategoriesAction() {
        $helper = Mage::helper('tmwidgets');
        $result = array ();
        $stores = Mage::app()->getStores();

        if($helper->isMultiDomainEnable()) {
            $_multiStoreFlag = true;
        } else {
            $_multiStoreFlag = false;
        }

        if(empty($stores)) {
            $helper->writeLog("No stores found!");
            echo "No stores found!";
            return;
        }

        foreach ($stores as $_eachStoreId => $val) {
            $rootcatId= Mage::app()->getStore($_eachStoreId)->getRootCategoryId();
            $categories = Mage::getModel('catalog/category')->getCategories($rootcatId);
            $helper->getLeafCategoryIds($categories, $_multiStoreFlag, $_eachStoreId);
            $result["s" . $_eachStoreId] = explode(',', $GLOBALS['category_leaf']);
            unset($GLOBALS['category_leaf']);
        }

        $this->getResponse ()->setHeader ( 'Content-type', 'application/json; charset=UTF-8' );
        $this->getResponse ()->setBody ( json_encode ( $result ) );
    }

   /*
    * Method to get the current TM setting values
    */

    public function getsettingsAction() {
        $helper = Mage::helper ( 'tmwidgets' );

        $this->_imgHeight = $helper->getImgHeight();
        $this->_imgWidth = $helper->getImgWidth();
        $this->_mid = $helper->getMID();
        $this->_secretKey = $helper->getSecretKey();
        $this->_trackingPixel = $helper->isTrackingPixelEnabled();
        $this->_enableWidgets = $helper->isWidgetsEnabled();
        $this->_maxProductsInWidget = $helper->getNumberOfProducts();
        $this->_multipleDomainSupport = $helper->isMultiDomainEnable();
        $this->_region = $helper->getTmRegion();
        $this->_displayProductWithZeroPrice = $helper->isFilterProductWithPriceZeroEnabled();

        $result = array ();
        $result ["imageHeight"] = $this->_imgHeight;
        $result ["imageWidth"] = $this->_imgWidth;
        $result ["apiKey"] = $this->_mid . $this->_secretKey;
        $result ["isTrackingPixelEnabled"] = $this->_trackingPixel;
        $result ["isWidgetsEnabled"] = $this->_enableWidgets;
        $result ["_maxProductsInWidget"] = $this->_maxProductsInWidget;
        $result ["isMultiDomainEnable"] = $this->_multipleDomainSupport;
        $result ["region"] = $this->_region;
        $result ["displayProductWithZeroPrice"] = $this->_displayProductWithZeroPrice;

        $this->getResponse ()->setHeader ( 'Content-type', 'application/json; charset=UTF-8' );
        $this->getResponse ()->setBody ( json_encode ( $result ) );
    }

    /*
    * Method to set the imageHeight, imageWidth, isMultipleDomainSupport, region options in TM settings page
    */

    public function setsettingsAction(){

        $helper = Mage::helper('tmwidgets');
        $_width = Mage::App ()->getRequest ()->getParam ('width');
        $_height = Mage::App()->getRequest()->getParam('height');
        $_multipleDomainSupport = Mage::App()->getRequest()->getParam('multidomain');
        $_region = Mage::App()->getRequest()->getParam('region');

        $this->_checkAuth();

        if(!is_null($_width)) {
            $helper->setImageWidth($_width);
        }

        if(!is_null($_height)){
            $helper->setImageHeight($_height);
        }

        if(!is_null($_multipleDomainSupport)){
            $helper->setMultipleDomainSupport($_multipleDomainSupport);
        }

        if(!is_null($_region)) {
            $helper->setRegion($_region);
        }

        //Cache to be flushed for the config changes to reflect appropriately.
        Mage::app()->getCacheInstance('config')->flush(); 

        echo "<br/>" . "Successfully updated settings!";
        $helper->writeLog("Successfully updated settings!");
    }
}