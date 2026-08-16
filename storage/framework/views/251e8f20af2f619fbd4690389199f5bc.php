<?php echo $__env->make('LoginPage.role-login', [
    'role' => 'Librarian',
    'message' => 'Manage catalog records, borrowing requests, returns, penalties, and book availability.',
    'loginAction' => route('login.store', 'librarian'),
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


<?php /**PATH C:\laragon\www\system integ\librarya_System\resources\views/LoginPage/librarianlogin.blade.php ENDPATH**/ ?>