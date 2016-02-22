<?php
/**
 * 
 * @author mani
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * 
 * Exporter model for generating catalog feeds and past behaviour data
 * feeds
 */
class Targetingmantra_Tmwidgets_Model_Exporter extends Varien_Object
{
    const CATALOG_BATCH_SIZE = 250;
    const EVENTS_BATCH_SIZE = 1000;
    /**
     * Records container
     *
     * @var array
     */
    protected $_dataCollection = null;
    protected $_writerIO = null;
    protected $_fileName = null;
    protected $_imgWidth = - 1;
    protected $_imgHeight = - 1;
    protected $_usrWidth = 150;
    protected $_usrHeight = 150;
    protected $_imgMode = 1;
    protected $_mainStoreId = "-1";
    protected $_displayProductWithZeroPrice = true;
    
    /**
     * Default attributes to include in catalog data
     *
     * @var unknown
     */
    protected $_catalogKeys = array (
            "entity_id",
            "sku",
            "name",
            "brand",
            "manufacturer",
            "short_description",
            "description",
            "url",
            "image",
            "price",
            "is_salable",
            "qty",
            "special_price",
            "created_at",
            "status",
            "visibility",
            "color",
            "gender",
            "material",
            "weight",
            "msrp",
            "is_salable" 
    );
    protected $_configurableKeys = array ();
    protected $_customAttributes = array ();
    /**
     *
     * @param product $products            
     * @return multitype:
     */
    protected function getCollectionHeader()
    {
        $product = current ( $this->_dataCollection );
        $headers = array_keys ( $product->getData () );
        
        return $headers;
    }
    /**
     *
     * @param unknown $_string            
     * @return string
     */
    protected function preProcessText($_string)
    {
        $_string = trim ( preg_replace ( "/\||\n/", '', $_string ) );
        return $_string;
    }
    /**
     *
     * @param array $callbackForIndividual            
     */
    protected function walkFile(array $callbackForIndividual, $storeId="-1")
    {
        $this->writeLog ( "Bulk walk file export mode enabled" );
        $batchSize = self::CATALOG_BATCH_SIZE;
        $this->_dataCollection->setPageSize ( $batchSize );
        $currentPage = 1;
        $pages = $this->_dataCollection->getLastPageNumber ();
        do {
            $this->_dataCollection->setCurPage ( $currentPage );
            $this->_dataCollection->load ();
            $this->writeLog ( "Writing batch #" . $currentPage );
            foreach ( $this->_dataCollection as $item ) {
                call_user_func ( $callbackForIndividual, $item, $storeId );
            }
            $currentPage ++;
            $this->_dataCollection->clear ();
        } while ( $currentPage <= $pages );
    }
    /**
     *
     * @param String $msg            
     */
    protected function writeLog($msg)
    {
        Mage::log ( $msg, null, 'tm_export.log' );
    }
    
    /**
     * Creates file with time as a filename
     */
    protected function createFile()
    {
        $path = Mage::getBaseDir ( 'var' ) . DS . 'export' . DS;
        $name = md5 ( microtime () );
        $this->_fileName = $path . DS . $name . '.csv';
        $this->_writerIO->setAllowCreateFolders ( true );
        $this->_writerIO->open ( array (
                'path' => $path 
        ) );
        $this->_writerIO->streamOpen ( $this->_fileName, 'w+' );
        $this->writeLog ( 'File ' . $this->_fileName . ' created' );
        $this->_writerIO->streamLock ( true );
    }
    /**
     * Enumerate over all custom attributes defined by shop
     * owner and insert into customAttributes hash array
     */
    protected function getCustomAttributes()
    {
        $attributes = Mage::getResourceModel ( 'catalog/product_attribute_collection' )->getItems ();
        foreach ( $attributes as $attribute ) {
            $attributeCode = $attribute->getAttributecode ();
            if ($attribute->getIsUserDefined () && ! in_array ( $attributeCode, $this->_catalogKeys )) {
                $attributeType = $attribute->getBackendType ();
                $this->_customAttributes [$attributeCode] = $attributeType;
                array_push ( $this->_catalogKeys, $attributeCode );
            }
        }
    }
    protected function isDateValid($str)
    {
        if (! is_string ( $str )) {
            return false;
        }
        $stamp = strtotime ( $str );
        if (! is_numeric ( $stamp )) {
            return false;
        }
        if (checkdate ( date ( 'm', $stamp ), date ( 'd', $stamp ), date ( 'Y', $stamp ) )) {
            return true;
        }
        return false;
    }

