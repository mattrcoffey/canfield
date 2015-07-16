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
 * @copyright  Copyright (c) 2013 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Advanced Product Options extension
 *
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @author     MageWorx Dev Team
 */
class MageWorx_CustomOptions_Model_Catalog_Product_Option extends Mage_Catalog_Model_Product_Option {

    protected function _construct() {
        parent::_construct();
        $this->_init('customoptions/product_option');       
    }    
    
    public function decodeViewIGI($IGI) {
        $tmp = explode('x', $IGI);
        if (count($tmp)<2) return intval($IGI);        
        return ((intval($tmp[0])*65535) + intval($tmp[1]));
    }                
    

    public function _prepareOptions($options, $groupId, $groupIsActive = 2) {
        $out = array();
        foreach ($options as $key=>$op) {                
            //unset($op['option_id']);

            if (isset($op['id']) && ($op['type']=='field' || $op['type']=='area') && !isset($op['image_path'])) {
                if (Mage::helper('customoptions')->isCustomOptionsFile($groupId, $op['id'])) {                        
                    $op['image_path'] = $groupId . DS . $op['id'] . DS;
                } else {
                    $op['image_path'] = '';
                }                    
            }
            $op['customer_groups'] = isset($op['customer_groups']) ? implode(',', $op['customer_groups']) : '';
            if ($groupIsActive==2) $op['is_enabled'] = 0;                

            if (isset($op['in_group_id'])) {
                $op['in_group_id'] = ((intval($op['in_group_id'])>0)?($groupId * 65535) + intval($op['in_group_id']):0);
                $key = 'IGI'.$op['in_group_id'];
            }    
            if (!isset($op['sku'])) $op['sku'] = '';
            if (!isset($op['max_characters'])) $option['max_characters'] = null;
            if (!isset($op['file_extension'])) $option['file_extension'] = null;
            if (!isset($op['image_size_x'])) $option['image_size_x'] = 0;
            if (!isset($op['image_size_y'])) $option['image_size_y'] = 0;

            if (isset($op['qnty_input'])) $op['qnty_input'] = intval($op['qnty_input']);


            if ($op['type']=='drop_down' || $op['type']=='radio' || $op['type']=='checkbox' || $op['type']=='multiple') {
                if (isset($op['values']) && is_array($op['values'])) {
                    $defaultArray = isset($op['default']) ? $op['default'] : array();
                    $tm = array();
                    foreach ($op['values'] as $k=>$value) {
                        //unset($value['option_type_id']);
                        $value['default'] = (array_search($k, $defaultArray)!==false?1:0);
                        if (!isset($value['image_path'])) {
                            if (Mage::helper('customoptions')->isCustomOptionsFile($groupId, $op['id'], $k)) {
                                $value['image_path'] = $groupId . DS . $op['id'] . DS . $k;
                            } else {
                                $value['image_path'] = '';
                            }
                        }

                        if (isset($value['dependent_ids']) && $value['dependent_ids']!='') {                                
                            $dependentIds = array();
                            $dependentIdsTmp = explode(',', $value['dependent_ids']);
                            foreach ($dependentIdsTmp as $d_id) {
                                if (intval($d_id)>0) $dependentIds[] = ($groupId * 65535) + intval($d_id);
                            }
                            $value['dependent_ids'] = implode(',', $dependentIds);
                        }


                        if (!isset($value['customoptions_qty'])) $value['customoptions_qty'] = '';
                        if (isset($value['in_group_id'])) {
                            $value['in_group_id'] = ($groupId * 65535) + intval($value['in_group_id']);
                            $k = 'IGI'.$value['in_group_id'];
                        }    
                        $tm[$k] = $value;                                            
                    }
                    $op['values'] = $tm;
                } else {
                    $op['values'] = array();
                }
            }                

            $out[$key] = $op;
        }              
        return $out;
    }

    public function removeProductOptionsAndRelationByGroup($groupId) {
        $result = false;
        if ($groupId>0) {
            $result = $this->removeProductOptions($groupId);
            Mage::getResourceSingleton('customoptions/relation')->deleteGroup($groupId); // just in case
        }
        return $result;
    }
    
    // comparison arrays - quadruple nesting
    public function comparisonArrays(array $newOptions, array $prevOptions) {
        $diffOptions = array();
        foreach ($newOptions as $key=>$op) {
            if (isset($prevOptions[$key])) {
                if (is_array($op)) {
                    foreach ($op as $kkk=>$ooo) {
                        if (isset($prevOptions[$key][$kkk])) {
                            if (is_array($ooo)) {
                                foreach ($ooo as $kk=>$oo) {
                                    if (isset($prevOptions[$key][$kkk][$kk])) {
                                        if (is_array($oo)) {
                                            foreach ($oo as $k=>$o) {
                                                if (isset($prevOptions[$key][$kkk][$kk][$k])) {
                                                    if ($prevOptions[$key][$kkk][$kk][$k]!=$o) $diffOptions[$key][$kkk][$kk][$k] = $o;
                                                } else {
                                                    $diffOptions[$key][$kkk][$kk][$k] = $o;
                                                }
                                            }
                                        } else {
                                            if ($prevOptions[$key][$kkk][$kk]!=$oo) $diffOptions[$key][$kkk][$kk] = $oo;
                                        }    
                                    } else {
                                        $diffOptions[$key][$kkk][$kk] = $oo;
                                    }
                                }
                            } else {
                                if ($prevOptions[$key][$kkk]!=$ooo) $diffOptions[$key][$kkk] = $ooo;
                            }
                        } else {
                            $diffOptions[$key][$kkk] = $ooo;
                        }
                    }
                } else {
                    if ($prevOptions[$key]!=$op) $diffOptions[$key] = $op;
                }
            } else {                    
                $diffOptions[$key] = $op;
            }    
        }        
        return $diffOptions;        
    }
    
