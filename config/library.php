<?php

return [
    'fine_amount_per_day' => (float) env('LIBRARY_FINE_AMOUNT_PER_DAY', 5),

    // Set the official ISU schedule here when it is confirmed by the library.
    // Null values keep the dashboard from displaying inaccurate opening hours.
    'hours' => [
        ['day' => 'Monday', 'open' => null, 'close' => null],
        ['day' => 'Tuesday', 'open' => null, 'close' => null],
        ['day' => 'Wednesday', 'open' => null, 'close' => null],
        ['day' => 'Thursday', 'open' => null, 'close' => null],
        ['day' => 'Friday', 'open' => null, 'close' => null],
        ['day' => 'Saturday', 'open' => null, 'close' => null],
        ['day' => 'Sunday', 'open' => null, 'close' => null],
    ],
];

