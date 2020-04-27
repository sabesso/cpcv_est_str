<?php
/**
 * Slider Responsive Premium Shortcode
 *
 * @access    public
 *
 * @return    Create Fontend Slider Output
 */
add_shortcode('awl-slider', 'awl_slider_responsive_shortcode');
function awl_slider_responsive_shortcode($post_id) {
	ob_start();
	wp_enqueue_script('awl-owl-carousel-js', SR_PLUGIN_URL .'js/awl-owl-carousel.js', array('jquery'));
	wp_enqueue_style('awl-owl-carousel-css', SR_PLUGIN_URL .'css/awl-owl-carousel.css');
	wp_enqueue_style('awl-owl-carousel-theme-css', SR_PLUGIN_URL .'css/awl-owl-theme.css');
	wp_enqueue_style('awl-owl-carousel-transitions-css', SR_PLUGIN_URL .'css/awl-owl-transitions.css');
	$allslidesetting = unserialize(base64_decode(get_post_meta( $post_id['id'], 'awl_sr_settings_'.$post_id['id'], true)));
	if(isset($allslidesetting['slides'])) $slides = $allslidesetting['slides']; else $slides = "1";
	if(isset($allslidesetting['srspeed'])) $srspeed = $allslidesetting['srspeed']; else $srspeed = "200";
	if(isset($allslidesetting['autoplay'])) $autoplay = $allslidesetting['autoplay']; else $autoplay = "true";
	if(isset($allslidesetting['navigation'])) $navigation = $allslidesetting['navigation']; else $navigation = "false";
	if(isset($allslidesetting['navigation_n'])) $navigation_n = $allslidesetting['navigation_n']; else $navigation_n = "Next";
	if(isset($allslidesetting['navigation_p'])) $navigation_p = $allslidesetting['navigation_p']; else $navigation_p = "Prev";
	if(isset($allslidesetting['auto_height'])) $auto_height = $allslidesetting['auto_height']; else $auto_height = "false";
	if(isset($allslidesetting['touch_slide'])) $touch_slide = $allslidesetting['touch_slide']; else $touch_slide = "true";
	if(isset($allslidesetting['show_title'])) $show_title = $allslidesetting['show_title']; else $show_title = "false";
	if(isset($allslidesetting['show_desc'])) $show_desc = $allslidesetting['show_desc']; else $show_desc = "false";
	if(isset($allslidesetting['show_link'])) $show_link = $allslidesetting['show_link']; else $show_link = "false";
	if(isset($allslidesetting['link_text'])) $link_text = $allslidesetting['link_text']; else $link_text = "Visit";
	if(isset($allslidesetting['link_on'])) $link_on = $allslidesetting['link_on']; else $link_on = "false";
	if(isset($allslidesetting['text_align'])) $text_align = $allslidesetting['text_align']; else $text_align = "center";
	if(isset($allslidesetting['custom-css'])) $custom_css = $allslidesetting['custom-css']; else $custom_css = "";
	$slider_id = $post_id['id'];
	?>
	<style>
	#sr-slider-<?php echo $slider_id; ?> .sr-image {
	  margin: 5px;
	}
	#sr-slider-<?php echo $slider_id; ?> .sr-image img {
	  display: block;
	  width: 100%;
	  height: auto;
	}
	#sr-slider-<?php echo $slider_id; ?> .sr-title {
		text-align: <?php echo $text_align; ?>;
		font-weight: bolder;
	}
	#sr-slider-<?php echo $slider_id; ?> .sr-desc {
		text-align: <?php echo $text_align; ?>;
	}
	#sr-slider-<?php echo $slider_id; ?> .sr-image .sr-link {
		text-align: <?php echo $text_align; ?>;
	}
	<?php echo $custom_css; ?>
	</style>
		<!-- HTML Script Part Start From Here-->
	<script>
	jQuery(document).ready(function() {
	   jQuery("#sr-slider-<?php echo $slider_id; ?>").owlCarousel({ 
			// Most important owl features
			items : <?php echo $slides; ?>,

			//Basic Speeds
			slideSpeed : <?php echo $srspeed; ?>,
			paginationSpeed : <?php echo $srspeed; ?>,
			rewindSpeed : <?php echo $srspeed*2; ?>,

			//Autoplay
			autoPlay : <?php echo $autoplay; ?>,
		 
			// Navigation
			navigation : <?php echo $navigation; ?>,
			navigationText : ["<?php echo $navigation_p; ?>","<?php echo $navigation_n; ?>"],
			rewindNav : true,
	 
			// Responsive 
			responsive: true,
			responsiveRefreshRate : 200,
			responsiveBaseWidth: window,
		 
			// CSS Styles
			baseClass : "owl-carousel",
			theme : "owl-theme",
		 
			//Auto height
			autoHeight : <?php echo $auto_height; ?>,
		 
			//Mouse Events
			dragBeforeAnimFinish : true,
			mouseDrag : <?php echo $touch_slide; ?>,
			touchDrag : <?php echo $touch_slide; ?>,
		 
			//Transitions
			transitionStyle : "fade",
		});
	});
	</script>
	<?php
	
    $allslides = array(  'p' => $post_id['id'], 'post_type' => 'slider_responsive', 'orderby' => 'ASC');
    $loop = new WP_Query( $allslides );
	while ( $loop->have_posts() ) : $loop->the_post();
	
		$post_id = get_the_ID();
		$allslidesetting = unserialize(base64_decode(get_post_meta( $post_id, 'awl_sr_settings_'.$post_id, true)));
		
		// start the sider contents
		?>
		<div id="sr-slider-<?php echo $slider_id; ?>">
			<?php
			if(isset($allslidesetting['slide-ids']) && count($allslidesetting['slide-ids']) > 0) {
				$count=0;
				
				foreach($allslidesetting['slide-ids'] as $attachment_id) {
					$slide_link =  $allslidesetting['slide-link'][$count];
					$thumb = wp_get_attachment_image_src($attachment_id, 'thumb', true);
					$thumbnail = wp_get_attachment_image_src($attachment_id, 'thumbnail', true);
					$medium = wp_get_attachment_image_src($attachment_id, 'medium', true);
					$large = wp_get_attachment_image_src($attachment_id, 'large', true);
					$postthumbnail = wp_get_attachment_image_src($attachment_id, 'post-thumbnail', true);
					
					$attachment_details = get_post( $attachment_id );
						$href = get_permalink( $attachment_details->ID );
						$src = $attachment_details->guid;
						$title = $attachment_details->post_title;
						$description = $attachment_details->post_content;
						if(isset($slidetext) == 'true') {
							if($slidetextopt == 'title') $text = $title;
							if($slidetextopt == 'desc') $text = $description;
						} else {
							$text = $title;
						}
					?>
					<div class="sr-image">
						<?php if($link_on == "true") { ?>
						<a href="<?php echo $slide_link; ?>" target="_new">
							<img class="lazyOwl" src="<?php echo $thumb[0]; ?>" alt="<?php echo $text; ?>">
						</a>
						<?php } else { ?>
							<img class="lazyOwl" src="<?php echo $thumb[0]; ?>" alt="<?php echo $text; ?>">
						<?php } ?>
						
						<!--Text Below Slide-->
						<?php if($show_title == "true") { ?>
						<div class="sr-title"><?php echo $title; ?></div>
						<?php } ?>
						
						<?php if($show_desc == "true") { ?>
						<div class="sr-desc"><?php echo $description; ?></div>
						<?php } ?>
						
						<?php if($show_link == "true" && $link_on == "false") { ?>
						<a href="<?php echo $slide_link; ?>" target="_new">
							<div class="sr-link"><?php echo $link_text; ?></div>
						</a>
						<?php } ?>
						<!--Text Below Slide-->
					</div>
					<?php
					$count++;
				}// end of attachment foreach
			} else {
				
				_e('Sorry! No slides added to the slider shortcode yet. Please add few slide into shortcode', sr_txt_dm);
				echo ": [awl-slider id=$post_id]";
			} // end of if esle of slides avaialble check into slider
			?>
		</div>
		<?php
	endwhile;
	wp_reset_query();
    return ob_get_clean();
}
?>