<?php
/**
 * FD_Color — HSL color math utility.
 *
 * Immutable value object: every transformation returns a new instance.
 * Pure PHP, no external dependencies.
 *
 * @package FD_Design_Tokens
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FD_Color {

    /** @var float Hue 0-360 */
    public $h;

    /** @var float Saturation 0-100 */
    public $s;

    /** @var float Lightness 0-100 */
    public $l;

    /**
     * @param float $h Hue 0-360
     * @param float $s Saturation 0-100
     * @param float $l Lightness 0-100
     */
    public function __construct( float $h, float $s, float $l ) {
        $this->h = fmod( $h + 360, 360 );
        $this->s = max( 0, min( 100, $s ) );
        $this->l = max( 0, min( 100, $l ) );
    }

    // ─── Factories ──────────────────────────────────────────────

    /**
     * Parse a hex color string (#RGB or #RRGGBB) into an FD_Color.
     *
     * @param string $hex
     * @return self
     */
    public static function from_hex( string $hex ): self {
        $hex = ltrim( $hex, '#' );

        // Expand shorthand (#RGB → #RRGGBB)
        if ( strlen( $hex ) === 3 ) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $r = hexdec( substr( $hex, 0, 2 ) ) / 255;
        $g = hexdec( substr( $hex, 2, 2 ) ) / 255;
        $b = hexdec( substr( $hex, 4, 2 ) ) / 255;

        $max   = max( $r, $g, $b );
        $min   = min( $r, $g, $b );
        $delta = $max - $min;

        // Lightness
        $l = ( $max + $min ) / 2;

        if ( $delta == 0 ) {
            $h = 0;
            $s = 0;
        } else {
            // Saturation
            $s = $l > 0.5
                ? $delta / ( 2 - $max - $min )
                : $delta / ( $max + $min );

            // Hue
            if ( $max === $r ) {
                $h = fmod( ( $g - $b ) / $delta, 6 );
            } elseif ( $max === $g ) {
                $h = ( $b - $r ) / $delta + 2;
            } else {
                $h = ( $r - $g ) / $delta + 4;
            }

            $h *= 60;
            if ( $h < 0 ) {
                $h += 360;
            }
        }

        return new self( $h, $s * 100, $l * 100 );
    }

    // ─── Converters ─────────────────────────────────────────────

    /**
     * Convert to hex string (#rrggbb).
     *
     * @return string
     */
    public function to_hex(): string {
        $h = $this->h / 360;
        $s = $this->s / 100;
        $l = $this->l / 100;

        if ( $s == 0 ) {
            $r = $g = $b = $l;
        } else {
            $q = $l < 0.5
                ? $l * ( 1 + $s )
                : $l + $s - $l * $s;
            $p = 2 * $l - $q;

            $r = self::hue_to_rgb( $p, $q, $h + 1 / 3 );
            $g = self::hue_to_rgb( $p, $q, $h );
            $b = self::hue_to_rgb( $p, $q, $h - 1 / 3 );
        }

        return sprintf(
            '#%02x%02x%02x',
            (int) round( $r * 255 ),
            (int) round( $g * 255 ),
            (int) round( $b * 255 )
        );
    }

    /**
     * Return CSS hsl() string.
     *
     * @return string e.g. "hsl(215, 90%, 52%)"
     */
    public function to_hsl_string(): string {
        return sprintf(
            'hsl(%s, %s%%, %s%%)',
            round( $this->h, 1 ),
            round( $this->s, 1 ),
            round( $this->l, 1 )
        );
    }

    // ─── Transformations (all return new instance) ──────────────

    /**
     * Set absolute lightness.
     *
     * @param float $lightness 0-100
     * @return self
     */
    public function with_lightness( float $lightness ): self {
        return new self( $this->h, $this->s, $lightness );
    }

    /**
     * Set absolute saturation.
     *
     * @param float $saturation 0-100
     * @return self
     */
    public function with_saturation( float $saturation ): self {
        return new self( $this->h, $saturation, $this->l );
    }

    /**
     * Darken by a relative percentage of current lightness.
     *
     * @param float $amount 0-100
     * @return self
     */
    public function darken( float $amount ): self {
        $new_l = $this->l - ( $this->l * $amount / 100 );
        return new self( $this->h, $this->s, $new_l );
    }

    /**
     * Lighten by a relative percentage of remaining lightness headroom.
     *
     * @param float $amount 0-100
     * @return self
     */
    public function lighten( float $amount ): self {
        $new_l = $this->l + ( ( 100 - $this->l ) * $amount / 100 );
        return new self( $this->h, $this->s, $new_l );
    }

    /**
     * Adjust saturation by a relative percentage.
     *
     * @param float $amount Positive = more saturated, negative = less
     * @return self
     */
    public function saturate( float $amount ): self {
        if ( $amount >= 0 ) {
            $new_s = $this->s + ( ( 100 - $this->s ) * $amount / 100 );
        } else {
            $new_s = $this->s + ( $this->s * $amount / 100 );
        }
        return new self( $this->h, $new_s, $this->l );
    }

    /**
     * Mix with another color.
     *
     * @param FD_Color $other
     * @param float    $weight 0-1 (0 = all $this, 1 = all $other)
     * @return self
     */
    public function mix( self $other, float $weight = 0.5 ): self {
        $w = max( 0, min( 1, $weight ) );

        // Mix in RGB space for perceptual accuracy
        list( $r1, $g1, $b1 ) = $this->to_rgb_array();
        list( $r2, $g2, $b2 ) = $other->to_rgb_array();

        $r = $r1 * ( 1 - $w ) + $r2 * $w;
        $g = $g1 * ( 1 - $w ) + $g2 * $w;
        $b = $b1 * ( 1 - $w ) + $b2 * $w;

        $hex = sprintf( '#%02x%02x%02x', (int) round( $r ), (int) round( $g ), (int) round( $b ) );
        return self::from_hex( $hex );
    }

    /**
     * Calculate WCAG 2.1 relative luminance.
     *
     * @return float 0-1
     */
    public function luminance(): float {
        list( $r, $g, $b ) = $this->to_rgb_array();

        $r = $r / 255;
        $g = $g / 255;
        $b = $b / 255;

        $r = $r <= 0.03928 ? $r / 12.92 : pow( ( $r + 0.055 ) / 1.055, 2.4 );
        $g = $g <= 0.03928 ? $g / 12.92 : pow( ( $g + 0.055 ) / 1.055, 2.4 );
        $b = $b <= 0.03928 ? $b / 12.92 : pow( ( $b + 0.055 ) / 1.055, 2.4 );

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    /**
     * Calculate WCAG contrast ratio against another color.
     *
     * @param FD_Color $other
     * @return float 1-21
     */
    public function contrast_ratio( self $other ): float {
        $l1 = $this->luminance();
        $l2 = $other->luminance();

        $lighter = max( $l1, $l2 );
        $darker  = min( $l1, $l2 );

        return ( $lighter + 0.05 ) / ( $darker + 0.05 );
    }

    // ─── Internal helpers ───────────────────────────────────────

    /**
     * Convert to [R, G, B] array (0-255 each).
     *
     * @return array [int, int, int]
     */
    private function to_rgb_array(): array {
        $h = $this->h / 360;
        $s = $this->s / 100;
        $l = $this->l / 100;

        if ( $s == 0 ) {
            $v = (int) round( $l * 255 );
            return array( $v, $v, $v );
        }

        $q = $l < 0.5
            ? $l * ( 1 + $s )
            : $l + $s - $l * $s;
        $p = 2 * $l - $q;

        return array(
            (int) round( self::hue_to_rgb( $p, $q, $h + 1 / 3 ) * 255 ),
            (int) round( self::hue_to_rgb( $p, $q, $h ) * 255 ),
            (int) round( self::hue_to_rgb( $p, $q, $h - 1 / 3 ) * 255 ),
        );
    }

    /**
     * HSL hue-to-RGB channel helper.
     *
     * @param float $p
     * @param float $q
     * @param float $t
     * @return float 0-1
     */
    private static function hue_to_rgb( float $p, float $q, float $t ): float {
        if ( $t < 0 ) $t += 1;
        if ( $t > 1 ) $t -= 1;

        if ( $t < 1 / 6 ) return $p + ( $q - $p ) * 6 * $t;
        if ( $t < 1 / 2 ) return $q;
        if ( $t < 2 / 3 ) return $p + ( $q - $p ) * ( 2 / 3 - $t ) * 6;

        return $p;
    }
}
