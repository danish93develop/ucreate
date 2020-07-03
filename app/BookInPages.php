<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BookInPages extends Model
{
    
    protected $connection= 'mysql';
     protected $table = "book_in_pages";    

     protected $fillable = [
        'book_id', 'page_no', 'page_size_in_KB', 'content',
     ];
}
