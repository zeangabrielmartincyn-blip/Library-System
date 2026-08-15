@include('LoginPage.role-login', [
    'role' => 'Guest',
    'message' => 'Any mobile number',
    'loginAction' => route('login.store', 'guest'),
])


