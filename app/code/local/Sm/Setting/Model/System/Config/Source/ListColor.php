<?php
/*------------------------------------------------------------------------
 # SM setting - Version 1.0
 # Copyright (c) 2013 The YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Setting_Model_System_Config_Source_ListColor
{
	public function toOptionArray()
	{	
		return array(
		array('value'=>'default', 'label'=>Mage::helper('setting')->__('Default')),
		array('value'=>'cyan', 'label'=>Mage::helper('setting')->__('Cyan')),
		array('value'=>'green', 'label'=>Mage::helper('setting')->__('Green')),
		array('value'=>'grey', 'label'=>Mage::helper('setting')->__('Grey'))		
		);
	}
}
