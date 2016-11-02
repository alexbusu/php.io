<?php

namespace Phpio;

class io
{

    private static $stream = null; // defaults to STDOUT in CLI mode

    private static $progressBarDefaults = [
        'length' => 20,
        'last'   => '',
    ];

    private static $progressBarOptions = null;

    private static $progressDefaults = [
        'decimals' => 0,
        'last'     => '',
    ];

    public static $progressOptions;

    public static function newProgressBar( array $options = [ ] )
    {
        self::$progressBarOptions = array_merge( self::$progressBarDefaults, $options );
    }

    public static function updateProgressBar( $iTotal, $iCurrent, $format = '[%s]' )
    {
        $position = ceil( self::$progressBarOptions[ 'length' ] * $iCurrent / $iTotal );
        self::$progressBarOptions[ 'last' ]
            = $output
            = ( self::getCarriageReturn( self::$progressBarOptions[ 'last' ] )
            . sprintf( $format, str_pad( str_repeat( '-', $position ), self::$progressBarOptions[ 'length' ], ' ' ) ) );
        self::output( $output, $iTotal == $iCurrent );
    }

    public static function newProgressPercentage( array $options = [ ] )
    {
        self::$progressOptions = array_merge( self::$progressDefaults, $options );
    }

    public static function updateProgressPercentage( $iTotal, $iCurrent, $format = '%s' )
    {
        $position = self::getProgressPercentage( $iTotal, $iCurrent, self::$progressOptions[ 'decimals' ] );
        $cr = self::getCarriageReturn( self::$progressOptions[ 'last' ] );
        self::$progressOptions[ 'last' ] = sprintf( $format, $position );
        self::output( $cr . self::$progressOptions[ 'last' ], $iTotal == $iCurrent );
    }

    private static function getCarriageReturn( $str )
    {
        return
            self::getStream() === STDOUT ? ( ( $str ? sprintf( "\x1B[%uD", strlen( $str ) ) : '' ) . "\x1B[K" ) :
                str_repeat( chr( 8 ), strlen( $str ) ) . "\x1B[K";
    }

    public static function getProgressPercentage( $iTotal, $iCurrent, $decimals = 0 )
    {
        return number_format( 100 * $iCurrent / $iTotal, $decimals );
    }

    /**
     * Converts the message to string
     * @param mixed     $message
     * @param bool|true $newline
     */
    public static function write( $message, $newline = true )
    {
        self::output( print_r( $message, true ), $newline );
    }

    /**
     * Outputs the message to stream
     * @param string    $message
     * @param bool|true $newline
     */
    public static function output( $message, $newline = true )
    {

        // in CLI mode use STDOUT by default
        if( self::getStream() ) {

            // CLI - output to given stream
            fputs( self::getStream(), $message . ( $newline ? PHP_EOL : '' ) );

        } elseif( ob_get_level() ) {

            // Web but output buffering is on - bypass it
            $buffer = ob_get_clean();
            echo $message;
            flush();
            ob_start();
            echo $buffer;

        } else {

            // Web without output buffering
            echo $message . str_repeat( ' ', 2048 ) . PHP_EOL;
            flush();

        }
    }

    public static function confirm( $question, array $available_options = [ 'y', 'n' ], $confirmation_option = 'y', $default_option = 'y' )
    {
        if( !empty( $_GET[ 'cron_run' ] ) ) return 'y';
        // convert all answer options to lower case
        array_walk( $available_options, function( &$row ){ $row = strtolower( $row ); } );
        $confirmation_option = strtolower( $confirmation_option );
        $default_option = strtolower( $default_option );
        // set printable options
        $options_print = $available_options;
        // convert printable options to lower case except the default option, which will be upper cased
        array_walk( $options_print, function( &$row ) use ( $default_option ){
            $row = 0 === strcasecmp( $default_option, $row ) ? strtoupper( $row ) : strtolower( $row );
        } );
        // if the default option isn't found in available options then make it NULL
        if( !array_search( strtoupper( $default_option ), $options_print ) ) $default_option_key = null;

        self::write( $question . ' [' . implode( '/', $options_print ) . '] ', false );

        do {
            $one_more_try = false; // by default
            $in = trim( fgets( STDIN ) );
            if( !$in && $default_option ) {
                $in = $default_option;
            } else {
                $in = strtolower( substr( $in, 0, 1 ) );
                if( !in_array( $in, $available_options ) ) $one_more_try = true;
            }
        } while( $one_more_try );

        return $confirmation_option === $in;
    }

    public static function setStream( $stream )
    {
        self::$stream = $stream;
    }

    public static function getStream()
    {
        return self::$stream ? self::$stream : ( 'cli' === PHP_SAPI ? ( self::$stream = STDOUT ) : null );
    }

}
