<?php
/**
 * Typography
 *
 * @package ghostkit
 */

/**
 * GhostKit_Typography
 */
class GhostKit_Typography {
    /**
     * GhostKit_Typography constructor.
     */
    public function __construct() {
        add_filter( 'gkt_custom_typography', array( $this, 'add_default_typography' ), 9 );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_typography_assets' ), 100 );
        add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_typography_assets' ), 100 );
    }

    /**
     * Enqueue Typography assets to editor and front end.
     */
    public function enqueue_typography_assets() {
        $typography_css = $this->generate_typography_styles();
        $css            = ' ';

        if ( isset( $typography_css ) && ! empty( $typography_css ) && is_array( $typography_css ) ) {
            if ( ! is_admin() && isset( $typography_css['front'] ) && ! empty( $typography_css['front'] ) ) {
                $css = $typography_css['front'];
            }
            if ( function_exists( 'register_block_type' ) && is_admin() && isset( $typography_css['editor'] ) && ! empty( $typography_css['editor'] ) ) {
                $css = $typography_css['editor'];
            }
        }
        wp_register_style( 'ghostkit-typography', false, array(), '2.23.2' );
        wp_enqueue_style( 'ghostkit-typography' );
        wp_add_inline_style( 'ghostkit-typography', $css );
    }

    /**
     * Generate Typography Styles.
     *
     * @return array - Typography Css.
     */
    public function generate_typography_styles() {
        $typography_prepeare_styles = array();
        $typography_css             = array(
            'editor' => '',
            'front'  => '',
        );
        $default_typography         = apply_filters( 'gkt_custom_typography', array() );
        $screen                     = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
        $global_typography          = get_option( 'ghostkit_typography', array() );
        $is_admin_editor            = is_admin() && $screen && $screen->is_block_editor;

        if ( is_singular() ) {
            $post_id = get_the_ID();
        } elseif ( $is_admin_editor ) {
            global $post;
            $post_id = isset( $post->ID ) ? $post->ID : null;
        }

        $is_single       = is_singular() && $post_id;
        $is_admin_editor = $is_admin_editor && $post_id;

        if ( $this->is_exist( $default_typography ) ) {

            foreach ( $default_typography as $key => $typography ) {
                if ( $this->is_exist( $typography['output'] ) ) {
                    $typography_prepeare_styles[ $key ] = array(
                        'style-properties' => $typography['defaults'],
                        'output'           => $typography['output'],
                    );
                }
            }

            // Global custom Typography.
            if ( $this->is_exist( $global_typography ) && $this->is_exist( $global_typography['ghostkit_typography'] ) ) {
                $typography_prepeare_styles = $this->get_typography_values( $global_typography['ghostkit_typography'], $typography_prepeare_styles );
            }

            // Local custom Typography.
            if ( $is_single || $is_admin_editor ) {
                $meta_typography            = get_post_meta( $post_id, 'ghostkit_typography', true );
                $typography_prepeare_styles = $this->get_typography_values( $meta_typography, $typography_prepeare_styles );
            }

            // Collect all the styles for further transfer to the inline file on the edit or front page.
            foreach ( $typography_prepeare_styles as $typography_prepeare_style ) {
                if (
                    $this->is_exist( $typography_prepeare_style['output'] ) &&
                    is_array( $typography_prepeare_style['output'] ) &&
                    ( $this->is_exist( $typography_prepeare_style['style-properties'], 'font-family' ) ||
                        $this->is_exist( $typography_prepeare_style['style-properties'], 'font-size' ) ||
                        $this->is_exist( $typography_prepeare_style['style-properties'], 'font-weight' ) ||
                        $this->is_exist( $typography_prepeare_style['style-properties'], 'line-height' ) ||
                        $this->is_exist( $typography_prepeare_style['style-properties'], 'letter-spacing' )
                    )
                ) {
                    foreach ( $typography_prepeare_style['output'] as $output ) {
                        if ( $this->is_exist( $output['selectors'] ) ) {
                            $typography_styles  = '';
                            $typography_styles .= $output['selectors'] . '{';

                            if ( $this->is_exist( $typography_prepeare_style['style-properties'], 'font-family' ) ) {
                                $typography_styles .= 'font-family: ' . esc_attr( $typography_prepeare_style['style-properties']['font-family'] ) . ', sans-serif;';
                            }

                            if ( $this->is_exist( $typography_prepeare_style['style-properties'], 'font-size' ) ) {
                                $typography_styles .= 'font-size: ' . esc_attr( $typography_prepeare_style['style-properties']['font-size'] ) . ';';
                            }

                            if ( $this->is_exist( $typography_prepeare_style['style-properties'], 'font-weight' ) ) {
                                $font_weight = $typography_prepeare_style['style-properties']['font-weight'];

                                if ( false !== strpos( $font_weight, 'i' ) ) {
                                    $font_weight        = str_replace( 'i', '', $font_weight );
                                    $typography_styles .= 'font-style: italic;';
                                } else {
                                    $typography_styles .= 'font-style: normal;';
                                }

                                $typography_styles .= 'font-weight: ' . esc_attr( $font_weight ) . ';';
                            }

                            if ( $this->is_exist( $typography_prepeare_style['style-properties'], 'line-height' ) ) {
                                $typography_styles .= 'line-height: ' . esc_attr( $typography_prepeare_style['style-properties']['line-height'] ) . ';';
                            }

                            if ( $this->is_exist( $typography_prepeare_style['style-properties'], 'letter-spacing' ) ) {
                                $typography_styles .= 'letter-spacing: ' . esc_attr( $typography_prepeare_style['style-properties']['letter-spacing'] ) . ';';
                            }

                            $typography_styles .= '}';

                            if ( isset( $output['editor'] ) && true === $output['editor'] ) {
                                $typography_css['editor'] .= $typography_styles;
                            } else {
                                $typography_css['front'] .= $typography_styles;
                            }
                        }
                    }
                }
            }
        }

        return $typography_css;
    }

