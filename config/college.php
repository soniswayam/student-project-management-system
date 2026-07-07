<?php

/*
|--------------------------------------------------------------------------
| College / Institute details
|--------------------------------------------------------------------------
| Shown in the header of every generated PDF (reports, project reports,
| certificates). Change these values here or override them in your .env file.
*/

return [
    'name' => env('COLLEGE_NAME', 'Your College Name'),
    'tagline' => env('COLLEGE_TAGLINE', 'Department of Computer Applications'),
    'address' => env('COLLEGE_ADDRESS', 'College Road, City, State - 000000'),
    'affiliation' => env('COLLEGE_AFFILIATION', 'Affiliated to XYZ University'),
    'email' => env('COLLEGE_EMAIL', 'info@yourcollege.edu'),
    'phone' => env('COLLEGE_PHONE', '+91 00000 00000'),
    'website' => env('COLLEGE_WEBSITE', 'www.yourcollege.edu'),
];
