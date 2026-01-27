<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Firebase Project ID
    |--------------------------------------------------------------------------
    |
    | The Firebase project identifier. This can be found in your Firebase
    | console project settings.
    |
    */

    'project_id' => env('FIREBASE_PROJECT_ID'),

    /*
    |--------------------------------------------------------------------------
    | Firebase Credentials Path
    |--------------------------------------------------------------------------
    |
    | Path to the Firebase service account credentials JSON file.
    | This should be relative to the Laravel base path.
    |
    */

    'credentials_path' => env('FIREBASE_CREDENTIALS', 'storage/app/firebase-credentials.json'),

];
