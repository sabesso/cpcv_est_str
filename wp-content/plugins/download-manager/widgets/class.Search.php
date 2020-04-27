<?php

if(!class_exists('WPDM_SearchWidget')){

    class WPDM_SearchWidget extends WP_Widget {

        function __construct() {
            parent::__construct(false, 'WPDM Search');
        }

        function widget($args, $instance) {
            extract( $args );
            $title = apply_filters('widget_title', $instance['title']);
            $url = get_permalink($instance['rpage']);

            echo isset( $before_widget ) ? $before_widget : "";
            if ( $title ) echo isset($before_title) ? $before_title : "" . $title . (isset( $after_title ) ? $after_title : "");
            echo "<div class='w3eden'><form action='".$url."' class='wpdm-pro'>";
            echo "<div class='input-group'><input type=text class='form-control' name='search' /><span class='input-group-btn'><button class='btn btn-secondary'><i class='fas fa-search'></i> ".__( "Search" , "download-manager" )."</button></span></div><div class='clear'></div>";
            echo "</form></div>";
            echo isset( $after_widget ) ? $after_widget : "";
        }

        function update($new_instance, $old_instance) {
            $instance = $old_instance;
            $instance['title'] = strip_tags($new_instance['title']);
            $instance['rpage'] = strip_tags($new_instance['rpage']);
            return $instance;
        }

        function form($instance) {
            $title = isset($instance['title']) ? esc_attr($instance['title']) : "";
            $rpage = isset($instance['rpage']) ? esc_attr($instance['rpage']) : "";
            ?>
            <p>
                <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
            </p>
            <p>
                <?php echo __("Search Result Page","wpdmap").":<br/>".wp_dropdown_pages("selected={$rpage}&echo=0&name=".$this->get_field_name('rpage'));  ?>
            </p>
            <div style="border:1px solid #ccc;padding:15px;margin-bottom: 15px;font-size:8pt">
                <?php _e("Note: Create a page with short-code <code>[wpdm_search_result]</code> and select that page as search redult page", "wpdmap");?>
            </div>
            <?php
        }
    }

    add_action('widgets_init', function(){
        register_widget("WPDM_SearchWidget");
    });
}
