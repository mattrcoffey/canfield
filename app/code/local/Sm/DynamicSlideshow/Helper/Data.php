<?php
/*------------------------------------------------------------------------
 # SM Dynamic Slideshow - Version 1.0.0
 # Copyright (c) 2013 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_DynamicSlideshow_Helper_Data extends Mage_Core_Helper_Abstract {
	public function __construct(){
		$this->defaults = array(
			/* General setting */
			'isenabled'		=> '1',
			'title' 		=> 'SM Dynamic Slideshow',
			/* product query */
			'button_style'  => 'round',
			'navigationArrows'  => 'nexttobullets',
			'shownavigation'        => '1',
			'shownavigation_when'   => 'alway',
			'navigationhalign'      => 'center',
			'navigationvalign'		=> 'bottom',
			'timer_show'            => '1',
			'timer_position'		=> 'top',
			'play'         => '1',
			'pause'         => '1',
			'interval'      => '3000',
			'slider1'		=> '&lt;li data-transition=&quot;random&quot; data-slotamount=&quot;5&quot; data-link=&quot;http://www.google.de&quot; data-masterspeed=&quot;1000&quot;&gt;
	&lt;img src=&quot;images/dynamicslideshow/slides/image1.jpg&quot;&gt;
	&lt;div class=&quot;caption lfb big_white&quot;  data-x=&quot;400&quot; data-y=&quot;100&quot; data-speed=&quot;900&quot; data-start=&quot;1700&quot; data-easing=&quot;easeOutBack&quot;&gt;Kickstart Your Website&lt;/div&gt;
	&lt;div class=&quot;caption lft big_orange&quot;  data-x=&quot;400&quot; data-y=&quot;137&quot; data-speed=&quot;900&quot; data-start=&quot;1900&quot; data-easing=&quot;easeOutBack&quot;&gt;&lt;span style=&quot;font-weight:normal;&quot;&gt;With&lt;/span&gt;&lt;em&gt; Slider Revolution&lt;/em&gt;&lt;/div&gt;
	&lt;div class=&quot;caption lfr medium_grey&quot;  data-x=&quot;510&quot; data-y=&quot;210&quot; data-speed=&quot;300&quot; data-start=&quot;2500&quot; data-easing=&quot;easeOutExpo&quot;&gt;Unlimited Transitions&lt;/div&gt;
	&lt;div class=&quot;caption sfl&quot; data-x=&quot;510&quot; data-y=&quot;250&quot; data-speed=&quot;300&quot; data-start=&quot;2600&quot; data-easing=&quot;easeOutExpo&quot;&gt;&lt;img src=&quot;images/dynamicslideshow/slides/check.png&quot;&gt;&lt;/div&gt;
	&lt;div class=&quot;caption lfr small_text&quot;  data-x=&quot;560&quot; data-y=&quot;248&quot; data-speed=&quot;300&quot; data-start=&quot;2600&quot; data-easing=&quot;easeOutExpo&quot;&gt;Sliding, Fading, Slots, Box Transitions&lt;br/&gt;
	&lt;span style=&quot;color: #ffe400;&quot;&gt;SLIDER REVOLUTION&lt;/span&gt; has it all!&lt;/div&gt;
	&lt;div class=&quot;caption sfl&quot; data-x=&quot;510&quot; data-y=&quot;300&quot; data-speed=&quot;300&quot; data-start=&quot;2900&quot; data-easing=&quot;easeOutExpo&quot;&gt;&lt;img src=&quot;images/dynamicslideshow/slides/check.png&quot;&gt;&lt;/div&gt;
	&lt;div class=&quot;caption lfr small_text&quot;  data-x=&quot;560&quot; data-y=&quot;307&quot; data-speed=&quot;300&quot; data-start=&quot;2900&quot; data-easing=&quot;easeOutExpo&quot;&gt;Responsive and Mobile Optimized&lt;/div&gt;
	&lt;div class=&quot;caption sfl&quot; data-x=&quot;510&quot; data-y=&quot;350&quot; data-speed=&quot;300&quot; data-start=&quot;3200&quot; data-easing=&quot;easeOutExpo&quot;&gt;&lt;img src=&quot;images/dynamicslideshow/slides/check.png&quot;&gt;&lt;/div&gt;
	&lt;div class=&quot;caption lfr small_text&quot;  data-x=&quot;560&quot; data-y=&quot;348&quot; data-speed=&quot;300&quot; data-start=&quot;3200&quot; data-easing=&quot;easeOutExpo&quot;&gt;Customizable Navigation&lt;br/&gt;Arrows, Bullets, Thumbs&lt;/div&gt;
&lt;/li&gt;',
			'slider2'		=> '&lt;li data-transition=&quot;random&quot; data-slotamount=&quot;20&quot;&gt;
	&lt;img src=&quot;images/dynamicslideshow/slides/image4.jpg&quot;  &gt;
	&lt;div class=&quot;caption lft big_white&quot;  data-x=&quot;100&quot; data-y=&quot;70&quot; data-speed=&quot;1200&quot; data-start=&quot;800&quot; data-easing=&quot;easeOutBack&quot;&gt;American Muscle Car&lt;/div&gt;
	&lt;div class=&quot;caption lfl medium_grey&quot;  data-x=&quot;270&quot; data-y=&quot;180&quot; data-speed=&quot;900&quot; data-start=&quot;1200&quot; data-easing=&quot;easeOutExpo&quot;&gt;4.6l V8 Engine&lt;/div&gt;
	&lt;div class=&quot;caption lfr medium_grey&quot;  data-x=&quot;480&quot; data-y=&quot;330&quot; data-speed=&quot;900&quot; data-start=&quot;1400&quot; data-easing=&quot;easeOutExpo&quot;&gt;20 inch Rims&lt;/div&gt;
