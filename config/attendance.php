<?php

return [
    'office_latitude'          => (float) env('ATTENDANCE_OFFICE_LATITUDE', -6.123456),
    'office_longitude'         => (float) env('ATTENDANCE_OFFICE_LONGITUDE', 106.123456),
    'radius_meters'            => (int)   env('ATTENDANCE_RADIUS_METERS', 100),

    // Lebih kecil berarti lebih ketat; 0.35 membantu menolak wajah yang berbeda.
    'face_threshold'           => (float) env('ATTENDANCE_FACE_THRESHOLD', 0.35),
    'face_duplicate_threshold' => (float) env('ATTENDANCE_FACE_DUPLICATE_THRESHOLD', 0.45),
    'enrollment_average_threshold' => (float) env('ATTENDANCE_ENROLLMENT_AVERAGE_THRESHOLD', 0.38),
    'enrollment_consistency_threshold' => (float) env('ATTENDANCE_ENROLLMENT_CONSISTENCY_THRESHOLD', 0.42),

    'challenge_ttl_seconds'    => (int)   env('ATTENDANCE_CHALLENGE_TTL_SECONDS', 600),
    'min_brightness'           => (float) env('ATTENDANCE_MIN_BRIGHTNESS', 30),
    'max_brightness'           => (float) env('ATTENDANCE_MAX_BRIGHTNESS', 220),
    'min_sharpness'            => (float) env('ATTENDANCE_MIN_SHARPNESS', 8),
    'max_location_accuracy_meters' => (float) env('ATTENDANCE_MAX_LOCATION_ACCURACY_METERS', 80),
    'web_max_location_accuracy_meters' => (float) env('ATTENDANCE_WEB_MAX_LOCATION_ACCURACY_METERS', 180),
    'fast_location_accuracy_meters' => (float) env('ATTENDANCE_FAST_LOCATION_ACCURACY_METERS', 25),
    'max_location_age_seconds' => (int) env('ATTENDANCE_MAX_LOCATION_AGE_SECONDS', 20),
    'max_client_time_skew_seconds' => (int) env('ATTENDANCE_MAX_CLIENT_TIME_SKEW_SECONDS', 120),
    'required_location_samples' => (int) env('ATTENDANCE_REQUIRED_LOCATION_SAMPLES', 3),
    'web_required_location_samples' => (int) env('ATTENDANCE_WEB_REQUIRED_LOCATION_SAMPLES', 2),
    'max_location_sample_spread_meters' => (float) env('ATTENDANCE_MAX_LOCATION_SAMPLE_SPREAD_METERS', 35),
];