    protected function getLeafCategories($productObject){
        $categoryIds = $productObject->getCategoryIds();
        $categories = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToSelect('name') //you can add more attributes using this
            ->addAttributeToFilter('entity_id', array('in'=>$categoryIds))
            ->addAttributeToSort('level', 'desc');
        $subCategoryIds = "";
        $lastLevel=-1;
        foreach($categories as $cat) {
            $catLevel = $cat->getLevel();
            if($lastLevel == -1) $lastLevel = $catLevel;
            else if($lastLevel > $catLevel)
                continue;
            if($this->_mainStoreId != "-1")
                $subCategoryIds .= "s". $this->_mainStoreId . "_" . $cat->getId() . ",";
            else
                $subCategoryIds .= $cat->getId() . ",";
        }
        $leafCats = trim($subCategoryIds, ',');
        return $leafCats;
    }

    
    /**
     * Creates record for each product for catalog data and
     * order of attributes in a record is fixed
     *
     * TODO: Refactor this method and get cache image url
     *
     * @param Catalog/Product $item            
     */
    protected function generateCatalogRecord($item, $storeId="-1")
    {
        $this->_mainStoreId = $storeId;
        $recordData = array ();
        if ($storeId != "-1") {
            $item->setStoreId($storeId);
        }
        $attributeOptions = array ();
        $itemType = $item->getTypeID ();
        if ($itemType == 'configurable') {
            $productAttributeOptions = $item->getTypeInstance ( true )->getConfigurableAttributesAsArray ( $item );
            foreach ( $productAttributeOptions as $productAttribute ) {
                $fieldLabel = strtolower ( $productAttribute ['label'] );
                $attributeOptions [$fieldLabel] = '';
                foreach ( $productAttribute ['values'] as $attribute ) {
                    $attributeOptions [$fieldLabel] .= $attribute ['store_label'] . ",";
                }
                $attributeOptions [$fieldLabel] = substr ( $attributeOptions [$fieldLabel], 0, - 1 );
            }
        }
        $productSubCategory = '';
        if ($storeId != "-1") {
            $productObject = Mage::getModel('catalog/product')->setStoreId($storeId)->load($item->getId());
        } else {
            $productObject = Mage::getModel('catalog/product')->load($item->getId());
        }
        
        //$product->getCategoryCollection()->addAttributeToFilter('children_count', 0);
        $productData = $item->getData ();
        if(! isset ($productData ['price'])) {
            if(isset($productData['msrp']))
                $productData ['price'] = $prodctData['msrp'];
        }
        if(! isset($productData ['special_price'])) {
            $productData ['special_price'] = $productData['price'];
        }
        foreach ( $this->_catalogKeys as $fieldKey ) {
            $fieldData = '';
            if (array_key_exists ( $fieldKey, $attributeOptions )) {
                $fieldData = $attributeOptions [$fieldKey];
            } else if ($fieldKey == 'url') {
                $fieldData = $item->getProductUrl ();
            }  else if($fieldKey == 'entity_id') {
                $fieldData = $item->getId();
                if($storeId != "-1"){
                    $fieldData = $fieldData . "_" . $storeId;
                }
            } else if ($fieldKey == 'image') {
                $fieldData = Mage::getBaseUrl ( Mage_Core_Model_Store::URL_TYPE_MEDIA ) . "catalog/product" . $item->getImage ();
                if ($this->_imgMode == 1) {
                    if ($this->_imgWidth != -1) {
                        try {
                            $fieldData = ( string ) Mage::helper ( 'catalog/image' )->init ( $productObject, 'image' )->resize ( $this->_imgWidth, $this->_imgHeight );
                        } catch ( Exception $e ) {
                        }
                    } else {
                        try {
                            $fieldData = $item->getSmallImageUrl ();
                        } catch ( Exception $e ) {
                        }
                    }
                } else if($this->_imgMode == 2){
                    $fieldData = ( string ) Mage::helper ( 'catalog/image' )->init ( $productObject, 'image' )->resize ( $this->_usrWidth, $this->_usrHeight );
                }
            } else if ($fieldKey == 'msrp') {
                if (! isset ( $productData [$fieldKey] )) {
                    $fieldData = $productData ['price'];
                } else {
                	$fieldData = $productData['msrp'];
                }
            } else if ($fieldKey == 'category_tree') {
                $cats = $item->getCategoryIds ();
                $productCategoryTree = '';
                foreach ( $cats as $categoryId ) {
                    if ($storeId != "-1") {
                       $categoryId = "s". $storeId . "_" . $categoryId;
                    }
                    $productCategoryTree = $productCategoryTree . $categoryId . " > ";
                }
                $fieldData = substr ( $productCategoryTree, 0, - 3 );
            } else if ($fieldKey == 'subcategory') {
                $fieldData = $this->getLeafCategories($productObject);
            } else if ($fieldKey == 'isinstock') {
                $fieldData = $productData ['stock_item'] ['is_in_stock'];
            } else if ($fieldKey == 'storeId') {
                $fieldData = "s" . $storeId;
            } else if (isset ( $productData [$fieldKey] ))
                $fieldData = $this->preProcessText ( $productData [$fieldKey] );
            array_push ( $recordData, $fieldData );
        }
        // don't add products with 0 price if set in config settings
        if ($this->_displayProductWithZeroPrice || 
        		((!isset ($productData ['price']) || $productData ['price'] != 0 ) && 
        				(!isset ($productData ['special_price']) || $productData ['special_price'] != 0 ) && 
        				(!isset ($productData ['msrp']) || $productData['msrp'] != 0 ))) {
        	$this->_writerIO->streamWriteCsv ( $recordData, '|' );
        }
    }