&lt;/li&gt;',
			'slider3'		=> '&lt;li data-transition=&quot;random&quot; data-slotamount=&quot;14&quot; data-delay=&quot;25000&quot;&gt;
	&lt;img src=&quot;images/dynamicslideshow/slides/image6.jpg&quot;  &gt;
	&lt;div class=&quot;caption lft boxshadow&quot; data-x=&quot;70&quot; data-y=&quot;120&quot; data-speed=&quot;900&quot; data-start=&quot;500&quot; data-easing=&quot;easeOutBack&quot;&gt;&lt;iframe src=&quot;http://www.youtube.com/embed/YHWkro9-e9Q?hd=1&amp;wmode=opaque&amp;controls=1&amp;showinfo=0&quot; width=&quot;460&quot; height=&quot;259&quot;&gt;&lt;/iframe&gt;&lt;/div&gt;
	&lt;div class=&quot;caption sft big_black&quot;  data-x=&quot;550&quot; data-y=&quot;120&quot; data-speed=&quot;300&quot; data-start=&quot;1200&quot; data-easing=&quot;easeOutExpo&quot;&gt;Video Support&lt;/div&gt;
	&lt;div class=&quot;caption sft big_white&quot;  data-x=&quot;550&quot; data-y=&quot;157&quot; data-speed=&quot;300&quot; data-start=&quot;1300&quot; data-easing=&quot;easeOutExpo&quot;&gt;Youtube Example&lt;/div&gt;
	&lt;div class=&quot;caption lfb medium_grey&quot;  data-x=&quot;550&quot; data-y=&quot;210&quot; data-speed=&quot;300&quot; data-start=&quot;1400&quot; data-easing=&quot;easeOutExpo&quot;&gt;You can easily add&lt;/div&gt;
	&lt;div class=&quot;caption lfb medium_grey&quot;  data-x=&quot;550&quot; data-y=&quot;234&quot; data-speed=&quot;300&quot; data-start=&quot;1500&quot; data-easing=&quot;easeOutExpo&quot;&gt;Vimeo and Youtube Videos&lt;/div&gt;
	&lt;div class=&quot;caption lfb medium_grey&quot;  data-x=&quot;550&quot; data-y=&quot;258&quot; data-speed=&quot;300&quot; data-start=&quot;1600&quot; data-easing=&quot;easeOutExpo&quot;&gt;to your Slides&lt;/div&gt;
&lt;/li&gt;',
			'slider4'		=> '&lt;li data-transition=&quot;random&quot; data-slotamount=&quot;20&quot;&gt;
	&lt;img src=&quot;images/dynamicslideshow/slides/image5.jpg&quot;  &gt;
	&lt;div class=&quot;caption lft&quot; data-x=&quot;330&quot; data-y=&quot;156&quot; data-speed=&quot;900&quot; data-start=&quot;500&quot; data-easing=&quot;easeOutExpo&quot;&gt;&lt;img src=&quot;images/dynamicslideshow/slides/burger.png&quot;&gt;&lt;/div&gt;
	&lt;div class=&quot;caption lft&quot; data-x=&quot;450&quot; data-y=&quot;18&quot; data-speed=&quot;900&quot; data-start=&quot;700&quot; data-easing=&quot;easeOutExpo&quot;&gt;&lt;img src=&quot;images/dynamicslideshow/slides/coke.png&quot;&gt;&lt;/div&gt;
	&lt;div class=&quot;caption sft big_orange&quot;  data-x=&quot;80&quot; data-y=&quot;340&quot; data-speed=&quot;900&quot; data-start=&quot;1200&quot; data-easing=&quot;easeOutBack&quot;&gt;Serving you the Ultimate Slider&lt;/div&gt;
	&lt;div class=&quot;caption sft medium_text black&quot;  data-x=&quot;85&quot; data-y=&quot;385&quot; data-speed=&quot;900&quot; data-start=&quot;1300&quot; data-easing=&quot;easeOutBack&quot;&gt;Get Slider Revolution today!&lt;/div&gt;
&lt;/li&gt;',
			'slider5'		=> '',
			'slider6'		=> '',
			'slider7'		=> '',
			'slider8'		=> '',
			'slider9'		=> '',
			'slider10'		=> '',
			/*advanced*/
			'include_jquery'	=> '1',
			'pretext'			=> '',
			'posttext'			=> ''
		);
	}

	function get($attributes=array())
	{
		$data = $this->defaults;
		$general 					= Mage::getStoreConfig("dynamicslideshow_cfg/general");
		$product_selection 			= Mage::getStoreConfig("dynamicslideshow_cfg/product_selection");
		$module_setting 			= Mage::getStoreConfig("dynamicslideshow_cfg/module_setting");
		$advanced 					= Mage::getStoreConfig("dynamicslideshow_cfg/advanced");
		if (!is_array($attributes)) {
			$attributes = array($attributes);
		}
		if (is_array($general))					$data = array_merge($data, $general);
		if (is_array($product_selection)) 		$data = array_merge($data, $product_selection);
		if (is_array($module_setting)) 		    $data = array_merge($data, $module_setting);
		if (is_array($advanced)) 				$data = array_merge($data, $advanced);

		return array_merge($data, $attributes);;
	}
}
?>