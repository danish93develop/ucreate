@extends('layouts.app')

@section('content')
<div id="wrap">
   <div class="container">

    @if(Session::has('message'))
      <p class="alert {{ Session::get('alert-class', 'alert-info') }}">{{ Session::get('message') }}</p>
    @endif
      <h1 class="font-weight-light text-center text-lg-left mt-4 mb-0" style="margin-top: 56px;">Book Gallery</h1>
      <hr class="mt-2 mb-5">
      <div class="row text-center text-lg-left">
          @foreach($books as $book)
          <div class="col-md-4 ">
            <a href="{{route('editBook', [$book->id, $pageNo])}}" class="d-block mb-4 h-100" target= {{ (\Auth::guest()) ? '_blank' : '' }}>
                  <!-- <img class="img-fluid img-thumbnail" src="https://source.unsplash.com/pWkk7iiCoDM/400x300" alt=""> -->
                  <h3 class="booktitle">{{$book->book_title}}</h3>
                </a>
          </div>
          @endforeach          
        </div>
    </div>
</div>
    
@endsection

