<?php

require __DIR__ . '/../src/io.php';

use Phpio\io;

$iterations = 123;

io::write( 'Starting...' );

io::newProgressBar( [ 'length' => 50 ] );

for( $i = 1; $i <= $iterations; $i++ ) {
    io::updateProgressBar( $iterations, $i );
    usleep( 10000 );
}

io::write( "\n\nNext... ", false );

io::newProgressPercentage( [ 'decimals' => 1 ] );

for( $i = 1; $i <= $iterations; $i++ ) {
    io::updateProgressPercentage( $iterations, $i, '%s%% done' . ( $iterations == $i ? '!' : '...' ) );
    usleep( 30000 );
}