    public function saveProductOptions($newOptions, array $prevOptions, array $productIds, Varien_Object $group, $prevGroupIsActive = 1, $place = 'apo', $prevStoreOptionsData = array()) {
        if (isset($productIds) && is_array($productIds) && count($productIds)>0) {
            $relation = Mage::getResourceSingleton('customoptions/relation');            

            

            $groupId = $group->getId();
            $groupIsActive = $group->getIsActive();

            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
            $tablePrefix = (string) Mage::getConfig()->getTablePrefix();

            $condition = '';
            if ($place == 'product') {
                $condition = ' AND product_id IN (' . implode(',', $productIds) . ')';
            }

            // get and prepare $optionRelations
            $select = $connection->select()->from($tablePrefix . 'custom_options_relation')->where('group_id = ' . $groupId . $condition);
            $optionRelations = $connection->fetchAll($select);
            if (is_array($optionRelations) && count($optionRelations)>0) {
                $tmp = array();
                foreach ($optionRelations as $option) {
                    $tmp[$option['product_id']][$option['option_id']] = $option;
                }
                $optionRelations = $tmp;
            } else {
                $optionRelations = array();
            }                        
            
            if (isset($newOptions) && is_array($newOptions)) {
            
                $newOptions = $this->_prepareOptions($newOptions, $groupId, $groupIsActive);
                $prevOptions = $this->_prepareOptions($prevOptions, $groupId, $prevGroupIsActive);            

                // comparison arrays - quadruple nesting            
                $diffOptions = $this->comparisonArrays($newOptions, $prevOptions);


                // get all store options            
                $select = $connection->select()->from($tablePrefix . 'custom_options_group_store')->where('group_id = '.$groupId);
                $allStoreOptions = $connection->fetchAll($select);            
                foreach ($allStoreOptions as $key=>$storeOptions) {
                    if ($storeOptions['hash_options']) $hashOptions = unserialize($storeOptions['hash_options']); else $hashOptions = array();
                    if (isset($prevStoreOptionsData['store_id']) && $storeOptions['store_id']==$prevStoreOptionsData['store_id']) {
                        if ($prevStoreOptionsData['hash_options']) $prevHashOptions = unserialize($prevStoreOptionsData['hash_options']); else $prevHashOptions = array();
                        // link to reset no deault!!
                        foreach ($prevHashOptions as $optionId=>$option) {
                            if (!isset($hashOptions[$optionId])) $hashOptions[$optionId] = array('option_id' => $option['option_id'], 'type'=>$option['type']);                            
                            if (isset($option['values'])) {
                                foreach ($option['values'] as $valueId=>$value) {
                                    // add to check remove store option value
                                    if (!isset($hashOptions[$optionId]['values'][$valueId])) {
                                        $hashOptions[$optionId]['values'][$valueId]['option_type_id'] = $value['option_type_id'];
                                    }
                                }
                            }
                            
                        }
                    }                
                    $allStoreOptions[$key]['hash_options'] = $hashOptions;
                }

//                print_r($newOptions);
//                print_r($prevOptions);
//                print_r($diffOptions);
//                exit;                        
                        
            
            
                
                foreach ($productIds as $productId) {                
                    $realOptionIds = array();
                    $options = $newOptions; // work copy options
                    
                    // $optionRelations
                    // update options
                    if (isset($optionRelations[$productId])) {
                        foreach ($optionRelations[$productId] as $optionId=>$prevOption) {
                            //$optionId = $prevOption['option_id'];
                            $prevOption = Mage::getModel('catalog/product_option')->load($optionId);
                            if (isset($options['IGI'.$prevOption->getInGroupId()]) && (!isset($options['IGI'.$prevOption->getInGroupId()]['is_delete']) || $options['IGI'.$prevOption->getInGroupId()]['is_delete']!=1)) {
                                $option = $options['IGI'.$prevOption->getInGroupId()];                                
                                if (isset($diffOptions['IGI'.$prevOption->getInGroupId()])) $diffOption = $diffOptions['IGI'.$prevOption->getInGroupId()]; else $diffOption = array();                                
                                $this->saveOption($productId, $diffOption, $optionId, 0, $option['type']);                                
                                $realOptionIds[$option['option_id']]['value'] = $optionId;
                                
                                if ($option['type']=='drop_down' || $option['type']=='radio' || $option['type']=='checkbox' || $option['type']=='multiple') {
                                    $select = $connection->select()->from($tablePrefix . 'catalog_product_option_type_value')->where('option_id = '.$optionId);
                                    $prevValues = $connection->fetchAll($select);
                                    if (is_array($prevValues) && count($prevValues)>0) {
                                        foreach ($prevValues as $prValue) {
                                            if (isset($option['values']['IGI'.$prValue['in_group_id']]) && (!isset($option['values']['IGI'.$prValue['in_group_id']]['is_delete']) || $option['values']['IGI'.$prValue['in_group_id']]['is_delete']!=1)) {
                                                // update option value
                                                if (isset($diffOption['values']['IGI'.$prValue['in_group_id']])) $diffValue = $diffOption['values']['IGI'.$prValue['in_group_id']]; else $diffValue = array();
                                                $this->saveOptionValue($optionId, $diffValue, $prValue['option_type_id'], 0);
                                                $realOptionIds[$option['option_id']][$option['values']['IGI'.$prValue['in_group_id']]['option_type_id']] = $prValue['option_type_id'];
                                                unset($option['values']['IGI'.$prValue['in_group_id']]);
                                            } else {
                                                // delete option value
                                                $connection->delete($tablePrefix . 'catalog_product_option_type_value', 'option_type_id = ' . $prValue['option_type_id']);
                                            }
                                        }
                                    }                                    
                                    // insert option values
                                    if (count($option['values'])>0) {
                                        foreach ($option['values'] as $value) {
                                            if (isset($value['is_delete']) && $value['is_delete'] == 1) continue;
                                            $this->saveOptionValue($optionId, $value, false, 0);
                                        }
                                    }

                                }                                    

                                unset($options['IGI'.$prevOption->getInGroupId()]);
                                
                            } else {
                                if (isset($prevOption['option_id'])) {
                                    // delete option
                                    $connection->delete($tablePrefix . 'catalog_product_option', 'option_id = ' . $prevOption['option_id']);
                                    $connection->delete($tablePrefix . 'custom_options_option_description', 'option_id = ' . $prevOption['option_id']);
                                    $connection->delete($tablePrefix . 'custom_options_option_default', 'option_id = ' . $prevOption['option_id']);
                                    $connection->delete($tablePrefix . 'custom_options_relation', 'group_id = '.$groupId.' AND product_id = '.$productId.' AND option_id = ' . $prevOption['option_id']);
                                }
                            }                            
                        }
                        unset($optionRelations[$productId]);
                    }                                        
                    
                    // insert default options
                    foreach ($options as $option) {
                        if (isset($option['is_delete']) && $option['is_delete'] == 1) continue;
                        $optionId = $this->saveOption($productId, $option, false, 0, $option['type']);
                        $realOptionIds[$option['option_id']]['value'] = $optionId;
                        $optionRelation = array(
                            'option_id' => $optionId,
                            'group_id' => $groupId,
                            'product_id' => $productId,
                        );
                        $connection->insert($tablePrefix . 'custom_options_relation', $optionRelation);
                        
                        // insert option values
                        if (($option['type']=='drop_down' || $option['type']=='radio' || $option['type']=='checkbox' || $option['type']=='multiple') && count($option['values'])>0) {
                            foreach ($option['values'] as $value) {
                                if (isset($value['is_delete']) && $value['is_delete'] == 1) continue;
                                $optionTypeId = $this->saveOptionValue($optionId, $value, false, 0);
                                $realOptionIds[$option['option_id']][$value['option_type_id']] = $optionTypeId;
                            }
                        }
                    }
                    

                    // insert all store options
                    //print_r($allStoreOptions); exit;
                    foreach ($allStoreOptions as $storeOptions) {
                        foreach ($storeOptions['hash_options'] as $option) {
                            if (isset($realOptionIds[$option['option_id']]['value']) && $realOptionIds[$option['option_id']]['value']) $optionId = $this->saveOption($productId, $option, $realOptionIds[$option['option_id']]['value'], $storeOptions['store_id'], $option['type']); else $optionId = false;
                            // insert option values
                            if ($optionId && ($option['type']=='drop_down' || $option['type']=='radio' || $option['type']=='checkbox' || $option['type']=='multiple') && count($option['values'])>0) {
                                foreach ($option['values'] as $value) {
                                    if (isset($realOptionIds[$option['option_id']][$value['option_type_id']]) && $realOptionIds[$option['option_id']][$value['option_type_id']]) $this->saveOptionValue($optionId, $value, $realOptionIds[$option['option_id']][$value['option_type_id']], $storeOptions['store_id']);
                                }
                            }
                        }                        
                    }
                    
                    
                    $this->updateProductFlags($productId, $group->getAbsolutePrice(), $group->getAbsoluteWeight());                    
                }                
            }
                        
            // remnants of the options that must be removed
            if (count($optionRelations)>0) {
                foreach ($optionRelations as $productId=>$prevOptions) {
                    if (count($prevOptions)>0 && !in_array($productId, $productIds)) {
                        foreach ($prevOptions as $prevOption) {
                            $connection->delete($tablePrefix . 'catalog_product_option', 'option_id = ' . $prevOption['option_id']);
                            $connection->delete($tablePrefix . 'custom_options_option_description', 'option_id = ' . $prevOption['option_id']);
                            $connection->delete($tablePrefix . 'custom_options_option_default', 'option_id = ' . $prevOption['option_id']);
                            $connection->delete($tablePrefix . 'custom_options_relation', 'group_id = '.$groupId.' AND product_id = '.$productId.' AND option_id = ' . $prevOption['option_id']);
                        }
                        $this->updateProductFlags($productId, $group->getAbsolutePrice(), $group->getAbsoluteWeight());
                    }
                }
            }    
            
            
        }
                
    }
    
