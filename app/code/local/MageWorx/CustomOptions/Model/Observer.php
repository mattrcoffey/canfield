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

class MageWorx_CustomOptions_Model_Observer {

    // ckeckout/cart
    public function checkQuoteItemQtyAndCustomerGroup($observer) {
        if (!Mage::helper('customoptions')->isEnabled()) return $this;
        $quoteItem = $observer->getEvent()->getItem();
        /* @var $quoteItem Mage_Sales_Model_Quote_Item */
        if (!$quoteItem || !$quoteItem->getProductId() || !$quoteItem->getQuote() || $quoteItem->getQuote()->getIsSuperMode()) {
            return $this;
        }

        $helper = Mage::helper('customoptions');      
        if (!$helper->isInventoryEnabled() && !$helper->isCustomerGroupsEnabled()) return $this;
        
        // product Qty
        $qty = 0;        
        // if update cart -> cart[182][qty]
        $quoteItemId = $quoteItem->getId();        
        if ($quoteItemId>0) {            
            $cartPost = Mage::app()->getRequest()->getParam('cart', false);
            if ($cartPost && isset($cartPost[$quoteItemId]['qty'])) $qty = $cartPost[$quoteItemId]['qty'];
        }                
        
        // standart add to cart
        if (!$qty) $qty = $quoteItem->getQty();
        
        if (!$qty) $qty = Mage::app()->getRequest()->getParam('qty', false);
        
        // get correctly options
        $options = false;        
        $post = Mage::app()->getRequest()->getParams();        
        
        if (isset($post['id'])) {
            // if update quote item 
            if ($post['id']==$quoteItemId) {
                // if quote item edited:
                if (isset($post['options'])) $options = $post['options'];
                $qty = Mage::app()->getRequest()->getParam('qty', false);                
            } else {
                return $this;
            }
        } else {        
            $post = $quoteItem->getProduct()->getCustomOption('info_buyRequest')->getValue();
            if ($post) $post = unserialize ($post); else $post = array();
            if (isset($post['options'])) $options = $post['options'];
        }        
        
        if ($options) {
            if (Mage::app()->getStore()->isAdmin()) {
                $sessionQuote = Mage::getSingleton('adminhtml/session_quote');
                if ($sessionQuote) $customerGroupId = $sessionQuote->getCustomer()->getGroupId(); else $customerGroupId = 0;        
            } else {
                $customerGroupId = Mage::getSingleton('customer/session')->isLoggedIn() ? Mage::getSingleton('customer/session')->getCustomer()->getGroupId() : 0;            
            }
            
            foreach ($options as $optionId => $option) {                     
                $productOption = Mage::getModel('catalog/product_option')->load($optionId);

                // check Options Customer Group
                if ($helper->isCustomerGroupsEnabled()) {
                    $groups = $productOption->getCustomerGroups();
                    if ($groups!=='' && !in_array($customerGroupId, explode(',', $groups))) {
                        $fullMessage = $helper->__('Some options are not available for your customer group. Please, edit product "%s"', $quoteItem->getProduct()->getName());
                        $message = $helper->__('Some options are not available for your customer group');
                        
                        $quoteItem->setHasError(true)->setMessage($message);
                        if ($quoteItem->getParentItem()) {
                            $quoteItem->getParentItem()->setMessage($message);
                        }
                        $quoteItem->getQuote()->setHasError(true)->addMessage($fullMessage, 'options');
                        return $this;
                        break;
                    }
                }
                
                // check Options Inventory
                if ($helper->isInventoryEnabled()) {
                    $optionType = $productOption->getType();                    
                    if ($optionType!='drop_down' && $optionType!='multiple' && $optionType!='radio' && $optionType!='checkbox') continue;                                        
                    if (!is_array($option)) $option = array($option);
                    foreach ($option as $optionTypeId) {
                        if (!$optionTypeId) continue;
                        $row = $productOption->getOptionValue($optionTypeId);                        
                        $customoptionsQty = $helper->getCustomoptionsQty(isset($row['customoptions_qty'])?$row['customoptions_qty']:'', isset($row['sku'])?$row['sku']:'', $optionId, $optionTypeId, $quoteItem->getId());
                        if (substr($customoptionsQty, 0, 1)!='x' && $customoptionsQty!=='') {
                            switch ($optionType) {
                                case 'checkbox':                            
                                    if (isset($post['options_'.$optionId.'_'.$optionTypeId.'_qty'])) $optionQty = intval($post['options_'.$optionId.'_'.$optionTypeId.'_qty']); else $optionQty = 1;
                                    break;
                                case 'drop_down':
                                case 'radio':                                                    
                                    if (isset($post['options_'.$optionId.'_qty'])) $optionQty = intval($post['options_'.$optionId.'_qty']); else $optionQty = 1;
                                    break;
                                case 'multiple':
                                    $optionQty = 1;                            
                                    break;                       
                            }                            
                            $optionTotalQty = ($productOption->getCustomoptionsIsOnetime()?$optionQty:$optionQty*$qty);
                            
                            // is null if add new product -> correction inventory
                            if (is_null($quoteItem->getId())) $customoptionsQty += $optionTotalQty;
                            if (intval($customoptionsQty)<$optionTotalQty) {
                                $message = Mage::helper('cataloginventory')->__('The requested quantity for "%s" is not available.', $quoteItem->getProduct()->getName());

                                $quoteItem->setHasError(true)->setMessage($message);
                                if ($quoteItem->getParentItem()) {
                                    $quoteItem->getParentItem()->setMessage($message);
                                }
                                $quoteItem->getQuote()->setHasError(true)->addMessage($message, 'qty');
                                return $this;
                                break; break;
                            }                            
                        }
                    }
                }    
            }
        }        
        return $this;
    }
    
    
    // before create order -> setCustomOptionsDetails
    public function convertQuoteItemToOrderItem($observer) {
    	if (!Mage::helper('customoptions')->isEnabled()) return $this;
        $orderItem = $observer->getEvent()->getOrderItem();                
        $item = $observer->getEvent()->getItem();
        $product = $item->getProduct();
        // if bad magento))
        if (is_null($product->getHasOptions())) $product->load($product->getId());
        if (!$product->getHasOptions()) return $this;
        
        // multiplier - to order: 3 x Red
        Mage::helper('customoptions/product_configuration')->setCustomOptionsDetails($item);
        $quoteOptions = $product->getTypeInstance(true)->getOrderOptions($product);
        $orderOptions = $orderItem->getProductOptions();        
        if (!is_array($orderOptions)) return $this;
        
        // htmlspecialchars_decode titles
        if (isset($quoteOptions['options']) && is_array($quoteOptions['options'])) {
            foreach ($quoteOptions['options'] as $key=>$op) {
                if (isset($op['label'])) $quoteOptions['options'][$key]['label'] = htmlspecialchars_decode($op['label']);                
                if (isset($op['value'])) $quoteOptions['options'][$key]['value'] = htmlspecialchars_decode($op['value']);
                if (isset($op['print_value'])) unset($quoteOptions['options'][$key]['print_value']);
            }
        	$orderOptions['options'] = $quoteOptions['options'];
        }
        $orderItem->setProductOptions($orderOptions);
        return $this;
    }
    
