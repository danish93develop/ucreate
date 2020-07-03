<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EditedBookInPages extends Model
{
    
    protected $connection= 'mysql';
     protected $table = "edited_book_in_pages";    

     protected $fillable = [
        'user_id', 'book_id', 'page_no', 'page_size_in_KB', 'content',
     ];
}
