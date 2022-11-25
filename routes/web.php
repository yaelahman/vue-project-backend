<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use Illuminate\Support\Facades\Auth;

// header('oke', true);

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});


$router->get('/privacy-policy', function () use ($router) {
    return view('privacy');
});

$router->group(['prefix' => 'isi'], function () use ($router) {
    $router->get('get', 'IsiController@index');
    $router->post('store', 'IsiController@store');
    $router->delete('destroy/{id}', 'IsiController@destroy');
});


$router->get('/privacy', function () {
    return view('privacy');
});
$router->get('/up/privacy', function () {
    return view('privacy_up');
});


$router->get('register/company', 'Web\UserCompanyController@viewPage');
$router->post('save/company', 'Web\UserCompanyController@saveCompany');

$router->get('mail/verify/{id}', 'Web\UserCompanyController@mail');

// Auth::routes();

$router->get('/login', 'Web\AuthController@indexLogin');
$router->post('/login-proses', 'Web\AuthController@prosesLogin');
$router->post('/register', 'Web\AuthController@register');
$router->get('/logout-proses', 'Web\AuthController@Logout');
$router->post('/forgot-password', 'Web\AuthController@forgotPassword');
$router->post('/reset-password', [
    // 'as' => 'password.reset',
    'uses' => 'Web\AuthController@resetPassword'
]);
// $router->post('/logout-proses', 'Web\AuthController@prosesLogout')->name('logout.proses');

$router->get('/index-company-industri', 'Web\CompanyIndustriController@index');
$router->get('mail/verify/{id}', [
    'as' => 'verify.email.user',
    'uses' => 'Web\UserCompanyController@mail'
]);
$router->get('faq/get', 'Web\FaqController@index');
$router->get('faq/website', 'Web\FaqController@website');