    /**
     * Check value on the existence and emptiness.
     *
     * @param void   $value - Checking value.
     * @param bool   $attribute - Checking attribute of Array Value.
     * @param string $mode - Full or isset for partial check.
     * @return bool  $value - True or false.
     */
    public function is_exist( $value, $attribute = false, $mode = 'full' ) {
        $check = false;

        if ( $attribute ) {
            if ( 'full' === $mode && isset( $value[ $attribute ] ) && ! empty( $value[ $attribute ] ) ) {
                $check = true;
            }
            if ( 'isset' === $mode && isset( $value[ $attribute ] ) ) {
                $check = true;
            }
        } else {
            if ( 'full' === $mode ) {
                $check = ( isset( $value ) && ! empty( $value ) ) ? true : false;
            }
            if ( 'isset' === $mode ) {
                $check = ( isset( $value ) ) ? true : false;
            }
        }

        return $check;
    }

    /**
     * Function for get Typography Values.
     *
     * @param object $typography_object - Current typography.
     * @param array  $typography_prepeare_styles - Previous Array With Current Styles Properties.
     * @return mixed - Next Array With Current Styles Properties.
     */
    public function get_typography_values( $typography_object, $typography_prepeare_styles ) {
        $conformity_attributes = array(
            'fontFamily'         => 'font-family',
            'fontFamilyCategory' => 'font-family-category',
            'fontSize'           => 'font-size',
            'fontWeight'         => 'font-weight',
            'lineHeight'         => 'line-height',
            'letterSpacing'      => 'letter-spacing',
            'label'              => 'label',
            'childOf'            => 'child-of',
        );

        if ( $this->is_exist( $typography_object ) ) {
            foreach ( json_decode( $typography_object ) as $meta_typography_key => $meta_typography_value ) {
                if ( array_key_exists( $meta_typography_key, $typography_prepeare_styles ) ) {
                    foreach ( $meta_typography_value as $typography_attribute_key => $typography_attribute ) {
                        if ( $this->is_exist( $conformity_attributes[ $typography_attribute_key ] ) &&
                            $this->is_exist( $typography_prepeare_styles[ $meta_typography_key ]['style-properties'], $conformity_attributes[ $typography_attribute_key ], 'isset' ) ) {
                            if ( '' !== $typography_attribute ) {
                                $typography_prepeare_styles[ $meta_typography_key ]['style-properties'][ $conformity_attributes[ $typography_attribute_key ] ] = $typography_attribute;
                            }
                        }
                    }
                }
            }
        }

        return $typography_prepeare_styles;
    }

