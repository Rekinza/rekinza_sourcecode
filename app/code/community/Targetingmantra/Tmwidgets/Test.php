<?php
require_once '../../../../Mage.php';

Varien_Profiler::enable ();
Mage::setIsDeveloperMode ( true );
ini_set ( 'display_errors', 1 );

umask ( 0 );
Mage::app ();

function rfunc($category){
  if($category->hasChildren()){
    $childs = $category->getChildren();
    $ret = "";
    foreach(explode(',',$childs) as $c){
       $c = Mage::getModel('catalog/category')->load($c);
       $ret = $ret . "," . rfunc($c);
    }
    return $ret;
  }
  return $category->getId();
}

$categoryId = 21;
$_category = Mage::getModel('catalog/category')->load($categoryId);
$result = trim(rfunc($_category),',');
echo $result;