$router->group([
    'middleware' => 'auth:api'
], function () use ($router) {
    $router->get('checkrole', 'Web\UserController@check');
    $router->get('/user-auth', 'Web\UserController@getUserAuth');
    $router->get('/user', 'Web\UserController@getUser');

    $router->get('/role', 'Web\RoleController@GetRole');
    // Profil
    $router->get('/profil-user-detail/{id}', 'Web\UserController@detailProfilUser');
    $router->post('/profil-user-update', 'Web\UserController@editProfilUser');
    $router->post('/update-password', 'Web\UserController@updatePassword');

    $router->get('/home', 'HomeController@index');

    $router->group(['prefix' => 'faq'], function () use ($router) {
        $router->get('show/{id}', 'Web\FaqController@show');
        $router->post('store', 'Web\FaqController@store');
        $router->delete('{id}', 'Web\FaqController@destroy');
    });

    // Company Industri
    $router->post('/ceate-edit-company-industri', 'Web\CompanyIndustriController@create');
    $router->get('/detail-company-industri/{id}', 'Web\CompanyIndustriController@detail');
    $router->delete('/delete-company-industri/{id}', 'Web\CompanyIndustriController@delete');

    // Company
    $router->get('/index-user-company', 'Web\UserCompanyController@index');
    $router->post('/ceate-edit-user-company', 'Web\UserCompanyController@create');
    $router->get('/detail-user-company/{id}', 'Web\UserCompanyController@detail');
    $router->delete('/delete-user-company/{id}', 'Web\UserCompanyController@delete');

    // Data Personel
    $router->get('/index-data-personel', 'Web\DataPersonelController@index');
    $router->get('/show-data-personel/{id}', 'Web\DataPersonelController@show');
    $router->post('/create-edit-data-personel', 'Web\DataPersonelController@create');
    $router->get('/generate-token-data-personel/{id}', 'Web\DataPersonelController@generateToken');
    $router->get('/generate-password-data-personel/{id}', 'Web\DataPersonelController@generatePassword');
    $router->get('/reset-deviceid-data-personel/{id}', 'Web\DataPersonelController@resetDeviceId');
    $router->get('/change-status-data-personel/{id}', 'Web\DataPersonelController@changeStatus');
    $router->delete('/delete-data-personel/{id}', 'Web\DataPersonelController@delete');

    // Attendance Spot
    $router->get('/attendance-spot', 'Web\AttendanceSpotController@index');
    $router->get('/attendance-spot/{id}', 'Web\AttendanceSpotController@show');
    $router->post('/attendance-spot', 'Web\AttendanceSpotController@create');
    $router->delete('/attendance-spot/{id}', 'Web\AttendanceSpotController@delete');

    // Attendance Personel
    $router->get('/index-attendance-personel/{id}', 'Web\AttendancePersonelController@index');
    $router->get('/get-attendance-personel/{id}', 'Web\AttendancePersonelController@getDataPersonel');
    $router->post('/attendance-personel/add-personel', 'Web\AttendancePersonelController@addPersonel');
    $router->delete('/delete-attendance-personel/{id}', 'Web\AttendancePersonelController@delete');

    // Work Pattern
    $router->get('/index-work-pattern', 'Web\WorkPatternController@index');
    $router->post('/create-edit-work-pattern', 'Web\WorkPatternController@create');
    $router->get('/detail-work-pattern/{id}', 'Web\WorkPatternController@detail');
    $router->delete('/delete-work-pattern/{id}', 'Web\WorkPatternController@delete');

    // Daily Attendance
    $router->get('/daily-attendance', 'Web\DailyAttendanceController@index');
    $router->post('/daily-attendance', 'Web\DailyAttendanceController@create');
    $router->delete('/daily-attendance/{id}', 'Web\DailyAttendanceController@delete');
    $router->get('/attendance-summary', 'Web\DailyAttendanceController@attendanceSummary');
    $router->get('/detail-attendance-summary', 'Web\DailyAttendanceController@attendanceSummaryDetail');

    // Personel Time Work
    $router->get('/index-personel-time-work', 'Web\PersonelTimeWorkController@index');
    $router->get('/get-personel', 'Web\PersonelTimeWorkController@getDataPersonel');
    $router->get('/get-edit-personel', 'Web\PersonelTimeWorkController@getDataEditPersonel');
    $router->post('/create-edit-personel-time-work', 'Web\PersonelTimeWorkController@create');
    $router->get('/detail-personel-time-work/{id}', 'Web\PersonelTimeWorkController@detail');
    $router->delete('/delete-personel-time-work/{id}', 'Web\PersonelTimeWorkController@delete');

    // Device Settings
    $router->get('/detail-device-settings', 'Web\DeviceSettingsController@detail');
    $router->post('/create-device-setting', 'Web\DeviceSettingsController@create');
    $router->post('/update-device-settings', 'Web\DeviceSettingsController@update');


    // Departemen
    $router->get('/index-departemen', 'Web\DepartemenController@index');
    $router->post('/ceate-edit-departemen', 'Web\DepartemenController@create');
    $router->get('/detail-departemen/{id}', 'Web\DepartemenController@detail');
    $router->delete('/delete-departemen/{id}', 'Web\DepartemenController@delete');

    // Overtime
    $router->get('/overtime', 'Web\OvertimeController@index');
    $router->post('/overtime', 'Web\OvertimeController@store');
    $router->post('/overtime/approve', 'Web\OvertimeController@approve');
    $router->delete('/overtime/{id}', 'Web\OvertimeController@destroy');

    // Visit
    $router->get('/visit', 'Web\VisitController@index');
    $router->post('/visit', 'Web\VisitController@store');
    $router->delete('/visit/{id}', 'Web\VisitController@destroy');

    $router->get('/checkAbsen', 'Web\DashboardController@checkAbsen');
    $router->get('/checkAkumulasiSuperadmin', 'Web\DashboardController@checkAkumulasiSuperadmin');
    $router->get('/checkPersonelBelumAbsen', 'Web\DashboardController@checkPersonelBelumAbsen');
    $router->get('/checkPersonelSudahAbsen', 'Web\DashboardController@checkPersonelSudahAbsen');
    $router->get('/checkPersonelWFH', 'Web\DashboardController@checkPersonelWFH');
    $router->get('/checkPersonelKunjungan', 'Web\DashboardController@checkPersonelKunjungan');
    $router->get('/checkIzin', 'Web\DashboardController@checkIzin');
    $router->get('/checkCuti', 'Web\DashboardController@checkCuti');
    $router->get('/countApproval', 'Web\DashboardController@countApproval');
    $router->get('/chart', 'Web\DashboardController@chart');
    $router->get('/chart2', 'Web\DashboardController@chart2');


    $router->group(['prefix' => 'exports'], function () use ($router) {
        $router->get('absensi', 'Web\DailyAttendanceController@ExportExcel');
        $router->get('ringkasan_kehadiran', 'Web\DailyAttendanceController@ExportExcelAttendanceSummary');
        $router->get('lembur', 'Web\OvertimeController@ExportExcel');
        $router->get('kunjungan', 'Web\VisitController@ExportExcel');
        $router->get('izin/{type}', 'Web\PermitController@ExportExcel');
    });
    $router->group(['prefix' => 'izin'], function () use ($router) {
        $router->get('get/{type}', 'Web\PermitController@get');
        $router->get('detail/{id}', 'Web\PermitController@detail');
        $router->delete('{type}/{id}', 'Web\PermitController@destroy');
        $router->post('approve', 'Web\PermitController@approve');
    });
});