    /**
     * Add Default Typography.
     *
     * @param array $custom_typography - Current typography.
     * @return array - New Typography.
     */
    public function add_default_typography( $custom_typography ) {
        $custom_typography = array(
            'body' => array(
                'label'    => esc_html__( 'Body', 'ghostkit' ),
                'defaults' => array(
                    'font-family-category' => 'default',
                    'font-family'          => '',
                    'font-size'            => '',
                    'font-weight'          => '',
                    'line-height'          => '',
                    'letter-spacing'       => '',
                ),
                'output'   => array(
                    array(
                        'selectors' => 'body',
                    ),
                    array(
                        'selectors' => '#editor .editor-styles-wrapper, .editor-styles-wrapper p',
                        'editor'    => true,
                    ),
                ),
            ),
            'buttons' => array(
                'label'    => esc_html__( 'Buttons', 'ghostkit' ),
                'defaults' => array(
                    'font-family-category' => 'default',
                    'font-family'          => '',
                    'font-size'            => '',
                    'font-weight'          => '',
                    'line-height'          => '',
                    'letter-spacing'       => '',
                ),
                'output'   => array(
                    array(
                        'selectors' => '.wp-block-button, .ghostkit-button, .entry .entry-content .wp-block-button .wp-block-button__link',
                    ),
                    array(
                        'selectors' => '#editor .editor-styles-wrapper .wp-block-button .wp-block-button__link, #editor .editor-styles-wrapper .ghostkit-button',
                        'editor'    => true,
                    ),
                ),
            ),
            'headings' => array(
                'label'    => esc_html__( 'Headings', 'ghostkit' ),
                'defaults' => array(
                    'font-family-category' => 'default',
                    'font-family'          => '',
                    'font-weight'          => '',
                    'line-height'          => '',
                    'letter-spacing'       => '',
                ),
                'output'   => array(
                    array(
                        'selectors' => 'h1, h1.entry-title, h2, h3, h4, h5, h6',
                    ),
                    array(
                        'selectors' => '#editor .editor-styles-wrapper h1, #editor .editor-styles-wrapper h2, #editor .editor-styles-wrapper h3, #editor .editor-styles-wrapper h4, #editor .editor-styles-wrapper h5, #editor .editor-styles-wrapper h6, #editor .editor-styles-wrapper .editor-post-title__block .editor-post-title__input',
                        'editor'    => true,
                    ),
                ),
            ),
            'h1' => array(
                'label'    => esc_html__( 'H1', 'ghostkit' ),
                'defaults' => array(
                    'font-size'      => '',
                    'line-height'    => '',
                    'letter-spacing' => '',
                ),
                'child-of' => 'headings',
                'output'   => array(
                    array(
                        'selectors' => 'h1, h1.entry-title',
                    ),
                    array(
                        'selectors' => '#editor .editor-styles-wrapper h1, #editor .editor-styles-wrapper .editor-post-title__block .editor-post-title__input',
                        'editor'    => true,
                    ),
                ),
            ),
            'h2' => array(
                'label'    => esc_html__( 'H2', 'ghostkit' ),
                'defaults' => array(
                    'font-size'      => '',
                    'line-height'    => '',
                    'letter-spacing' => '',
                ),
                'child-of' => 'headings',
                'output'   => array(
                    array(
                        'selectors' => 'h2',
                    ),
                    array(
                        'selectors' => '#editor .editor-styles-wrapper h2',
                        'editor'    => true,
                    ),
                ),
            ),
            'h3' => array(
                'label'    => esc_html__( 'H3', 'ghostkit' ),
                'defaults' => array(
                    'font-size'      => '',
                    'line-height'    => '',
                    'letter-spacing' => '',
                ),
                'child-of' => 'headings',
                'output'   => array(
                    array(
                        'selectors' => 'h3',
                    ),
                    array(
                        'selectors' => '#editor .editor-styles-wrapper h3',
                        'editor'    => true,
                    ),
                ),
            ),
            'h4' => array(
                'label'    => esc_html__( 'H4', 'ghostkit' ),
                'defaults' => array(
                    'font-size'      => '',
                    'line-height'    => '',
                    'letter-spacing' => '',
                ),
                'child-of' => 'headings',
                'output'   => array(
                    array(
                        'selectors' => 'h4',
                    ),
                    array(
                        'selectors' => '#editor .editor-styles-wrapper h4',
                        'editor'    => true,
                    ),
                ),
            ),
            'h5' => array(
                'label'    => esc_html__( 'H5', 'ghostkit' ),
                'defaults' => array(
                    'font-size'      => '',
                    'line-height'    => '',
                    'letter-spacing' => '',
                ),
                'child-of' => 'headings',
                'output'   => array(
                    array(
                        'selectors' => 'h5',
                    ),
                    array(
                        'selectors' => '#editor .editor-styles-wrapper h5',
                        'editor'    => true,
                    ),
                ),
            ),
            'h6' => array(
                'label'    => esc_html__( 'H6', 'ghostkit' ),
                'defaults' => array(
                    'font-size'      => '',
                    'line-height'    => '',
                    'letter-spacing' => '',
                ),
                'child-of' => 'headings',
                'output'   => array(
                    array(
                        'selectors' => 'h6',
                    ),
                    array(
                        'selectors' => '#editor .editor-styles-wrapper h6',
                        'editor'    => true,
                    ),
                ),
            ),
        );
        return $custom_typography;
    }
}
new GhostKit_Typography();
