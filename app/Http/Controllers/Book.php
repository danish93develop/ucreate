<?php

namespace App\Http\Controllers;
use App\SampleBooks;
use DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Session;
use PHPHtmlParser\Dom;
use App\BookInPages;
use App\EditedBookInPages;

class Book extends Controller
{

    public function showBooks()
    {
       $books = SampleBooks::all(); 
        return view('book.showBooks', ['books' => $books, 'pageNo' => 1]);
    }

    public function editBook($bookId, $requestedPageNo, Request $request)
    {
        $data = $request->input();
        $bookId = intval($bookId);
        $requestedPageNo = intval($requestedPageNo);

        $book = (new SampleBooks())->where('id',$bookId)->first();
        if(!$book || !$requestedPageNo)  return abort(404);

        if(\Auth::guest())  return view('book.guestView', ["htmlText" => $this->getBookHtmlForGuestUser($book, $requestedPageNo), "bookPage" => $this->getBookPage($book)]);

        $userId = auth()->user()->id;
        $userEditedBooks = (new EditedBookInPages())->where('user_id', $userId)->where('book_id', $book->id)->skip($requestedPageNo-6)->take(10)->get();
        $totalPageCount = (new EditedBookInPages())->where('user_id', $userId)->where('book_id', $book->id)->count();
        // $isFirstTimeEdit = ($userEditedBooks->isEmpty()) ? 1 : 0;
        $isFirstTimeEdit = 0;
        if($userEditedBooks->isEmpty()){
            $this->createCopyOfBookPagesForUser($book, $userId);
            $userEditedBooks = (new EditedBookInPages())->where('user_id', $userId)->where('book_id', $book->id)->skip($requestedPageNo-6)->take(10)->get();
            $totalPageCount = (new EditedBookInPages())->where('user_id', $userId)->where('book_id', $book->id)->count();
        }

        $editedText = $userEditedBooks->where('page_no', $requestedPageNo)->pluck('content')[0];
        if ($request->has('saveEditedtext')){
            $editedText = $data['edited_text'];
            $isFirstTimeEdit = 0;
            $response = $this->saveUserEditedBookPages($editedText, $bookId, $requestedPageNo, $userId);
            if($response){
                Session::flash('message', 'Data saved successfully.');
                Session::flash('alert-class', 'alert-success');
            }else{
                Session::flash('message', 'Error! While saving data.');
                Session::flash('alert-class', 'alert-danger');
                return redirect('/add-book/'.$bookId);
            }
        }

        $nextPageNo = ($requestedPageNo + 1 < $totalPageCount)? $requestedPageNo + 1: -1;
        $previousPageNo = ($requestedPageNo - 1 > 0) ? $requestedPageNo - 1: -1;
        return view('book.editBook', ['editedText' => $editedText, 'isFirstTimeEdit' => $isFirstTimeEdit, "note" => $this->getNoteForGuestUser($book), 'userEditedBooks' => $userEditedBooks, "totalPageCount" => $totalPageCount, "requestedPageNo" => $requestedPageNo, "nextPageNo" => $nextPageNo, 'previousPageNo' => $previousPageNo, "currentBook" => $book]);
    }

    public function getBookPage($book)
    {
        $bookPage = (new BookInPages())->where("book_id", $book->id)->where("page_no", 1)->get();
        if($bookPage->isEmpty()){
            return [];
        }
        else{
            $bookPage = $bookPage->first();
            return $bookPage;
        }
    }

    public function saveUserEditedBookPages($editedText, $bookId, $pageNo, $userId)
    {
        $userEditBookpage = (new EditedBookInPages())->where('user_id', $userId)->where('book_id', $bookId)->where('page_no', $pageNo)->get()[0];
        $userEditBookpage->content = $editedText;
        $userEditBookpage->page_size_in_KB = $this->formatSizeUnits(mb_strlen($editedText, '8bit'));
        return $userEditBookpage->save();
    }

