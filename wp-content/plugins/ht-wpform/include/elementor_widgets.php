<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HTwpform_Elementor_Widget extends Widget_Base {

    public function get_name() {
        return 'htwpform-addons';
    }
    
    public function get_title() {
        return __( 'HT WPForm', 'ht-wpform' );
    }

    public function get_icon() {
        return 'eicon-mail';
    }

    public function get_categories() {
        return [ 'general' ];
    }

    public function htwpform_wpforms_forms(){
        $formlist = array();
        $forms_args = array( 'posts_per_page' => -1, 'post_type'=> 'wpforms' );
        $forms = get_posts( $forms_args );
        if( $forms ){
            foreach ( $forms as $form ){
                $formlist[$form->ID] = $form->post_title;
            }
        }else{
            $formlist['0'] = __('Form not found','ht-wpform');
        }
        return $formlist;
    }

    protected function _register_controls() {

        $this->start_controls_section(
            'wpforms_content',
            [
                'label' => __( 'WP Form', 'ht-wpform' ),
            ]
        );
            $this->add_control(
                'contact_form_list',
                [
                    'label'             => __( 'Select Form', 'ht-wpform' ),
                    'type'              => Controls_Manager::SELECT,
                    'label_block'       => true,
                    'options'           => $this->htwpform_wpforms_forms(),
                    'default'           => '0',
                ]
            );

            $this->add_control(
                'show_form_title',
                [
                    'label'                 => __( 'Title', 'ht-wpform' ),
                    'type'                  => Controls_Manager::SWITCHER,
                    'default'               => 'no',
                    'label_on'              => __( 'Show', 'ht-wpform' ),
                    'label_off'             => __( 'Hide', 'ht-wpform' ),
                    'return_value'          => 'yes',
                ]
            );

            $this->add_control(
                'show_form_description',
                [
                    'label'                 => __( 'Description', 'ht-wpform' ),
                    'type'                  => Controls_Manager::SWITCHER,
                    'default'               => 'no',
                    'label_on'              => __( 'Show', 'ht-wpform' ),
                    'label_off'             => __( 'Hide', 'ht-wpform' ),
                    'return_value'          => 'yes',
                ]
            );

        $this->end_controls_section();

        // Style Title tab section
        $this->start_controls_section(
            'wpforms_title_style_section',
            [
                'label' => __( 'Title', 'ht-wpform' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition'=>[
                    'show_form_title'=>'yes',
                ],
            ]
        );
            
            $this->add_control(
                'wpforms_title_color',
                [
                    'label' => __( 'Color', 'ht-wpform' ),
                    'type' => Controls_Manager::COLOR,
                    'scheme' => [
                        'type' => Scheme_Color::get_type(),
                        'value' => Scheme_Color::COLOR_1,
                    ],
                    'default' => '#212529',
                    'selectors' => [
                        '{{WRAPPER}} .wpforms-container .wpforms-title' => 'color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name' => 'wpforms_title_typography',
                    'scheme' => Scheme_Typography::TYPOGRAPHY_4,
                    'selector' => '{{WRAPPER}} .wpforms-container .wpforms-title',
                ]
            );

            $this->add_responsive_control(
                'wpforms_title_padding',
                [
                    'label' => __( 'Padding', 'ht-wpform' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'selectors' => [
                        '{{WRAPPER}} .wpforms-container .wpforms-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                    'separator' =>'before',
                ]
            );

            $this->add_responsive_control(
                'wpforms_title_margin',
                [
                    'label' => __( 'Margin', 'ht-wpform' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'selectors' => [
                        '{{WRAPPER}} .wpforms-container .wpforms-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Border::get_type(),
                [
                    'name' => 'wpforms_title_border',
                    'label' => __( 'Border', 'ht-wpform' ),
                    'selector' => '{{WRAPPER}} .wpforms-container .wpforms-title',
                ]
            );

            $this->add_responsive_control(
                'wpforms_title_border_radius',
                [
                    'label' => esc_html__( 'Border Radius', 'ht-wpform' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'selectors' => [
                        '{{WRAPPER}} .wpforms-container .wpforms-title' => 'border-radius: {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px;',
                    ],
                ]
            );

        $this->end_controls_section(); // Form title style

        // Style Description tab section
        $this->start_controls_section(
            'wpforms_description_style_section',
            [
                'label' => __( 'Description', 'ht-wpform' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition'=>[
                    'show_form_description'=>'yes',
                ],
            ]
        );
            
            $this->add_control(
                'wpforms_description_color',
                [
                    'label' => __( 'Color', 'ht-wpform' ),
                    'type' => Controls_Manager::COLOR,
                    'scheme' => [
                        'type' => Scheme_Color::get_type(),
                        'value' => Scheme_Color::COLOR_1,
                    ],
                    'default' => '#212529',
                    'selectors' => [
                        '{{WRAPPER}} .wpforms-container .wpforms-description' => 'color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name' => 'wpforms_description_typography',
                    'scheme' => Scheme_Typography::TYPOGRAPHY_4,
                    'selector' => '{{WRAPPER}} .wpforms-container .wpforms-description',
                ]
            );

            $this->add_responsive_control(
                'wpforms_description_padding',
                [
                    'label' => __( 'Padding', 'ht-wpform' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'selectors' => [
                        '{{WRAPPER}} .wpforms-container .wpforms-description' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                    'separator' =>'before',
                ]
            );

            $this->add_responsive_control(
                'wpforms_description_margin',
                [
                    'label' => __( 'Margin', 'ht-wpform' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'selectors' => [
                        '{{WRAPPER}} .wpforms-container .wpforms-description' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Border::get_type(),
                [
                    'name' => 'wpforms_description_border',
                    'label' => __( 'Border', 'ht-wpform' ),
                    'selector' => '{{WRAPPER}} .wpforms-container .wpforms-description',
                ]
            );

            $this->add_responsive_control(
                'wpforms_description_border_radius',
                [
                    'label' => esc_html__( 'Border Radius', 'ht-wpform' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'selectors' => [
                        '{{WRAPPER}} .wpforms-container .wpforms-description' => 'border-radius: {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px;',
                    ],
                ]
            );

        $this->end_controls_section(); // Form Description style


        // Label style tab start
        $this->start_controls_section(
            'wpforms_label_style',
            [
                'label'     => __( 'Label', 'ht-wpform' ),
                'tab'       => Controls_Manager::TAB_STYLE,
            ]
        );

            $this->add_control(
                'wpforms_label_background',
                [
                    'label'     => __( 'Background', 'ht-wpform' ),
                    'type'      => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .wpforms-form .wpforms-field-label'   => 'background-color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_control(
                'wpforms_label_text_color',
                [
                    'label'     => __( 'Color', 'ht-wpform' ),
                    'type'      => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .wpforms-form .wpforms-field-label'   => 'color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name' => 'wpforms_label_typography',
                    'scheme' => Scheme_Typography::TYPOGRAPHY_1,
                    'selector' => '{{WRAPPER}} .wpforms-form .wpforms-field-label',
                ]
            );

            $this->add_group_control(
                Group_Control_Border::get_type(),
                [
                    'name' => 'wpforms_label_border',
                    'label' => __( 'Border', 'ht-wpform' ),
                    'selector' => '{{WRAPPER}} .wpforms-form .wpforms-field-label',
                ]
            );

            $this->add_responsive_control(
                'wpforms_label_border_radius',
                [
                    'label' => esc_html__( 'Border Radius', 'ht-wpform' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'selectors' => [
                        '{{WRAPPER}} .wpforms-form .wpforms-field-label' => 'border-radius: {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px;',
                    ],
                    'separator' =>'before',
                ]
            );

            $this->add_responsive_control(
                'wpforms_label_padding',
                [
                    'label' => __( 'Padding', 'ht-wpform' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'selectors' => [
                        '{{WRAPPER}} .wpforms-form .wpforms-field-label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                    'separator' =>'before',
                ]
            );

            $this->add_responsive_control(
                'wpforms_label_margin',
                [
                    'label' => __( 'Margin', 'ht-wpform' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'selectors' => [
                        '{{WRAPPER}} .wpforms-form .wpforms-field-label' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                    'separator' =>'after',
                ]
            );

            $this->add_control(
                'sub_label_popover',
                [
                    'label' => __( 'Sub Lavel', 'ht-wpform' ),
                    'type' => Controls_Manager::POPOVER_TOGGLE,
                ]
            );

            $this->start_popover();
                
                $this->add_control(
                    'wpforms_sublabel_color',
                    [
                        'label'     => __( 'Sub Label Color', 'ht-wpform' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [
                            '{{WRAPPER}} .wpforms-form .wpforms-field-sublabel'   => 'color: {{VALUE}};',
                        ],
                    ]
                );

                $this->add_group_control(
                    Group_Control_Typography::get_type(),
                    [
                        'name' => 'wpforms_sublabel_typography',
                        'scheme' => Scheme_Typography::TYPOGRAPHY_4,
                        'selector' => '{{WRAPPER}} .wpforms-form .wpforms-field-sublabel',
                    ]
                );

            $this->end_popover();

            $this->add_control(
                'wpforms_label_required_color',
                [
                    'label'     => __( 'Required Label Color', 'ht-wpform' ),
                    'type'      => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .wpforms-form .wpforms-required-label'   => 'color: {{VALUE}};',
                    ],
                    'separator' =>'before',
                ]
            );

        $this->end_controls_section(); // // Label style tab end

        // Style Input tab section
        $this->start_controls_section(
            'wpforms_input_style_section',
            [
                'label' => __( 'Input', 'ht-wpform' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

            $this->add_control(
                'wpforms_input_background_color',
                [
                    'label' => __( 'Background Color', 'ht-wpform' ),
                    'type' => Controls_Manager::COLOR,
                    'scheme' => [
                        'type' => Scheme_Color::get_type(),
                        'value' => Scheme_Color::COLOR_1,
                    ],
                    'default' => '#ffffff',
                    'selectors'         => [
                        '{{WRAPPER}} .wpforms-field input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .wpforms-field select' => 'background-color: {{VALUE}}',
                    ],
                ]
            );

            $this->add_control(
                'wpforms_input_color',
                [
                    'label' => __( 'Color', 'ht-wpform' ),
                    'type' => Controls_Manager::COLOR,
                    'scheme' => [
                        'type' => Scheme_Color::get_type(),
                        'value' => Scheme_Color::COLOR_1,
                    ],
                    'default' => '#212529',
                    'selectors'         => [
                        '{{WRAPPER}} .wpforms-field input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .wpforms-field select' => 'color: {{VALUE}}',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name' => 'wpforms_input_typography',
                    'scheme' => Scheme_Typography::TYPOGRAPHY_4,
                    'selector' => '{{WRAPPER}} .wpforms-field input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .wpforms-field select',
                ]
            );

            $this->add_responsive_control(
                'wpforms_input_height',
                [
                    'label'             => __( 'Height', 'ht-wpform' ),
                    'type'              => Controls_Manager::SLIDER,
                    'range'             => [
                        'px' => [
                            'min'   => 0,
                            'max'   => 100,
                            'step'  => 1,
                        ],
                    ],
                    'size_units'        => [ 'px', 'em', '%' ],
                    'selectors'         => [
                        '{{WRAPPER}} .wpforms-field input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .wpforms-field select' => 'height: {{SIZE}}{{UNIT}}',
                    ],
                ]
            );

            $this->add_responsive_control(
                'wpforms_input_padding',
                [
                    'label' => __( 'Padding', 'ht-wpform' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'selectors' => [
                        '{{WRAPPER}} .wpforms-field input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file])' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                        '{{WRAPPER}} .wpforms-field select' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                    'separator' =>'before',
                ]
            );

            $this->add_responsive_control(
                'wpforms_input_margin',
                [
                    'label' => __( 'Margin', 'ht-wpform' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'selectors' => [
                        '{{WRAPPER}} .wpforms-field input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file])' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                        '{{WRAPPER}} .wpforms-field select' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Border::get_type(),
                [
                    'name' => 'wpforms_input_border',
                    'label' => __( 'Border', 'ht-wpform' ),
                    'selector' => '{{WRAPPER}} .wpforms-field input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .wpforms-field select',
                ]
            );

            $this->add_responsive_control(
                'wpforms_input_border_radius',
                [
                    'label' => esc_html__( 'Border Radius', 'ht-wpform' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'selectors' => [
                        '{{WRAPPER}} .wpforms-field input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file])' => 'border-radius: {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px;',
                        '{{WRAPPER}} .wpforms-field select' => 'border-radius: {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px;',
                    ],
                ]
            );

        $this->end_controls_section(); // Form input style

        // Style Textarea tab section
        $this->start_controls_section(
            'wpforms_textarea_style_section',
            [
                'label' => __( 'Textarea', 'ht-wpform' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

            $this->add_control(
                'wpforms_textarea_background_color',
                [
                    'label' => __( 'Background Color', 'ht-wpform' ),
                    'type' => Controls_Manager::COLOR,
                    'scheme' => [
                        'type' => Scheme_Color::get_type(),
                        'value' => Scheme_Color::COLOR_1,
                    ],
                    'default' => '#ffffff',
                    'selectors'         => [
                        '{{WRAPPER}} .wpforms-field textarea' => 'background-color: {{VALUE}}',
                    ],
                ]
            );

            $this->add_control(
                'wpforms_textarea_color',
                [
                    'label' => __( 'Color', 'ht-wpform' ),
                    'type' => Controls_Manager::COLOR,
                    'scheme' => [
                        'type' => Scheme_Color::get_type(),
                        'value' => Scheme_Color::COLOR_1,
                    ],
                    'default' => '#212529',
                    'selectors'         => [
                        '{{WRAPPER}} .wpforms-field textarea' => 'color: {{VALUE}}',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name' => 'wpforms_textarea_typography',
                    'scheme' => Scheme_Typography::TYPOGRAPHY_4,
                    'selector' => '{{WRAPPER}} .wpforms-field textarea',
                ]
            );

            $this->add_responsive_control(
                'wpforms_textarea_height',
                [
                    'label'             => __( 'Height', 'ht-wpform' ),
                    'type'              => Controls_Manager::SLIDER,
                    'range'             => [
                        'px' => [
                            'min'   => 0,
                            'max'   => 500,
                            'step'  => 1,
                        ],
                    ],
                    'size_units'        => [ 'px', 'em', '%' ],
                    'selectors'         => [
                        '{{WRAPPER}} .wpforms-field textarea' => 'height: {{SIZE}}{{UNIT}}',
                    ],
                ]
            );

            $this->add_responsive_control(
                'wpforms_textarea_padding',
                [
                    'label' => __( 'Padding', 'ht-wpform' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'selectors' => [
                        '{{WRAPPER}} .wpforms-field textarea' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                    'separator' =>'before',
                ]
            );

            $this->add_responsive_control(
                'wpforms_textarea_margin',
                [
                    'label' => __( 'Margin', 'ht-wpform' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'selectors' => [
                        '{{WRAPPER}} .wpforms-field textarea' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Border::get_type(),
                [
                    'name' => 'wpforms_textarea_border',
                    'label' => __( 'Border', 'ht-wpform' ),
                    'selector' => '{{WRAPPER}} .wpforms-field textarea',
                ]
            );

            $this->add_responsive_control(
                'wpforms_textarea_border_radius',
                [
                    'label' => esc_html__( 'Border Radius', 'ht-wpform' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'selectors' => [
                        '{{WRAPPER}} .wpforms-field textarea' => 'border-radius: {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px;',
                    ],
                ]
            );

        $this->end_controls_section(); // Form input style


        // Input submit button style tab start
        $this->start_controls_section(
            'wpforms_inputsubmit_style',
            [
                'label'     => __( 'Button', 'ht-wpform' ),
                'tab'       => Controls_Manager::TAB_STYLE,
            ]
        );
            
            $this->add_control(
                'wpforms_submit_btn_width',
                [
                    'label' => __( 'Button Full Width?', 'ht-wpform' ),
                    'type' => Controls_Manager::SWITCHER,
                    'label_on' => __( 'Yes', 'ht-wpform' ),
                    'label_off' => __( 'No', 'ht-wpform' ),
                    'return_value' => 'yes',
                    'default' => 'no',
                ]
            );

            $this->add_responsive_control(
                'wpforms_button_width',
                [
                    'label' => __( 'Button Width', 'ht-wpform' ),
                    'type' => Controls_Manager::SLIDER,
                    'size_units' => [ 'px', '%' ],
                    'default' => [
                        'unit' => '%',
                        'size' => 100
                    ],
                    'range' => [
                        '%' => [
                            'min' => 1,
                            'max' => 100,
                        ],
                        'px' => [
                            'min' => 1,
                            'max' => 800,
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .wpforms-submit' => 'width: {{SIZE}}{{UNIT}}; display: block;',
                    ],
                    'condition' => [
                        'wpforms_submit_btn_width' => 'yes'
                    ],
                ]
            );

            $this->add_responsive_control(
                'wpforms_submit_btn_position',
                [
                    'label' => __( 'Button Position', 'ht-wpform' ),
                    'type' => Controls_Manager::CHOOSE,
                    'options' => [
                        'left' => [
                            'title' => __( 'Left', 'ht-wpform' ),
                            'icon' => 'eicon-align-left',
                        ],
                        'center' => [
                            'title' => __( 'Center', 'ht-wpform' ),
                            'icon' => 'eicon-align-center',
                        ],
                        'right' => [
                            'title' => __( 'Right', 'ht-wpform' ),
                            'icon' => 'eicon-align-right',
                        ],
                    ],
                    'desktop_default' => 'left',
                    'toggle' => false,
                    'selectors' => [
                        '{{WRAPPER}} .wpforms-submit-container' => 'text-align: {{Value}};',
                    ],
                    'condition' => [
                        'wpforms_button_width' => '',
                    ],
                ]
            );

            $this->start_controls_tabs('wpforms_submit_style_tabs');

                // Button Normal tab start
                $this->start_controls_tab(
                    'wpforms_submit_style_normal_tab',
                    [
                        'label' => __( 'Normal', 'ht-wpform' ),
                    ]
                );

                    $this->add_control(
                        'wpforms_input_submit_height',
                        [
                            'label' => __( 'Height', 'ht-wpform' ),
                            'type'  => Controls_Manager::SLIDER,
                            'range' => [
                                'px' => [
                                    'max' => 150,
                                ],
                            ],
                            'selectors' => [
                                '{{WRAPPER}} .wpforms-form button[type="submit"]' => 'height: {{SIZE}}{{UNIT}};',
                            ],
                        ]
                    );

                    $this->add_group_control(
                        Group_Control_Typography::get_type(),
                        [
                            'name' => 'wpforms_input_submit_typography',
                            'scheme' => Scheme_Typography::TYPOGRAPHY_1,
                            'selector' => '{{WRAPPER}} .wpforms-form button[type="submit"]',
                        ]
                    );

                    $this->add_control(
                        'wpforms_input_submit_text_color',
                        [
                            'label'     => __( 'Color', 'ht-wpform' ),
                            'type'      => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .wpforms-form button[type="submit"]'  => 'color: {{VALUE}};',
                            ],
                        ]
                    );

                    $this->add_control(
                        'wpforms_input_submit_background_color',
                        [
                            'label'     => __( 'Background Color', 'ht-wpform' ),
                            'type'      => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .wpforms-form button[type="submit"]'  => 'background-color: {{VALUE}};',
                            ],
                        ]
                    );

                    $this->add_responsive_control(
                        'wpforms_input_submit_padding',
                        [
                            'label' => __( 'Padding', 'ht-wpform' ),
                            'type' => Controls_Manager::DIMENSIONS,
                            'size_units' => [ 'px', '%', 'em' ],
                            'selectors' => [
                                '{{WRAPPER}} .wpforms-form button[type="submit"]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                            ],
                            'separator' =>'before',
                        ]
                    );

                    $this->add_responsive_control(
                        'wpforms_input_submit_margin',
                        [
                            'label' => __( 'Margin', 'ht-wpform' ),
                            'type' => Controls_Manager::DIMENSIONS,
                            'size_units' => [ 'px', '%', 'em' ],
                            'selectors' => [
                                '{{WRAPPER}} .wpforms-form button[type="submit"]' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                            ],
                            'separator' =>'before',
                        ]
                    );

                    $this->add_group_control(
                        Group_Control_Border::get_type(),
                        [
                            'name' => 'wpforms_input_submit_border',
                            'label' => __( 'Border', 'ht-wpform' ),
                            'selector' => '{{WRAPPER}} .wpforms-form button[type="submit"]',
                        ]
                    );

                    $this->add_responsive_control(
                        'wpforms_input_submit_border_radius',
                        [
                            'label' => esc_html__( 'Border Radius', 'ht-wpform' ),
                            'type' => Controls_Manager::DIMENSIONS,
                            'selectors' => [
                                '{{WRAPPER}} .wpforms-form button[type="submit"]' => 'border-radius: {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px;',
                            ],
                        ]
                    );

                    $this->add_group_control(
                        Group_Control_Box_Shadow::get_type(),
                        [
                            'name' => 'wpforms_input_submit_box_shadow',
                            'label' => __( 'Box Shadow', 'ht-wpform' ),
                            'selector' => '{{WRAPPER}} .wpforms-form button[type="submit"]',
                        ]
                    );

                $this->end_controls_tab(); // Button Normal tab end

                // Button Hover tab start
                $this->start_controls_tab(
                    'wpforms_submit_style_hover_tab',
                    [
                        'label' => __( 'Hover', 'ht-wpform' ),
                    ]
                );

                    $this->add_control(
                        'wpforms_input_submithover_text_color',
                        [
                            'label'     => __( 'Color', 'ht-wpform' ),
                            'type'      => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .wpforms-form button[type="submit"]:hover'  => 'color: {{VALUE}};',
                            ],
                        ]
                    );

                    $this->add_control(
                        'wpforms_input_submithover_background_color',
                        [
                            'label'     => __( 'Background Color', 'ht-wpform' ),
                            'type'      => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .wpforms-form button[type="submit"]:hover'  => 'background-color: {{VALUE}};',
                            ],
                        ]
                    );

                    $this->add_group_control(
                        Group_Control_Border::get_type(),
                        [
                            'name' => 'wpforms_input_submithover_border',
                            'label' => __( 'Border', 'ht-wpform' ),
                            'selector' => '{{WRAPPER}} .wpforms-form button[type="submit"]:hover',
                        ]
                    );

                $this->end_controls_tab(); // Button Hover tab end

            $this->end_controls_tabs();

        $this->end_controls_section(); // Input submit button style tab end


    }

    protected function render( $instance = [] ) {

        $settings   = $this->get_settings_for_display();
        $id         = $this->get_id();
        $this->add_render_attribute( 'htwpform_attr', 'class', 'htwpform_area' );

    ?>
            <div <?php echo $this->get_render_attribute_string('htwpform_attr'); ?> >
                <?php
                    if ( !$settings['contact_form_list'] ) {
                        echo '<p>'.esc_html__('Please Select form.','ht-wpform').'</p>';
                    }else{
                        $show_form_title = $settings['show_form_title'];
                        $show_form_description = $settings['show_form_description'];
                        echo wpforms_display( $settings['contact_form_list'], $show_form_title, $show_form_description );
                    }
                ?>
            </div>

        <?php
    }
}

Plugin::instance()->widgets_manager->register_widget_type( new HTwpform_Elementor_Widget() );