    public function saveOption($productId, $option, $optionId = 0, $storeId = 0, $type = '') {
        
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix = (string) Mage::getConfig()->getTablePrefix();
        
        if ($storeId==0) {
            $optionData = array();
            if (isset($option['type'])) $optionData['type'] = $option['type'];
            if (isset($option['is_require'])) $optionData['is_require'] = $option['is_require'];
            if (isset($option['is_enabled'])) $optionData['is_enabled'] = $option['is_enabled'];
            if (isset($option['sku'])) $optionData['sku'] = $option['sku'];
            if (isset($option['max_characters'])) $optionData['max_characters'] = $option['max_characters'];
            if (isset($option['file_extension'])) $optionData['file_extension'] = $option['file_extension'];
            if (isset($option['image_path'])) $optionData['image_path'] = $option['image_path'];
            if (isset($option['image_size_x'])) $optionData['image_size_x'] = $option['image_size_x'];
            if (isset($option['image_size_y'])) $optionData['image_size_y'] = $option['image_size_y'];
            if (isset($option['sort_order'])) $optionData['sort_order'] = $option['sort_order'];
            if (isset($option['customoptions_is_onetime'])) $optionData['customoptions_is_onetime'] = $option['customoptions_is_onetime'];
            if (isset($option['customer_groups'])) $optionData['customer_groups'] = $option['customer_groups'];
            if (isset($option['qnty_input'])) $optionData['qnty_input'] = $option['qnty_input'];
            if (isset($option['in_group_id'])) $optionData['in_group_id'] = $option['in_group_id'];
            if (isset($option['is_dependent'])) $optionData['is_dependent'] = $option['is_dependent'];
            if (isset($option['div_class'])) $optionData['div_class'] = $option['div_class'];

            if (count($optionData)>0) $optionData['product_id'] = $productId;        

            if ($optionId) {            
                $updateFlag = true;
                if (count($optionData)>0) $connection->update($tablePrefix . 'catalog_product_option', $optionData, 'option_id = '.$optionId);
            } else {
                $updateFlag = false;
                $connection->insert($tablePrefix . 'catalog_product_option', $optionData);
                $optionId = $connection->lastInsertId($tablePrefix . 'catalog_product_option');            
            }
        }    
                
        if (isset($option['title'])) {
            $optionTitle = array (    
                'option_id' => $optionId,
                'store_id' => $storeId,
                'title' => $option['title']
            );
            
            if ($storeId>0) {
                $select = $connection->select()->from($tablePrefix . 'catalog_product_option_title', array('title'))->where('option_id = '.$optionId.' AND `store_id` = '.$storeId);
                $updateFlag = $connection->fetchOne($select);                
            }            
            if ($option['title']!==$updateFlag) {
                if ($updateFlag) {
                    $connection->update($tablePrefix . 'catalog_product_option_title', $optionTitle, 'option_id = '.$optionId.' AND store_id = '.$storeId);
                } else {
                    $connection->insert($tablePrefix . 'catalog_product_option_title', $optionTitle);
                }
            }    
        } elseif ($storeId>0) {
            $connection->delete($tablePrefix . 'catalog_product_option_title', 'option_id = '.$optionId.' AND store_id = '.$storeId);
        }
        
        if (isset($option['description']) && $option['description']!='') {
            $optionDesc = array(
                'option_id' => $optionId,
                'store_id' => $storeId,
                'description' => $option['description']
            );
            $select = $connection->select()->from($tablePrefix . 'custom_options_option_description', array('description'))->where('option_id = '.$optionId.' AND `store_id` = '.$storeId);
            $updateDescriptionFlag = $connection->fetchOne($select);
            if ($option['description']!==$updateFlag) {
                if ($updateDescriptionFlag) {
                    $connection->update($tablePrefix . 'custom_options_option_description', $optionDesc, 'option_id = '.$optionId.' AND `store_id` = '.$storeId);
                } else {
                    $connection->insert($tablePrefix . 'custom_options_option_description', $optionDesc);
                }
            }    
        } elseif ($storeId>0 || isset($option['description']) && $option['description']=='') {
            $connection->delete($tablePrefix . 'custom_options_option_description', 'option_id = '.$optionId.' AND store_id = '.$storeId);
        }
        
        if (isset($option['default_text']) && $option['default_text']!='') {
            $optionDef = array(
                'option_id' => $optionId,
                'store_id' => $storeId,
                'default_text' => $option['default_text']
            );            
            $select = $connection->select()->from($tablePrefix . 'custom_options_option_default', array('default_text'))->where('option_id = '.$optionId.' AND `store_id` = '.$storeId);
            $updateDefaultTextFlag = $connection->fetchOne($select);
            if ($option['default_text']!==$updateFlag) {
                if ($updateDefaultTextFlag) {
                    $connection->update($tablePrefix . 'custom_options_option_default', $optionDef, 'option_id = '.$optionId.' AND `store_id` = '.$storeId);
                } else {
                    $connection->insert($tablePrefix . 'custom_options_option_default', $optionDef);
                }
            }    
        } elseif ($storeId>0 || (isset($option['default_text']) && $option['default_text']=='')) {
            $connection->delete($tablePrefix . 'custom_options_option_default', 'option_id = '.$optionId.' AND store_id = '.$storeId);
        }
        
        
        
        if ($type=='field' || $type=='area' || $type=='file' || $type=='date' || $type=='date_time' || $type=='time') {
            $optionPrice = array();
            if (isset($option['price'])) $optionPrice['price'] = $option['price'];
            if (isset($option['price_type'])) $optionPrice['price_type'] = $option['price_type'];
            if (count($optionPrice)>0) {
                $optionPrice['option_id'] = $optionId;
                $optionPrice['store_id'] = $storeId;
                if ($storeId>0) {
                    $select = $connection->select()->from($tablePrefix . 'catalog_product_option_price', array('price', 'price_type'))->where('option_id = '.$optionId.' AND `store_id` = '.$storeId);
                    $updateFlag = $connection->fetchRow($select);
                }
                
                if (!is_array($updateFlag) || (isset($option['price']) && $option['price']!=$updateFlag['price']) || (isset($option['price_type']) && $option['price_type']!=$updateFlag['price_type'])) {
                    if ($updateFlag) {
                        $connection->update($tablePrefix . 'catalog_product_option_price', $optionPrice, 'option_id = '.$optionId.' AND `store_id` = '.$storeId);
                    } else {
                        $connection->insert($tablePrefix . 'catalog_product_option_price', $optionPrice);
                    }
                }    
            } elseif ($storeId>0) {
                $connection->delete($tablePrefix . 'catalog_product_option_price', 'option_id = '.$optionId.' AND store_id = '.$storeId);
            }   
        }
        
        return $optionId;
    }
    
