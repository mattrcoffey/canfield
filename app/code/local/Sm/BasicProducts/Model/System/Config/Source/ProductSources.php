<?php
/**
 * @package SmartAddons
 * @category SM Basic Products
 * @copyright (c) 2009-2012 YouTech Company. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * @author YouTech Company http://www.magentech.com
 */

class Sm_BasicProducts_Model_System_Config_Source_ProductSources
{
	public function toOptionArray()
	{
		return array(
			array('value'=>'catalog',	'label'=>Mage::helper('basicproducts')->__('Catalog')),
        	array('value'=>'product',	'label'=>Mage::helper('basicproducts')->__('Product'))
		);
	}
}
