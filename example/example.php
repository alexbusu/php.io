<?php

require __DIR__ . '/../src/io.php';

use Phpio\io;

$iterations = 123;

io::write( "\nProgress bar... ", false );

io::newProgressBar( [ 'length' => 50, 'total' => $iterations ] );

for( $i = 1; $i <= $iterations; $i++ ) {
    io::updateProgressBar();
    usleep( 10000 );
}

io::write( "\nPercentage... ", false );

io::newProgressPercentage( [ 'decimals' => 1, 'format' => '%s%% done', 'total' => $iterations ] );

for( $i = 1; $i <= $iterations; $i++ ) {
    io::updateProgressPercentage();
    usleep( 30000 );
}