    public function saveOptionValue($optionId, $value, $optionTypeId = 0, $storeId = 0) {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix = (string) Mage::getConfig()->getTablePrefix();

        if ($storeId==0) {
            $optionValue = array();        
            if (isset($value['sku'])) $optionValue['sku'] = $value['sku'];
            if (isset($value['sort_order'])) $optionValue['sort_order'] = $value['sort_order'];
            if (isset($value['customoptions_qty'])) $optionValue['customoptions_qty'] = $value['customoptions_qty'];
            if (isset($value['default'])) $optionValue['default'] = $value['default'];
            if (isset($value['in_group_id'])) $optionValue['in_group_id'] = $value['in_group_id'];
            if (isset($value['image_path'])) $optionValue['image_path'] = $value['image_path'];
            if (isset($value['dependent_ids'])) $optionValue['dependent_ids'] = $value['dependent_ids'];
            if (isset($value['weight'])) $optionValue['weight'] = $value['weight'];

            if (count($optionValue)>0) $optionValue['option_id'] = $optionId;

            if ($optionTypeId) {
                $updateFlag = true;
                unset($optionValue['customoptions_qty']);
                unset($optionValue['option_id']);
                if (count($optionValue)>0) $connection->update($tablePrefix . 'catalog_product_option_type_value', $optionValue, 'option_type_id = '.$optionTypeId);
            } else {
                $updateFlag = false;
                $connection->insert($tablePrefix . 'catalog_product_option_type_value', $optionValue);
                $optionTypeId = $connection->lastInsertId($tablePrefix . 'catalog_product_option_type_value');
            }
        }
        
        $optionTypePrice = array();
        if (isset($value['price'])) $optionTypePrice['price'] = $value['price'];
        if (isset($value['price_type'])) $optionTypePrice['price_type'] = $value['price_type'];
        
        if (count($optionTypePrice)>0) {
            $optionTypePrice['option_type_id'] = $optionTypeId;
            $optionTypePrice['store_id'] = $storeId;
            if ($storeId>0) {
                $select = $connection->select()->from($tablePrefix . 'catalog_product_option_type_price', array('price', 'price_type'))->where('option_type_id = '.$optionTypeId.' AND `store_id` = '.$storeId);
                $updateFlag = $connection->fetchRow($select);                
            }
            
            if (!is_array($updateFlag) || (isset($value['price']) && $value['price']!=$updateFlag['price']) || (isset($value['price_type']) && $value['price_type']!=$updateFlag['price_type'])) {                
                if ($updateFlag) {
                    $connection->update($tablePrefix . 'catalog_product_option_type_price', $optionTypePrice, 'option_type_id = '.$optionTypeId.' AND `store_id` = '.$storeId);
                } else {            
                    $connection->insert($tablePrefix . 'catalog_product_option_type_price', $optionTypePrice);
                }
            }
        } elseif ($storeId>0) {
            $connection->delete($tablePrefix . 'catalog_product_option_type_price', 'option_type_id = '.$optionTypeId.' AND store_id = '.$storeId);
        }
        
        if (isset($value['title'])) {
            $optionTypeTitle = array(
                'option_type_id' => $optionTypeId,
                'store_id' => $storeId,
                'title' => $value['title']
            );
            
            if ($storeId>0) {
                $select = $connection->select()->from($tablePrefix . 'catalog_product_option_type_title', array('title'))->where('option_type_id = '.$optionTypeId.' AND `store_id` = '.$storeId);
                $updateFlag = $connection->fetchOne($select);
            }
            if ($value['title']!==$updateFlag) {
                if ($updateFlag) {
                    $connection->update($tablePrefix . 'catalog_product_option_type_title', $optionTypeTitle, 'option_type_id = '.$optionTypeId.' AND `store_id` = '.$storeId);
                } else {
                    $connection->insert($tablePrefix . 'catalog_product_option_type_title', $optionTypeTitle);
                }
            }    
        } elseif ($storeId>0) {
            $connection->delete($tablePrefix . 'catalog_product_option_type_title', 'option_type_id = '.$optionTypeId.' AND store_id = '.$storeId);
        }   
        
        return $optionTypeId;
    }
    
