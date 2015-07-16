<?php
/*------------------------------------------------------------------------
 # SM Slider - Version 1.1
 # Copyright (c) 2013 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Slider_Model_System_Config_Source_ListAnchor
{
	public function toOptionArray()
	{	
		return array(
		array('value'=>'top', 'label'=>Mage::helper('slider')->__('Top')),
		array('value'=>'bottom', 'label'=>Mage::helper('slider')->__('Bottom')),
		array('value'=>'middle', 'label'=>Mage::helper('slider')->__('Middle')),
		);
	}
}
