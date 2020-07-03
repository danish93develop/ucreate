@extends('layouts.app')
  <style type="text/css">
    body {
  background: #fff;
  font-family: Arial;
  font-size: 12px;
}
.Differences {
  width: 100%;
  border-collapse: collapse;
  border-spacing: 0;
  empty-cells: show;
}

.Differences thead th {
  text-align: left;
  border-bottom: 1px solid #000;
  background: #aaa;
  color: #000;
  padding: 4px;
}
.Differences tbody th {
  text-align: right;
  background: #ccc;
  width: 4em;
  padding: 1px 2px;
  border-right: 1px solid #000;
  vertical-align: top;
  font-size: 13px;
}

.Differences td {
  padding: 1px 2px;
  font-family: Consolas, monospace;
  font-size: 13px;
}

.DifferencesSideBySide .ChangeInsert td.Left {
  background: #dfd;
}

.DifferencesSideBySide .ChangeInsert td.Right {
  background: #cfc;
}

.DifferencesSideBySide .ChangeDelete td.Left {
  background: #f88;
}

.DifferencesSideBySide .ChangeDelete td.Right {
  background: #faa;
}

.DifferencesSideBySide .ChangeReplace .Left {
  background: #fe9;
}

.DifferencesSideBySide .ChangeReplace .Right {
  background: #fd8;
}

.Differences ins, .Differences del {
  text-decoration: none;
}

.DifferencesSideBySide .ChangeReplace ins, .DifferencesSideBySide .ChangeReplace del {
  background: #fc0;
}

.Differences .Skipped {
  background: #f7f7f7;
}

.DifferencesInline .ChangeReplace .Left,
.DifferencesInline .ChangeDelete .Left {
  background: #fdd;
}

.DifferencesInline .ChangeReplace .Right,
.DifferencesInline .ChangeInsert .Right {
  background: #dfd;
}

.DifferencesInline .ChangeReplace ins {
  background: #9e9;
}

.DifferencesInline .ChangeReplace del {
  background: #e99;
}
.DifferencesInline .ChangeReplace del{
  text-decoration: line-through;
}

pre {
  width: 100%;
  overflow: auto;
}
#wrap{ margin-top: 60px; }
</style>
@section('content')
  @if($diffString == '')
  <h1 style="margin: 73px;">No text changed.</h1>
  @else
  <div style="margin: 73px;">
    {!! $diffString !!}
    
  </div>
  @endif
@endsection
@section('script')
<script type="text/javascript">
  $(document).ready(function() {
    // $("td").removeClass("Left");
    // $("td").removeClass("Right");
  });
</script>
@endsection