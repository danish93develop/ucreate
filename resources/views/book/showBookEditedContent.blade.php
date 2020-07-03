<div class="content">
	{!! $bookPage->content !!}
</div>
<div class="hidden newPageContent"></div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
<script type="text/javascript">
	var url = "{{route('getBookPageContent', [$bookPage->book_id, 1])}}"
	var pageNo = 2;
	var processing = false;
	var x = null;
	$(document ).ready(function() {
		$("img").remove();
		$("pre").remove();
		$("a").remove();
		$('body').on('DOMSubtreeModified', '.content', function(){
			$("img").remove();
			$("pre").remove();
			$("a").remove();
		});
		if (processing)
		    return false;
		$(window).scroll(function(){
			if(pageNo > 1){
				var i = url.lastIndexOf('/');
				url = url.substr(0, i) + "/" + pageNo;
			}
			if(!processing && pageNo > 0){
				if ($(window).scrollTop() >= ($(window).height())*0.8){
				    processing = true; //sets a processing AJAX request flag
				    $.get(url, function(response){
				    	if(response.status == 'success' && response.content == ''){
				    		pageNo = -1;
				    	}
				    	else{
				    		content = response.data.content;
				    		content = content.replace('<body>', '').replace('</body>', '');
				    		// console.log(content);
				    		$('.content').append(content)
				    		pageNo = pageNo+1;
				    	}
				        processing = false; //resets the ajax flag once the callback concludes
				    });
				}
			}
		});
	});
</script>
   		
