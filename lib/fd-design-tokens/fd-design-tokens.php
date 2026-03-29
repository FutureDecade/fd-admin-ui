<?php
/**
 * FD Design Tokens - Version Competition Loader
 *
 * Both fd-theme and fd-admin-ui bundle this file identically.
 * At runtime, the copy with the highest version wins and loads the classes.
 *
 * @package FD_Design_Tokens
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// This file's version — MUST be kept in sync with FD_Design_Tokens::VERSION
$_fd_dt_version = '1.0.0';

// Register this candidate
if ( ! isset( $GLOBALS['_fd_design_tokens_candidates'] ) ) {
    $GLOBALS['_fd_design_tokens_candidates'] = array();
}

$GLOBALS['_fd_design_tokens_candidates'][] = array(
    'version' => $_fd_dt_version,
    'path'    => __DIR__,
);

// Define the resolver function and attach it only once
if ( ! function_exists( '_fd_design_tokens_resolve_winner' ) ) {

    /**
     * Resolve which candidate copy to load based on version comparison.
     * Runs at after_setup_theme priority 0, before any consumer code.
     */
    function _fd_design_tokens_resolve_winner() {
        // Guard: already loaded
        if ( defined( 'FD_DESIGN_TOKENS_VERSION' ) ) {
            return;
        }

        $candidates = $GLOBALS['_fd_design_tokens_candidates'];

        // Sort descending by version — highest first
        usort( $candidates, function ( $a, $b ) {
            return version_compare( $b['version'], $a['version'] );
        } );

        $winner = $candidates[0];

        define( 'FD_DESIGN_TOKENS_VERSION', $winner['version'] );
        define( 'FD_DESIGN_TOKENS_PATH', $winner['path'] );

        require_once $winner['path'] . '/class-fd-color.php';
        require_once $winner['path'] . '/class-fd-design-tokens.php';
    }

    add_action( 'after_setup_theme', '_fd_design_tokens_resolve_winner', 0 );
}
