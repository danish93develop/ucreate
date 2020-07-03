@extends('layouts.app')
@section('content')
<div id="wrap">
   <div class="container">
      <div style="margin-top: 8%;">
         <div class="jumbotron">
              @if(Session::has('message'))
              <p class="alert {{ Session::get('alert-class', 'alert-info') }}">{{ Session::get('message') }}</p>
              @endif
              <form method="post" name="frmExcelImport" id="frmExcelImport" enctype="multipart/form-data" class="d-flex" style="margin-top: 65px;">
                  <!-- <input type="hidden" name="_token" value="{{ csrf_token() }}"> -->
                  @csrf
                   <div class="custom-file">
                    <input type="file" class="custom-file-input" id="customFile" name="file"  accept=".txt,.htm,.html">
                    <label class="custom-file-label" for="customFile">Choose file</label>
                  </div>
                  <div class="ml-3">
                    <button type="submit" name="import" class="btn btn-primary">Upload</button>
                  </div>
              </form>
          </div>
      </div>
  </div>
@endsection
