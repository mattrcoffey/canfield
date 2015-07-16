<?php
/*------------------------------------------------------------------------
 # SM Dynamic Slideshow - Version 1.0.0
 # Copyright (c) 2013 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_DynamicSlideshow_Block_List extends Mage_Core_Block_Template
{
	protected $_config = null;
	protected $_storeId = null;
	protected $_productCollection = null;

	public function __construct($attributes = array()){
		parent::__construct();
		$this->_config = Mage::helper('dynamicslideshow/data')->get($attributes);
	}

	public function getConfig($name=null, $value=null){
		if (is_null($this->_config)){
			$this->_config = Mage::helper('dynamicslideshow/data')->get(null);
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
		if(!$this->_config['isenabled']) return;
		$template_file = "sm/dynamicslideshow/default.phtml";
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
			if (Mage::getStoreConfigFlag('dynamicslideshow_cfg/advanced/include_jquery')){
				// if module allowed jquery.
				$import_str .= $jsHelper->includeSkinScript('sm/dynamicslideshow/js/jquery-1.8.2.min.js');
				Mage::register('jsmart.jquery', 1);
			}
		}
		if (null == Mage::registry('jquery-noconflict')){
			// add once noConflict
			$import_str .= $jsHelper->includeSkinScript('sm/dynamicslideshow/js/jquery-noconflict.js');
			Mage::register('jquery-noconflict', 1);
		}

		if (null == Mage::registry('jsmart.dynamicslideshow.js')){
			// add script for this module.
			 $import_str .= $jsHelper->includeSkinScript('sm/dynamicslideshow/js/jquery.themepunch.plugins.min.js');
			 $import_str .= $jsHelper->includeSkinScript('sm/dynamicslideshow/js/jquery.themepunch.revolution.js');
			Mage::register('jsmart.dynamicslideshow.js', 1);
		}
		return $import_str;
	}
}
