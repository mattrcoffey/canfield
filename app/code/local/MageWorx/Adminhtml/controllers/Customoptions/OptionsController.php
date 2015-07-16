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

class MageWorx_Adminhtml_Customoptions_OptionsController extends Mage_Adminhtml_Controller_Action {    

    public function indexAction() {
        $this->_title($this->__('APO'))->_title($this->__('Manage Templates'));
        $this->loadLayout()
            ->_setActiveMenu('catalog/customoptions')
            ->_addBreadcrumb($this->__('APO'), $this->__('Manage Templates'))
            ->renderLayout();
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function editAction() {
        $id = (int) $this->getRequest()->getParam('group_id');
        $storeId = (int) $this->getRequest()->getParam('store', 0);        
        Mage::register('store_id', $storeId);
        $model = Mage::getModel('customoptions/group')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
                if ($id) {
                    $model->setId($id);
                }
            }
                        
            Mage::register('customoptions_data', $model);
            $title = $model->getTitle()?$model->getTitle():$this->__('New Template');
            $this->_title($this->__('APO'))->_title($this->__('Manage Templates'))->_title($title);
            $this->loadLayout()
            ->_setActiveMenu('catalog/customoptions')
            ->_addBreadcrumb($this->__('APO'), $this->__('Manage Templates'))
            ->_addContent($this->getLayout()->createBlock('mageworx/customoptions_options_edit'))
            ->_addLeft($this->getLayout()->createBlock('adminhtml/store_switcher'))
            ->_addLeft($this->getLayout()->createBlock('mageworx/customoptions_options_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Options do not exist'));
            $this->_redirect('*/*/');
        }
    }

    private function _isEmptyOptions($options) {
        $result = true;
        if ($options && is_array($options)) {
            foreach ($options as $value) {
                
                if ($value['is_delete'] != 1) {
                    $result = false;
                    break;
                }
            }
        }
        return $result;
    }

    private function _prepareOptions($options, $groupId, $checkImg = true) {                
        
        if ($options && is_array($options)) {
            $helper = Mage::helper('customoptions');
            // is_delete + sort_order            
            $optionPrepare = array();
            foreach ($options as $key => $value) {
                
                // option
                $options[$key]['previous_type'] = $value['type'];
                if (!isset($value['is_delete']) || $value['is_delete']!=1) {                   
                    $sortOrder = substr('00000000'.(isset($value['sort_order'])?$value['sort_order']:'0'), -8).'_'.$key;
                    
                    if ($checkImg && isset($value['id']) && ($value['type']=='field' || $value['type']=='area')) {
                        if ($helper->isCustomOptionsFile($groupId, $value['id'])) {                        
                            $value['image_path'] = $groupId . DS . $value['id'] . DS;
                        } else {
                            $value['image_path'] = '';
                        }                    
                    }                   
                    
                    $optionPrepare[$sortOrder] = $value;                                    
                    // item option
                    if (isset($value['values']) && is_array($value['values'])) {
                        $itemOptionPrepare = array();
                        foreach ($value['values'] as $vKey => $val) {
                            if (!isset($val['is_delete']) || $val['is_delete']!=1) {
                                $itemSortOrder = substr('00000000'.(isset($val['sort_order'])?$val['sort_order']:'0'), -8).'_'.$vKey;
                                if ($checkImg) {
                                    if ($helper->isCustomOptionsFile($groupId, $value['id'], $vKey)) {
                                        $val['image_path'] = $groupId . DS . $value['id'] . DS . $vKey;
                                    } else {
                                        $val['image_path'] = '';
                                    }
                                }
                                if (isset($val['weight'])) $val['weight'] = floatval($val['weight']);                                                                
                                $val['sku'] = trim($val['sku']);
                                
                                // prepare customoptions_qty
                                $customoptionsQty = '';
                                if (isset($val['customoptions_qty']) && $helper->getProductIdBySku($val['sku'])==0) {                        
                                    $customoptionsQty = strtolower(trim($val['customoptions_qty']));
                                    if (substr($customoptionsQty, 0, 1)!='x' && !is_numeric($customoptionsQty)) $customoptionsQty='';
                                    if (is_numeric($customoptionsQty)) $customoptionsQty = intval($customoptionsQty);
                                    $val['customoptions_qty'] = $customoptionsQty;
                                }
   
                                $itemOptionPrepare[$itemSortOrder] = $val;
                            }
                        }
                        ksort($itemOptionPrepare);                        
                        unset($optionPrepare[$sortOrder]['values']);                        
                        foreach ($itemOptionPrepare as $val) {
                            $optionPrepare[$sortOrder]['values'][$val['option_type_id']] = $val;
                        }                        
                    }                                        
                }                                
                
            }
            ksort($optionPrepare);
            $options = array();            
            foreach ($optionPrepare as $value) {
                $options[$value['option_id']] = $value;
            }            
        }        
        return $options;
    }

    public function duplicateAction() {
        $id = (int) $this->getRequest()->getParam('group_id');

        try {
            $group = Mage::getSingleton('customoptions/group')->load($id);
            $newGroupId = $group->duplicate();
            
            $helper = Mage::helper('customoptions');
            
            $helper->copyFolder($helper->getCustomOptionsPath($id), $helper->getCustomOptionsPath($newGroupId));
            
        } catch (Exception $e) {
            if ($e->getMessage()) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('group_id' => $id));
            }
        }

        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Options were successfully duplicated'));
        
        $this->_redirect('*/*/edit', array('group_id' => $newGroupId));
    }

    public function saveAction() {
        @ini_set('max_execution_time', 1800);
        @ini_set('memory_limit', 734003200);
        
        $data = $this->getRequest()->getPost();
        $id = (int) $this->getRequest()->getParam('group_id');
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        $redirectParams = array('group_id' => $id);
        if ($storeId>0) $redirectParams['store'] = $storeId;
        
        $error = false;
        if ($data) {
            $data = Mage::helper('customoptions')->getFilter($data);
            try {
                $productOptions = array();
                if (!isset($data['product']['options']) || $this->_isEmptyOptions($data['product']['options'])) {
                    Mage::getSingleton('adminhtml/session')->addError($this->__('There are no Options'));
                    $error = true;
                } else {
                    $productOptions = $data['product']['options'];                    
                    foreach ($productOptions as $i => $option) {
                        if (isset($option['values'])) {
                            //$option['values'] = array_reverse($option['values'], true);
                            foreach($option['values'] as $key => $value) {
                                if ($value['option_type_id'] == '-1') {
                                    $option['values'][$key]['option_type_id'] = (string)$key;
                                }
                                if (!isset($value['scope']['price'])) {
                                    if (!isset($value['price'])) $option['values'][$key]['price'] = '0.00';
                                    if (!isset($value['price_type'])) $option['values'][$key]['price_type'] = 'fixed';
                                }                                
                            }
                        } else {
                            if (!isset($option['scope']['price'])) {
                                if (!isset($option['price'])) $option['price'] = '0.00';
                                if (!isset($option['price_type'])) $option['price_type'] = 'fixed';
                            }
                        }
                        $option['option_id'] = $i;
                        // qnty_input
                        if (!isset($option['qnty_input']) || ($option['type']!='drop_down' && $option['type']!='radio' && $option['type']!='checkbox')) $option['qnty_input'] = 0;
                        $productOptions[$i] = $option;
                    }
                    $data['general']['hash_options'] = serialize(array());
                }
                if ($error) {
                    if (isset($data['in_products']) && is_array($data['in_products']) && count($data['in_products']) > 0) {
                        $data['in_products'] = implode(',', $data['in_products']);
                    }
                    throw new Exception();
                }
                
                $optionsPrev = array();
                $prevGroupIsActive = 1;
                if ($id) {
                    $group = Mage::getSingleton('customoptions/group')->load($id);
                    $prevGroupIsActive = $group->getIsActive();
                    if ($group->getHashOptions()!='') $optionsPrev = unserialize($group->getHashOptions());
                } else {
                    $group = Mage::getSingleton('customoptions/group');
                }                
                
                
                
                // insert
                if (!$id) {
                    $group->setData($data['general']);
                    $group->save();
                    $id = $group->getId();
                }                                
                
                $productIds = array();
                if (isset($data['in_products']) && $data['in_products']) {
                    $productIds = explode(',', $data['in_products']);
                }                                

                //remove files
                if (isset($data['image_delete'])) {
                    foreach ($data['image_delete'] as $key1 => $optionId) {
                        if (!is_array($optionId)) {
                            Mage::getSingleton('catalog/product_option')->removeOptionFile($group->getId(), $optionId, false, true);
                        } else {
                            foreach ($optionId as $valueId => $optionId2) {
                                Mage::getSingleton('catalog/product_option')->removeOptionFile($group->getId(), $optionId2, $valueId, true);                                
                            }
                        }
                    }
                }                
                
                //upload files
                foreach ($productOptions as $option) {
                    if ($option['option_id'] == 0) {
                        $option['option_id'] = 1;
                    }
                    switch ($option['type']) {
                        case 'field':
                        case 'area':                        
                            $this->_uploadImage('file_' . $option['option_id'], $id, $option['option_id']);
                            break;
                        case 'drop_down':
                        case 'radio':
                        case 'checkbox':
                        case 'multiple':
                            if (isset($option['values']) && is_array($option['values']) && !empty($option['values'])) {
                                foreach ($option['values'] as $key => $value) {
                                    $counter = $value['option_type_id'] == '-1' ? $key : $value['option_type_id'];
                                    $this->_uploadImage('file_' . $option['option_id'] . '-' . $counter, $id, $option['option_id'], $counter);
                                }
                            }    
                            break;
                        case 'file':
                        case 'date':
                        case 'date_time':
                        case 'time':
                            // no image
                            if (isset($option['option_id'])) {                                
                                Mage::getSingleton('catalog/product_option')->removeOptionFile($id, $option['option_id'], false, true);                                
                            }
                            break;
                    }
                }
                              
                $prevStoreOptionsData = array();
                if ($storeId>0) { // if no default store
                    $defaultOptions = $optionsPrev;
                    // add to store defoult values + add new options to defoult or mark is_delete flag
                    foreach ($productOptions as $key=>$option) {
                        if (isset($optionsPrev[$key])) {
                            if (isset($option['scope'])) {
                                foreach ($option['scope'] as $field=>$value) {
                                    if ($value && isset($optionsPrev[$key][$field])) $productOptions[$key][$field] = $optionsPrev[$key][$field];
                                }
                                unset($productOptions[$key]['scope']);
                            }                            
                            $defaultOptions[$key]['is_delete'] = $option['is_delete'];                            
                            
                            if (isset($option['values'])) {
                                foreach ($option['values'] as $valueId=>$optionValue) {
                                    if (isset($optionsPrev[$key]['values'][$valueId])) {                                    
                                        if (isset($optionValue['scope'])) {
                                            foreach ($optionValue['scope'] as $field=>$value) {
                                                if ($value && isset($optionsPrev[$key]['values'][$valueId][$field])) $productOptions[$key]['values'][$valueId][$field] = $optionsPrev[$key]['values'][$valueId][$field];
                                            }
                                            unset($productOptions[$key]['values'][$valueId]['scope']);
                                        }
                                        $defaultOptions[$key]['values'][$valueId]['is_delete'] = $optionValue['is_delete'];
                                    } else {
                                        // found new option value
                                        $defaultOptions[$key]['values'][$valueId] = $optionValue;
                                    }
                                }
                            }                                                        
                        } else {
                            // found new option
                            $defaultOptions[$key] = $option;
                        }
                    }
                    
                    // difference default and store
                    $storeOptions = Mage::getSingleton('catalog/product_option')->comparisonArrays($productOptions, $defaultOptions);
                    // add option_id/option_type_id/type to $storeOptions
                    foreach($storeOptions as $optionId=>$option) {
                        $storeOptions[$optionId]['option_id'] = $optionId;
                        $storeOptions[$optionId]['type'] = $productOptions[$optionId]['type'];
                        
                        if (isset($option['price']) && !isset($option['price_type'])) $storeOptions[$optionId]['price_type'] = $productOptions[$optionId]['price_type'];
                        if (!isset($option['price']) && isset($option['price_type'])) $storeOptions[$optionId]['price'] = $productOptions[$optionId]['price'];
                        
                        if (isset($option['values'])) {
                            foreach ($option['values'] as $valueId=>$optionValue) {
                                $storeOptions[$optionId]['values'][$valueId]['option_type_id'] = $valueId;
                                if (isset($optionValue['price']) && !isset($optionValue['price_type'])) $storeOptions[$optionId]['values'][$valueId]['price_type'] = $productOptions[$optionId]['values'][$valueId]['price_type'];
                                if (!isset($optionValue['price']) && isset($optionValue['price_type'])) $storeOptions[$optionId]['values'][$valueId]['price'] = $productOptions[$optionId]['values'][$valueId]['price'];
                            }
                        }
                    }
                    // save store options
                    $groupStore = Mage::getSingleton('customoptions/group_store')->loadByGroupAndStore($id, $storeId);                    
                    $prevStoreOptionsData = $groupStore->getData();                    
                    $groupStore->setGroupId($id)->setStoreId($storeId)
                        ->setHashOptions(serialize($this->_prepareOptions($storeOptions, $id, false)))
                        ->save();

//                    print_r($optionsPrev);
//                    print_r($defaultOptions);                    
//                    print_r($storeOptions);
//                    exit;
                    
                    
                } else {
                    // default store
                    $defaultOptions = $productOptions;
                    
                    
                    // foreach all no default store and mark is_delete flag or no option
                    $groupStoreCollection = Mage::getResourceModel('customoptions/group_store_collection')->addFieldToFilter('group_id', $id);                    
                    if (count($groupStoreCollection)>0) {
                        foreach ($groupStoreCollection as $groupStore) {
                            $groupStoreOptions = $groupStore->getHashOptions();
                            if ($groupStoreOptions) $groupStoreOptions = unserialize($groupStoreOptions);
                            $changeFlag = false;
                            foreach ($groupStoreOptions as $optionId=>$option) {
                                if (!isset($defaultOptions[$optionId]) || (isset($defaultOptions[$optionId]['is_delete']) && $defaultOptions[$optionId]['is_delete'])) {
                                    unset($groupStoreOptions[$optionId]);
                                    $changeFlag = true;
                                } else {
                                    if (isset($option['values']) && count($option['values'])>0) {
                                        foreach ($option['values'] as $valueId=>$value) {
                                            if (!isset($defaultOptions[$optionId]['values'][$valueId]) || (isset($defaultOptions[$optionId]['values'][$valueId]['is_delete']) && $defaultOptions[$optionId]['values'][$valueId]['is_delete'])) {
                                                unset($groupStoreOptions[$optionId]['values'][$valueId]);
                                                if (count($groupStoreOptions[$optionId]['values'])==0) unset($groupStoreOptions[$optionId]['values']);
                                                $changeFlag = true;
                                            }
                                        }
                                    }
                                }
                            }
                            if ($changeFlag) {
                                if (count($groupStoreOptions)>0) {
                                    $groupStore->setHashOptions(serialize($groupStoreOptions))->save();
                                } else {
                                    $groupStore->delete();
                                }
                            }    
                        }
                    }                                        
                }                               

                //print_r($defaultOptions); exit;
                
                // save default options
                if (!isset($data['general']['absolute_price'])) $data['general']['absolute_price'] = 0;
                if (!isset($data['general']['absolute_weight'])) $data['general']['absolute_weight'] = 0;
                
                
                
                $data['general']['hash_options'] = serialize($this->_prepareOptions($defaultOptions, $id));
                $approximateOptionCount = ceil((strlen($data['general']['hash_options'])+1)/500); // 500 byte = ~1 option
                
                $group->setData($data['general']);
                $group->setId($id);
                $group->save();
                
                Mage::getSingleton('adminhtml/session')->setCustomoptionsData(null);
                
                if ($this->getRequest()->getParam('back')) {
                    $redirectParams['group_id'] = $group->getId();
                    $redirectData = array('*/*/edit', $redirectParams);
                } else {
                    $redirectData = array('*/*/', array());
                }
                               
                if ($productOptions && isset($productIds) && is_array($productIds)) {
                    
                    if (count($productIds) > 0) {                        
                        if (count($productIds)*$approximateOptionCount<250) {
                            // apply default and store
                            Mage::getModel('catalog/product_option')->saveProductOptions($defaultOptions, $optionsPrev, $productIds, $group, $prevGroupIsActive, 'apo', $prevStoreOptionsData);
                            //Mage::getModel('catalog/product_indexer_price')->reindexAll(); // make reindex
                            Mage::getResourceModel('catalog/product_indexer_price')->reindexProductIds($productIds); // make reindex
                        } else {
                            // start multi-step apply
                            $limit = ceil(250/$approximateOptionCount);
                            Mage::getSingleton('adminhtml/session')->setCustomoptionsApplyData(array($defaultOptions, $optionsPrev, $productIds, $group, $prevGroupIsActive, $prevStoreOptionsData, $redirectData, $limit));
                            return $this->_redirect('*/*/apply');
                        }
                    } else {
                        if (Mage::getModel('catalog/product_option')->removeProductOptionsAndRelationByGroup($group->getId())) {
                            Mage::getModel('catalog/product_indexer_price')->reindexAll(); // make reindex
                        }
                    }
                }
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Options were successfully saved'));
                return $this->_redirect($redirectData[0], $redirectData[1]);
                
            } catch (Exception $e) {
                if ($e->getMessage()) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }
                Mage::getSingleton('adminhtml/session')->setData('customoptions_data', $data);
                $this->_redirect('*/*/edit', $redirectParams);
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError($this->__('Unable to find Options to save'));
        $this->_redirect('*/*/');
    }
    
    public function applyAction() {
        $this->loadLayout();
        $this->renderLayout();
    }
    
    protected function _apply($current, $limit, $customoptionsApplyData) {
        $defaultOptions = $customoptionsApplyData[0];
        $optionsPrev = $customoptionsApplyData[1];
        $productIds = array_slice($customoptionsApplyData[2], $current, $limit);
        $group = $customoptionsApplyData[3];
        $prevGroupIsActive = $customoptionsApplyData[4];
        $prevStoreOptionsData = $customoptionsApplyData[5];
        Mage::getModel('catalog/product_option')->saveProductOptions($defaultOptions, $optionsPrev, $productIds, $group, $prevGroupIsActive, 'product', $prevStoreOptionsData);
    }
    
    public function runApplyAction() {
        @ini_set('max_execution_time', 1800);
        @ini_set('memory_limit', 734003200);        
        
        $current = $this->getRequest()->getParam('current', 0);
        $customoptionsApplyData = Mage::getSingleton('adminhtml/session')->getCustomoptionsApplyData();
        if (count($customoptionsApplyData)!=8) return $this->_redirect('*/*/');
        
        $limit = $customoptionsApplyData[7];
        $productIds = $customoptionsApplyData[2];        
        $total = count($productIds);
        $result = array();        
        
        if ($current=='checkUnassigned') { // check and remove of unassigned options
            $group = $customoptionsApplyData[3];
            Mage::getModel('catalog/product_option')->saveProductOptions(null, array(), $productIds, $group, 1, 'apo');
            $result['url'] = $this->getUrl('*/*/runApply/', array('current'=>'reindex'));
            $result['text'] = $this->__('Unassigned options removed (100%)...');
        } elseif ($current=='reindex') {
            $result['stop'] = 1;
            //Mage::getModel('catalog/product_indexer_price')->reindexAll(); // make reindex
            Mage::getResourceModel('catalog/product_indexer_price')->reindexProductIds($productIds); // make reindex
            
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Options were successfully saved'));
            $result['url'] = $this->getUrl($customoptionsApplyData[6][0], $customoptionsApplyData[6][1]); // $redirectData
            $result['text'] = $this->__('Product prices reindexed (100%)...');
        } elseif ($current<$total) {
            $this->_apply($current, $limit, $customoptionsApplyData);
            $current += $limit;            
            if ($current>=$total) {
                $current = $total;
                $result['url'] = $this->getUrl('*/*/runApply/', array('current'=>'checkUnassigned'));
            } else {
                $result['url'] = $this->getUrl('*/*/runApply/', array('current'=>$current));
            }
            $result['text'] = $this->__('Total %1$s, processed %2$s products (%3$s%%)...', $total, $current, round($current*100/$total, 2));
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    

    public function productGridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('mageworx/customoptions_options_edit_tab_product')->toHtml()
        );
    }

    public function deleteAction() {
        $id = (int) $this->getRequest()->getParam('group_id');
        if ($id > 0) {
            try {
                $model = Mage::getModel('customoptions/group');
                $model->load($id);
                $model->setId($id)->delete();
                
                $helper = Mage::helper('customoptions');
                $helper->deleteFolder($helper->getCustomOptionsPath($id));

                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Options were successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $id));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction() {
        $ids = $this->getRequest()->getParam('groups');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select Option(s)'));
        } else {
            try {
                if (isset($ids) && is_array($ids))
                    foreach ($ids as $id) {
                        $model = Mage::getModel('customoptions/group')->load($id);
                        $model->delete();
                    }
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Total of %d record(s) were successfully deleted', count($ids)));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction() {
        $ids = $this->getRequest()->getParam('groups');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select Option(s)'));
        } else {
            try {
                $data = array();
                $relation = Mage::getSingleton('customoptions/relation');
                $model = Mage::getSingleton('customoptions/group');
                if (isset($ids) && is_array($ids)) {
                    foreach ($ids as $id) {
                        $model->load($id)
                            ->setIsActive((int) $this->getRequest()->getParam('is_active'))
                            ->save();
                        $relation->changeOptionsStatus($model);
                        $data[$model->getId()] = $model;
                    }
                    $relation->changeHasOptions($data);
                    $this->_getSession()->addSuccess($this->__('Total of %d record(s) were successfully updated', count($ids)));
                }
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    private function _uploadImage($keyFile, $groupId, $optionId, $valueId = false) {
        if (isset($_FILES[$keyFile]['name']) && $_FILES[$keyFile]['name'] != '') {
            try {
                Mage::helper('customoptions')->deleteValueFile($groupId, $optionId, $valueId);

                $uploader = new Varien_File_Uploader($keyFile);
                $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);

                $uploader->save(Mage::helper('customoptions')->getCustomOptionsPath($groupId, $optionId, $valueId), $_FILES[$keyFile]['name']);
            } catch (Exception $e) {
                if ($e->getMessage()) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }
            }
        }
    }
}