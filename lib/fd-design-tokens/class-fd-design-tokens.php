<?php
/**
 * FD_Design_Tokens — Main design token generator.
 *
 * Accepts 3 brand colors and produces a complete design token system
 * including color scales, semantic colors, state colors, and dark mode mapping.
 *
 * Core principle: Brand color ≠ Component color.
 * Brand colors are used only to generate system colors.
 *
 * @package FD_Design_Tokens
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FD_Design_Tokens {

    const VERSION = '1.0.0';

    /** @var array Brand color hex values */
    private $brand;

    /** @var array Semantic color overrides */
    private $semantic;

    /** @var array Gray scale overrides */
    private $gray_overrides;

    /** @var array|null Cached token result */
    private $cache = null;

    /**
     * Default Tailwind Slate gray scale.
     */
    const DEFAULT_GRAYS = array(
        50  => '#f8fafc',
        100 => '#f1f5f9',
        200 => '#e2e8f0',
        300 => '#cbd5e1',
        400 => '#94a3b8',
        500 => '#64748b',
        600 => '#475569',
        700 => '#334155',
        800 => '#1e293b',
        900 => '#0f172a',
    );

    /**
     * Lightness targets for generating 50-900 scales.
     * Saturation multiplier reduces saturation at extremes to avoid neon/mud.
     */
    const SCALE_STEPS = array(
        50  => array( 'l' => 97, 'sm' => 0.30 ),
        100 => array( 'l' => 93, 'sm' => 0.45 ),
        200 => array( 'l' => 86, 'sm' => 0.60 ),
        300 => array( 'l' => 76, 'sm' => 0.75 ),
        400 => array( 'l' => 64, 'sm' => 0.90 ),
        500 => array( 'l' => 0,  'sm' => 1.00 ), // placeholder — uses clamped original
        600 => array( 'l' => 42, 'sm' => 0.95 ),
        700 => array( 'l' => 34, 'sm' => 0.90 ),
        800 => array( 'l' => 26, 'sm' => 0.85 ),
        900 => array( 'l' => 18, 'sm' => 0.80 ),
    );

    /**
     * Dark mode lightness targets (inverted and optimized for dark backgrounds).
     */
    const DARK_SCALE_STEPS = array(
        50  => array( 'l' => 10, 'sm' => 0.50 ),
        100 => array( 'l' => 14, 'sm' => 0.55 ),
        200 => array( 'l' => 20, 'sm' => 0.60 ),
        300 => array( 'l' => 28, 'sm' => 0.70 ),
        400 => array( 'l' => 38, 'sm' => 0.80 ),
        500 => array( 'l' => 55, 'sm' => 0.85 ),
        600 => array( 'l' => 65, 'sm' => 0.80 ),
        700 => array( 'l' => 76, 'sm' => 0.70 ),
        800 => array( 'l' => 86, 'sm' => 0.55 ),
        900 => array( 'l' => 93, 'sm' => 0.40 ),
    );

    // ─── Constructor ────────────────────────────────────────────

    /**
     * @param array $config {
     *     @type string $primary    Required. Primary brand hex color.
     *     @type string $secondary  Required. Secondary brand hex color.
     *     @type string $accent     Required. Accent brand hex color.
     *     @type array  $semantic   Optional. {success, warning, error, info} hex overrides.
     *     @type array  $gray       Optional. {50=>hex, 100=>hex, ...} gray overrides.
     * }
     */
    public function __construct( array $config ) {
        $this->brand = array(
            'primary'   => $config['primary']   ?? '#1a73e8',
            'secondary' => $config['secondary'] ?? '#4285f4',
            'accent'    => $config['accent']    ?? '#EC407A',
        );

        $this->semantic = array_merge(
            array(
                'success' => '#4CAF50',
                'warning' => '#FFA000',
                'error'   => '#F44336',
                'info'    => $this->brand['secondary'],
            ),
            $config['semantic'] ?? array()
        );

        $this->gray_overrides = $config['gray'] ?? array();
    }

    // ─── Static factory ─────────────────────────────────────────

    /**
     * Create instance from WordPress options.
     * Auto-detects fd-theme vs standalone fd-admin-ui.
     *
     * @return self
     */
    public static function from_options(): self {
        $is_fd_theme = self::is_fd_theme_active();

        if ( $is_fd_theme ) {
            return new self( array(
                'primary'   => get_option( 'fd_primary_color', '#1a73e8' ),
                'secondary' => get_option( 'fd_secondary_color', '#4285f4' ),
                'accent'    => get_option( 'fd_rose_color', '#EC407A' ),
                'semantic'  => array(
                    'success' => get_option( 'fd_success_color', '#4CAF50' ),
                    'warning' => get_option( 'fd_amber_color', '#FFA000' ),
                    'error'   => get_option( 'fd_error_color', '#F44336' ),
                    'info'    => get_option( 'fd_secondary_color', '#4285f4' ),
                ),
            ) );
        }

        // Standalone: read from fd_admin_ui_options array
        $options  = get_option( 'fd_admin_ui_options', array() );
        $defaults = array(
            'vi_primary_color'   => '#3b82f6',
            'vi_secondary_color' => '#4285f4',
            'vi_rose_color'      => '#ec4899',
            'vi_success_color'   => '#10b981',
            'vi_warning_color'   => '#f59e0b',
            'vi_error_color'     => '#ef4444',
        );
        $opts = array_merge( $defaults, $options );

        return new self( array(
            'primary'   => $opts['vi_primary_color'],
            'secondary' => $opts['vi_secondary_color'],
            'accent'    => $opts['vi_rose_color'],
            'semantic'  => array(
                'success' => $opts['vi_success_color'],
                'warning' => $opts['vi_warning_color'],
                'error'   => $opts['vi_error_color'],
                'info'    => $opts['vi_secondary_color'],
            ),
        ) );
    }

    // ─── Core API ───────────────────────────────────────────────

    /**
     * Generate and return the complete token set as a flat array.
     * Keys use dot-notation: 'primary.500', 'gray.100', 'semantic.success', etc.
     *
     * @return array<string, string>
     */
    public function get_tokens(): array {
        if ( $this->cache !== null ) {
            return $this->cache;
        }

        $tokens = array();

        // A. Color scales for brand colors
        foreach ( array( 'primary', 'secondary', 'accent' ) as $name ) {
            $scale = $this->generate_scale( $this->brand[ $name ] );
            foreach ( $scale as $step => $hex ) {
                $tokens[ "{$name}.{$step}" ] = $hex;
            }
        }

        // B. Gray scale
        $grays = $this->get_gray_scale();
        foreach ( $grays as $step => $hex ) {
            $tokens[ "gray.{$step}" ] = $hex;
        }

        // C. Semantic colors (base / light / dark)
        foreach ( $this->semantic as $name => $hex ) {
            $variants = $this->generate_semantic_variants( $hex );
            $tokens[ "semantic.{$name}" ]       = $variants['base'];
            $tokens[ "semantic.{$name}.light" ] = $variants['light'];
            $tokens[ "semantic.{$name}.dark" ]  = $variants['dark'];
        }

        // D. State colors (derived from primary)
        $states = $this->generate_state_variants( $this->brand['primary'] );
        foreach ( $states as $state => $hex ) {
            $tokens[ "state.{$state}" ] = $hex;
        }

        $this->cache = $tokens;
        return $tokens;
    }

    /**
     * Get a single token value by dot-notation key.
     *
     * @param string $key      e.g. 'primary.500'
     * @param string $fallback
     * @return string
     */
    public function get( string $key, string $fallback = '' ): string {
        $tokens = $this->get_tokens();
        return $tokens[ $key ] ?? $fallback;
    }

    // ─── Output: CSS ────────────────────────────────────────────

    /**
     * Generate light-mode CSS custom properties for :root.
     *
     * @return string CSS (no <style> tags)
     */
    public function to_css(): string {
        $tokens = $this->get_tokens();
        $lines  = array();

        $lines[] = ':root {';

        // Color scales
        foreach ( array( 'primary', 'secondary', 'accent' ) as $name ) {
            $lines[] = "    /* {$name} scale */";
            foreach ( array( 50, 100, 200, 300, 400, 500, 600, 700, 800, 900 ) as $step ) {
                $val     = $tokens[ "{$name}.{$step}" ];
                $lines[] = "    --fd-{$name}-{$step}: {$val};";
            }
        }

        // Gray scale
        $lines[] = '    /* gray scale */';
        foreach ( array( 50, 100, 200, 300, 400, 500, 600, 700, 800, 900 ) as $step ) {
            $val     = $tokens[ "gray.{$step}" ];
            $lines[] = "    --fd-gray-{$step}: {$val};";
        }

        // Semantic colors
        $lines[] = '    /* semantic colors */';
        foreach ( array( 'success', 'warning', 'error', 'info' ) as $name ) {
            $lines[] = "    --fd-{$name}: {$tokens["semantic.{$name}"]};";
            $lines[] = "    --fd-{$name}-light: {$tokens["semantic.{$name}.light"]};";
            $lines[] = "    --fd-{$name}-dark: {$tokens["semantic.{$name}.dark"]};";
        }

        // State colors
        $lines[] = '    /* state colors */';
        $lines[] = "    --fd-state-hover: {$tokens['state.hover']};";
        $lines[] = "    --fd-state-active: {$tokens['state.active']};";
        $lines[] = "    --fd-state-disabled: {$tokens['state.disabled']};";
        $lines[] = "    --fd-state-focus: {$tokens['state.focus']};";

        // Backward-compatible aliases
        $lines[] = '    /* backward-compatible aliases */';
        $lines[] = '    --fd-primary: var(--fd-primary-500);';
        $lines[] = '    --fd-primary-hover: var(--fd-primary-600);';
        $lines[] = '    --fd-primary-light: var(--fd-primary-100);';

        $lines[] = '}';

        return implode( "\n", $lines ) . "\n";
    }

    /**
     * Generate dark-mode CSS custom properties.
     *
     * @param string $strategy 'media-query' | 'data-attribute' | 'both'
     * @return string CSS
     */
    public function to_css_dark( string $strategy = 'both' ): string {
        $dark_vars = $this->generate_dark_mode_vars();

        $inner = '';
        foreach ( $dark_vars as $var_name => $value ) {
            $inner .= "    {$var_name}: {$value};\n";
        }

        $css = '';

        if ( $strategy === 'data-attribute' || $strategy === 'both' ) {
            $css .= "[data-theme=\"dark\"] {\n{$inner}}\n";
        }

        if ( $strategy === 'media-query' || $strategy === 'both' ) {
            $css .= "@media (prefers-color-scheme: dark) {\n";
            $css .= "    :root:not([data-theme=\"light\"]) {\n";
            // Re-indent inner
            foreach ( $dark_vars as $var_name => $value ) {
                $css .= "        {$var_name}: {$value};\n";
            }
            $css .= "    }\n}\n";
        }

        return $css;
    }

    /**
     * Generate combined light + dark CSS.
     *
     * @param string $dark_strategy
     * @return string
     */
    public function to_css_all( string $dark_strategy = 'both' ): string {
        return $this->to_css() . "\n" . $this->to_css_dark( $dark_strategy );
    }

    // ─── Output: GraphQL ────────────────────────────────────────

    /**
     * Return tokens as camelCase array for GraphQL resolver.
     *
     * @return array<string, string>
     */
    public function to_graphql_array(): array {
        $tokens = $this->get_tokens();
        $result = array();

        // Color scales: primary.500 → primary500
        foreach ( $tokens as $key => $value ) {
            $camel = str_replace( '.', '', $key );
            // semantic.success → semanticSuccess, semantic.success.light → semanticSuccessLight
            $parts = explode( '.', $key );
            if ( count( $parts ) === 2 ) {
                $camel = $parts[0] . $parts[1];
            } elseif ( count( $parts ) === 3 ) {
                $camel = $parts[0] . ucfirst( $parts[1] ) . ucfirst( $parts[2] );
            }
            $result[ $camel ] = $value;
        }

        // Add full CSS strings for direct frontend injection
        $result['cssVariables']     = $this->to_css();
        $result['cssVariablesDark'] = $this->to_css_dark( 'both' );

        return $result;
    }

    /**
     * Return GraphQL field definitions for registering FDDesignTokens type.
     *
     * @return array WPGraphQL field definition array
     */
    public static function graphql_field_definitions(): array {
        $fields = array();

        // Color scales
        foreach ( array( 'primary', 'secondary', 'accent', 'gray' ) as $name ) {
            foreach ( array( 50, 100, 200, 300, 400, 500, 600, 700, 800, 900 ) as $step ) {
                $fields[ "{$name}{$step}" ] = array(
                    'type'        => 'String',
                    'description' => ucfirst( $name ) . " color step {$step}",
                );
            }
        }

        // Semantic colors
        foreach ( array( 'success', 'warning', 'error', 'info' ) as $name ) {
            $fields[ "semantic{$name}" ] = array(
                'type'        => 'String',
                'description' => ucfirst( $name ) . ' base color',
            );
            $fields[ "semantic" . ucfirst( $name ) . "Light" ] = array(
                'type'        => 'String',
                'description' => ucfirst( $name ) . ' light variant',
            );
            $fields[ "semantic" . ucfirst( $name ) . "Dark" ] = array(
                'type'        => 'String',
                'description' => ucfirst( $name ) . ' dark variant',
            );
        }

        // State colors
        foreach ( array( 'hover', 'active', 'disabled', 'focus' ) as $state ) {
            $fields[ "state" . ucfirst( $state ) ] = array(
                'type'        => 'String',
                'description' => "State color: {$state}",
            );
        }

        // Full CSS string fields
        $fields['cssVariables'] = array(
            'type'        => 'String',
            'description' => 'Complete light-mode CSS custom properties string',
        );
        $fields['cssVariablesDark'] = array(
            'type'        => 'String',
            'description' => 'Dark-mode CSS custom properties string',
        );

        return $fields;
    }

    // ─── Scale generation ───────────────────────────────────────

    /**
     * Generate a 10-step color scale (50~900) from a base hex.
     * The base color is anchored at step 500.
     *
     * @param string $hex Base brand color
     * @return array<int, string> [50 => '#hex', ..., 900 => '#hex']
     */
    public function generate_scale( string $hex ): array {
        $color = FD_Color::from_hex( $hex );
        $h     = $color->h;
        $s     = $color->s;

        // Clamp the base lightness to a usable midrange for step 500
        $l500 = max( 40, min( 55, $color->l ) );

        $scale = array();

        foreach ( self::SCALE_STEPS as $step => $params ) {
            $target_l = ( $step === 500 ) ? $l500 : $params['l'];
            $adj_s    = $s * $params['sm'];

            $new_color   = new FD_Color( $h, $adj_s, $target_l );
            $scale[ $step ] = $new_color->to_hex();
        }

        return $scale;
    }

    /**
     * Generate semantic color variants (base, light, dark).
     *
     * @param string $hex
     * @return array ['base' => hex, 'light' => hex, 'dark' => hex]
     */
    public function generate_semantic_variants( string $hex ): array {
        $color = FD_Color::from_hex( $hex );
        return array(
            'base'  => $color->to_hex(),
            'light' => ( new FD_Color( $color->h, $color->s * 0.5, 92 ) )->to_hex(),
            'dark'  => ( new FD_Color( $color->h, $color->s, 30 ) )->to_hex(),
        );
    }

    /**
     * Generate state color variants from a base hex.
     *
     * @param string $hex
     * @return array ['hover' => hex, 'active' => hex, 'disabled' => hex, 'focus' => hex]
     */
    public function generate_state_variants( string $hex ): array {
        $color = FD_Color::from_hex( $hex );
        return array(
            'hover'    => $color->darken( 8 )->to_hex(),
            'active'   => $color->darken( 15 )->to_hex(),
            'disabled' => ( new FD_Color( $color->h, $color->s * 0.3, 75 ) )->to_hex(),
            'focus'    => $color->lighten( 35 )->to_hex(),
        );
    }

    // ─── Dark mode ──────────────────────────────────────────────

    /**
     * Generate dark mode CSS variable overrides.
     *
     * @return array<string, string> CSS variable name => value
     */
    private function generate_dark_mode_vars(): array {
        $vars = array();

        // Color scales — use dark lightness targets
        foreach ( array( 'primary', 'secondary', 'accent' ) as $name ) {
            $color = FD_Color::from_hex( $this->brand[ $name ] );

            foreach ( self::DARK_SCALE_STEPS as $step => $params ) {
                $adj_s = $color->s * $params['sm'];
                $new   = new FD_Color( $color->h, $adj_s, $params['l'] );
                $vars[ "--fd-{$name}-{$step}" ] = $new->to_hex();
            }
        }

        // Gray scale — reversed
        $grays = $this->get_gray_scale();
        $steps = array( 50, 100, 200, 300, 400, 500, 600, 700, 800, 900 );
        $reversed_steps = array_reverse( $steps );
        foreach ( $steps as $i => $step ) {
            $source_step = $reversed_steps[ $i ];
            $vars[ "--fd-gray-{$step}" ] = $grays[ $source_step ];
        }

        // Semantic colors — adjusted for dark backgrounds
        foreach ( $this->semantic as $name => $hex ) {
            $color = FD_Color::from_hex( $hex );
            // Base: slightly lighter and desaturated
            $dark_base = new FD_Color( $color->h, $color->s * 0.85, max( $color->l, 55 ) );
            // Light: very dark tinted background
            $dark_light = new FD_Color( $color->h, $color->s * 0.4, 15 );
            // Dark: lighter for use as text on dark backgrounds
            $dark_dark = new FD_Color( $color->h, $color->s * 0.6, 75 );

            $vars[ "--fd-{$name}" ]       = $dark_base->to_hex();
            $vars[ "--fd-{$name}-light" ] = $dark_light->to_hex();
            $vars[ "--fd-{$name}-dark" ]  = $dark_dark->to_hex();
        }

        // State colors for dark mode
        $primary_dark = FD_Color::from_hex( $vars['--fd-primary-500'] );
        $vars['--fd-state-hover']    = $primary_dark->lighten( 10 )->to_hex();
        $vars['--fd-state-active']   = $primary_dark->lighten( 18 )->to_hex();
        $vars['--fd-state-disabled'] = ( new FD_Color( $primary_dark->h, $primary_dark->s * 0.2, 35 ) )->to_hex();
        $vars['--fd-state-focus']    = $primary_dark->darken( 30 )->to_hex();

        // Backward-compatible aliases remain as var() references — no override needed

        return $vars;
    }

    // ─── Helpers ────────────────────────────────────────────────

    /**
     * Get the gray scale (with optional overrides applied).
     *
     * @return array<int, string>
     */
    private function get_gray_scale(): array {
        return array_replace( self::DEFAULT_GRAYS, $this->gray_overrides );
    }

    /**
     * Detect if fd-theme is the active theme.
     * Mirrors the logic from FD_VI_Settings::is_fd_theme_active().
     *
     * @return bool
     */
    private static function is_fd_theme_active(): bool {
        if ( ! function_exists( 'wp_get_theme' ) ) {
            return false;
        }
        $theme = wp_get_theme();
        return (
            $theme->get_template() === 'fd-theme'
            || $theme->get( 'TextDomain' ) === 'fd-theme'
            || function_exists( 'fd_register_graphql_vi_settings' )
        );
    }
}
