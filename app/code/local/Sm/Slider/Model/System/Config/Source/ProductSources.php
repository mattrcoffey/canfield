<?php
/*------------------------------------------------------------------------
 # SM Slider - Version 1.1
 # Copyright (c) 2013 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Slider_Model_System_Config_Source_ProductSources
{
	public function toOptionArray()
	{
		return array(
			array('value'=>'catalog',	'label'=>Mage::helper('slider')->__('Catalog')),
			array('value'=>'media',	'label'=>Mage::helper('slider')->__('Media')),
		);
	}
}
