<?php
/*------------------------------------------------------------------------
 # SM Dynamic Slideshow - Version 1.0.0
 # Copyright (c) 2013 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_DynamicSlideshow_Model_System_Config_Source_OrderBy
{
	public function toOptionArray()
	{
		return array(
			array('value' => 'position',	'label' => Mage::helper('dynamicslideshow')->__('Position')),
			array('value' => 'created_at', 	'label' => Mage::helper('dynamicslideshow')->__('Date Created')),
			array('value' => 'name', 		'label' => Mage::helper('dynamicslideshow')->__('Name')),
			array('value' => 'price', 		'label' => Mage::helper('dynamicslideshow')->__('Price')),
			array('value' => 'random', 		'label' => Mage::helper('dynamicslideshow')->__('Random')),
			array('value' => 'top_rating', 	'label' => Mage::helper('dynamicslideshow')->__('Top Rating')),
			array('value' => 'most_reviewed',	'label' => Mage::helper('dynamicslideshow')->__('Most Reviews')),
			array('value' => 'most_viewed',	'label' => Mage::helper('dynamicslideshow')->__('Most Visited')),
			array('value' => 'best_sales',	'label' => Mage::helper('dynamicslideshow')->__('Most Selling')),
		);
	}
}
