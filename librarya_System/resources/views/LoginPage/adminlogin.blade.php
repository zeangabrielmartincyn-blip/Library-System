@include('LoginPage.role-login', [
    'role' => 'Admin',
    'message' => 'Control user access, review library activity, and keep the BookTrack system organized.',
    'loginAction' => route('login.store', 'admin'),
])


