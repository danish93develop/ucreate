<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SampleBooks extends Model
{
    
    protected $connection= 'mysql';
     protected $table = "sample_books";    

     protected $fillable = [
        'book_name', 'user_id', 'book_title'
     ];    

}
