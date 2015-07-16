<?php
/**
 * @package SmartAddons
 * @category SM Basic Products
 * @copyright (c) 2009-2012 YouTech Company. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * @author YouTech Company http://www.magentech.com
 */

class Sm_BasicProducts_IndexController extends Mage_Core_Controller_Front_Action{
    public function IndexAction() {
	  $this->loadLayout();
      $this->renderLayout();
    }
}