<?php
/**
 * 
 * @author mani
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class Targetingmantra_Tmwidgets_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_SECRET_API_KEY = 'sales/tmwidgets/tm_api_key';
    const XML_PATH_WIDGETS_ACTIVE = 'sales/tmwidgets/tm_widgets_enable';
    const XML_PATH_PIXEL_ACTIVE = 'sales/tmwidgets/tm_pixel_enable';
    const XML_PATH_NUM_PRODUCTS = 'sales/tmwidgets/tm_num_products';
    const XML_PATH_IMG_WIDTH = 'sales/tmwidgets/tm_img_width';
    const XML_PATH_IMG_HEIGHT = 'sales/tmwidgets/tm_img_height';
    const XML_PATH_MULTIDOMAIN_ACTIVE = 'sales/tmwidgets/tm_multidomain_enable';
    const XML_PATH_REGION = "sales/tmwidgets/tm_region";
    const XML_PATH_FILTER_PRODUCT_PRICE_ZERO = "sales/tmwidgets/tm_filter_product_price_zero";
    
    /**
     *
     * @param string $store            
     * @return string
     */
    public function getSecretKey($store = null)
    {
        $apiKey = Mage::getStoreConfig ( self::XML_PATH_SECRET_API_KEY, $store );
        $secretKey = substr ( $apiKey, 6 );
        return $secretKey;
    }
    /**
     *
     * @param string $store            
     * @return string
     */
    public function getMID($store = null)
    {
        $apiKey = Mage::getStoreConfig ( self::XML_PATH_SECRET_API_KEY, $store );
        $mid = substr ( $apiKey, 0, 6 );
        return $mid;
    }

    public function getNumberOfProducts($store = null)
    {
        $numProducts = Mage::getStoreConfig( self::XML_PATH_NUM_PRODUCTS, $store);
        if(!$numProducts){
            $numProducts = 10;
        }
        return $numProducts;
    }

    public function getImgWidth($store = null)
    {
        $imgWidth = Mage::getStoreConfig( self::XML_PATH_IMG_WIDTH, $store);
        if(!$imgWidth)
            $imgWidth = 150;
        return $imgWidth;
    }

    public function getImgHeight($store = null)
    {
        $imgHeight = Mage::getStoreConfig( self::XML_PATH_IMG_HEIGHT, $store);
        if(!$imgHeight)
            $imgHeight = 150;
        return $imgHeight;
    }

    public function getTmRegion($store = null)
    {
        $tmRegion = Mage::getStoreConfig( self::XML_PATH_REGION, $store);
        if(!$tmRegion)
            $tmRegion = "na";
        return $tmRegion;
    }

    /**
     *
     * @param string $store            
     * @return boolean
     */
    public function isWidgetsEnabled($store = null)
    {
        $widgetsEnabledStatus = Mage::getStoreConfig ( self::XML_PATH_WIDGETS_ACTIVE, $store );
        if ($widgetsEnabledStatus)
            return true;
        else
            return false;
    }
    
    /**
     * 
     * @param string $store
     * @return boolean
     */
    public function isFilterProductWithPriceZeroEnabled($store = null) 
    {
    	$status = Mage::getStoreConfig ( self::XML_PATH_FILTER_PRODUCT_PRICE_ZERO, $store );
    	if ($status)
    		return true;
    	else 
    		return false;
    }
    /**
     *
     * @param string $store            
     * @return boolean
     */
    public function isTrackingPixelEnabled($store = null)
    {
        $trackingEnabledStatus = Mage::getStoreConfig ( self::XML_PATH_PIXEL_ACTIVE, $store );
        if ($trackingEnabledStatus)
            return true;
        else
            return false;
    }
    /**
     *
     * @param string $store            
     * @return boolean
     */
    public function isMultiDomainEnable($store = null)
    {
        $multiDomainStatus = Mage::getStoreConfig ( self::XML_PATH_MULTIDOMAIN_ACTIVE, $store );
        if ($multiDomainStatus == 1)
            return true;
        else
            return false;
    }

    /**
     * Returns user id if user is logged in else returns session id
     *
     * @return unknown
     */
    public function getUserId()
    {
        $session = Mage::getSingleton ( 'customer/session' );
        $userId = "";
        if ($session && $session->isLoggedIn ())
            $userId = $session->getId ();
        return $userId;
    }
    /**
     *
     * @return integer
     */
    public function getProductCount()
    {
        $totalProductsCount = Mage::getModel ( 'catalog/product' )->getCollection ()->addAttributeToSelect ( '*' )->addAttributeToFilter ( 'status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED )->addAttributeToFilter ( 'price', array (
                'gt' => 0 
        ) )->addAttributeToFilter ( 'visibility', array (
                'in' => array (
                        Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
                        Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG 
                ) 
        ) )->getSize ();
        return $totalProductsCount;
    }
    /**
     *
     * @return integer
     */
    public function getCartCount()
    {
        $cartEventId = 1;
        foreach ( Mage::getModel ( 'reports/event_type' )->getCollection () as $eventType ) {
            if ($eventType->getEventName () == 'checkout_cart_add_product') {
                $cartEventId = ( int ) $eventType->getId ();
                break;
            }
        }
        $resource = Mage::getSingleton ( 'core/resource' );
        $conn = $resource->getConnection ( 'core_read' );
        $query = $conn->select ()->from ( array (
                'p' => $resource->getTableName ( 'reports/event' ) 
        ), new Zend_Db_Expr ( 'COUNT(*)' ) )->where ( 'event_type_id = ?', $cartEventId );
        $addCartCount = $conn->fetchOne ( $query );
        return $addCartCount;
    }
    /**
     *
     * @return integer
     */
    public function getPurchaseCount()
    {
        $purchasesCount = Mage::getModel ( 'Sales/Order' )->getCollection ()->addAttributeToSelect ( '*' )->getSize ();
        return $purchasesCount;
    }
    /**
     *
     * @return integer
     */
    public function getViewsCount()
    {
        $viewEventId = 1;
        foreach ( Mage::getModel ( 'reports/event_type' )->getCollection () as $eventType ) {
            if ($eventType->getEventName () == 'catalog_product_view') {
                $viewEventId = ( int ) $eventType->getId ();
                break;
            }
        }
        $resource = Mage::getSingleton ( 'core/resource' );
        $conn = $resource->getConnection ( 'core_read' );
        $query = $conn->select ()->from ( array (
                'p' => $resource->getTableName ( 'reports/event' ) 
        ), new Zend_Db_Expr ( 'COUNT(*)' ) )->where ( 'event_type_id = ?', $viewEventId );
        $viewsCount = $conn->fetchOne ( $query );
        return $viewsCount;
    }
    /**
     *
     * @param String $msg            
     */
    public function writeLog($msg)
    {
        Mage::log ( $msg, null, 'tm_export.log' );
    }
    /**
     * Returns last k lines of targetingmantra log file
     *
     * @param number $lines            
     * @param string $adaptive            
     * @return boolean
     */
    public function tailLog($filePath, $lines = 1, $adaptive = true)
    {
        $lineCount = 0;
        // Open file
        $f = fopen ( $filePath, "r" );
        if ($f == false)
            return false;
            // Sets buffer size
        if (! $adaptive)
            $buffer = 4096;
        else
            $buffer = ($lines < 2 ? 64 : ($lines < 10 ? 512 : 4096));
            // Jump to last character
        fseek ( $f, - 1, SEEK_END );
        // Read it and adjust line number if necessary
        // (Otherwise the result would be wrong if file doesn't end with a blank line)
        if (fread ( $f, 1 ) != "\n")
            $lines -= 1;
            // Start reading
        $output = "";
        $chunk = '';
        // While we would like more
        while ( ftell ( $f ) > 0 && $lines >= 0 ) {
            // Figure out how far back we should jump
            $seek = min ( ftell ( $f ), $buffer );
            // Do the jump (backwards, relative to where we are)
            fseek ( $f, - $seek, SEEK_CUR );
            // Read a chunk and prepend it to our output
            $chunk = fread ( $f, $seek );
            $chunkLines = explode ( "\n", $chunk );
            foreach ( $chunkLines as $chunkLine ) {
                $lineCount += 1;
                $output = "<tr><td><b>" . $lineCount . "</b></td><td>" . $chunkLine . "</td></tr>\n" . $output;
                $lines -= 1;
            }
            // Jump back to where we started reading
            fseek ( $f, - mb_strlen ( $chunk, '8bit' ), SEEK_CUR );
            // Decrease our line counter
        }
        // While we have too many lines
        // (Because of buffer size we might have read too many)
        while ( $lines ++ < 0 ) {
            // Find first newline and remove all text before that
            $output = substr ( $output, strpos ( $output, "\n" ) + 1 );
        }
        // Close file and return
        fclose ( $f );
        return trim ( $output );
    }

    public function  getLeafCategoryIds($categories, $_multiStoreFlag, $_storeId) {
        foreach($categories as $category) {
            $cat = Mage::getModel('catalog/category')->load($category->getId());
            $products = Mage::getModel('catalog/category')->load($cat->getId())
                ->getProductCollection()
                ->addAttributeToSelect('entity_id')
                ->addAttributeToFilter('status', 1)
                ->addAttributeToFilter('visibility', 4);
            
            if($cat->hasChildren()){
                $this->getLeafCategoryIds(Mage::getModel('catalog/category')
                    ->getCategories($cat->getId()), $_multiStoreFlag, $_storeId);
            } else {
                if($products->count() > 0) {
                    //echo "Leaf Category - " . $cat->getName() . "\t" . "Product Count - " . $products->count() . "<br/>";
                    if(is_null($GLOBALS['category_leaf'])) {
                        if($_multiStoreFlag) {
                            $GLOBALS['category_leaf'] = $GLOBALS['category_leaf'] . "s" . $_storeId . "_" . $cat->getId();
                        } else {
                            $GLOBALS['category_leaf'] = $GLOBALS['category_leaf'] . $cat->getId();
                        }
                    } else {
                        if($_multiStoreFlag) {
                            $GLOBALS['category_leaf'] = $GLOBALS['category_leaf'] . "," . "s" . $_storeId . "_" . $cat->getId();
                        } else {
                            $GLOBALS['category_leaf'] = $GLOBALS['category_leaf'] . "," . $cat->getId();
                        }
                    }
                }       
            }           
        }
    }

    public function setImageWidth($value){
        if((int) $value <= 600 && (int) $value >= 50) {
            Mage::getModel('core/config')->saveConfig(self::XML_PATH_IMG_WIDTH, (int) $value);
        } else {
            $this->writeLog("ERROR: image width should be in the range [50,600]");
            echo "<br/>" . "ERROR: image width should be in the range [50,600]";
        }
    }

    public function setImageHeight($value){
        if((int) $value <= 600 && (int) $value >= 50) {
            Mage::getModel('core/config')->saveConfig(self::XML_PATH_IMG_HEIGHT, $value);
        } else {
            $this->writeLog("ERROR: image height should be in the range [50,600]");
            echo "<br/>" . "ERROR: image height should be in the range [50,600]";
        }
    }

    public function setMultipleDomainSupport($value){
        if($value === 'true' || $value == 1) {
            Mage::getModel('core/config')->saveConfig(self::XML_PATH_MULTIDOMAIN_ACTIVE, 1);
        } else if($value === 'false' || $value == 0){
            Mage::getModel('core/config')->saveConfig(self::XML_PATH_MULTIDOMAIN_ACTIVE, 0);
        } else {
            $this->writeLog("ERROR: multidomain value should be one of {true,1,false,0}");
            echo "<br/>" . "ERROR: multidomain value should be one of {true,1,false,0}";
        }
    }

    public function setRegion($value){
        $_regionArray = Mage::getModel('tmwidgets/System_Config_Source_Dropdown_Values')->toOptionArray();
        $_set = false;
        foreach($_regionArray as $_region) {
            if($value == $_region[value]) {
                Mage::getModel('core/config')->saveConfig(self::XML_PATH_REGION, $value);
                $_set = true;
            } else if($value == $_region[label]) {
                Mage::getModel('core/config')->saveConfig(self::XML_PATH_REGION, $_region[label]);
                $_set = true;
            }
        }
        if(!$_set) {
            $this->writeLog("ERROR: region value should be one of {na, North America, asia, Asia, sa, South America}");
            echo "<br/>" . "ERROR: region value should be one of {na, North America, asia, Asia, sa, South America}";
        }
    }
}
