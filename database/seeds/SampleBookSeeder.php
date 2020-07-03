<?php

use Illuminate\Database\Seeder;
use App\SampleBooks;
use App\Http\Controllers\Book as BookController;

class SampleBookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$bookNames = scandir(storage_path().'/books/');
    	unset($bookNames[0]);
    	unset($bookNames[1]);

		foreach ($bookNames as $fileName) {
            print_r(">> Adding Book `$fileName` \r\n");

	     	$book = new SampleBooks();
	     	$book->book_title = explode('.html', $fileName)[0];
	     	$book->book_name = $fileName;
	     	$book->user_id = null;
	     	$book->save();
            print_r(">> Dividing Book `$fileName` into pages. \r\n");
	     	(new BookController())->divideBookIntoPages($book);
            print_r(" DONE \r\n\r\n");
	    }
        print_r(">> Book Seeder executed successfully. \r\n");
    }
}
