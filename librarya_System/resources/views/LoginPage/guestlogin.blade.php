@include('LoginPage.role-login', [
    'role' => 'Guest',
    'message' => 'Sign in with any mobile number to browse public library resources, search the catalog, and read announcements.',
    'loginAction' => route('login.store', 'guest'),
])


