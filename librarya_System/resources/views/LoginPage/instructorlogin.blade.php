@include('LoginPage.role-login', [
    'role' => 'Instructor',
    'message' => 'Access teaching references, request materials, and support student research needs.',
    'loginAction' => route('login.store', 'instructor'),
])


