<?php

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

// Route::get('/', function () {	
// 	if(Auth::check()):
//     	return view('book.showBooks');	
// 	else:
// 		return view('welcome');
// 	endif;
// });

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/', 'HomeController@index')->name('home');

Route::middleware(['auth'])->group(function () {
	Route::get('/add-book','Book@loadNewEditor')->name('loadNewEditor');
	Route::post('/add-book','Book@addBook')->name('addBook');    


	Route::match(['GET', 'POST'], '/list-edited-books','Book@listEditedBooks')->name('listEditedBooks');
	Route::match(['GET', 'POST'], '/edited-content/{id}','Book@showBookEditedContent')->name('showBookEditedContent');
	Route::match(['POST'], '/save-edited-book/{id}','Book@saveEditedBook')->name('saveEditedBook');
	Route::match(['GET'], '/show-edited-book/{id}','Book@showEditedBook')->name('showEditedBook');

});
Route::match(['GET', 'POST'], '/book-editor/{id}/{pageNo}','Book@editBook')->name('editBook');
Route::get('/show-books','Book@showBooks')->name('showBooks');
Route::get('/edited-book/{bookId}/{pageNo}','Book@getBookPageContent')->name('getBookPageContent');
Route::get('/guest-book/{bookId}/{pageNo}','Book@getBookPageContentForGuest')->name('getBookPageContentForGuest');

Auth::routes();

