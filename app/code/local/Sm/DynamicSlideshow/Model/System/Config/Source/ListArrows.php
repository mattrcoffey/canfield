<?php
/*------------------------------------------------------------------------
 # SM Dynamic Slideshow - Version 1.0.0
 # Copyright (c) 2013 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_DynamicSlideshow_Model_System_Config_Source_ListArrows
{
	public function toOptionArray()
	{
		return array(
		array('value'=>'nexttobullets', 'label'=>Mage::helper('dynamicslideshow')->__('Next to Bullets')),
		array('value'=>'solo', 'label'=>Mage::helper('dynamicslideshow')->__('Vertical Centered')),
		//array('value'=>'none', 'label'=>Mage::helper('dynamicslideshow')->__('None')),
		);
	}
}
