<?php
/*------------------------------------------------------------------------
 # SM Slider - Version 1.1
 # Copyright (c) 2013 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Slider_Model_System_Config_Source_ListControl
{
	public function toOptionArray()
	{	
		return array(
		array('value'=>'style', 'label'=>Mage::helper('slider')->__('Type 01')),
		array('value'=>'style2', 'label'=>Mage::helper('slider')->__('Type 02')),
		array('value'=>'style3', 'label'=>Mage::helper('slider')->__('Type 03')),
		);
	}
}
