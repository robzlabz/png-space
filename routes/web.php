<?php

use App\Models\Media;
use App\Models\User;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

require __DIR__ . '/auth.php';


Route::get('/test/{user}', function (User $user) {

    $user->addImage('https://www.pngfind.com/pngs/m/570-5700216_vector-peta-indonesia-cdr-png-hd-peta-indonesia.png', 'indonesia', 'indonesia,pulau');

    return 'ok';
});
