<?php
/**
 * @package SmartAddons
 * @category SM Basic Products
 * @copyright (c) 2009-2012 YouTech Company. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * @author YouTech Company http://www.magentech.com
 */

class Sm_BasicProducts_Model_System_Config_Source_OrderBy
{
	public function toOptionArray()
	{
		return array(
			array('value' => 'position',	'label' => Mage::helper('basicproducts')->__('Position')),
			array('value' => 'created_at', 	'label' => Mage::helper('basicproducts')->__('Date Created')),
			array('value' => 'name', 		'label' => Mage::helper('basicproducts')->__('Name')),
			array('value' => 'price', 		'label' => Mage::helper('basicproducts')->__('Price')),
			array('value' => 'random', 		'label' => Mage::helper('basicproducts')->__('Random')),
			array('value' => 'top_rating', 	'label' => Mage::helper('basicproducts')->__('Top Rating')),
			array('value' => 'most_reviewed',	'label' => Mage::helper('basicproducts')->__('Most Reviews')),
			array('value' => 'most_viewed',	'label' => Mage::helper('basicproducts')->__('Most Viewed')),
			array('value' => 'best_sales',	'label' => Mage::helper('basicproducts')->__('Most Selling')),
		);
	}
}
