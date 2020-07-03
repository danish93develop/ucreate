@extends('layouts.app')

@section('content')
<div class="row justify-content-center" style="height:calc(100vh - 70px); padding-top:70px; margin: 0;">
  @if(Session::has('message'))
    <p  class="alert {{ Session::get('alert-class', 'alert-info') }}">{{ Session::get('message') }}</p>
  @endif
  @if(Session::has('message'))
  <div class="col-md-10 col-md-offset-1" style="height: calc(100% - 72px);">
    @else
  <div class="col-md-10 col-md-offset-1" style="height: 100%;">
  @endif
  <div class="row">
    {!! $note !!}
  </div>
  <div class="text-right" style="margin-bottom:12px;">
    <!-- Button trigger modal -->
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#previewEditedBook">
      <span class="glyphicon glyphicon-eye-open"></span> Preview
    </button>
  </div>
    

  <div class="hidden"  id="lastEditedBookHtml">{{$editedText}}</div>
  <input type="hidden" id="isFirstTimeEdit" value="{{$isFirstTimeEdit}}">
    <form method="POST"  style="height:100%;">
      <div class="well well-sm"  style="height:80%;">
        @csrf
        <textarea  style="height:100%;" name="edited_text" id="edited_text"></textarea>
      </div>
      <span class="bg-danger danger text-danger" id="alertMsg"></span>
      <div class="text-center">
        <nav aria-label="Page navigation example">
          <ul class="pagination">
            @if($previousPageNo == -1)
            <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
            @else
            <li class="page-item"><a class="page-link" href="{{route('editBook', [$currentBook->id, $previousPageNo])}}">Previous</a></li>
            @endif
            @foreach($userEditedBooks as $userEditedBook)
            @if($userEditedBook->page_no == $requestedPageNo)
            <li class="page-item active"><a class="page-link" href="{{route('editBook', [$currentBook->id, $userEditedBook->page_no])}}">{{$userEditedBook->page_no}}</a></li>
            @else
            <li class="page-item"><a class="page-link" href="{{route('editBook', [$currentBook->id, $userEditedBook->page_no])}}">{{$userEditedBook->page_no}}</a></li>
            @endif
            @endforeach
            @if($nextPageNo == -1)
            <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
            @else
            <li class="page-item"><a class="page-link" href="{{route('editBook', [$currentBook->id, $nextPageNo])}}">Next</a></li>
            @endif
          </ul>
        </nav>
        <button class="btn btn-primary" name="saveEditedtext"> SAVE</button>
      </div>
    </form>
  </div>
</div>
<div class="hiddenContent hidden"></div>
<div class="hiddenOriginalContent hidden"></div>
<div class="hiddenOriginalContentParent hidden"></div>
<div class="hiddenOriginalContentFirstChild hidden"></div>
<div class="isCollapsed hidden"></div>