    public function createCopyOfBookPagesForUser($book, $userId)
    {
        $BookPages = (new BookInPages())->where('book_id', $book->id)->get();
        foreach ($BookPages as $bookPage) {
            $userEditedBook = new EditedBookInPages();
            $userEditedBook->user_id = $userId;
            $userEditedBook->book_id = $book->id;
            $userEditedBook->page_no = $bookPage->page_no;
            $userEditedBook->page_size_in_KB = $bookPage->page_size_in_KB;
            $userEditedBook->content = $bookPage->content;
            $userEditedBook->save();
        }
    }

    public function getBookHtmlForGuestUser($book, $requestedPageNo)
    {
        $originalText = $this->getTextFromOriginalBookForGuest($book, $requestedPageNo);
        $bookHtml = '';
        if($originalText){
            $bootstrapLatest = '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">';
            $note = $this->getNoteForGuestUser($book);
            $bookHtml = $bootstrapLatest . $note . $originalText;
        }
        return $bookHtml;
    }

    public function getNoteForGuestUser($book)
    {
        $note = '<div class="col-md-12 pl-5">
            <span class="text-primary registrationText"> Want to create your own version of <strong>"' . $book->book_title . '"</strong> ? It\'s easy and free, in these simple steps: </span>
            <ol class="text-info">
                <h5 class="text-warning">*** NOTE ***</h5>
                <li class="note1">Create an account here <a href="'. route('register') .'">Link</a></li>
                <li class="note2">Just start deleting text - you\'ll see a red line through what you have deleted - or add text wherever you like.</li>
                <li class="note3">Save your version by using the "Save" button at the bottom of the page.</li>
                <li class="note3">You can come back and work on it as often as you like.</li>
            </ol>
        </div>';
        return $note;
    }

    public function getBookFileExtension($book)
    {
        $name = $book->book_name;
        $name = explode('.', $name);
        return last($name);
    }

    public function saveUserEditedBook($currentEditedText, $bookId, $pathToUserEditedBooks, $bookFileExtension)
    {
        $timestamp = time();
        $userId = auth()->user()->id;
        // $pathToUserEditedBook = "$pathToUserEditedBooks/$timestamp"."_"."$bookId"."_"."$userId";
        $pathToUserEditedBook = "$pathToUserEditedBooks/copy_"."$bookId"."_"."$userId.$bookFileExtension";
        return (file_put_contents($pathToUserEditedBook, $currentEditedText));
    }

    public function getFilesInDirectory($dirPath)
    {
        $files = scandir($dirPath);
        array_shift($files);
        array_shift($files);
        return $files;
    }

    public function getBookTextFromDB($getBook)
    {
        $targetPath = storage_path(). '/books/'.$getBook->book_name; 
        $originalOrLastEditedText = file_get_contents($targetPath, "r");
        return $originalOrLastEditedText;
    }

    public function getTextFromOriginalBookForGuest($book, $requestedPageNo)
    {
        $book = (new BookInpages())->where('book_id', $book->id)->where('page_no', $requestedPageNo)->pluck('content');
        if($book->isEmpty())
            return '';
        else
            return $book[0];
    }

    public function getLastEditedText($files, $bookId, $pathToUserEditedBooks)
    {
        $pathToLastEditedFile = '';
        $userId = auth()->user()->id;
        if(empty($files))
            return $pathToLastEditedFile;
        $searchword = "_" . "$bookId" . "_" . "$userId";
        $matches = array_filter($files, function($var) use ($searchword) { return preg_match("/$searchword/i", $var); });
        $matches = array_values($matches);
        asort($matches);

        if(!empty($matches))
            $pathToLastEditedFile = $pathToUserEditedBooks . '/'. $matches[0];

        return $pathToLastEditedFile;
    }

    public function listEditedBooks()
    {
        $userId = auth()->user()->id;
        $bookIds = (new EditedBookInPages())->where('user_id', $userId)->pluck('book_id');
        $sampleBooks = (new SampleBooks())->whereIn('id', $bookIds)->get();
        return view('book.listEditedBooks', ['getAllEditedBooks' => $sampleBooks]);
    }