    protected function checkProductAvaialbility($productId)
    {
        $model = Mage::getModel ( 'catalog/product' );
        $_product = $model->load ( $productId );
        $stocklevel = ( int ) Mage::getModel ( 'cataloginventory/stock_item' )->loadByProduct ( $_product )->getQty ();
        if ($stocklevel <= 0) {
            return false;
        }
        return true;
    }
    /**
     * Creates record for each past order event in a store
     *
     * @param Sales/Order $order            
     */
    protected function generateOrderRecord($order, $storeId="-1")
    {
        $orderId = $order->getId ();
        $orderedItems = $order->getAllItems ();
        $customerId = $order->getCustomerId ();
        $orderDate = $order->getCreatedAtFormated ( 'short' );
        $eventDateEpoch = '';
        if ($this->isDateValid ( $orderDate )) {
            $eventDate = new DateTime ( $orderDate );
            $eventDateEpoch = $eventDate->format ( 'U' );
        }
        if ($eventDateEpoch == '') {
            $eventDateEpoch = strtotime ( str_replace ( "/", "-", $orderDate ) );
        }
        $refTag = '';
        $deviceType = 'web';
        $sessionId = '';
        if ($customerId != '') {
            foreach ( $orderedItems as $item ) {
                $itemId = $item->getProductId ();
                if ($storeId != "-1") {
                	$itemId = $itemId . "_" . $storeId;
                }
                $recordData = array ();
                array_push ( $recordData, $customerId );
                array_push ( $recordData, $sessionId );
                array_push ( $recordData, $itemId );
                array_push ( $recordData, $eventDateEpoch );
                array_push ( $recordData, $refTag );
                array_push ( $recordData, $deviceType );
                $this->_writerIO->streamWriteCsv ( $recordData, ',' );
            }
        }
    }
    /**
     * Creates record for each past view event
     *
     * @param Report/Events $viewEvent            
     */
    protected function generateViewsRecord($viewEvent, $storeId="-1")
    {
        $recordData = array ();
        $customerId = $viewEvent ['subject_id'];
        $productId = $viewEvent ['object_id'];
        if ($storeId != "-1") {
        	$productId = $productId . "_" . $storeId;
        }
        $eventDate = new DateTime ( $viewEvent ['logged_at'] );
        $eventDateEpoch = $eventDate->format ( 'U' );
        $refTag = '';
        $deviceType = 'web';
        $sessionId = '';
        
        array_push ( $recordData, $customerId );
        array_push ( $recordData, $sessionId );
        array_push ( $recordData, $productId );
        array_push ( $recordData, $eventDateEpoch );
        array_push ( $recordData, $refTag );
        array_push ( $recordData, $deviceType );
        $this->_writerIO->streamWriteCsv ( $recordData, ',' );
    }
    