    public function updateProductFlags($productId, $absolutePrice = 0, $absoluteWeight = 0) {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix = (string) Mage::getConfig()->getTablePrefix();
        
        // check Has & RequiredOptions
        $select = $connection->select()->from($tablePrefix . 'catalog_product_option', array('MAX(is_require)'))->where('product_id = '.$productId.' AND `is_enabled` = 1');
        $isRequire = $connection->fetchOne($select);
        if (!isset($isRequire)) {
            $isRequire = 0;
            $hasOptions = 0;
        } else {
            $hasOptions = 1;
        }
 
        // if no options - absolute = 0
        if ($hasOptions==0) {
            $absolutePrice = 0;
            $absoluteWeight = 0;
        }
        
        $connection->update($tablePrefix . 'catalog_product_entity', array('has_options'=>$hasOptions, 'required_options'=>$isRequire, 'absolute_price'=>$absolutePrice, 'absolute_weight'=>$absoluteWeight), 'entity_id = ' . $productId);
    }

    public function removeProductOptions($groupId, $productId = null) {
        $relation = Mage::getResourceSingleton('customoptions/relation');
        if (is_null($productId)) {
            $productIds = $relation->getProductIds($groupId);
            if (isset($productIds) && is_array($productIds) && count($productIds)>0) {
                foreach ($productIds as $productId) {                    
                    $relationOptionIds = $relation->getOptionIds($groupId, $productId);
                    $this->_removeRelationOptions($relationOptionIds);                                        
                    $this->updateProductFlags($productId);
                }
                return true;
            } else {
                return false;
            }            
        } else {
            $relationOptionIds = $relation->getOptionIds($groupId, $productId);
            if (isset($relationOptionIds) && is_array($relationOptionIds) && count($relationOptionIds)>0) {
                $this->_removeRelationOptions($relationOptionIds);
                return true;
            } else {
                return false;
            }
        }
    }

    private function _removeOptionDescription($id) {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix = (string) Mage::getConfig()->getTablePrefix();
        $connection->delete($tablePrefix . 'custom_options_option_description', 'option_id = ' . $id);
    }
    
    private function _removeOptionDefaultText($id) {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix = (string) Mage::getConfig()->getTablePrefix();
        $connection->delete($tablePrefix . 'custom_options_option_default', 'option_id = ' . $id);
    }

    private function _removeRelationOptions($relationOptionIds) {
        if (isset($relationOptionIds) && is_array($relationOptionIds)) {
            foreach ($relationOptionIds as $id) {
                $this->_removeOptionDescription($id);
                $this->_removeOptionDefaultText($id);
                $this->getValueInstance()->deleteValue($id);
                $this->deletePrices($id);
                $this->deleteTitles($id);
                $this->setId($id)->delete();
            }
        }
    }

