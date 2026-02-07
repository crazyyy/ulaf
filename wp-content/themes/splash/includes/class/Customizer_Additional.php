<?php

final class CustomizerAdditional
{
    private $fields = array();


    function __construct()
    {
        $this->setFields();
    }

    private function setFields()
    {
        $this->fields = array(
            'americanfootball' => array( /*Layout name*/
                'site_style' => array(/*Section name*/
                    'site_style' => array(
                        'label' => esc_html__('Style', 'splash'),
                        'type' => 'stm-select',
                        'choices' => array(
                            'default' => esc_html__('Default', 'splash'),
                            'site_style_custom' => esc_html__('Custom Color', 'splash')
                        ),
                        'default' => 'default'
                    )
                ),
                'header_top_bar' => array( /*Section name*/
                    'top_bar_enable_socials' => array(
                        'label' => esc_html__('Top bar enable socials', 'splash'),
                        'type' => 'stm-checkbox',
                        'sanitize_callback' => 'sanitize_checkbox',
                        'default' => true
                    ),
                    'top_bar_enable_ticker' => array(
                        'label' => esc_html__('Top bar enable news ticker', 'splash'),
                        'type' => 'stm-checkbox',
                        'sanitize_callback' => 'sanitize_checkbox',
                        'default' => true
                    ),
                    'top_bar_link_ticker' => array(
                        'label' => esc_html__('News Ticker Link To the Post Page', 'splash'),
                        'type' => 'stm-checkbox',
                        'sanitize_callback' => 'sanitize_checkbox',
                        'default' => false
                    ),
                    'top_bar_ticker_title' => array(
                        'label' => esc_html__('Ticker title', 'splash'),
                        'type' => 'text',
                        'default' => esc_html__('Breaking news', 'splash'),
                    ),
                    'top_bar_enable_tickets' => array(
                        'label' => esc_html__('Top bar enable tickets', 'splash'),
                        'type' => 'stm-checkbox',
                        'sanitize_callback' => 'sanitize_checkbox',
                        'default' => true
                    )
                )
            ),
            'basketball' => array( /*Layout name*/
                'site_style' => array(/*Section name*/
                    'site_style' => array(
                        'label' => esc_html__('Style', 'splash'),
                        'type' => 'stm-select',
                        'choices' => array(
                            'default' => esc_html__('Default', 'splash'),
                            'blue' => esc_html__('Blue', 'splash'),
                            'blue-violet' => esc_html__('Blue Violet', 'splash'),
                            'choco' => esc_html__('Choco', 'splash'),
                            'gold' => esc_html__('Gold', 'splash'),
                            'green' => esc_html__('Green', 'splash'),
                            'orange' => esc_html__('Orange', 'splash'),
                            'sky-blue' => esc_html__('Sky blue', 'splash'),
                            'turquose' => esc_html__('Turquoise', 'splash'),
                            'violet-red' => esc_html__('Violet Red', 'splash'),
                            'site_style_custom' => esc_html__('Custom Color', 'splash'),
                        ),
                        'default' => 'default'
                    )
                ),
                'header_top_bar' => array( /*Section name*/
                    'top_bar_enable_socials' => array(
                        'label' => esc_html__('Top bar enable socials', 'splash'),
                        'type' => 'stm-checkbox',
                        'sanitize_callback' => 'sanitize_checkbox',
                        'default' => true
                    ),
                    'top_bar_enable_ticker' => array(
                        'label' => esc_html__('Top bar enable news ticker', 'splash'),
                        'type' => 'stm-checkbox',
                        'sanitize_callback' => 'sanitize_checkbox',
                        'default' => true
                    ),
                    'top_bar_link_ticker' => array(
                        'label' => esc_html__('News Ticker Link To the Post Page', 'splash'),
                        'type' => 'stm-checkbox',
                        'sanitize_callback' => 'sanitize_checkbox',
                        'default' => false
                    ),
                    'top_bar_ticker_title' => array(
                        'label' => esc_html__('Ticker title', 'splash'),
                        'type' => 'text',
                        'default' => esc_html__('Breaking news', 'splash'),
                    ),
                    'top_bar_enable_tickets' => array(
                        'label' => esc_html__('Top bar enable tickets', 'splash'),
                        'type' => 'stm-checkbox',
                        'sanitize_callback' => 'sanitize_checkbox',
                        'default' => true
                    )
                )
            ),
            'soccer' => array( /*Layout name*/
                'header_top_bar' => array( /*Section name*/
                    'top_bar_enable_socials' => array(
                        'label' => esc_html__('Top bar enable socials', 'splash'),
                        'type' => 'stm-checkbox',
                        'sanitize_callback' => 'sanitize_checkbox',
                        'default' => true
                    ),
                    'top_bar_enable_ticker' => array(
                        'label' => esc_html__('Top bar enable news ticker', 'splash'),
                        'type' => 'stm-checkbox',
                        'sanitize_callback' => 'sanitize_checkbox',
                        'default' => true
                    ),
                    'top_bar_link_ticker' => array(
                        'label' => esc_html__('News Ticker Link To the Post Page', 'splash'),
                        'type' => 'stm-checkbox',
                        'sanitize_callback' => 'sanitize_checkbox',
                        'default' => false
                    ),
                    'top_bar_ticker_title' => array(
                        'label' => esc_html__('Ticker title', 'splash'),
                        'type' => 'text',
                        'default' => esc_html__('Breaking news', 'splash'),
                    ),
                    'top_bar_enable_search' => array(
                        'label' => esc_html__('Top bar enable search', 'splash'),
                        'type' => 'stm-checkbox',
                        'sanitize_callback' => 'sanitize_checkbox',
                        'default' => false
                    ),
                    'top_bar_enable_tickets' => array(
                        'label' => esc_html__('Top bar enable tickets', 'splash'),
                        'type' => 'stm-checkbox',
                        'sanitize_callback' => 'sanitize_checkbox',
                        'default' => true
                    ),
                    'top_bar_enable_profile' => array(
                        'label' => esc_html__('Top bar enable profile', 'splash'),
                        'type' => 'stm-checkbox',
                        'sanitize_callback' => 'sanitize_checkbox',
                        'default' => false
                    )
                )
            ),
            'magazine_one' => array( /*Layout name*/
                'header_top_bar' => array( /*Section name*/
                    'top_bar_enable_search' => array(
                        'label' => esc_html__('Top bar enable search', 'splash'),
                        'type' => 'stm-checkbox',
                        'sanitize_callback' => 'sanitize_checkbox',
                        'default' => false
                    ),
                    'top_bar_enable_profile' => array(
                        'label' => esc_html__('Top bar enable profile', 'splash'),
                        'type' => 'stm-checkbox',
                        'sanitize_callback' => 'sanitize_checkbox',
                        'default' => false
                    ),
                    'top_bar_phone' => array(
                        'label' => esc_html__('Phone', 'splash'),
                        'type' => 'text',
                        'default' => esc_html__('+1 234 567 890', 'splash'),
                    ),
                    'top_bar_email' => array(
                        'label' => esc_html__('Email', 'splash'),
                        'type' => 'text',
                        'default' => esc_html__('info@splash.com', 'splash'),
                    ),
                )
            ),
            'magazine_two' => array( /*Layout name*/
                'header_top_bar' => array( /*Section name*/
                    'top_bar_enable_search' => array(
                        'label' => esc_html__('Top bar enable search', 'splash'),
                        'type' => 'stm-checkbox',
                        'sanitize_callback' => 'sanitize_checkbox',
                        'default' => false
                    ),
                    'top_bar_enable_profile' => array(
                        'label' => esc_html__('Top bar enable profile', 'splash'),
                        'type' => 'stm-checkbox',
                        'sanitize_callback' => 'sanitize_checkbox',
                        'default' => false
                    ),
                    'top_bar_phone' => array(
                        'label' => esc_html__('Phone', 'splash'),
                        'type' => 'text',
                        'default' => esc_html__('+1 234 567 890', 'splash'),
                    ),
                    'top_bar_email' => array(
                        'label' => esc_html__('Email', 'splash'),
                        'type' => 'text',
                        'default' => esc_html__('info@splash.com', 'splash'),
                    ),
                )
            ),
            'basketball_two' => array( /*Layout name*/
                'site_style' => array(/*Section name*/
                    'site_style' => array(
                        'label' => esc_html__('Style', 'splash'),
                        'type' => 'stm-select',
                        'choices' => array(
                            'default' => esc_html__('Default', 'splash'),
                            'site_style_custom' => esc_html__('Custom Color', 'splash')
                        ),
                        'default' => 'default'
                    )
                ),
                'header_top_bar' => array( /*Section name*/
                    'top_bar_enable_socials' => array(
                        'label' => esc_html__('Top bar enable socials', 'splash'),
                        'type' => 'stm-checkbox',
                        'sanitize_callback' => 'sanitize_checkbox',
                        'default' => true
                    ),
                    'top_bar_enable_tickets' => array(
                        'label' => esc_html__('Top bar enable tickets', 'splash'),
                        'type' => 'stm-checkbox',
                        'sanitize_callback' => 'sanitize_checkbox',
                        'default' => true
                    )
                )
            ),
            'hockey' => array( /*Layout name*/
                'site_style' => array(/*Section name*/
                    'site_style' => array(
                        'label' => esc_html__('Style', 'splash'),
                        'type' => 'stm-select',
                        'choices' => array(
                            'default' => esc_html__('Default', 'splash'),
                            'blue' => esc_html__('Blue', 'splash'),
                            'blue-violet' => esc_html__('Blue Violet', 'splash'),
                            'choco' => esc_html__('Choco', 'splash'),
                            'gold' => esc_html__('Gold', 'splash'),
                            'green' => esc_html__('Green', 'splash'),
                            'orange' => esc_html__('Orange', 'splash'),
                            'sky-blue' => esc_html__('Sky blue', 'splash'),
                            'turquose' => esc_html__('Turquoise', 'splash'),
                            'violet-red' => esc_html__('Violet Red', 'splash'),
                            'site_style_custom' => esc_html__('Custom Color', 'splash'),
                        ),
                        'default' => 'default'
                    )
                ),
                'header_top_bar' => array( /*Section name*/
                    'top_bar_enable_socials' => array(
                        'label' => esc_html__('Top bar enable socials', 'splash'),
                        'type' => 'stm-checkbox',
                        'sanitize_callback' => 'sanitize_checkbox',
                        'default' => true
                    ),
                    'top_bar_enable_ticker' => array(
                        'label' => esc_html__('Top bar enable news ticker', 'splash'),
                        'type' => 'stm-checkbox',
                        'sanitize_callback' => 'sanitize_checkbox',
                        'default' => true
                    ),
                    'top_bar_link_ticker' => array(
                        'label' => esc_html__('News Ticker Link To the Post Page', 'splash'),
                        'type' => 'stm-checkbox',
                        'sanitize_callback' => 'sanitize_checkbox',
                        'default' => false
                    ),
                    'top_bar_ticker_title' => array(
                        'label' => esc_html__('Ticker title', 'splash'),
                        'type' => 'text',
                        'default' => esc_html__('Breaking news', 'splash'),
                    ),
                    'top_bar_enable_tickets' => array(
                        'label' => esc_html__('Top bar enable tickets', 'splash'),
                        'type' => 'stm-checkbox',
                        'sanitize_callback' => 'sanitize_checkbox',
                        'default' => true
                    )
                )
            ),
            'soccer_news' => array( /*Layout name*/
                'header_top_bar' => array( /*Section name*/
                    'top_bar_enable_search' => array(
                        'label' => esc_html__('Top bar enable search', 'splash'),
                        'type' => 'stm-checkbox',
                        'sanitize_callback' => 'sanitize_checkbox',
                        'default' => false
                    ),
                    'top_bar_enable_profile' => array(
                        'label' => esc_html__('Top bar enable profile', 'splash'),
                        'type' => 'stm-checkbox',
                        'sanitize_callback' => 'sanitize_checkbox',
                        'default' => false
                    ),
                    'top_bar_phone' => array(
                        'label' => esc_html__('Phone', 'splash'),
                        'type' => 'text',
                        'default' => esc_html__('+1 234 567 890', 'splash'),
                    ),
                    'top_bar_email' => array(
                        'label' => esc_html__('Email', 'splash'),
                        'type' => 'text',
                        'default' => esc_html__('info@splash.com', 'splash'),
                    ),
                )
            )

        );
    }

    public function getFields($ident)
    {
        $layoutName = splash_get_layout_name();
        if (isset($this->fields[$layoutName][$ident])) {
            return $this->fields[$layoutName][$ident];
        }
    }
}