    /**
     * Creates record for each add to cart event
     *
     * @param Report/Event $addCartEvent            
     */
    protected function generateCartRecord($addCartEvent, $storeId="-1")
    {
        $recordData = array ();
        $customerId = $addCartEvent ['subject_id'];
        $productId = $addCartEvent ['object_id'];
        if ($storeId != '-1') {
        	$productId = $productId . "_" . $storeId;
        }
        $eventDate = new DateTime ( $addCartEvent ['logged_at'] );
        $eventDateEpoch = $eventDate->format ( 'U' );
        $refTag = '';
        $deviceType = 'web';
        $sessionId = '';
        
        array_push ( $recordData, $customerId );
        array_push ( $recordData, $sessionId );
        array_push ( $recordData, $productId );
        array_push ( $recordData, $eventDateEpoch );
        array_push ( $recordData, $refTag );
        array_push ( $recordData, $deviceType );
        $this->_writerIO->streamWriteCsv ( $recordData, ',' );
    }
    /**
     * Exports catalog data of store to a temp file
     * This method will be called periodically by TM for updating
     * catalog data.
     *
     *
     * @return multitype:string boolean
     */
    public function generateCatalogPSV($sendPages, $pageNum, $pageLimit, $imgWidth, $imgHeight, $imgMode, $customFields, $storeId)
    {
        @set_time_limit ( 0 );
        $this->_imgWidth = $imgWidth;
        $this->_imgHeight = $imgHeight;
        $this->_usrWidth = Mage::helper ( 'tmwidgets' )->getImgWidth();
        $this->_usrHeight = Mage::helper ( 'tmwidgets' )->getImgHeight();
        $this->_displayProductWithZeroPrice = Mage::helper ( 'tmwidgets' )->isFilterProductWithPriceZeroEnabled();
        array_push ( $this->_catalogKeys, 'category_tree' );
        array_push ( $this->_catalogKeys, 'subcategory' );
        array_push ( $this->_catalogKeys, 'isinstock' );
        array_push ( $this->_catalogKeys, 'storeId' );
        if($customFields != '0'){
            $this->getCustomAttributes ();
        }
        $this->_imgMode = $imgMode;
        if($storeId != -1){
            Mage::app()->setCurrentStore($storeId);
        }
        $this->_dataCollection = Mage::getModel ( 'catalog/product' )->getCollection ()->addAttributeToSelect ( '*' )->addAttributeToFilter ( 'status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED )->addAttributeToFilter ( 'price', array (
                'gt' => 0 
        ) )->addAttributeToFilter ( 'visibility', array (
                'in' => array (
                        Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
                        Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG 
                ) 
        ) );
        if ($storeId != '-1') {
            $this->_dataCollection->addStoreFilter($storeId);
        }
        $this->_writerIO = new Varien_Io_File ();
        $this->createFile ();
        if ($sendPages) {
            $this->writeLog ( "Catalog page " . $pageNum . " request of limit " . $pageLimit );
            $this->_dataCollection->setPageSize ( $pageLimit );
            $pages = $this->_dataCollection->getLastPageNumber ();
            /*if ($pageNum == 1) {
                $this->_writerIO->streamWriteCsv ( $this->_catalogKeys, '|' );
            }*/
            if ($pageNum <= $pages) {
                $this->_dataCollection->setCurPage ( $pageNum );
                $this->_dataCollection->load ();
                $this->writeLog ( "Writing page request #" . $pageNum );
                foreach ( $this->_dataCollection as $item ) {
                    $this->generateCatalogRecord ( $item , $storeId);
                }
            }
            $this->_dataCollection->clear ();
        } else {
            $this->_writerIO->streamWriteCsv ( $this->_catalogKeys, '|' );
            $this->walkFile ( array (
                    $this,
                    'generateCatalogRecord'
            ), $storeId );
        }
        return array (
                'type' => 'filename',
                'value' => $this->_fileName,
                'rm' => true 
        );
    }
    /**
     * Exports all past product views data in a file.
     * This
     * method will be invoked only once at the time of signup
     *
     * @return multitype:string boolean
     */
    public function generateViewsCSV($numDays, $storeId)
    {
        @set_time_limit ( 0 );
        $viewEventId = 1;
        foreach ( Mage::getModel ( 'reports/event_type' )->getCollection () as $eventType ) {
            if ($eventType->getEventName () == 'catalog_product_view') {
                $viewEventId = ( int ) $eventType->getId ();
                break;
            }
        }
        $resource = Mage::getSingleton ( 'core/resource' );
        $readConnection = $resource->getConnection ( 'core_read' );
        $select = 'SELECT * FROM ' . $resource->getTableName ( 'reports/event' ) . ' WHERE event_type_id = ' . $viewEventId . ' AND logged_at BETWEEN CURDATE() - INTERVAL ' . $numDays . ' DAY AND CURDATE()';
        $query = $readConnection->query ( $select );
        
        $this->_writerIO = new Varien_Io_File ();
        $this->createFile ();
        $viewEventCount = 0;
        while ( $row = $query->fetch () ) {
            $viewEventCount += 1;
            if (($viewEventCount % self::EVENTS_BATCH_SIZE) == 0) {
                $this->writeLog ( 'Writing views batch #' . ($viewEventCount / self::EVENTS_BATCH_SIZE) );
            }
            $this->generateViewsRecord ( $row , $storeId );
        }
        return array (
                'type' => 'filename',
                'value' => $this->_fileName,
                'rm' => true 
        );
    }
    /**
     * Exports all past orders data in a file.
     * This method will be invoked only once at the time of signup
     *
     * @return multitype:string boolean
     */
    public function generateOrdersCSV($sendPages, $pageNum, $pageLimit, $numDays, $storeId)
    {
        @set_time_limit ( 0 );
        $time = time ();
        $to = date ( 'Y-m-d H:i:s', $time );
        $lastTime = $time - (86400 * $numDays); // 60*60*24
        $from = date ( 'Y-m-d H:i:s', $lastTime );
        $this->_dataCollection = Mage::getModel ( 'Sales/Order' )->getCollection ()->addAttributeToSelect ( '*' )->addAttributeToFilter ( 'created_at', array (
                'from' => $from,
                'to' => $to 
        ) );
        // $this->_dataCollection = Mage::getModel ( 'Sales/Order' )->getCollection ()->addAttributeToSelect ( '*' );
        $this->_writerIO = new Varien_Io_File ();
        $this->createFile ();
        if ($sendPages) {
            $this->writeLog ( "Orders page " . $pageNum . " request of limit " . $pageLimit );
            $this->_dataCollection->setPageSize ( $pageLimit );
            $pages = $this->_dataCollection->getLastPageNumber ();
            if ($pageNum <= $pages) {
                $this->_dataCollection->setCurPage ( $pageNum );
                $this->_dataCollection->load ();
                $this->writeLog ( "Writing Orders page request #" . $pageNum );
                foreach ( $this->_dataCollection as $item ) {
                    $this->generateOrderRecord ( $item, $storeId );
                }
            }
            $this->_dataCollection->clear ();
        } else {
            $this->walkFile ( array (
                    $this,
                    'generateOrderRecord'
            ), $storeId );
        }
        return array (
                'type' => 'filename',
                'value' => $this->_fileName,
                'rm' => true 
        );
    }

    protected function generateUserRecord($user)
    {
        $recordData = array ();
        $id = $user->getId();
        $email = $user->getEmail();
        array_push ( $recordData, $id );
        array_push ( $recordData, $email );
        array_push ( $recordData, $user->getFirstname() );
        array_push ( $recordData, $user->getLastname());
        $this->_writerIO->streamWriteCsv ( $recordData, ',' );
    }

    public function generateUsersCSV($sendPages, $pageNum, $pageLimit)
    {
        @set_time_limit ( 0 );
        $this->_dataCollection = Mage::getModel('customer/customer')->getCollection()
           ->addAttributeToSelect('*');
        // $this->_dataCollection = Mage::getModel ( 'Sales/Order' )->getCollection ()->addAttributeToSelect ( '*' );
        $this->_writerIO = new Varien_Io_File ();
        $this->createFile ();
        if ($sendPages) {
            $this->writeLog ( "Users page " . $pageNum . " request of limit " . $pageLimit );
            $this->_dataCollection->setPageSize ( $pageLimit );
            $pages = $this->_dataCollection->getLastPageNumber ();
            if ($pageNum <= $pages) {
                $this->_dataCollection->setCurPage ( $pageNum );
                $this->_dataCollection->load ();
                $this->writeLog ( "Writing Users page request #" . $pageNum );
                foreach ( $this->_dataCollection as $user ) {
                    $this->generateUserRecord ( $user );
                }
            }
            $this->_dataCollection->clear ();
        } else {
            $this->walkFile ( array (
                    $this,
                    'generateUserRecord' 
            ) );
        }
        return array (
                'type' => 'filename',
                'value' => $this->_fileName,
                'rm' => true 
        );
    }

    /**
     * Exports all past add to cart events data in a file.
     * This method will be invoked intiatially after signup
     *
     * @return multitype:string boolean
     */
    public function generateCartCSV($numDays, $storeId)
    {
        @set_time_limit ( 0 );
        $cartEventId = 1;
        foreach ( Mage::getModel ( 'reports/event_type' )->getCollection () as $eventType ) {
            if ($eventType->getEventName () == 'checkout_cart_add_product') {
                $cartEventId = ( int ) $eventType->getId ();
                break;
            }
        }
        $resource = Mage::getSingleton ( 'core/resource' );
        $readConnection = $resource->getConnection ( 'core_read' );
        $select = 'SELECT * FROM ' . $resource->getTableName ( 'reports/event' ) . ' WHERE event_type_id = ' . $cartEventId . ' AND logged_at BETWEEN CURDATE() - INTERVAL ' . $numDays . ' DAY AND CURDATE()';
        $query = $readConnection->query ( $select );
        
        $this->_writerIO = new Varien_Io_File ();
        $this->createFile ();
        $viewEventCount = 0;
        while ( $row = $query->fetch () ) {
            $viewEventCount += 1;
            if (($viewEventCount % self::EVENTS_BATCH_SIZE) == 0) {
                $this->writeLog ( 'Writing add to cart events batch #' . ($viewEventCount / self::EVENTS_BATCH_SIZE) );
            }
            $this->generateCartRecord ( $row, $storeId );
        }
        return array (
                'type' => 'filename',
                'value' => $this->_fileName,
                'rm' => true 
        );
    }

    /**
     *
     * @return multitype:string boolean NULL
     */
    public function generateCategories()
    {
        $this->_writerIO = new Varien_Io_File ();
        $this->createFile ();
        $categories = Mage::getModel ( 'catalog/category' )->getCollection ()->addAttributeToSelect ( '*' )->addAttributeToFilter ( 'level', array (
                'gt' => 0 
        ) )->addAttributeToFilter ( 'is_active', 1 );
        
        foreach ( $categories as $cat ) {
            $parentCategories = $cat->getParentCategories ();
            $categoryName = $cat->getName ();
            $categoryId = $cat->getId ();
            $categoryLevel = $cat->getLevel();
            $isChildren = true;
            if($cat->hasChildren()){
                $isChildren = false;
            }
            $nodeLabel = 'intermediate';
            
            $numParents = count($parentCategories);
            foreach ( $parentCategories as $pc ) {
                $parentId = $pc->getId ();
                if($isChildren)
                    $nodeLabel = 'leaf';
                if($numParents == 1)
                    $nodeLabel = 'root';
                
                if ($parentId != $categoryId || $numParents == 1) {
                    $categoryRecord = array ();
                    array_push ( $categoryRecord, $categoryId );
                    array_push ( $categoryRecord, $categoryName );
                    array_push ( $categoryRecord, $parentId );
                    array_push ( $categoryRecord, $categoryLevel );
                    array_push ( $categoryRecord, $nodeLabel );
                    $this->_writerIO->streamWriteCsv ( $categoryRecord, ',' );
                }
            }
        }
        return array (
                'type' => 'filename',
                'value' => $this->_fileName,
                'rm' => true 
        );
    }
}