<?php

class WPDM_Affiliate extends WP_Widget {
    /** constructor */
    function __construct() {
        parent::__construct(false, 'WPDM Pro Affiliate');
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {
        extract( $args );
        $instance['title'] = isset($instance['title']) ? $instance['title'] : '';
        $title = apply_filters('widget_title', $instance['title']);

        echo $before_widget;

        echo "<div class='w3eden'><div class='panel panel-primary card'>";
        echo "<div class='panel-heading card-header' style='font-size: 10pt'>Best File & Document Management Plugin</div><div class='panel-body' style='padding-bottom:0;background:#F2F2F2;'><a href='https://www.wpdownloadmanager.com/?affid={$title}'><img src='https://cdn.wpdownloadmanager.com/wp-content/uploads/2017/09/WordPress-Download-Manager-Intro-4.png' alt='WordPress Download Manager' /></a></div>";
        echo "<div class='panel-footer card-footer' style='line-height: 30px'><a class='pull-right btn btn-sm btn-danger' href='https://www.wpdownloadmanager.com/?affid={$title}'>Buy Now <i class='fa fa-angle-right'></i></a><span class='label label-success' style='font-size: 12pt;border-radius: 2px'>$59.00</span></div></div></div>";
        echo $after_widget;
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {
        $title = isset($instance['title'])?esc_attr($instance['title']):"";
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('WPDM Affiliate ID:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
            <em>It is your account <b>username</b> at www.wpdownloadmanager.com. You will get up to 20% from each sale referred by you</em>
        </p>
        <?php
    }

}

add_action('widgets_init', function (){
    register_widget("WPDM_Affiliate");
});
