@include('LoginPage.role-login', [
    'role' => 'Librarian',
    'message' => 'Manage catalog records, borrowing requests, returns, penalties, and book availability.',
    'loginAction' => route('login.store', 'librarian'),
])


