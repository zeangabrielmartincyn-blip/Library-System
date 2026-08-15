<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\BookScanController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\GuestDashboardController;
use App\Http\Controllers\InstructorDashboardController;
use App\Http\Controllers\LibrarianDashboardController;
use App\Http\Controllers\RoleLoginController;
use App\Http\Controllers\StudentDashboardController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'index')->name('home');

Route::view('/login/admin', 'LoginPage.adminlogin')->name('login.admin');
Route::view('/login/librarian', 'LoginPage.librarianlogin')->name('login.librarian');
Route::view('/login/instructor', 'LoginPage.instructorlogin')->name('login.instructor');
Route::view('/login/student', 'LoginPage.studentlogin')->name('login.student');
Route::view('/login/guest', 'LoginPage.guestlogin')->name('login.guest');

Route::post('/login/{role}', [RoleLoginController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('login.store');
Route::get('/register/student', [RoleLoginController::class, 'showStudentRegistrationForm'])->name('register.student.form');
Route::post('/register/student', [RoleLoginController::class, 'registerStudent'])
    ->middleware('throttle:10,1')
    ->name('register.student');
Route::post('/logout', [RoleLoginController::class, 'destroy'])->name('logout');

Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
    ->middleware('throttle:5,1')
    ->name('password.email');
Route::get('/forgot-password/verify', [ForgotPasswordController::class, 'showOtpForm'])->name('password.otp.form');
Route::post('/forgot-password/verify', [ForgotPasswordController::class, 'verifyOtp'])->name('password.otp.verify');
Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');

Route::get('/scan/{token}', [BookScanController::class, 'showScanner'])->name('scan.show');
Route::post('/scan/{token}', [BookScanController::class, 'submitBookScan'])->name('scan.submit');
Route::get('/scan/{token}/status', [BookScanController::class, 'bookScanStatus'])->name('scan.status');

Route::middleware('auth')->group(function () {
    Route::post('/dashboard/chat', [ChatController::class, 'store'])->middleware('throttle:20,1')->name('chat.send');

    Route::get('/dashboard/admin', [AdminDashboardController::class, 'dashboard'])->name('dashboard.admin');
    Route::post('/dashboard/admin/chat', [AdminDashboardController::class, 'chat'])->name('admin.chat');
    Route::get('/dashboard/admin/users', [AdminDashboardController::class, 'users'])->name('admin.users');
    Route::post('/dashboard/admin/users', [AdminDashboardController::class, 'storeUser'])->name('admin.users.store');
    Route::get('/dashboard/admin/books', [AdminDashboardController::class, 'books'])->name('admin.books');
    Route::post('/dashboard/admin/books', [AdminDashboardController::class, 'storeBook'])->name('admin.books.store');
    Route::post('/dashboard/admin/books/{isbn}/issue', [AdminDashboardController::class, 'issueBook'])->name('admin.books.issue');
    Route::patch('/dashboard/admin/books/{isbn}', [AdminDashboardController::class, 'updateBook'])->name('admin.books.update');
    Route::delete('/dashboard/admin/books/{isbn}', [AdminDashboardController::class, 'deleteBook'])->name('admin.books.destroy');
    Route::get('/dashboard/admin/reports', [AdminDashboardController::class, 'reports'])->name('admin.reports');
    Route::get('/dashboard/admin/activity', [AdminDashboardController::class, 'activity'])->name('admin.activity');
    Route::get('/dashboard/admin/login-history', [AdminDashboardController::class, 'loginHistory'])->name('admin.login-history');
    Route::get('/dashboard/admin/announcements', [AdminDashboardController::class, 'announcements'])->name('admin.announcements');
    Route::post('/dashboard/admin/announcements', [AdminDashboardController::class, 'storeAnnouncement'])->name('admin.announcements.store');
    Route::patch('/dashboard/admin/announcements/{announcementId}', [AdminDashboardController::class, 'updateAnnouncement'])->name('admin.announcements.update');
    Route::delete('/dashboard/admin/announcements/{announcementId}', [AdminDashboardController::class, 'deleteAnnouncement'])->name('admin.announcements.destroy');
    Route::patch('/dashboard/admin/users/{userId}/status', [AdminDashboardController::class, 'toggleUserStatus'])->name('admin.users.status');
    Route::get('/dashboard/admin/charts', [AdminDashboardController::class, 'charts'])->name('admin.charts');
    Route::get('/dashboard/admin/fines', [AdminDashboardController::class, 'fines'])->name('admin.fines');
    Route::post('/dashboard/admin/fines/recalculate', [AdminDashboardController::class, 'recalculateFines'])->name('admin.fines.recalculate');
    Route::patch('/dashboard/admin/fines/{id}/pay', [AdminDashboardController::class, 'payFine'])->name('admin.fines.pay');
    Route::patch('/dashboard/admin/fines/{id}/waive', [AdminDashboardController::class, 'waiveFine'])->name('admin.fines.waive');
    Route::get('/dashboard/admin/fines/export', [AdminDashboardController::class, 'exportFines'])->name('admin.fines.export');
    Route::get('/dashboard/admin/reviews', [AdminDashboardController::class, 'reviews'])->name('admin.reviews');
    Route::get('/dashboard/admin/reviews/export', [AdminDashboardController::class, 'exportReviews'])->name('admin.reviews.export');
    Route::get('/dashboard/admin/notifications', [AdminDashboardController::class, 'notifications'])->name('admin.notifications');
    Route::post('/dashboard/admin/notifications/read', [AdminDashboardController::class, 'markNotificationsRead'])->name('admin.notifications.read');
    Route::patch('/dashboard/admin/notifications/{notificationId}/read', [AdminDashboardController::class, 'markNotificationRead'])->name('admin.notifications.read.one');
    Route::get('/dashboard/admin/reports/export', [AdminDashboardController::class, 'exportReport'])->name('admin.reports.export');

    Route::get('/dashboard/student', [StudentDashboardController::class, 'dashboard'])->name('dashboard.student');
    Route::get('/dashboard/student/catalog', [StudentDashboardController::class, 'catalog'])->name('student.catalog');
    Route::post('/dashboard/student/catalog/{isbn}/reserve', [StudentDashboardController::class, 'reserve'])->name('student.reserve');
    Route::get('/dashboard/student/reservations', [StudentDashboardController::class, 'reservations'])->name('student.reservations');
    Route::delete('/dashboard/student/reservations/{isbn}', [StudentDashboardController::class, 'cancelReservation'])->name('student.reservations.cancel');
    Route::get('/dashboard/student/borrowed', [StudentDashboardController::class, 'borrowed'])->name('student.borrowed');
    Route::get('/dashboard/student/history', [StudentDashboardController::class, 'history'])->name('student.history');
    Route::get('/dashboard/student/profile', [StudentDashboardController::class, 'profile'])->name('student.profile');
    Route::patch('/dashboard/student/profile', [StudentDashboardController::class, 'updateProfile'])->name('student.profile.update');
    Route::patch('/dashboard/student/profile/password', [StudentDashboardController::class, 'updatePassword'])->name('student.profile.password');
    Route::post('/dashboard/student/catalog/{isbn}/review', [StudentDashboardController::class, 'submitReview'])->name('student.review');
    Route::get('/dashboard/student/fines', [StudentDashboardController::class, 'fines'])->name('student.fines');
    Route::get('/dashboard/student/notifications', [StudentDashboardController::class, 'notifications'])->name('student.notifications');
    Route::post('/dashboard/student/notifications/read', [StudentDashboardController::class, 'markNotificationsRead'])->name('student.notifications.read');
    Route::patch('/dashboard/student/notifications/{notificationId}/read', [StudentDashboardController::class, 'markNotificationRead'])->name('student.notifications.read.one');

    Route::get('/dashboard/instructor', [InstructorDashboardController::class, 'dashboard'])->name('dashboard.instructor');
    Route::get('/dashboard/instructor/catalog', [InstructorDashboardController::class, 'catalog'])->name('instructor.catalog');
    Route::post('/dashboard/instructor/catalog/{isbn}/reserve', [InstructorDashboardController::class, 'reserve'])->name('instructor.reserve');
    Route::get('/dashboard/instructor/reservations', [InstructorDashboardController::class, 'reservations'])->name('instructor.reservations');
    Route::delete('/dashboard/instructor/reservations/{isbn}', [InstructorDashboardController::class, 'cancelReservation'])->name('instructor.reservations.cancel');
    Route::get('/dashboard/instructor/borrowed', [InstructorDashboardController::class, 'borrowed'])->name('instructor.borrowed');
    Route::get('/dashboard/instructor/history', [InstructorDashboardController::class, 'history'])->name('instructor.history');
    Route::get('/dashboard/instructor/profile', [InstructorDashboardController::class, 'profile'])->name('instructor.profile');
    Route::patch('/dashboard/instructor/profile', [InstructorDashboardController::class, 'updateProfile'])->name('instructor.profile.update');
    Route::patch('/dashboard/instructor/profile/password', [InstructorDashboardController::class, 'updatePassword'])->name('instructor.profile.password');
    Route::post('/dashboard/instructor/catalog/{isbn}/review', [InstructorDashboardController::class, 'submitReview'])->name('instructor.review');
    Route::get('/dashboard/instructor/fines', [InstructorDashboardController::class, 'fines'])->name('instructor.fines');
    Route::get('/dashboard/instructor/notifications', [InstructorDashboardController::class, 'notifications'])->name('instructor.notifications');
    Route::post('/dashboard/instructor/notifications/read', [InstructorDashboardController::class, 'markNotificationsRead'])->name('instructor.notifications.read');
    Route::patch('/dashboard/instructor/notifications/{notificationId}/read', [InstructorDashboardController::class, 'markNotificationRead'])->name('instructor.notifications.read.one');

    Route::get('/dashboard/librarian', [LibrarianDashboardController::class, 'dashboard'])->name('dashboard.librarian');
    Route::post('/dashboard/librarian/books', [LibrarianDashboardController::class, 'addBook'])->name('librarian.books.store');
    Route::post('/dashboard/librarian/students', [LibrarianDashboardController::class, 'addStudent'])->name('librarian.students.store');
    Route::post('/dashboard/librarian/instructors', [LibrarianDashboardController::class, 'addInstructor'])->name('librarian.instructors.store');
    Route::post('/dashboard/librarian/announcements', [LibrarianDashboardController::class, 'storeAnnouncement'])->name('librarian.announcements.store');
    Route::patch('/dashboard/librarian/announcements/{announcementId}', [LibrarianDashboardController::class, 'updateAnnouncement'])->name('librarian.announcements.update');
    Route::delete('/dashboard/librarian/announcements/{announcementId}', [LibrarianDashboardController::class, 'deleteAnnouncement'])->name('librarian.announcements.destroy');
    Route::post('/dashboard/librarian/books/{isbn}/issue', [LibrarianDashboardController::class, 'issue'])->name('librarian.books.issue');
    Route::post('/dashboard/librarian/reservations/{reservationId}/issue', [LibrarianDashboardController::class, 'issueReservation'])->name('librarian.reservations.issue');
    Route::post('/dashboard/librarian/reservations/{reservationId}/decline', [LibrarianDashboardController::class, 'declineReservation'])->name('librarian.reservations.decline');
    Route::post('/dashboard/librarian/loans/{loanId}/return', [LibrarianDashboardController::class, 'return'])->name('librarian.loans.return');
    Route::post('/dashboard/librarian/chat', [LibrarianDashboardController::class, 'chat'])->name('librarian.chat');
    Route::post('/dashboard/librarian/users', [LibrarianDashboardController::class, 'storeUser'])->name('librarian.users.store');
    Route::get('/books/lookup/{isbn}', [LibrarianDashboardController::class, 'isbnMetadata'])->name('books.lookup');
    Route::patch('/dashboard/librarian/books/{isbn}', [LibrarianDashboardController::class, 'updateBook'])->name('librarian.books.update');
    Route::delete('/dashboard/librarian/books/{isbn}', [LibrarianDashboardController::class, 'deleteBook'])->name('librarian.books.destroy');
    Route::patch('/dashboard/librarian/users/{userId}/status', [LibrarianDashboardController::class, 'toggleUserStatus'])->name('librarian.users.status');
    Route::post('/dashboard/librarian/fines/recalculate', [LibrarianDashboardController::class, 'recalculateFines'])->name('librarian.fines.recalculate');
    Route::patch('/dashboard/librarian/fines/{id}/pay', [LibrarianDashboardController::class, 'payFine'])->name('librarian.fines.pay');
    Route::patch('/dashboard/librarian/fines/{id}/waive', [LibrarianDashboardController::class, 'waiveFine'])->name('librarian.fines.waive');
    Route::get('/dashboard/librarian/fines/export', [LibrarianDashboardController::class, 'exportFines'])->name('librarian.fines.export');
    Route::get('/dashboard/librarian/reviews/export', [LibrarianDashboardController::class, 'exportReviews'])->name('librarian.reviews.export');
    Route::post('/dashboard/librarian/notifications/read', [LibrarianDashboardController::class, 'markNotificationsRead'])->name('librarian.notifications.read');
    Route::patch('/dashboard/librarian/notifications/{notificationId}/read', [LibrarianDashboardController::class, 'markNotificationRead'])->name('librarian.notifications.read.one');
    Route::get('/dashboard/librarian/reports/export', [LibrarianDashboardController::class, 'exportReport'])->name('librarian.reports.export');

    Route::get('/dashboard/guest', [GuestDashboardController::class, 'dashboard'])->name('dashboard.guest');
    Route::get('/dashboard/guest/catalog', [GuestDashboardController::class, 'catalog'])->name('guest.catalog');
    Route::get('/dashboard/guest/announcements', [GuestDashboardController::class, 'announcements'])->name('guest.announcements');
    Route::get('/dashboard/guest/books/{isbn}', [GuestDashboardController::class, 'bookDetails'])->name('guest.book-details');
});