    public function loadNewEditor()
    {
        return view('book.addBook');
    }

    public function addBook(Request $request)
    {        
        $originalName = $_FILES['file']['name'];
        if(!isset($_FILES['file']) || empty($originalName)){
            Session::flash('message', 'Error! Please attach text file.');
            Session::flash('alert-class', 'alert-danger');
            return redirect('/add-book')->withInput(); 
        }
        if(isset($_FILES['file']) && !empty($originalName)){
            $allowed = array('txt','html');
            $ext = pathinfo($originalName, PATHINFO_EXTENSION);
            if (!in_array($ext, $allowed)) {
                Session::flash('message', 'Error! Please upload only .txt format type files.');
                Session::flash('alert-class', 'alert-danger');
                return redirect('/add-book')->withInput();                 
            }
        }
         $targetPath = storage_path(). '/uploads/'.$originalName;
         move_uploaded_file($_FILES['file']['tmp_name'], $targetPath);
         $string = file_get_contents($targetPath, "r");         

        $book = (new SampleBooks())->where('book_name', $originalName)->get();
        if (count($book) > 0){
            $getBook = (new SampleBooks())->where('book_name', $originalName)->first();
            Session::flash('message', 'Book Already Exist.');
            Session::flash('alert-class', 'alert-warning');
            return view('book.addBook');
        }else
        {                 
            $newBook = new SampleBooks();
            $newBook->user_id = auth()->user()->id;
            $newBook->book_name = $originalName;
            $newBook->book_title = explode('.', $originalName)[0];
            $newBook->save();
            $this->divideBookIntoPages($newBook);

            if (!empty($newBook->id)){
                $getBook = (new SampleBooks())->where('id',$newBook->id)->first();
                Session::flash('message', 'Book successfully added.');
                Session::flash('alert-class', 'alert-success');
                return view('book.addBook');
            } else {                        
                Session::flash('message', 'Error! Failed to insert into Database. Please try again.');
                Session::flash('alert-class', 'alert-danger');
                return redirect('/add-book');
            }
        }
    }

    // public function divideBookIntoPages(Request $request)
    public function divideBookIntoPages($book)
    {
        ini_set('max_execution_time', 900);
        $originalText = $this->getBookTextFromDB($book);
        $dom = new Dom;
        $dom->load($originalText);
        $dom->clearSelfClosingTags();
        $head = $this->getHead($originalText);
        $headWithStyle = $this->getHeadWithStyleOnly($head);
        $immediateChildrenOfBody = $dom->find('body')[0]->getChildren();
        $this->makePagesAndSave($immediateChildrenOfBody, $book->id, $head, $headWithStyle);
    }

    public function getHeadWithStyleOnly($head)
    {
        return '<!DOCTYPE html><html lang="en"><head>' . substr($head, strpos($head, '<style'), strpos($head, '</style>') + 8 - strpos($head, '<style')) . '</head>';
    }

    public function getHead($originalText)
    {
        return substr($originalText, 0, strpos($originalText, '</head>') + 7);
    }

    public function makePagesAndSave($immediateChildrenOfBody, $bookId, $head, $headWithStyle)
    {
        $children = $this->getChildrenText($immediateChildrenOfBody);
        $bookpages = (new BookInpages())->get();
        $pages = $this->convertTopages($children);
        $this->savePagesToDB($pages, $bookId, $head, $headWithStyle);
    }

    function getChildrenText($immediateChildrenOfBody)
    {
        $children = [];
        $tagList = ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
        foreach ($immediateChildrenOfBody as $index => $childElement) {
            $tagName = $childElement->__get('tag')->name();
            if($tagName == 'pre')
                continue;
            elseif (in_array($tagName, $tagList)) {
                $tagAndText = (trim($childElement->__toString()) == '')? '<br /><br />' : $childElement->__toString();
            }
            else
                $tagAndText = (trim($childElement->__toString()) == '')? '<br />' : $childElement->__toString();

            $children[$index]['tagAndtext'] = $tagAndText;
            $children[$index]['sizeInbyte'] = mb_strlen($tagAndText, '8bit');
        }
        return $children;
    }