    // after create order - reduce inventory
    public function quoteSubmitSuccess($observer) {
    	if (!Mage::helper('customoptions')->isEnabled()) return $this;
    	
        // inventory
        if (Mage::helper('customoptions')->isInventoryEnabled()) {
            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
            $tablePrefix = (string) Mage::getConfig()->getTablePrefix();
            $orderItems = $observer->getEvent()->getOrder()->getAllItems();
            
            foreach ($orderItems as $orderItem) {
               
                // product sku -> reduce option with sku inventory
//                $select = $connection->select()->from($tablePrefix . 'catalog_product_option_type_value', array('option_type_id', 'customoptions_qty'))->where('sku = ?', $orderItem->getSku());
//                $allOptionValues = $connection->fetchAll($select);
//                if ($allOptionValues && count($allOptionValues)>0) {
//                    foreach ($allOptionValues as $opValue) {
//                        if (isset($opValue['option_type_id']) && isset($opValue['customoptions_qty']) && intval($opValue['customoptions_qty'])>0) {
//                            $customoptionsQty = intval($opValue['customoptions_qty'])-intval($orderItem->getQtyOrdered());
//                            if ($customoptionsQty<0) $customoptionsQty = 0;
//                            // model 'catalog/product_option_value' - do not use!
//                            $connection->update($tablePrefix . 'catalog_product_option_type_value', array('customoptions_qty'=>$customoptionsQty), 'option_type_id = '.intval($opValue['option_type_id']));
//                        }    
//                    }
//                }                
                
                // options sku -> reduce product inventory or options inventory
                $productOptions = $orderItem->getProductOptions();            
                if (!isset($productOptions['options'])) continue;

                $qty = $orderItem->getQtyOrdered();
                foreach ($productOptions['options'] as $option) {                
                    switch ($option['option_type']) {
                        case 'drop_down':
                        case 'radio':
                        case 'checkbox':                        
                        case 'multiple':
                            $optionId = $option['option_id'];
                            $customoptionsIsOnetime = Mage::getModel('catalog/product_option')->load($optionId)->getCustomoptionsIsOnetime();                                                
                            $optionTypeIds = explode(',', $option['option_value']);
                            foreach ($optionTypeIds as $optionTypeId) {                        
                                $productOptionValueModel = Mage::getModel('catalog/product_option_value')->load($optionTypeId);
                                $customoptionsQty = $productOptionValueModel->getCustomoptionsQty();
                                $sku = $productOptionValueModel->getSku();
                                if ($customoptionsQty!=='' || $sku!='') {
                                    if (isset($productOptions['info_buyRequest']['options_'.$optionId.'_qty'])) {
                                        $optionQty = intval($productOptions['info_buyRequest']['options_'.$optionId.'_qty']);
                                    } elseif (isset($productOptions['info_buyRequest']['options_'.$optionId.'_'.$optionTypeId.'_qty'])) {
                                        $optionQty = intval($productOptions['info_buyRequest']['options_'.$optionId.'_'.$optionTypeId.'_qty']);
                                    } else {
                                        $optionQty = 1;
                                    }                            
                                    $optionTotalQty = ($customoptionsIsOnetime?$optionQty:$optionQty*$qty);

                                    if ($customoptionsQty!=='' && substr($customoptionsQty, 0, 1)!='x' && $customoptionsQty>0) {
                                        $customoptionsQty = $customoptionsQty - $optionTotalQty;
                                        if ($customoptionsQty<0) $customoptionsQty = 0;
                                        // model 'catalog/product_option_value' - do not use!
                                        $connection->update($tablePrefix . 'catalog_product_option_type_value', array('customoptions_qty'=>$customoptionsQty), 'option_type_id = '.$optionTypeId);
                                    }    

                                    if ($sku!=='') {
                                        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
                                        if (isset($product) && $product && $product->getId() > 0) {
                                            $item = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);                                        
                                            if ($item->getQty() > 0) {
                                                if ($item->getQty() < $optionTotalQty) $optionTotalQty = intval($item->getQty());
                                                $item->subtractQty($optionTotalQty);
                                                $item->save();
                                            }                                        
                                        }
                                    }    

                                }    
                            }     
                    }    
                }
            }
        }
        return $this;
    }
    
    
    public function cancelOrderItem($observer) {        
        if (!Mage::helper('customoptions')->isInventoryEnabled()) return $this;
        
        $orderItem = $observer->getEvent()->getItem();
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix = (string) Mage::getConfig()->getTablePrefix();
        
        // qty cancel now
        $qty = intval($orderItem->getQtyToCancel());
        
//        // product sku -> increase option with sku inventory
//        $select = $connection->select()->from($tablePrefix . 'catalog_product_option_type_value', array('option_type_id', 'customoptions_qty'))->where('sku = ?', $orderItem->getSku());
//        $allOptionValues = $connection->fetchAll($select);
//        if ($allOptionValues && count($allOptionValues)>0) {
//            foreach ($allOptionValues as $opValue) {
//                if (isset($opValue['option_type_id']) && isset($opValue['customoptions_qty'])) {
//                    $customoptionsQty = intval($opValue['customoptions_qty']) + $qty;
//                    // model 'catalog/product_option_value' - do not use!
//                    $connection->update($tablePrefix . 'catalog_product_option_type_value', array('customoptions_qty'=>$customoptionsQty), 'option_type_id = '.intval($opValue['option_type_id']));
//                }    
//            }
//        }
        
        // options sku -> increase product inventory or options inventory
        $productOptions = $orderItem->getProductOptions();
        if (!isset($productOptions['options'])) return $this;
        
        
        foreach ($productOptions['options'] as $option) {                
            switch ($option['option_type']) {
                case 'drop_down':
                case 'radio':
                case 'checkbox':                        
                case 'multiple':
                    $optionId = $option['option_id'];
                    $customoptionsIsOnetime = Mage::getModel('catalog/product_option')->load($optionId)->getCustomoptionsIsOnetime();
                    $optionTypeIds = explode(',', $option['option_value']);
                    foreach ($optionTypeIds as $optionTypeId) {                    
                        $productOptionValueModel = Mage::getModel('catalog/product_option_value')->load($optionTypeId);
                        $customoptionsQty = $productOptionValueModel->getCustomoptionsQty();
                        $sku = $productOptionValueModel->getSku();
                        if ($customoptionsQty!=='' || $sku!='') {
                            if (isset($productOptions['info_buyRequest']['options_'.$optionId.'_qty'])) {
                                $optionQty = intval($productOptions['info_buyRequest']['options_'.$optionId.'_qty']);
                            } elseif (isset($productOptions['info_buyRequest']['options_'.$optionId.'_'.$optionTypeId.'_qty'])) {
                                $optionQty = intval($productOptions['info_buyRequest']['options_'.$optionId.'_'.$optionTypeId.'_qty']);                            
                            } else {
                                $optionQty = 1;
                            }                                                                        
                            $optionTotalQty = ($customoptionsIsOnetime?$optionQty:$optionQty*$qty);                        
                            
                            if ($customoptionsQty!=='' && substr($customoptionsQty, 0, 1)!='x') {
                                $customoptionsQty = $customoptionsQty + $optionTotalQty;                                
                                // model 'catalog/product_option_value' - do not use!
                                $connection->update($tablePrefix . 'catalog_product_option_type_value', array('customoptions_qty'=>$customoptionsQty), 'option_type_id = '.$optionTypeId);                                
                            }
                            
                            if ($sku!=='') {
                                $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
                                if (isset($product) && $product && $product->getId() > 0) {
                                    $item = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);                                    
                                    $item->addQty($optionTotalQty);
                                    $item->save();
                                }
                            }
                        }    
                    }    
            }            

        }        
        
        return $this;
        
    }

    public function creditMemoRefund($observer) {
        if (!Mage::helper('customoptions')->isInventoryEnabled()) return $this;

        $orderItems = $observer->getEvent()->getCreditmemo()->getOrder()->getItemsCollection();                
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix = (string) Mage::getConfig()->getTablePrefix();     
        
        foreach ($orderItems as $orderItem) {
            // qty refund now
            $qty = intval($orderItem->getQtyRefunded()) - intval($orderItem->getOrigData('qty_refunded'));
            
            // product sku -> increase option with sku inventory
//            $select = $connection->select()->from($tablePrefix . 'catalog_product_option_type_value', array('option_type_id', 'customoptions_qty'))->where('sku = ?', $orderItem->getSku());
//            $allOptionValues = $connection->fetchAll($select);
//            if ($allOptionValues && count($allOptionValues)>0) {
//                foreach ($allOptionValues as $opValue) {
//                    if (isset($opValue['option_type_id']) && isset($opValue['customoptions_qty'])) {
//                        $customoptionsQty = intval($opValue['customoptions_qty']) + $qty;
//                        // model 'catalog/product_option_value' - do not use!
//                        $connection->update($tablePrefix . 'catalog_product_option_type_value', array('customoptions_qty'=>$customoptionsQty), 'option_type_id = '.intval($opValue['option_type_id']));
//                    }    
//                }
//            }
            
            // options sku -> increase product inventory and options inventory
            $productOptions = $orderItem->getProductOptions();
            if (!isset($productOptions['options'])) continue;
            
            foreach ($productOptions['options'] as $option) {                
                switch ($option['option_type']) {
                    case 'drop_down':
                    case 'radio':
                    case 'checkbox':                        
                    case 'multiple':
                        $optionId = $option['option_id'];
                        $customoptionsIsOnetime = Mage::getModel('catalog/product_option')->load($optionId)->getCustomoptionsIsOnetime();                        
                        $optionTypeIds = explode(',', $option['option_value']);
                        foreach ($optionTypeIds as $optionTypeId) {                        
                            $productOptionValueModel = Mage::getModel('catalog/product_option_value')->load($optionTypeId);
                            $customoptionsQty = $productOptionValueModel->getCustomoptionsQty();
                            $sku = $productOptionValueModel->getSku();
                            if ($customoptionsQty!=='' || $sku!='') {
                                if (isset($productOptions['info_buyRequest']['options_'.$optionId.'_qty'])) {
                                    $optionQty = intval($productOptions['info_buyRequest']['options_'.$optionId.'_qty']);
                                } elseif (isset($productOptions['info_buyRequest']['options_'.$optionId.'_'.$optionTypeId.'_qty'])) {
                                    $optionQty = intval($productOptions['info_buyRequest']['options_'.$optionId.'_'.$optionTypeId.'_qty']);    
                                } else {
                                    $optionQty = 1;
                                }                            
                                $optionTotalQty = ($customoptionsIsOnetime?$optionQty:$optionQty*$qty);
                                
                                if ($customoptionsQty!=='' && substr($customoptionsQty, 0, 1)!='x') {
                                    $customoptionsQty = $customoptionsQty + $optionTotalQty;
                                    // model 'catalog/product_option_value' - do not use!
                                    $connection->update($tablePrefix . 'catalog_product_option_type_value', array('customoptions_qty'=>$customoptionsQty), 'option_type_id = '.$optionTypeId);
                                }
                                
                                if ($sku!=='') {
                                    $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
                                    if (isset($product) && $product && $product->getId() > 0) {
                                        $item = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);                                    
                                        $item->addQty($optionTotalQty);
                                        $item->save();
                                    }
                                }                                
                            }    
                        }     
                }    
                    
            }
        }            
        return $this;                                              
    }

    
    public function quoteItemSetProduct($observer) {
        
        if (!Mage::helper('customoptions')->isEnabled() || !Mage::helper('customoptions')->isWeightEnabled()) return $this;
    
        
        $quoteItem = $observer->getEvent()->getQuoteItem();
        if (!$quoteItem || !$quoteItem->getProductId() || !$quoteItem->getQuote() || $quoteItem->getQuote()->getIsSuperMode()) return $this;
        
        // prepare post data
        $post = $quoteItem->getProduct()->getCustomOption('info_buyRequest')->getValue();
        if ($post) $post = unserialize ($post); else $post = array();
        if (isset($post['options'])) $options = $post['options']; else $options = false;      
            
        if ($options) {
            if (Mage::app()->getStore()->isAdmin()) {
                $sessionQuote = Mage::getSingleton('adminhtml/session_quote');
                if ($sessionQuote) $customerGroupId = $sessionQuote->getCustomer()->getGroupId(); else $customerGroupId = 0;        
            } else {
                $customerGroupId = Mage::getSingleton('customer/session')->isLoggedIn() ? Mage::getSingleton('customer/session')->getCustomer()->getGroupId() : 0;            
            }
            $optionsWeight = 0;
            foreach ($options as $optionId => $option) {                     
                $productOption = Mage::getModel('catalog/product_option')->load($optionId);
                
                // check Options Customer Group
                if (Mage::helper('customoptions')->isCustomerGroupsEnabled() && $productOption->getCustomerGroups()!=='' && !in_array($customerGroupId, explode(',', $productOption->getCustomerGroups()))) continue;
                
                // set options weight
                $optionType = $productOption->getType();                    
                if ($optionType!='drop_down' && $optionType!='multiple' && $optionType!='radio' && $optionType!='checkbox') continue;
                if (!is_array($option)) $option = array($option);
                //product Qty
                $qty = intval($quoteItem->getQty());
                
                
                foreach ($option as $optionTypeId) {
                    if (!$optionTypeId) continue;
                    $row = $productOption->getOptionValue($optionTypeId);
                    if (isset($row['weight']) && $row['weight']>0) {

                        switch ($optionType) {
                            case 'checkbox':                            
                                if (isset($post['options_'.$optionId.'_'.$optionTypeId.'_qty'])) $optionQty = intval($post['options_'.$optionId.'_'.$optionTypeId.'_qty']); else $optionQty = 1;
                                break;
                            case 'drop_down':
                            case 'radio':                                                    
                                if (isset($post['options_'.$optionId.'_qty'])) $optionQty = intval($post['options_'.$optionId.'_qty']); else $optionQty = 1;
                                break;
                            case 'multiple':
                                $optionQty = 1;                            
                                break;                       
                        } 
                        
                        // get option weight
                        $weight = floatval($row['weight']);
                        if ($productOption->getCustomoptionsIsOnetime()) $weight = $weight / $qty;
                        $optionsWeight += $weight * $optionQty;                        
                    }
                }
            }
            
            if ($optionsWeight>0) {
                // check absolute weight
                $product = $observer->getEvent()->getProduct();
                if (!Mage::helper('customoptions')->getProductAbsoluteWeight($product)) $optionsWeight += $quoteItem->getWeight();
                // set weight for qty=1
                $quoteItem->setWeight($optionsWeight);
            }
        }
        return $this;
    }

}