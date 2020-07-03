@extends('layouts.app')

@section('content')
<div id="wrap">
   <div class="container">

    @if(Session::has('message'))
      <p class="alert {{ Session::get('alert-class', 'alert-info') }}">{{ Session::get('message') }}</p>
    @endif
    <?php //dd(count($getAllEditedBooks)); ?>
      <div class="row text-center" style="margin-top: 60px;">
       @if(count($getAllEditedBooks) > 0)
       @foreach($getAllEditedBooks as $data)
         <div class="col-md-4">
              <a href="{{url('/edited-content').'/'.$data->id}}" class="d-block mb-4 h-100" target="_blank">
                <!-- <img class="img-fluid img-thumbnail" src="https://source.unsplash.com/pWkk7iiCoDM/400x300" alt=""> -->
                <h3 class="booktitle">{{$data->book_title}}</h3>
              </a>
            </div>
      @endforeach
      </div> 
      @else
        <div class="col-md-12 mt-4"><h3>Currently no book available.</h3></div>
      @endif
    </div>
</div>
    
@endsection
