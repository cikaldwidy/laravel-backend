<?php

return [
    'office_latitude'          => (float) env('ATTENDANCE_OFFICE_LATITUDE', -6.123456),
    'office_longitude'         => (float) env('ATTENDANCE_OFFICE_LONGITUDE', 106.123456),
    'radius_meters'            => (int)   env('ATTENDANCE_RADIUS_METERS', 100),

    // Dinaikkan dari 0.55 → 0.65 agar lebih toleran terhadap variasi pencahayaan/sudut
    'face_threshold'           => (float) env('ATTENDANCE_FACE_THRESHOLD', 0.65),

    'challenge_ttl_seconds'    => (int)   env('ATTENDANCE_CHALLENGE_TTL_SECONDS', 600),
    'min_brightness'           => (float) env('ATTENDANCE_MIN_BRIGHTNESS', 30),
    'max_brightness'           => (float) env('ATTENDANCE_MAX_BRIGHTNESS', 220),
    'min_sharpness'            => (float) env('ATTENDANCE_MIN_SHARPNESS', 8),
];