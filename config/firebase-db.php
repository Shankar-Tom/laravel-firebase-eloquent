<?php

return [
    // Realtime Database only (this package). Keep Firestore for future extension if you want.
    'credentials' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase/credentials.json')),

    // Your Realtime Database URL, e.g. https://your-project-id-default-rtdb.firebaseio.com
    'database_url' => env('FIREBASE_DATABASE_URL', ''),

    // Default per-page for paginate()
    'per_page' => env('FIREBASE_DB_PER_PAGE', 15),

    // Enable automatic created_at / updated_at
    'timestamps' => true,
];
