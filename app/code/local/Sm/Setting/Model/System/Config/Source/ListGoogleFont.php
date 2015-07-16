<?php
/*------------------------------------------------------------------------
 # SM setting - Version 1.1
 # Copyright (c) 2013 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Setting_Model_System_Config_Source_ListGoogleFont
{
	public function toOptionArray()
	{	
		return array(
			array('value'=>'', 'label'=>Mage::helper('setting')->__('No select')),
			array('value'=>'Roboto Condensed', 'label'=>Mage::helper('setting')->__('Roboto Condensed')),
			array('value'=>'Lato', 'label'=>Mage::helper('setting')->__('Lato')),			
			array('value'=>'Anton', 'label'=>Mage::helper('setting')->__('Anton')),
			array('value'=>'Questrial', 'label'=>Mage::helper('setting')->__('Questrial')),
			array('value'=>'Kameron', 'label'=>Mage::helper('setting')->__('Kameron')),
			array('value'=>'Oswald', 'label'=>Mage::helper('setting')->__('Oswald')),
			array('value'=>'Open Sans', 'label'=>Mage::helper('setting')->__('Open Sans')),
			array('value'=>'BenchNine', 'label'=>Mage::helper('setting')->__('BenchNine')),
			array('value'=>'Droid Sans', 'label'=>Mage::helper('setting')->__('Droid Sans')),
			array('value'=>'Droid Serif', 'label'=>Mage::helper('setting')->__('Droid Serif')),
			array('value'=>'PT Sans', 'label'=>Mage::helper('setting')->__('PT Sans')),
			array('value'=>'Vollkorn', 'label'=>Mage::helper('setting')->__('Vollkorn')),
			array('value'=>'Ubuntu', 'label'=>Mage::helper('setting')->__('Ubuntu')),
			array('value'=>'Neucha', 'label'=>Mage::helper('setting')->__('Neucha')),
			array('value'=>'Cuprum', 'label'=>Mage::helper('setting')->__('Cuprum'))
		);
	}
}