<!-- Modal -->
<div class="modal fade" id="previewEditedBook" tabindex="-1" role="dialog" aria-labelledby="previewEditedBookTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-center" id="exampleModalLongTitle">{{$currentBook->book_title}}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        {!!$editedText!!}
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endsection
@section('script')
  <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
  <script>

    var func, run;
    if($('#isFirstTimeEdit').val() == 1){
      var bookHtmlString = $('#lastEditedBookHtml').text();
      var updatedHtmlString = addBrTagAfter(bookHtmlString, 'p');
      var updatedHtmlString = addBrTagAfter(updatedHtmlString, 'h1');
      var updatedHtmlString = addBrTagAfter(updatedHtmlString, 'h2');
      var updatedHtmlString = addBrTagAfter(updatedHtmlString, 'h3');
      var updatedHtmlString = addBrTagAfter(updatedHtmlString, 'h4');
      var updatedHtmlString = addBrTagAfter(updatedHtmlString, 'h5');
      var updatedHtmlString = addBrTagAfter(updatedHtmlString, 'h6');
      $('#lastEditedBookHtml').text(updatedHtmlString);
      func = preprocessBookText;
      run = setInterval(func, 300);
    }
    else{
      func = preprocessBookText;
      run = setInterval(func, 300);
    }

    var updatedDomElement = null;
    var originalDomElement = null;
    var newchar = 'newchar';
    var inp = null;
    var isCollapsed = true;
    var lastUpdatedContent = '';
    var selectedText = null;
    var selectedNode = null;
    var tagCount = 0;
    var pressedCharCount = {count:0};
    
    $(document).ready(function() {
      $('.registrationText').remove();
      $('.note1').remove();
      //initialize tinymce texeditor to the text-area
      tinymce.init({
        selector: '#edited_text',
        force_br_newlines : true,
        forced_root_block : false,
        valid_elements : '*[*]',
        menubar: false,
        statusbar: false,
        toolbar: false,
        plugins: [
          "fullpage"
        ],
        init_instance_callback: function(editor) {
          editor.on('keydown', function(e) {
            // updateUpdatedDomElement();
            /*if(e.keyCode == 13){
              tinymce.activeEditor.dom.remove(tinymce.activeEditor.selection.getEnd());
              e.preventDefault();
              e.stopPropagation();
              updateUpdatedDomElement();
              return false;
            }*/
            if(e.ctrlKey && (e.keyCode == 86 || e.keyCode == 65)){
              return true;
            }
            if(e.ctrlKey && e.keyCode == 82){
              window.location.reload();
              return;
            }

            if(e.shiftKey && e.keyCode == 13){
              return true;
            }
            
            if (e.keyCode == 8) {
              updateUpdatedDomElement();
            }
            if((e.keyCode == 8 && $(originalDomElement).data('type') == newchar)) {
              return true;
            }

            else if((e.keyCode == 46 || e.keyCode == 8)) { //handling the delete and backspace events
              if(($(selectedNode).length > 0) && $(selectedNode)[0].tagName == 'DEL'){}
              else
                addDelTagOnSelectedText(e);
              e.preventDefault();
              e.stopPropagation();
              return true;
            }

            else if ($.inArray(e.keyCode, [9, 13, 16, 17, 18, 19, 20, 33, 34, 35, 36]) !== -1) { 
              e.preventDefault();
              e.stopPropagation();
              updateUpdatedDomElement();
              updateParentAndChildVariableDataOfSelection();
            }
            
            else {
              var allowedExtraChars = ($.inArray(e.key, ['"', "'", "<", ">", ",", ".", "?", "/", ";", ":", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "+"]) !== -1)
              var arrowKeyPressed = ($.inArray(e.keyCode, [37, 38, 39, 40]) !== -1)
              inp = e.key;

              if(arrowKeyPressed) return ; 
              if($(updatedDomElement).attr('id') == 'tinymce'){
                e.preventDefault();
                e.stopPropagation();
              }
              if(/[a-zA-Z0-9-_ ]/.test(inp) || allowedExtraChars){
                /*
                pressedCharCount.count = pressedCharCount.count + 1;
                if(pressedCharCount.count > 1 ){
                  showMessage("Please type little bit slowly.");
                  e.preventDefault();
                  e.stopPropagation();
                  setTimeout(function(){
                    pressedCharCount.count = 0;
                  }, 1, pressedCharCount);
                  return;
                }
                else{*/
                  updateUpdatedDomElement();
                  handleKeyEvent(e);
                // }
              }
            }
          });

          editor.on('mouseup', function(e) { //catching the mouse events click
            selectedText = tinymce.activeEditor.selection.getContent();
            selectedText = preprocessSelectedText(selectedText); //removes the mutipara and heading tags if present
            selectedNode = tinymce.activeEditor.selection.getNode();
            if(tagCount > 1)
              selectedText = selectedText.split('<br />').join('<br /><br />');
            updateUpdatedDomElement()
          });

          editor.on('keyup', function(e) { //catching the arrow key press
            var arrowKeyPressed = ($.inArray(e.keyCode, [37, 38, 39, 40]) !== -1)
            selectedText = tinymce.activeEditor.selection.getContent();
            selectedText = preprocessSelectedText(selectedText); //removes the mutipara and heading tags if present
            selectedNode = tinymce.activeEditor.selection.getNode();
            if(tagCount > 1)
              selectedText = selectedText.split('<br />').join('<br /><br />');
            if(arrowKeyPressed)
              updateUpdatedDomElement();
          });

          editor.on('paste', function(e) { //catching the arrow key press
            let paste = (e.clipboardData || window.clipboardData).getData('text');
            var spanId = randomStr(70);
            addNewNode(spanId, paste, 'span');
            e.preventDefault();
          });
        },
      });
    });

    function preprocessSelectedText(selectedText) {
      var newText = selectedText; 
      tagCount = countTags(newText);
      newText = removeTag('p', newText);
      return newText;
    }

    function removeTag(tagName, stringWithTags) {
      stringWithTags = stringWithTags.split('<' + tagName + '>').join('')
      stringWithTags = stringWithTags.split('</' + tagName + '>').join('')
      return stringWithTags;
    }
    function countTags(newText) {
      count = 0;
      count = count + (newText.match(/<p>/g) || []).length;
      count = count + (newText.match(/<h1>/g) || []).length;
      count = count + (newText.match(/<h2>/g) || []).length;
      count = count + (newText.match(/<h2>/g) || []).length;
      count = count + (newText.match(/<h4>/g) || []).length;
      count = count + (newText.match(/<h5>/g) || []).length;
      count = count + (newText.match(/<h6>/g) || []).length;
      return count;
    }

    function handleKeyEvent(evt) {
      setTimeout(function () {
        updatedDomElement = tinymce.activeEditor.selection.getNode();
        highlightNewlyAddedTextAndSetInEditor(evt.key, pressedCharCount);
      }, 0, evt, pressedCharCount);
    }

    function highlightNewlyAddedTextAndSetInEditor(inp, pressedCharCount) {
      var originalString = $('.hiddenOriginalContent').text();
      var updatedString = updatedDomElement.outerHTML;
      var originalString = getOriginalString(originalString, updatedString);

      originalString = originalString.replace('&nbsp;', ' ');
      updatedString = updatedString.replace('&nbsp;', ' ');
      var spanId = randomStr(70);

      if(updatedString == originalString && (updatedString.indexOf("</body>") > 0) && !(tinymce.activeEditor.selection.isCollapsed())){
        showMessage("Please select only one para, multiple para editing is not supported.");
        return true;
      }
      if($(updatedDomElement).attr('id') == 'tinymce'){
        addNewNode(spanId, inp, 'p');
        return true 
      }
      if($(updatedString).data('type') == newchar){
        updateUpdatedDomElement();
        return true;
      }
      if(updatedString == originalString)
        return true;

      var resultHtmlString = gethighlightedText(originalString, updatedString, spanId);
      replaceWithHighlightedStringAndSetCursor(resultHtmlString, spanId);
      // pressedCharCount.count = 0;
      updateUpdatedDomElement();
      return true;
    }

    function getOriginalString(originalString, updatedString) { 
      var originalStringParentTag = ($('.hiddenOriginalContentParent').text() == '') ? '' : $($('.hiddenOriginalContentParent').text())[0].tagName;
      var originalStringFirstChildTag = ($('.hiddenOriginalContentFirstChild').text() == '') ? '' : $($('.hiddenOriginalContentFirstChild').text())[0].tagName;
      var updatedStringTag = $(updatedString)[0].tagName;
      var originalStringTag = $(originalString)[0].tagName;
      var updatedStringLength = $($(updatedString)[0]).text().length;
      var originalStringLength = $($(originalString)[0]).text().length;
       
      if(updatedStringTag !== originalStringTag){
        if(updatedStringTag == originalStringParentTag){
          originalString = $('.hiddenOriginalContentParent').text();
          $('.hiddenOriginalContentParent').text(originalString);
        }
        else if(updatedStringTag == originalStringFirstChildTag){
          originalString = $('.hiddenOriginalContentFirstChild').text();
          $('.hiddenOriginalContentFirstChild').text(originalString);
        }
        else{
          originalString = getMorePrecisedUpdatedString(updatedString, originalString);
        }
      }

      else if(updatedStringLength > (originalStringLength + 1)){
        originalString = $('.hiddenOriginalContentFirstChild').text();
        $('.hiddenOriginalContentFirstChild').text(originalString);
      }
      return originalString;
    }

    function getMorePrecisedUpdatedString(updatedString, originalString) {
      var updatedStringTag = $(updatedString)[0].tagName;
      var originalStringTag = $(originalString)[0].tagName;
    }

    function addNewNode(spanId, inpText, nodeName) {
      var content = '<span class"customSpan" data-type="'+ newchar +'" style="background:greenyellow" id="'+ spanId +'">'+ inpText +'</span>'
      var el = tinymce.activeEditor.dom.create(nodeName, {'class': 'newChar'}, content);
      tinymce.activeEditor.selection.setNode(el);
      var newNode = tinymce.activeEditor.dom.select("span#" + spanId);
      tinymce.activeEditor.selection.setCursorLocation(newNode[0], 1);
    }

    function replaceWithHighlightedStringAndSetCursor(resultHtmlString, spanId) {
      tinymce.activeEditor.dom.remove(updatedDomElement);
      tinymce.activeEditor.selection.setContent(resultHtmlString);
      var newNode = tinymce.activeEditor.dom.select("span#" + spanId);
      tinymce.activeEditor.selection.setCursorLocation(newNode[0], 1);
    }

    function gethighlightedText(originalString, updatedString, spanId) {
      var resultHtmlString = '';
      for (var index = 0; index < updatedString.length; index++) {
        var currentChar = updatedString[index];
        if(currentChar == originalString[index]){
          resultHtmlString = resultHtmlString + currentChar;
        }
        else{
          if(currentChar == ' ')
            resultHtmlString = resultHtmlString + '<span class"customSpan" data-type="'+ newchar +'" style="background:greenyellow" id="'+ spanId +'">'+ "&nbsp;" +'</span>' + originalString.substring(index, originalString.length);
          else
            resultHtmlString = resultHtmlString + '<span class"customSpan" data-type="'+ newchar +'" style="background:greenyellow" id="'+ spanId +'">'+ currentChar +'</span>' + originalString.substring(index, originalString.length);
          break;
        }
        continue;
      }
      return resultHtmlString;
    }
    function updateUpdatedDomElement() {
      updatedDomElement = tinymce.activeEditor.selection.getNode();
      originalDomElement = tinymce.activeEditor.selection.getNode();
      var htmlString = originalDomElement.outerHTML;
      $('.hiddenOriginalContent').text(htmlString);
    }

    function randomStr(len) { 
      var arr = "1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
      var ans = '';
      for (var i = len; i > 0; i--)
        ans += arr[Math.floor(Math.random() * arr.length)]; 
      return ans; 
    } 

    function setCharAt(str,index,chr) {
      if(index > str.length-1) return str;
      return str.substr(0,index) + chr + str.substr(index+1);
    }

    function addDelTagOnSelectedText(event) {
      if(selectedText){
        selectedText = addExtraBrBetweenParagraphs(selectedText);
        selectedText = selectedText.replace('<del>', '');
        selectedText = selectedText.replace('</del>', '');
        var newText = '<del style="text-decoration-color: red; ">' + selectedText + '</del>';
        tinymce.activeEditor.selection.setContent(newText);
      }

      $(selectedNode).children('del').each(function (index) {
        if($(this).text() == selectedText){
          var node = $(selectedNode).children('del')[index];
          tinymce.activeEditor.selection.setCursorLocation(node, 0);
        }
      });

      updateParentAndChildVariableDataOfSelection();
      updateUpdatedDomElement();
      selectedText = null;
      selectedNode = null;
      return true;
    }

    function addExtraBrBetweenParagraphs(text) {
      var openingDelTagPresent = (text.indexOf('<del style="text-decoration-color: red; ">') !== -1);
      var closingDelTagPresent = (text.indexOf('</del>') !== -1);
      
      var indexOfDelOpeningTag = text.indexOf('<del style="text-decoration-color: red; ">');
      var indexOfDelClosingTag = text.indexOf('</del>');

      if(openingDelTagPresent && closingDelTagPresent){
       return text;
      }
      if(openingDelTagPresent){
        return text;
      }
      else if(closingDelTagPresent){
        return text;
      }
      else{
        return text.split("<br /><br />").join("<br /><br /><br />");
      }
    }

    function updateParentAndChildVariableDataOfSelection() {
      originalElementParent = tinymce.activeEditor.selection.getSelectedBlocks()[0];
      if(originalElementParent){
        originalElementFirstChild = $(originalElementParent).children('del')[0];
        $(".hiddenOriginalContentParent").text(originalElementParent.outerHTML);
        $(".hiddenOriginalContentFirstChild").text((originalElementFirstChild == undefined) ? '' : originalElementFirstChild.outerHTML);
      } 
    }

    function addBrTagAfter(bookHtmlString, tag) {
      updatedHtmlString = bookHtmlString.split('</'+tag+'>');
      updatedHtmlString = updatedHtmlString.join('</'+tag+'><br/>');
      return updatedHtmlString;
    }
    //it is removing the un-necessary tags(pre, img, and anchor) and their text 
    function preprocessBookText() {
      if(tinymce.activeEditor){
        var result = $('#lastEditedBookHtml').text();
        result = removeTagWithText(result, 'pre');
        result = removeTagWithText(result, 'blockquote');
        result = result.replace(/<[\/]{0,1}(img)[^><]*>/ig,""); //remove img tag
        result = result.replace(/<[\/]{0,1}(a)[^><]*>/ig,""); //remove anchor tag
        tinymce.activeEditor.setContent(result);
        if(tinymce.activeEditor.getContent() !== ''){
          lastUpdatedContent = result;
          clearInterval(run);
        }
      }
    }

    function removeTagWithText(htmlString, tag) {
      var startOfPreTag = htmlString.indexOf('<' + tag) -1;
      var endofPreTeag = htmlString.indexOf('</' + tag) + 3 + tag.length;
      if(startOfPreTag > 0){
        htmlString = htmlString.replace(htmlString.substring(startOfPreTag, endofPreTeag), '');
      }
      return htmlString;
    }

    function showMessage(text) {
      $("#alertMsg").text(text);
      hideMessage();
    }
    function hideMessage() {
      setTimeout(function(){
        $("#alertMsg").text('');
      }, 4000);
    }
  </script>
<style type="text/css">
 .tox-notifications-container {
    display: none;
  }
</style>
@endsection