    private function _uploadImage($keyFile, $optionId, $valueId = false) {                
        if (isset($_FILES[$keyFile]['name']) && $_FILES[$keyFile]['name'] != '') {
            try {
                Mage::helper('customoptions')->deleteValueFile(null, $optionId, $valueId);

                $uploader = new Varien_File_Uploader($keyFile);
                $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);


                $uploader->save(Mage::helper('customoptions')->getCustomOptionsPath(false, $optionId, $valueId), $_FILES[$keyFile]['name']);
                return true;
            } catch (Exception $e) {
                if ($e->getMessage()) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }
            }
        }
    }

    // magento save from product page
    public function saveOptions() {
    	
    	if (!Mage::helper('customoptions')->isEnabled()) return parent::saveOptions();
    	
        $options = $this->getOptions();        
        $post = Mage::app()->getRequest()->getPost();          
        
        
        
        
        $productId = $this->getProduct()->getId();                
        
        $relation = Mage::getSingleton('customoptions/relation');

        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix = (string) Mage::getConfig()->getTablePrefix();
        
        if (isset($post['image_delete'])) {
            $productOption = Mage::getModel('catalog/product_option');
            foreach ($post['image_delete'] as $key1 => $optionId) {
                if (is_array($optionId)) {
                    foreach ($optionId as $vId => $oId) {
                        $connection->update($tablePrefix . 'catalog_product_option_type_value', array('image_path' => ''), 'option_type_id = ' . $vId);
                    }
                } else {
                	$connection->update($tablePrefix . 'catalog_product_option', array('image_path' => ''), 'option_id = ' . $optionId);
                }
            }
        }        

        foreach ($options as $option) {
            if (isset($option['option_id'])) {
                $connection->update($tablePrefix . 'catalog_product_option_type_value', array('default' => 0), 'option_id = ' . $option['option_id']);
                if (isset($option['default'])) {
                    foreach ($option['default'] as $value) {
                        $connection->update($tablePrefix . 'catalog_product_option_type_value', array('default' => 1), 'option_type_id = ' . $value);
                    }
                }
            }
        }
                        
        
        if (Mage::helper('customoptions')->isCustomerGroupsEnabled()) {
            $options = $this->getOptions();
            foreach ($options as $key => $option) {
                if (isset($option['customer_groups'])) {
                    $options[$key]['customer_groups'] = implode(',', $option['customer_groups']);
                }
            }
            $this->setOptions($options);
        }
        
        // qnty_input        
        $options = $this->getOptions();
        foreach ($options as $key => $option) {
            if (!isset($option['qnty_input']) || ($option['type']!='drop_down' && $option['type']!='radio' && $option['type']!='checkbox')) $options[$key]['qnty_input'] = 0;            
        }
        $this->setOptions($options);        
        
        
        
        // original code m1510 parent::saveOptions();
        foreach ($this->getOptions() as $option) {
            $this->setData($option)
                ->setData('product_id', $this->getProduct()->getId())
                ->setData('store_id', $this->getProduct()->getStoreId());

            if ($this->getData('option_id') == '0') {
                $this->unsetData('option_id');
            } else {
                $this->setId($this->getData('option_id'));
            }
            $isEdit = (bool)$this->getId()? true:false;

            if ($this->getData('is_delete') == '1') {
                if ($isEdit) {
                    $this->getValueInstance()->deleteValue($this->getId());
                    $this->deletePrices($this->getId());
                    $this->deleteTitles($this->getId());
                    $this->delete();
                    Mage::helper('customoptions')->deleteValueFile(null, $this->getId(), false);
                }
            } else {
                if ($this->getData('previous_type') != '') {
                    $previousType = $this->getData('previous_type');
                    //if previous option has dfferent group from one is came now need to remove all data of previous group
                    if ($this->getGroupByType($previousType) != $this->getGroupByType($this->getData('type'))) {

                        switch ($this->getGroupByType($previousType)) {
                            case self::OPTION_GROUP_SELECT:
                                $this->unsetData('values');
                                if ($isEdit) {
                                    $this->getValueInstance()->deleteValue($this->getId());
                                }
                                break;
                            case self::OPTION_GROUP_FILE:
                                $this->setData('file_extension', '');
                                $this->setData('image_size_x', '0');
                                $this->setData('image_size_y', '0');
                                break;
                            case self::OPTION_GROUP_TEXT:
                                $this->setData('max_characters', '0');
                                break;
                            case self::OPTION_GROUP_DATE:
                                break;
                        }
                        if ($this->getGroupByType($this->getData('type')) == self::OPTION_GROUP_SELECT) {
                            $this->setData('sku', '');
                            $this->unsetData('price');
                            $this->unsetData('price_type');
                            if ($isEdit) {
                                $this->deletePrices($this->getId());
                            }
                        }
                    }
                }
                $this->save();
                
                if (!isset($option['option_id']) || !$option['option_id']) {                                        
                    $values = $this->getValues();                    
                    $option['option_id']=$this->getId();                    
                }    
                
                switch ($option['type']) {
                    case 'field':
                    case 'area':
                        if ($this->_uploadImage('file_' . $option['id'], $option['option_id'])) {                            
                            $this->setImagePath('options' . DS . $option['option_id'] . DS)->save();
                        }
                        break;
                    case 'drop_down':
                    case 'radio':
                    case 'checkbox':
                    case 'multiple':
                        break;
                    case 'file':
                    case 'date':
                    case 'date_time':
                    case 'time':
                        // no image
                        if (isset($option['option_id'])) {
                            Mage::helper('customoptions')->deleteValueFile(null, $option['option_id'], false);
                            $this->setImagePath('')->save();                            
                        }                         
                        break;
                }
                
            }
        }//eof foreach()
        // end original code m1510 parent::saveOptions();                        
               
        if ($productId && isset($post['affect_product_custom_options'])) {
            
            if (isset($post['customoptions']['groups'])) $postGourps = $post['customoptions']['groups']; else $postGourps = array();
            
            
            $groupModel = Mage::getSingleton('customoptions/group');
            $groups = $relation->getResource()->getGroupIds($productId, false);            
            
            if (isset($groups) && is_array($groups) && count($groups)>0) {
                $keepOptionsFlag = (isset($post['general']['keep_options'])?$post['general']['keep_options']:0);
                
                foreach ($groups as $id) {                    
                    if (count($postGourps)==0 || !in_array($id, $postGourps)) {
                        if (!$keepOptionsFlag) $this->removeProductOptions($id, $productId);
                        $relation->getResource()->deleteGroupProduct($id, $productId);
                    } else {
                        $relationOptionIds = $relation->getResource()->getOptionIds($id, $productId);                        
                        if ($relationOptionIds && is_array($relationOptionIds) && count($relationOptionIds)>0) {
                            foreach ($relationOptionIds as $opId) {
                                $check = Mage::getModel('catalog/product_option')->load($opId)->getData();
                                if (empty($check)) $relation->getResource()->deleteOptionProduct($id, $productId, $opId);
                            }
                        }
                        if (count($postGourps)>0) {
                            $key = array_search($id, $postGourps);                        
                            unset($postGourps[$key]);
                        }    
                    }
                }
            }
            
            if (count($postGourps)>0) {
                foreach ($postGourps as $groupId) {
                    if (!empty($groupId)) {
                        $group = $groupModel->load($groupId);
                        $optionsHash = unserialize($group->getData('hash_options'));                        
                        $this->saveProductOptions($optionsHash, array(), array($productId), $group, 1, 'product');
                    }
                }
            } else {
                // save absolutePrice and absoluteWeight       
                $absolutePrice = (isset($post['general']['absolute_price'])?$post['general']['absolute_price']:0);
                $absoluteWeight = (isset($post['general']['absolute_weight'])?$post['general']['absolute_weight']:0);
                $this->updateProductFlags($productId, $absolutePrice, $absoluteWeight);                
            }                        
        }
        
        return $this;
    }       
    
    protected function _afterSave() {
		if (!Mage::helper('customoptions')->isEnabled()) return parent::_afterSave();
        
        $optionId = $this->getData('option_id');
        $defaultArray = $this->getData('default') ? $this->getData('default') : array();
        $tablePrefix = (string) Mage::getConfig()->getTablePrefix();
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        
        $helper = Mage::helper('customoptions');
        
        $storeId = $this->getProduct()->getStoreId(); // right store!
                
        if (is_array($this->getData('values'))) {
            $values=array();
            foreach ($this->getData('values') as $key => $value) {
                if (isset($value['option_type_id'])) {    
                                        
                    if (isset($value['dependent_ids']) && $value['dependent_ids']!='') {                                
                        $dependentIds = array();
                        $dependentIdsTmp = explode(',', $value['dependent_ids']);
                        foreach ($dependentIdsTmp as $d_id) {
                            if ($this->decodeViewIGI($d_id)>0) $dependentIds[] = $this->decodeViewIGI($d_id);
                        }
                        $value['dependent_ids'] = implode(',', $dependentIds);
                    }
                    
                    $value['sku'] = trim($value['sku']);
                    
                    // prepare customoptions_qty
                    $customoptionsQty = '';
                    if (isset($value['customoptions_qty']) && $helper->getProductIdBySku($value['sku'])==0) {                        
                        $customoptionsQty = strtolower(trim($value['customoptions_qty']));
                        if (substr($customoptionsQty, 0, 1)!='x' && !is_numeric($customoptionsQty)) $customoptionsQty='';
                        if (is_numeric($customoptionsQty)) $customoptionsQty = intval($customoptionsQty);
                    }
                    
                    $optionValue = array(
                        'option_id' => $optionId,
                        'sku' => $value['sku'],
                        'sort_order' => $value['sort_order'],
                        'customoptions_qty' => $customoptionsQty,
                        'default' => array_search($key, $defaultArray) !== false ? 1 : 0,
                        'in_group_id' => $value['in_group_id']
                    );                    
                    if (isset($value['dependent_ids'])) $optionValue['dependent_ids'] = $value['dependent_ids'];
                    if (isset($value['weight'])) $optionValue['weight'] = $value['weight'];

                    if (isset($value['option_type_id']) && $value['option_type_id']>0) {
                        $optionTypeId = $value['option_type_id'];
                        if ($value['is_delete']=='1') {
                            $connection->delete($tablePrefix . 'catalog_product_option_type_value', 'option_type_id = ' . $optionTypeId);                            
                            $helper->deleteValueFile(null, $optionId, $optionTypeId);
                        } else {                            
                                                        
                            $connection->update($tablePrefix . 'catalog_product_option_type_value', $optionValue, 'option_type_id = ' . $optionTypeId);

                            // update or insert price
                            if ($storeId>0) {
                                $select = $connection->select()->from($tablePrefix . 'catalog_product_option_type_price', array('COUNT(*)'))->where('option_type_id = '.$optionTypeId.' AND `store_id` = '.$storeId);
                                $isUpdate = $connection->fetchOne($select);
                            } else {
                                $isUpdate = 1;
                            }    
                            if (isset($value['price']) && isset($value['price_type'])) {
                                if ($isUpdate) {                                    
                                    $connection->update($tablePrefix . 'catalog_product_option_type_price', array('price' => $value['price'], 'price_type' => $value['price_type']), 'option_type_id = ' . $optionTypeId.' AND `store_id` = '.$storeId);
                                } else {
                                    $connection->insert($tablePrefix . 'catalog_product_option_type_price', array('option_type_id' =>$optionTypeId, 'store_id'=>$storeId, 'price' => $value['price'], 'price_type' => $value['price_type']));
                                }
                            } elseif (isset($value['scope']['price']) && $value['scope']['price']==1 && $isUpdate && $storeId>0) {
                                $connection->delete($tablePrefix . 'catalog_product_option_type_price', 'option_type_id = ' . $optionTypeId.' AND `store_id` = '.$storeId);
                            }                            
                            
                            // update or insert title
                            if ($storeId>0) {
                                $select = $connection->select()->from($tablePrefix . 'catalog_product_option_type_title', array('COUNT(*)'))->where('option_type_id = '.$optionTypeId.' AND `store_id` = '.$storeId);
                                $isUpdate = $connection->fetchOne($select);
                            } else {
                                $isUpdate = 1;
                            } 
                            if (isset($value['title'])) {                                
                                if ($isUpdate) {                                
                                    $connection->update($tablePrefix . 'catalog_product_option_type_title', array('title' => $value['title']), 'option_type_id = ' . $optionTypeId.' AND `store_id` = '.$storeId);
                                } else {
                                    $connection->insert($tablePrefix . 'catalog_product_option_type_title', array('option_type_id' =>$optionTypeId, 'store_id'=>$storeId, 'title' => $value['title']));
                                }
                            } elseif (isset($value['scope']['title']) && $value['scope']['title']==1 && $isUpdate && $storeId>0) {
                                $connection->delete($tablePrefix . 'catalog_product_option_type_title', 'option_type_id = ' . $optionTypeId.' AND `store_id` = '.$storeId);
                            }     
                        }    
                    } else {                    
                        $connection->insert($tablePrefix . 'catalog_product_option_type_value', $optionValue);                
                        $optionTypeId = $connection->lastInsertId($tablePrefix . 'catalog_product_option_type_value');
                        if (isset($value['price']) && isset($value['price_type'])) {                            
                            // save not default
                            if ($storeId>0) $connection->insert($tablePrefix . 'catalog_product_option_type_price', array('option_type_id' =>$optionTypeId, 'store_id'=>$storeId, 'price' => $value['price'], 'price_type' => $value['price_type']));
                            // save default
                            $connection->insert($tablePrefix . 'catalog_product_option_type_price', array('option_type_id' =>$optionTypeId, 'store_id'=>0, 'price' => $value['price'], 'price_type' => $value['price_type']));
                        }
                        if (isset($value['title'])) {
                            // save not default
                            if ($storeId>0) $connection->insert($tablePrefix . 'catalog_product_option_type_title', array('option_type_id' =>$optionTypeId, 'store_id'=>$storeId, 'title' => $value['title']));
                            // save default
                            $connection->insert($tablePrefix . 'catalog_product_option_type_title', array('option_type_id' =>$optionTypeId, 'store_id'=>0, 'title' => $value['title']));
                        }    
                    }

                    if ($optionTypeId>0) {
                        $id = $this->getData('id');                    
                        if ($this->_uploadImage('file_'.$id.'-'.$key, $optionId, $optionTypeId)) {                        
                            $connection->update($tablePrefix . 'catalog_product_option_type_value', array('image_path' => 'options' . DS . $optionId . DS . $optionTypeId . DS), 'option_type_id = ' . $optionTypeId);
                        }
                    }
                    unset($value['option_type_id']);
                }    
                
                $values[$key] = $value;
                
            }            
            $this->setData('values', $values);            
        
            
        } elseif ($this->getGroupByType($this->getType()) == self::OPTION_GROUP_SELECT) {
            Mage::throwException(Mage::helper('catalog')->__('Select type options required values rows.'));
        }
        
        if (version_compare(Mage::getVersion(), '1.4.0', '>=')) $this->cleanModelCache();
        
        Mage::dispatchEvent('model_save_after', array('object'=>$this));
        if (version_compare(Mage::getVersion(), '1.4.0', '>=')) Mage::dispatchEvent($this->_eventPrefix.'_save_after', $this->_getEventData());
        return $this;        
    }
    
    
    
    public function getProductOptionCollection(Mage_Catalog_Model_Product $product) {
        $helper = Mage::helper('customoptions');
        
        if (Mage::app()->getStore()->isAdmin() && Mage::app()->getRequest()->getControllerName()=='catalog_product') {
            $collection = $this->getCollection()->addFieldToFilter('product_id', $product->getId())
                //->addFieldToFilter('is_enabled', array('neq' => 0))
                ->addTitleToResult($product->getStoreId())
                ->addPriceToResult($product->getStoreId())
                ->addDescriptionToResult($product->getStoreId())
                ->addDefaultTextToResult($product->getStoreId())
                ->addTemplateTitleToResult()        
                ->setOrder('sort_order', 'asc')
                ->setOrder('title', 'asc')
                ->addValuesToResult($product->getStoreId());
        } else {
            $collection = $this->getCollection()->addFieldToFilter('product_id', $product->getId())
                ->addFieldToFilter('is_enabled', array('neq' => 0))
                ->addTitleToResult($product->getStoreId())
                ->addPriceToResult($product->getStoreId())
                ->addDescriptionToResult($product->getStoreId())
                ->addDefaultTextToResult($product->getStoreId())
                ->setOrder('sort_order', 'asc')
                ->setOrder('title', 'asc')
                ->addValuesToResult($product->getStoreId());
            
            if ($helper->isCustomerGroupsEnabled()) {
                $groupId = 0;
                if (Mage::app()->getStore()->isAdmin()) {                    
                    $sessionQuote = Mage::getSingleton('adminhtml/session_quote');
                    if ($sessionQuote) $groupId = $sessionQuote->getCustomer()->getGroupId();                    
                } else {                
                    $groupId = Mage::getSingleton('customer/session')->isLoggedIn() ? Mage::getSingleton('customer/session')->getCustomer()->getGroupId() : 0;
                }    
                $isRequire = false;

                foreach($collection as $key => $item) {
                    $groups = $item->getCustomerGroups();
                    if ($groups!=='' && !in_array($groupId, explode(',', $groups))) {
                        $collection->removeItemByKey($key);
                    } elseif ($item->getIsRequire(true)) {
                        $isRequire = true;
                    }
                }                
                
                if (!$isRequire) $product->setRequiredOptions(0);                
                if (count($collection)==0) $product->setHasOptions(0);                
            }
            
            // if all required options "Out of stock" -> set product "Out of stock"
            if ($product->getRequiredOptions() && Mage::app()->getRequest()->getControllerName()=='product') {
                if ($helper->isInventoryEnabled() && $helper->canHideOutOfStockOptions()) {
                    foreach ($collection as $option) {
                        $optionType = $option->getType();
                        if (!$option->getIsRequire(true) || $optionType!='drop_down' && $optionType!='multiple' && $optionType!='radio' && $optionType!='checkbox' || count($option->getValues())==0) continue;
                        $outOfStockFlag = true;
                        foreach ($option->getValues() as $value) {
                            $customoptionsQty = $helper->getCustomoptionsQty($value->getCustomoptionsQty(), $value->getSku(), $option->getId(), $value->getId(), 0);
                            if ($customoptionsQty!==0) {
                                $outOfStockFlag = false;
                                break;
                            }
                        }
                        if ($outOfStockFlag) {
                            $product->setData('is_salable', false);
                            break;
                        }
                    }
                }
            }
            
            // apply price linking via sku
            if ($helper->isSkuPriceLinkingEnabled()) {
                foreach ($collection as $option) {
                    $optionType = $option->getType();
                    if ($optionType=='drop_down' || $optionType=='multiple' || $optionType=='radio' || $optionType=='checkbox') {
                        if (count($option->getValues())>0) {
                            foreach ($option->getValues() as $value) {
                                if (!$value->getSku()) continue;
                                list($price, $priceType) = $helper->getOptionPriceAndPriceType($value->getPrice(), $value->getPriceType(), $value->getSku(), $product->getStore());
                                $value->setPrice($price);
                                $value->setPriceType($priceType);
                            }
                        }
                    } else {
                        if (!$option->getSku()) continue;
                        list($price, $priceType) = $helper->getOptionPriceAndPriceType($option->getPrice(), $option->getPriceType(), $option->getSku(), $product->getStore());
                        $option->setPrice($price);
                        $option->setPriceType($priceType);
                    }                    
                }
            }
            
        }
                
        return $collection;
    }

    public function getValuesAdvanced() {
        $values = $this->getResource()->getValuesAdvanced($this->getId());        
        return $values;
    }

    public function getOptionTitle() {
        $values = $this->getResource()->getOptionTitle($this->getId());
        return $values;
    }

    public function removeOptionFile($groupId, $optionId, $valueId = false, $isRemoveFolder = true) {
        $dir = Mage::helper('customoptions')->getCustomOptionsPath($groupId, $optionId, $valueId);
        $files = Mage::helper('customoptions')->getFiles($dir);
        if ($files) {
            foreach ($files as $value) {
                @unlink($value);
            }
            if ($isRemoveFolder === true) {
                $io = new Varien_Io_File();
                $io->rmdir($dir);
            }
        }
    }

    public function getOptionValue($valueId) {        
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix = (string) Mage::getConfig()->getTablePrefix();

        $select = $connection->select()->from($tablePrefix . 'catalog_product_option_type_value')->where('option_id = ' . $this->getId() . ' AND option_type_id = ' . $valueId);
        $row = $connection->fetchRow($select);
        return $row;
    }
    
    public function duplicate($oldProductId, $newProductId)
    {        
        if ($oldProductId>0 && $newProductId>0) {
            
            // standart magento duplicate options:
            $this->getResource()->duplicate($this, $oldProductId, $newProductId); 
                        
            // set relation template
            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
            $tablePrefix = (string) Mage::getConfig()->getTablePrefix();

            // transfer relation
            $select = $connection->select()->from($tablePrefix . 'catalog_product_option', array('option_id', 'in_group_id'))->where('product_id = '.$newProductId.' AND in_group_id > 65535');
            $newOptions = $connection->fetchAll($select);                
            if ($newOptions) {
                foreach ($newOptions as $option) {
                    $connection->insert($tablePrefix . 'custom_options_relation', array('option_id' => $option['option_id'], 'group_id' => floor((intval($option['in_group_id'])-1)/65535), 'product_id' => $newProductId));
                }
            }                        
            
            //$optionsGroupIds = Mage::getResourceSingleton('customoptions/relation')->getGroupIds($oldProductId);         
            //if (is_array($optionsGroupIds) && count($optionsGroupIds)>0) {                           
                //foreach ($optionsGroupIds as $groupId) {
                    //$group=Mage::getModel('customoptions/group')->load($groupId);
                    //if ($hashOptions=$group->getHashOptions()) $options = unserialize($hashOptions); else $options = false;                    
                    //if ($options) $this->saveProductOptions($options, array(), array($newProductId), $group, $group->getIsActive(), 'product');
                //}
            //}   
        }
        
        return $this;
    }
    
    // $isProductPage = false - is checkout
    public function getIsRequire($isProductPage = false) {
        if ($isProductPage) {
            return $this->getData('is_require');
        }

        // ckeck CustomerGroups
        if (Mage::helper('customoptions')->isCustomerGroupsEnabled()) {
            if (Mage::app()->getStore()->isAdmin()) {
                $sessionQuote = Mage::getSingleton('adminhtml/session_quote');
                if ($sessionQuote) $groupId = $sessionQuote->getCustomer()->getGroupId(); else $groupId = 0;        
            } else {
                $groupId = Mage::getSingleton('customer/session')->isLoggedIn() ? Mage::getSingleton('customer/session')->getCustomer()->getGroupId() : 0;            
            }            
            $groups = $this->getCustomerGroups();
            if ($groups!=='' && !in_array($groupId, explode(',', $groups))) {                        
                return 0;
            }
        }        
                   
        if ($this->getIsEnabled() && !$this->getIsDependent()) {
            return $this->getData('is_require');
        } else {
            return 0;
        }
        
    }               
    

}