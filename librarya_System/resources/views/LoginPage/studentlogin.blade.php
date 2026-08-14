@include('LoginPage.role-login', [
    'role' => 'Student',
    'message' => 'Search books, reserve materials, view borrowing status, and track due dates.',
    'loginAction' => route('login.store', 'student'),
])


