<?php

wp_enqueue_style('awl-bootstrap-css', SR_PLUGIN_URL .'css/bootstrap.css');
wp_enqueue_script('awl-bootstrap-js', SR_PLUGIN_URL .'js/bootstrap.js', array('jquery'), '' , true);
wp_enqueue_style('thickbox');
wp_enqueue_style('fp-bootstrap-css', SR_PLUGIN_URL.'featured-plugins/css/bootstrap.css');
wp_enqueue_style('fp-smartech-css', SR_PLUGIN_URL.'featured-plugins/css/smartech.css');
wp_enqueue_style('fp-feature-plugins', SR_PLUGIN_URL.'featured-plugins/css/feature-plugins.css');	
wp_enqueue_script('jquery');
wp_enqueue_script('fp-media-uploads',SR_PLUGIN_URL.'js/featured-plugins/acl-media-upload-script.js',array('media-upload','thickbox','jquery')); 
wp_enqueue_script('fp-bootstrap-min-js',SR_PLUGIN_URL.'js/featured-plugins/bootstrap.min.js');

?>
<style>
.col-md-12{
    background-color: #073b4c !important;
    color: #ffffff !important;
    font-family: initial !important;
    font-size: 18px !important;
    width: 100% !important;
}
.welcome-panel p {
    color: #ecf1f1 !important;
}
.desc p {
    color: #ffffff !important;
    font-size: 19px !important;
    line-height: 1.5 !important;
}
.thickbox {
    color: #177EE5 !important;
	font-size: 22px;
}
.authors a{
	 color: #F0A81E !important;
}
.hndle span {
    color: #ffffff !important;
    font-family: georgia !important;
    line-height: 1.4 !important;
    margin: 0 !important;
    padding: 8px 12px !important;
}
<!--buy buttons setting-->
.btn-info {
    height: 20%;
    width: 10%
</style>
<?php
		include( ABSPATH . "wp-admin/includes/plugin-install.php" );
		global $tabs, $tab, $paged, $type, $term;
		$tabs = array();
		$tab = "search";
		$per_page = 20;
		$args = array
		(
			"author"=> "awordpresslife",
			"page" => $paged,
			"per_page" => $per_page,
			"fields" => array( "last_updated" => true, "active_installs" => true, "downloaded" => true, "icons" => true, ),
			"locale" => get_locale(),
		);
		$arges = apply_filters( "install_plugins_table_api_args_$tab", $args );
		$api = plugins_api( "query_plugins", $arges );
		$item = $api->plugins;
		if(!function_exists("wp_star_rating"))
		{
			function wp_star_rating( $args = array() )
			{
				$defaults = array(
						'rating' => 0,
						'type' => 'rating',
						'number' => 0,
				);
				$r = wp_parse_args( $args, $defaults );
		
				// Non-english decimal places when the $rating is coming from a string
				$rating = str_replace( ',', '.', $r['rating'] );
		
				// Convert Percentage to star rating, 0..5 in .5 increments
				if ( 'percent' == $r['type'] ) {
					$rating = round( $rating / 10, 0 ) / 2;
				}
		
				// Calculate the number of each type of star needed
				$full_stars = floor( $rating );
				$half_stars = ceil( $rating - $full_stars );
				$empty_stars = 5 - $full_stars - $half_stars;
		
				if ( $r['number'] ) {
					/* translators: 1: The rating, 2: The number of ratings */
					$format = _n( '%1$s rating based on %2$s rating', '%1$s rating based on %2$s ratings', $r['number'] );
					$title = sprintf( $format, number_format_i18n( $rating, 1 ), number_format_i18n( $r['number'] ) );
				} else {
					/* translators: 1: The rating */
					$title = sprintf( __( '%s rating' ), number_format_i18n( $rating, 1 ) );
				}
		
				echo '<div class="star-rating" title="' . esc_attr( $title ) . '">';
				echo '<span class="screen-reader-text">' . $title . '</span>';
				echo str_repeat( '<div class="star star-full"></div>', $full_stars );
				echo str_repeat( '<div class="star star-half"></div>', $half_stars );
				echo str_repeat( '<div class="star star-empty"></div>', $empty_stars);
				echo '</div>';
			}
		}
	?>
	

<div class="wrap">
	<h2 class="hndle text-center" style="background-color: #073b4c;"><span><?php _e("Get More Free WordPress Plugin From A WP Life", 'AWP_LIFE'); ?></span></h2>	
	<div id="welcome-panel" class="welcome-panel">
		<div class="container">
			<div class="row">
				<table class="form-table2">
					<tr class="radio-span" style="border-bottom:none;">
						<td><?php
							foreach ((array) $item as $plugin) 
							{
								if (is_object( $plugin))
								{
									$plugin = (array) $plugin;
									
								}
								if (!empty($plugin["icons"]["svg"]))
								{
									$plugin_icon_url = $plugin["icons"]["svg"];
								} 
								elseif (!empty( $plugin["icons"]["2x"])) 
								{
									$plugin_icon_url = $plugin["icons"]["2x"];
								} 
								elseif (!empty( $plugin["icons"]["1x"]))
								{
									$plugin_icon_url = $plugin["icons"]["1x"];
								} 
								else 
								{
									$plugin_icon_url = $plugin["icons"]["default"];
								}
								$plugins_allowedtags = array
								(
									"a" => array( "href" => array(),"title" => array(), "target" => array() ),
									"abbr" => array( "title" => array() ),"acronym" => array( "title" => array() ),
									"code" => array(), "pre" => array(), "em" => array(),"strong" => array(),
									"ul" => array(), "ol" => array(), "li" => array(), "p" => array(), "br" => array()
								);
								$title = wp_kses($plugin["name"], $plugins_allowedtags);
								$description = strip_tags($plugin["short_description"]);
								$author = wp_kses($plugin["author"], $plugins_allowedtags);
								$version = wp_kses($plugin["version"], $plugins_allowedtags);
								$name = strip_tags( $title . " " . $version );
								$details_link   = self_admin_url( "plugin-install.php?tab=plugin-information&amp;plugin=" . $plugin["slug"] .
								"&amp;TB_iframe=true&amp;width=600&amp;height=550" );
								
								/* translators: 1: Plugin name and version. */
								$action_links[] = '<a href="' . esc_url( $details_link ) . '" class="thickbox" aria-label="' . esc_attr( sprintf("More information about %s", $name ) ) . '" data-title="' . esc_attr( $name ) . '">' . __( 'More Details' ) . '</a>';
								$action_links = array();
								if (current_user_can( "install_plugins") || current_user_can("update_plugins"))
								{
									$status = install_plugin_install_status( $plugin );
									switch ($status["status"])
									{
										case "install":
											if ( $status["url"] )
											{
												/* translators: 1: Plugin name and version. */
												$action_links[] = '<a class="install-now button" href="' . $status['url'] . '" aria-label="' . esc_attr( sprintf("Install %s now", $name ) ) . '">' . __( 'Install Now' ) . '</a>';
											}
										break;
										case "update_available":
											if ($status["url"])
											{
												/* translators: 1: Plugin name and version */
												$action_links[] = '<a class="button" href="' . $status['url'] . '" aria-label="' . esc_attr( sprintf( "Update %s now", $name ) ) . '">' . __( 'Update Now' ) . '</a>';
											}
										break;
										case "latest_installed":
										case "newer_installed":
											$action_links[] = '<span class="button button-disabled" title="' . esc_attr__( "This plugin is already installed and is up to date" ) . ' ">' . _x( 'Installed', 'plugin' ) . '</span>';
										break;
									}
								}
								?>
								<div class="col-md-12">
									<br>
									<div class="col-md-2">
										<div class="text-center">
											<a href="<?php echo esc_url( $details_link ); ?>">
												<img class="custom_icon" src="<?php echo esc_attr( $plugin_icon_url ) ?>" />
											</a>
										</div><br>
										<div class="text-center">
											<ul>
												<?php
													if ($action_links)
													{
														echo implode("", $action_links);
													}
														?>		
											</ul>
										</div>
									</div>	
									
									<div class="col-md-8">
										<div class="text-center plugin-div-inner-content">
											<div class="name column-name">
												<h4>
													<a href="<?php echo esc_url( $details_link ); ?>" class="thickbox"><?php echo $title; ?></a>
												</h4>
											</div>
											<div class="desc column-description">
												<p>
													<?php echo $description; ?>
												</p>
												<p class="authors">
													<cite>
														<?php _e( "By",'AWP_LIFE'); echo $author;?>
													</cite>
												</p>
											</div>
										</div>
									</div>
									<style>
										table .column-rating, table .column-visible, table .vers {
											text-align: center !important;
										}
									</style>
									<div class="col-md-2 text-center">
										<div class="plugin-div-inner-content">
											<div class="name column-name">
												<div class="vers column-rating">
													<?php wp_star_rating( array( "rating" => $plugin["rating"], "type" => "percent", "number" => $plugin["num_ratings"] ) ); ?>
													<span class="num-ratings">
														(<?php echo number_format_i18n( $plugin["num_ratings"] ); ?>)
													</span>
												</div>
												<div class="column-updated">
													<strong><?php _e("Last Updated:"); ?></strong> <span title="<?php echo esc_attr($plugin["last_updated"]); ?>">
														<?php printf("%s ago", human_time_diff(strtotime($plugin["last_updated"]))); ?>
													</span>
												</div>
												<div class="column-downloaded">
													<?php echo sprintf( _n("%s download", "%s downloads", $plugin["downloaded"]), number_format_i18n($plugin["downloaded"])); ?>
												</div>
											</div>
										</div>
									</div>
									<br>
									<div class="row">
										<div class="col-md-12 text-center custom-buy-button">
											<a href="https://awplife.com/wordpress-plugins/" target="_blank" class="btn btn-info btn-circle btn-xl"><i class="glyphicon glyphicon-shopping-cart"></i> Buy Now</a>
										</div>
									</div>
									<hr>
								</div>
								<hr>
										<?php
							}
										?>	
						</td>
					</tr>
				</table>
				<hr>
			</div> 
		</div>
	</div>
</div>
 