    public function formatSizeUnits($bytes)
    {
        return number_format($bytes / 1024, 3);
    }

    public function convertTopages($children)
    {
        $pages = [];
        $pageSize = 0;
        $pageContent = '';
        $pageNo = 1;
        foreach ($children as $index => $child) {
            $pageSize += $child['sizeInbyte'];
            $pageContent .= $child['tagAndtext'];
            $tempPageSize = $this->formatSizeUnits($pageSize); 
            if($tempPageSize > 10){ //if size is greter than 200 kb then call it a page and start next page from here
                $pages[$pageNo]['content'] = "<body>$pageContent</body>";
                $pages[$pageNo]['pageSizeInKB'] = $tempPageSize;
                $pageNo++;
                $pageContent = '';
                $pageSize = 0;
            }
        }
        if($pageContent !== ''){
            $pages[$pageNo]['content'] = "<body>$pageContent</body>";
            $pages[$pageNo]['pageSizeInKB'] = $tempPageSize;
        }
        return $pages;
    }

    public function savePagesToDB($pages, $bookId, $head, $headWithStyle)
    {
        foreach ($pages as $pageNo => $page) {
            $bookPage = (new BookInPages());
            $bookPage->book_id = $bookId;
            $bookPage->page_no = $pageNo;
            $bookPage->page_size_in_KB = $page['pageSizeInKB'];
            $bookPage->content = (($pageNo == 1) ? $head : $headWithStyle). $page['content'] . '</html>';
            $bookPage->save();
        }
    }
    public function showBookEditedContent(Request $request, $bookId)
    {
        $userId = auth()->user()->id;
        $bookPage = (new EditedBookInPages())->where('book_id', $bookId)->where('user_id', $userId)->where('page_no', 1)->get();
        if($bookPage->isEmpty())
            return abort(404);
        return view('book.showBookEditedContent', ["bookPage" => $bookPage[0]]);
    }

    public function getBookPageContent(Request $request, $bookId, $currentPageNo)
    {
        if(is_null($bookId) || is_null($currentPageNo)) return abort(503);
        $data = [];
        $bookPage = (new EditedBookInPages())->where('book_id', $bookId)->where('page_no', $currentPageNo)->get();
        if($bookPage->isEmpty()){
            $data['message'] = 'No More books pages in this book';
            $data['status'] = 'success';
            $data['content'] = '';
            $data['data'] = $bookPage->toArray();
        }else{
            $bookPage = $bookPage[0]->toArray();
            $bookPage['content'] = $this->getBodyElement($bookPage['content']);
            $data['data'] = $bookPage;
            $data['message'] = 'Page Fetched successfully';
            $data['status'] = 'success';
        }
        return $data;
    }

    public function getBodyElement($htmlText)
    {
        $dom = new Dom;
        $dom->load($htmlText);
        $dom->clearSelfClosingTags();
        $bodyText = $dom->find('body')[0]->__toString();
        return $bodyText;
    }

    public function getBookPageContentForGuest(Request $request, $bookId, $currentPageNo)
    {
        if(is_null($bookId) || is_null($currentPageNo)) return abort(503);
        $data = [];
        $bookPage = (new BookInpages())->where('book_id', $bookId)->where('page_no', $currentPageNo)->get();
        if($bookPage->isEmpty()){
            $data['message'] = 'No More books pages in this book';
            $data['status'] = 'success';
            $data['content'] = '';
            $data['data'] = $bookPage->toArray();
        }else{
            $bookPage = $bookPage[0]->toArray();
            $bookPage['content'] = $this->getBodyElement($bookPage['content']);
            $data['data'] = $bookPage;
            $data['message'] = 'Page Fetched successfully';
            $data['status'] = 'success';
        }
        return $data;
    }
}
