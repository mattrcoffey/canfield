<?php
/*------------------------------------------------------------------------
 # SM Dynamic Slideshow - Version 1.0.0
 # Copyright (c) 2013 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_DynamicSlideshow_Model_System_Config_Source_ListStyle
{
	public function toOptionArray()
	{
		return array(
		array('value'=>'round', 'label'=>Mage::helper('dynamicslideshow')->__('Round')),
		array('value'=>'square', 'label'=>Mage::helper('dynamicslideshow')->__('Square')),
		array('value'=>'navbar', 'label'=>Mage::helper('dynamicslideshow')->__('Navbar')),
		);
	}
}
