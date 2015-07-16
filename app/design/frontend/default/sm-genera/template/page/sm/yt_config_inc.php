<?php
/*------------------------------------------------------------------------
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

/*--- BEGIN: Theme param config ---*/
$_params = new ThemeParameter();
$_config = array(
	'showcpanel'				=>'1',
	'layoutstyle'				=>'1',
	'body_font_size'			=>'12px',
	'body_font_family'			=>'arial',
	'color'						=>'default',
	'menu_styles'				=>'1',
	'body_background_color'		=>'#ffffff',
	'body_background_image'		=>'14',
	'responsive_menu'           =>'1',
	'body_link_color'			=>'#686868',
	'body_text_color'			=>'#686868',
);
$attributes = array();
if( Mage::getConfig()->getNode('modules/Sm_Setting') ){
	$_config = Mage::helper('setting/data')->get($attributes);
}
// enable Cpanel
$_params->set('showCpanel',$_config['showcpanel']);//image: Image; text: Text


if($_config['layout_styles'] == 2){
	$layoutstyle = '2';
}else{
	$layoutstyle = '1';
}

$_params->set('layoutstyle',$layoutstyle);



$_params->set('body-bgimage', 'pattern'.$_config['body_background_image']);


// Fontsize
$_params->set('fontsize',$_config['body_font_size']);
// font family
$_params->set('font_name',$_config['body_font_family']);
// Google web font
$_params->set('googleWebFont',$_config['google_font']);
// Respnsive menu
$_params->set('responsive_menu',$_config['responsive_menu']);
// Google WebFont Targets
$_params->set('googleWebFontTargets',$_config['google_font_targets']);
// Theme color
$_params->set('sitestyle',$_config['color']);
// Theme menu
if($_config['menu_styles'] ==1) {	$menu_style='mega';}	
if($_config['menu_styles'] ==2) {	$menu_style='css';}	
$_params->set('menustyle',$menu_style);
// Body background-color
$_params->set('bgcolor', $_config['body_background_color']);
// Body background-image pattern13
//$_params->set('body-bgimage', 'pattern'.$_config['body_background_image']);
// Body link color
$_params->set('linkcolor', $_config['body_link_color']);
// Body text color
$_params->set('textcolor', $_config['body_text_color']);

$_params->set('yt_arraydisplaylist', array('4','5','16','17','18'));
/*--- END: Theme param config ---*/

// Array param for cookie
$paramscookie = array(
				  'direction', 
				  'fontsize',
				  'font_name',
				  'sitestyle',
				  'layoutstyle',				 
				  'bgcolor',
				  'body-bgimage',
				  'linkcolor',
				  'textcolor',				  
				  'menustyle',
				  'shownotice',				  
				  'googleWebFont'
);
global $var_yttheme;
$var_yttheme = new YtTheme('sm_setting', $_params, $paramscookie);
