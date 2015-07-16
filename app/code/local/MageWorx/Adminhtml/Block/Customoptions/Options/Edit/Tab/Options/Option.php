<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 *
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2012 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Advanced Product Options extension
 *
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @author     MageWorx Dev Team
 */

class MageWorx_Adminhtml_Block_Customoptions_Options_Edit_Tab_Options_Option extends MageWorx_Adminhtml_Block_Customoptions_Adminhtml_Catalog_Product_Edit_Tab_Options_Option {

    public function __construct() {
        parent::__construct();
    }

    protected function getStoreId() {
        if (Mage::app()->getStore()->isAdmin()) {
            return Mage::registry('store_id');
        } else {
            return Mage::app()->getStore()->getId();
        }
    }

    public function getOptionValues() {                        
        $data = array();                
        
        $optionsArr = '';
        $session = Mage::getSingleton('adminhtml/session');
        if ($data = $session->getData('customoptions_data')) {
            if (isset($data['general']['hash_options'])) {
                $optionsArr = $data['general']['hash_options'];
            }
        } elseif (Mage::registry('customoptions_data')) {
            $data = Mage::registry('customoptions_data')->getData();
            if (isset($data['hash_options'])) {
                $optionsArr = $data['hash_options'];
            }
        }
        
        
        $helper = Mage::helper('customoptions');

        $groupId = (int) $this->getRequest()->getParam('group_id');
        if ($optionsArr) $optionsArr = unserialize($optionsArr);
        
        $storeOptionsArr = array();
        $groupStore = Mage::getSingleton('customoptions/group_store')->loadByGroupAndStore($groupId, $this->getStoreId());        
        if ($groupStore->getHashOptions()) $storeOptionsArr = unserialize($groupStore->getHashOptions()); 
        //print_r($storeOptionsArr); exit;
        
        $product = Mage::getSingleton('catalog/product_option');                        
        
        if (!$this->_values && $optionsArr) {
            $values = array();
            $sortOrder = array();
            $scope = (int) Mage::app()->getStore()->getConfig(Mage_Core_Model_Store::XML_PATH_PRICE_SCOPE);
            $optionItemCount = count($optionsArr);
            foreach ($optionsArr as $optionId=>$option) {
                $option = new Varien_Object($option);
                $value = array();                
                if ($option->getIsDelete() != '1') {
                    $value['id'] = $option->getOptionId();
                    $value['item_count'] = $optionItemCount;
                    $value['option_id'] = $option->getOptionId();
                    $value['title'] = $this->htmlEscape(isset($storeOptionsArr[$optionId]['title'])?$storeOptionsArr[$optionId]['title']:$option->getTitle());
                    $value['type'] = $option->getType();
                    $value['is_require'] = $option->getIsRequire(true);
                    $value['is_enabled'] = $option->getIsEnabled();
                    $value['is_dependent'] = $option->getIsDependent();
                    $value['div_class'] = $option->getDivClass();
                    
                    $value['customoptions_is_onetime'] = $option->getCustomoptionsIsOnetime();
                    $value['qnty_input'] = ($option->getQntyInput()?'checked':'');
                    $value['qnty_input_disabled'] = (($option->getType()=='drop_down' || $option->getType()=='radio' || $option->getType()=='checkbox')?'':'disabled');
                    
                    
                    $value['description'] = $this->htmlEscape(isset($storeOptionsArr[$optionId]['description'])?$storeOptionsArr[$optionId]['description']:$option->getDescription());
                    if ($helper->isCustomerGroupsEnabled() && $option->getCustomerGroups() != null) {
                        $value['customer_groups'] = implode(',', $option->getCustomerGroups());
                    }
                    
                    $value['in_group_id'] = $option->getInGroupId();
                    $value['in_group_id_view'] = $option->getInGroupId();
                    
                    $value['sort_order'] = $this->_getSortOrder($option);                    

                    if ($this->getStoreId() != '0') {
                        $value['checkboxScopeTitle'] = $this->getCheckboxScopeHtml($option->getOptionId(), 'title', !isset($storeOptionsArr[$optionId]['title']));
                        $value['scopeTitleDisabled'] = !isset($storeOptionsArr[$optionId]['title']) ? 'disabled' : null;
                        $value['checkboxScopeDescription'] = $this->getCheckboxScopeHtml($option->getOptionId(), 'description', !isset($storeOptionsArr[$optionId]['description']));
                        $value['scopeDescriptionDisabled'] = !isset($storeOptionsArr[$optionId]['description']) ? 'disabled' : null;
                    }                                        

                    if ($product->getGroupByType($option->getType()) == Mage_Catalog_Model_Product_Option::OPTION_GROUP_SELECT) {                        
                        $countValues = count($option->getValues());
                        if ($countValues>0) {
                            foreach ($option->getValues() as $key => $_value) {
                                $_value = new Varien_Object($_value);
                                $_value->setOptionTypeId($key);

                                if ($_value->getIsDelete() != '1') {

                                    $defaultArray = $option->getDefault() !== null ? $option->getDefault() : array();                                    
                                    list($price, $priceType) = $helper->getOptionPriceAndPriceType(floatval(isset($storeOptionsArr[$optionId]['values'][$_value->getOptionTypeId()]['price'])?$storeOptionsArr[$optionId]['values'][$_value->getOptionTypeId()]['price']:$_value->getPrice()), isset($storeOptionsArr[$optionId]['values'][$_value->getOptionTypeId()]['price_type'])?$storeOptionsArr[$optionId]['values'][$_value->getOptionTypeId()]['price_type']:$_value->getPriceType(), $_value->getSku(), Mage::app()->getStore($this->getStoreId()));
                                    $priceDisabled = ($helper->isSkuPriceLinkingEnabled() && $helper->getProductIdBySku($_value->getSku())) ? 1 : 0;
                                	
                                    $value['optionValues'][$key] = array(
                                        'item_count' => $countValues,
                                        'option_id' => $option->getOptionId(),
                                        'option_type_id' => $_value->getOptionTypeId(),
                                        'title' => $this->htmlEscape(isset($storeOptionsArr[$optionId]['values'][$_value->getOptionTypeId()]['title'])?$storeOptionsArr[$optionId]['values'][$_value->getOptionTypeId()]['title']:$_value->getTitle()),
                                        'price' => $this->getPriceValue($price, $priceType),
                            			'price_type' => $priceType,
                            			'price_disabled' => $priceDisabled,
                            			'customoptions_qty' => $helper->getCustomoptionsQty($_value->getCustomoptionsQty(), $_value->getSku()),
                                        'customoptions_qty_disabled' => $helper->getProductIdBySku($_value->getSku())?'disabled="disabled"':'',
                                        'sku' => $this->htmlEscape($_value->getSku()),
                                        'sku_class' => $_value->getSku() && $helper->getProductIdBySku($_value->getSku())==0?'red':'',
                                        'sort_order' => $this->_getSortOrder($_value),                                        
                                        'checked' => array_search($_value->getOptionTypeId(), $defaultArray) !== false ? 'checked' : '',
                                        'default_type' => (($option->getType()=='checkbox' || $option->getType()=='multiple') ? 'checkbox' : 'radio'),                                    
                                        'in_group_id' => $_value->getInGroupId(),
                                        'in_group_id_view' => $_value->getInGroupId(),
                                        'dependent_ids' => $_value->getDependentIds(),
                                        'weight' => number_format(floatval($_value->getWeight()), 4, null, '')
                                    );                                

                                    $value['optionValues'][$key]['image_button_label'] = $helper->__('Add Image');


                                    $imgHtml = $helper->getImgHtml($_value->getImagePath(), $option->getId(), $_value->getOptionTypeId());
                                    if ($imgHtml) {
                                        $value['optionValues'][$key]['image'] = $imgHtml;
                                        $value['optionValues'][$key]['image_button_label'] = $helper->__('Change Image');
                                    }

                                    if ($this->getStoreId()!='0') {
                                        $value['optionValues'][$key]['checkboxScopeTitle'] = $this->getCheckboxScopeHtml($option->getOptionId(), 'title', !isset($storeOptionsArr[$optionId]['values'][$_value->getOptionTypeId()]['title']), $_value->getOptionTypeId());
                                        $value['optionValues'][$key]['scopeTitleDisabled'] = !isset($storeOptionsArr[$optionId]['values'][$_value->getOptionTypeId()]['title']) ? 'disabled' : null;

                                        if ($scope == Mage_Core_Model_Store::PRICE_SCOPE_WEBSITE) {
                                            if (!$priceDisabled) $value['optionValues'][$key]['checkboxScopePrice'] = $this->getCheckboxScopeHtml($option->getOptionId(), 'price', !isset($storeOptionsArr[$optionId]['values'][$_value->getOptionTypeId()]['price']), $_value->getOptionTypeId());
                                            $value['optionValues'][$key]['scopePriceDisabled'] = !isset($storeOptionsArr[$optionId]['values'][$_value->getOptionTypeId()]['price']) ? 'disabled' : null;
                                        }
                                    }
                                }
                            }
                            $value['optionValues'] = array_values($value['optionValues']);
                        }                        
                        
                    } else {
                    	list($price, $priceType) = $helper->getOptionPriceAndPriceType(floatval(isset($storeOptionsArr[$optionId]['price'])?$storeOptionsArr[$optionId]['price']:$option->getPrice()), isset($storeOptionsArr[$optionId]['price_type'])?$storeOptionsArr[$optionId]['price_type']:$option->getPriceType(), $option->getSku(), Mage::app()->getStore($this->getStoreId()));
                        $priceDisabled = ($helper->isSkuPriceLinkingEnabled() && $helper->getProductIdBySku($option->getSku())) ? 1 : 0;
                        
                        $value['price'] = $this->getPriceValue($price, $priceType);
                        $value['price_type'] = $priceType;
                        $value['price_disabled'] = $priceDisabled;
                        $value['sku'] = $this->htmlEscape($option->getSku());
                        $value['max_characters'] = $option->getMaxCharacters();
                        $value['default_text'] = $this->htmlEscape(isset($storeOptionsArr[$optionId]['default_text'])?$storeOptionsArr[$optionId]['default_text']:$option->getDefaultText());
                        $value['file_extension'] = $option->getFileExtension();
                        $value['image_size_x'] = $option->getImageSizeX();
                        $value['image_size_y'] = $option->getImageSizeY();
                        $value['image_button_label'] = $helper->__('Add Image');
                        
                        $imgHtml = $helper->getImgHtml($option->getImagePath(), $option->getId());
                        if ($imgHtml) {
                            $value['image'] = $imgHtml;
                            $value['image_button_label'] = $helper->__('Change Image');
                        }

                        if ($this->getStoreId() != '0' && $scope == Mage_Core_Model_Store::PRICE_SCOPE_WEBSITE) {
                            if (!$priceDisabled) $value['checkboxScopePrice'] = $this->getCheckboxScopeHtml($option->getOptionId(), 'price', !isset($storeOptionsArr[$optionId]['price']));
                            $value['scopePriceDisabled'] = !isset($storeOptionsArr[$optionId]['price']) ? 'disabled' : null;
                            
                            $value['checkboxScopeDefaultText'] = $this->getCheckboxScopeHtml($option->getOptionId(), 'default_text', !isset($storeOptionsArr[$optionId]['default_text']));
                            $value['scopeDefaultTextDisabled'] = !isset($storeOptionsArr[$optionId]['default_text']) ? 'disabled' : null;
                        }
                    }
                    $values[] = new Varien_Object($value);
                }
            }            
            $this->_values = $values;
        }
        return $this->_values ? $this->_values : array();
    }

    private function _getSortOrder(Varien_Object $obj) {
        $sortOrder = $obj->getSortOrder();
        return empty($sortOrder) ? 0 : $sortOrder;
    }
}