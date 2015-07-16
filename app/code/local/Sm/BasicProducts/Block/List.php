<?php
/*------------------------------------------------------------------------
 # SM BasicProducts - Version 1.0
 # Copyright (c) 2009-2011 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_BasicProducts_Block_List extends Mage_Core_Block_Template
{
	protected $_config = null;
	protected $_storeId = null;
	protected $_productCollection = null;
	
	public function __construct($attributes = array()){
		parent::__construct();
		$this->_config = Mage::helper('basicproducts/data')->get($attributes);
	}

	public function getConfig($name=null, $value=null){
		if (is_null($this->_config)){
			$this->_config = Mage::helper('basicproducts/data')->get(null);
		}
		if (!is_null($name) && !empty($name)){
			$valueRet = isset($this->_config[$name]) ? $this->_config[$name] : $value;
			return $valueRet;
		}
		return $this->_config;
	}
	
	public function setConfig($name, $value=null){
		if (is_null($this->_config)) $this->getConfig();
		if (is_array($name)){
			$this->_config = array_merge($this->_config, $name);
			return;
		}
		if (!empty($name)){
			$this->_config[$name] = $value;
		}
		return true;
	}
	
	protected function _toHtml(){
		$template = $this->getConfig('theme', 'theme1');
		$template_file = "sm/basicproducts/block_template_default.phtml";
		$this->setTemplate($template_file);
		return parent::_toHtml();
	}
	
	public function getStoreId(){
		if (is_null($this->_storeId)){
			$this->_storeId = Mage::app()->getStore()->getId();
		}
		return $this->_storeId;
	}
	public function setStoreId($storeId=null){
		$this->_storeId = $storeId;
	}
	
	protected function getProductCollection(){
		if (is_null($this->_productCollection)){
			if (!Mage::registry("sm_product_collection")){
				$collection = Mage::getSingleton('catalog/product')->getCollection();
				$collection->addAttributeToSelect('*');
				$collection->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
				$visibility = array(
					Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
					Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
				);
				$collection->addAttributeToFilter('visibility', $visibility);
				
				// add price data
				$collection->addPriceData();
				
				// add category_ids
				//$collection->addCategoryIds();
				
				$this->_addViewsCount($collection);
				$this->_addReviewsCount($collection);
				$this->_addOrderedCount($collection);
				
				Mage::register("sm_product_collection", $collection);
			}
			$this->_productCollection = Mage::registry("sm_product_collection");
		}
		return $this->_productCollection;
	}
	public function setProductCollection($collection=null){
		$this->_productCollection = $collection;
	}
	
	private function _getProducts(){
		$collection = $this->getProductCollection();
		if ($this->_config['product_source']=='product'){
			if (is_null($this->_config['product_ids']) || empty($this->_config['product_ids'])){
				return false;
			} else {
				$product_ids = preg_split("/[,\s\D]+/", $this->_config['product_ids']);
				$collection->addIdFilter($product_ids);
			}
		} else if ($this->_config['product_source']=='catalog') {
			if (Mage::registry('current_category')){
				// is category view page.
				$current_category = Mage::registry('current_category');
				$current_category_id = $current_category->getId();
				$product_ids = $current_category->getProductCollection()->getAllIds();
				$collection->addIdFilter($product_ids);
				$category_ids = array();
			} else {
				// if Mage::registry('product') - is product page or another page.
				$category_ids = preg_split("/[,\s\D]+/", $this->_config['product_category']);
				if (is_array($category_ids)){
					foreach ($category_ids as $i => $id) {
						if (!is_numeric($id)){
							unset($category_ids[$i]);
						}
					}
				}
			}
			if (isset($category_ids) && count($category_ids)>0) $this->_addCategoryFilter($collection, $category_ids);
								
			// Sort products in collection
			$dir = strtolower( $this->_config['product_order_dir'] );
			if (!in_array($dir, array('asc', 'desc'))){
				$dir = 'asc';
			}
			$attribute_to_sort = $this->_config['product_order_by'];
			switch ($attribute_to_sort){
				case 'name':
				case 'created_at':				
				case 'price':
					$collection->addAttributeToSort($attribute_to_sort, $dir);
					break;
				case 'position':
					break;
				case 'random':
					$collection->getSelect()->order(new Zend_Db_Expr('RAND()'));
					break;
				case 'top_rating':
					$collection->getSelect()->order('sm_rating_summary desc');
					break;
				case 'most_reviewed':
					$collection->getSelect()->order('sm_reviews_count desc');
					break;
				case 'most_viewed':
					$collection->getSelect()->order('sm_views_count desc');
					break;
				case 'best_sales':
					$collection->getSelect()->order('sm_ordered_count desc');
					break;
			}
		}
		$product_limitation = intval($this->_config['product_limitation']);
		if ($product_limitation>0){
			$collection->setPageSize($product_limitation);
		}
		return  $collection;
	}
	
	public function getProducts(){
		return $this->_getProducts();
	}
	
	public function getConfigObject(){
		return (object)$this->getConfig();
	}
	
	public function getScriptTags(){
		$import_str = "";
		$jsHelper = Mage::helper('core/js');
		if (null == Mage::registry('jsmart.jquery')){
			// jquery has not added yet
			if (Mage::getStoreConfigFlag('basicproducts_cfg/advanced/include_jquery')){
				// if module allowed jquery.
				$import_str .= $jsHelper->includeSkinScript('sm/basicproducts/js/jquery-1.5.min.js');
				Mage::register('jsmart.jquery', 1);
			}
		}
		if (null == Mage::registry('jsmart.jquerynoconfict')){
			// add once noConflict
			$import_str .= $jsHelper->includeSkinScript('sm/basicproducts/js/jsmart.noconflict.js');
			Mage::register('jsmart.jquerynoconfict', 1);
		}
		
		if (null == Mage::registry('jsmart.basicproducts.js')){
			// add script for this module.
			// $import_str .= $jsHelper->includeSkinScript('sm/basicproducts/js/jsfilename.js');
			
			Mage::register('jsmart.basicproducts.js', 1);
		}
		return $import_str;
	}
	
	private function _addCategoryFilter(& $collection, $category_ids){
		$category_collection = Mage::getModel('catalog/category')->getCollection();
		$category_collection->addAttributeToSelect('*');
		$category_collection->addIsActiveFilter();
		if (count($category_ids)>0){
			$category_collection->addIdFilter($category_ids);
		}
		
		if (!Mage::helper('catalog/category_flat')->isEnabled()) { 
			$category_collection->groupByAttribute('entity_id');
		}
		$category_products = array();
		foreach ($category_collection as $category){
			$cid = $category->getId();
			if (!array_key_exists( $cid, $category_products)){
				$category_products[$cid] = $category->getProductCollection()->getAllIds();
				//Mage::log("ID: " . $cid );
				//Mage::log("collection->count(): " . count($category_products[$cid]) );
			}			
		}
		$product_ids = array();
		if (count($category_products)){
			foreach ($category_products as $cp) {
				$product_ids = array_merge($product_ids, $cp);
			}
		}
		//Mage::log("merged_count: " . count($product_ids));
		$collection->addIdFilter($product_ids);
	}
	private function _addViewsCount(& $collection, $views_count_alias="sm_views_count"){
		// add views_count
		$reports_event_table		= Mage::getSingleton('core/resource')->getTableName('reports/event');
		$reports_event_types_table 	= Mage::getSingleton('core/resource')->getTableName('reports/event_type');
		$collection->getSelect()
		->joinLeft(
			array("re_table" => $reports_event_table),
			"e.entity_id = re_table.object_id",
			array(
				$views_count_alias => "COUNT(re_table.event_id)"
			)
		)->joinLeft(
			array("ret_table" => $reports_event_types_table),
			"re_table.event_type_id = ret_table.event_type_id AND ret_table.event_name = 'catalog_product_view'",
			array()
		)->group('e.entity_id');
	}
	private function _addReviewsCount(& $collection, $reviews_count_alias="sm_reviews_count", $rating_summary_alias="sm_rating_summary" ){
		// add reviews_count and rating_summary
		$review_summary_table = Mage::getSingleton('core/resource')->getTableName('review/review_aggregate');
		$collection->getSelect()->joinLeft(
			array("rs_table" => $review_summary_table),
			"e.entity_id = rs_table.entity_pk_value AND rs_table.store_id=" . $this->getStoreId(),
			array(
				$reviews_count_alias  => "rs_table.reviews_count",
				$rating_summary_alias => "rs_table.rating_summary"
			)
		);
	}
	private function _addOrderedCount(& $collection, $ordered_qty_alias="sm_ordered_count"){
		$order_table = Mage::getSingleton('core/resource')->getTableName('sales/order');
		$read = Mage::getSingleton('core/resource')->getConnection ('core_read');
		$orders_active_query = $read->select()->from(array("o_table"=>$order_table), 'o_table.entity_id')->where("o_table.state<>'" . Mage_Sales_Model_Order::STATE_CANCELED . "'");
		
		$order_item_table = Mage::getSingleton('core/resource')->getTableName('sales/order_item');		
		$collection->getSelect()->joinLeft(
			array("oi_table" => $order_item_table),
			"e.entity_id=oi_table.item_id AND oi_table.order_id IN ($orders_active_query)",
			array(
				$ordered_qty_alias => "SUM(oi_table.qty_ordered)"
			)
		);
	}
}
