<?php

use Phalcon\Mvc\Controller;

class PageController extends \Phalcon\Mvc\Controller {

    public function beforeExecuteRoute()
    {
        // Executed before every found action
        $rawBody = $this->request->getJsonRawBody(true);
        if($rawBody){
            foreach ($rawBody as $key => $value) {
                $_POST[$key] = $value;
            }
        }
    }
    
    public function indexAction()
    {
        //echo json_encode(array("status"=>"success","message"=>"Hello World!"));
    }

    public function addAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $errors = [];
        $data = [];
        $pages = new Pages();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        /** Validation Block **/
        //Author ID and Name
        $data['authorid'] = $this->request->getPost('userid');
        $data['author'] = $this->request->getPost('username');
        
        //Page content
        $data['content'] = $this->request->getPost('content');
        if (!is_string($data['content'])) {
            $errors['content'] = 'Page content expected';
        }

        //Page title
        $data['title'] = $this->request->getPost('title');
        if (!is_string($data['title'])) {
            $errors['title'] = 'Page title expected';
        }
        $data['pagecode'] = $generalService->getSafeURL($data['title']);

        //Product photo
        if($this->request->hasFiles() == true){ 
            $extIMG = array(
                'image/jpeg',
                'image/png',
                'image/gif',
            );
            foreach( $this->request->getUploadedFiles() as $file)
            {              
                // is it a valid extension?
                if ( in_array($file->getType() , $extIMG) && $file->getError() == 0 )
                {
                    $Name          = preg_replace("/[^A-Za-z0-9.]/", '-', $file->getName() );
                    $NewFileName=date("Ymdhis", strtotime("now")).rand(100,1000); //Random file new name
                    $FileExtension = $file->getExtension();
                    $FileName      = "files/" . $NewFileName . "." . $FileExtension;
                    if(!$file->moveTo($FileName)) { // move file to needed path";
                        return json_encode(array("status"=>"failed","message"=>"Error uploading photo"));
                    } else {
                        $data['pagebanner'] = "$FileName";
                    }
                } else {
                    return json_encode(array("status"=>"failed","message"=>"Photo must be either JPEG or PNG or GIF file format"));
                }
            }
        }

        //Get current date and time
        $data['lastupdated'] = date("d-m-Y");
        $data['date'] = date("d-m-Y");

        //Error form handling check and Submit Data
        if ($errors) {
            return json_encode(array("status"=>"failed","message"=>implode( ", ", $errors )));
        } 

        // Store to Database Model and check for errors
        $pages->addNewPage($data);
        echo json_encode(array("status"=>"success","message"=>"Page added successfully"));
    }

    public function updateAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $pages = new Pages();
        $data = [];

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        if($this->request->getPost('content')){ //content change check
            $data['content'] = $this->request->getPost('content');
            if (is_string($data['content'])) {
                $pages->savePageContent($this->request->getPost('pageid'), $this->request->getPost('content'), "content"); //Update page details changes
            } else {
                return json_encode(array("status"=>"failed","message"=>"Page content must be a string"));
            }
        }

        //Get Author
        $data['author'] = $this->request->getPost('username');
        $pages->savePageContent($this->request->getPost('pageid'), $data['author'], "author"); //Update page details changes

        //Get current date and time
        $data['lastupdated'] = date("d-m-Y");
        $pages->savePageContent($this->request->getPost('pageid'), $data['lastupdated'], "lastupdated"); //Update page details changes

        if($this->request->hasFiles() == true){ //Photo change check
            $extIMG = array(
                'image/jpeg',
                'image/png',
                'image/gif',
            );
            foreach( $this->request->getUploadedFiles() as $file)
            {              
                // is it a valid extension?
                if ( in_array($file->getType() , $extIMG) && $file->getError() == 0 )
                {
                    $Name          = preg_replace("/[^A-Za-z0-9.]/", '-', $file->getName() );
                    $NewFileName=date("Ymdhis", strtotime("now")).rand(100,1000); //Random file new name
                    $FileExtension = $file->getExtension();
                    $FileName      = "files/" . $NewFileName . "." . $FileExtension;
                    if(!$file->moveTo($FileName)) { // move file to needed path";
                        return json_encode(array("status"=>"failed","message"=>"Error uploading photo"));
                    } else {
                        $pages->savePageContent($this->request->getPost('pageid'), $FileName, "pagebanner"); //Update page photo changes
                    }
                } else {
                    return json_encode(array("status"=>"failed","message"=>"Photo must be either JPEG or PNG or GIF file format"));
                }
            }
        }
        
        echo json_encode(array("status"=>"success","message"=>"Page updated successfully"));
    }

    public function adminpagelistsAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $admin = new Admin();
        $pages = new Pages();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultNum=20;
        $resultData=$pages->getAllPages($resultNum); // Get Pages data from the Database
        foreach($resultData as $pageData) {
            $itemList['pageid']=$pageData->id;
            $itemList['pagename']=$pageData->pagename;
            $itemList['pagebanner']=$pageData->pagebanner;
            $itemList['pagecode']=$pageData->pagecode;
            $itemList['lastupdated']=$pageData->lastupdated;
            $itemList['author']=$pageData->author;
            $itemList['content']=$pageData->content;
            $itemList['date']=$pageData->date;
            $itemLists[] = $itemList;
        } 
        echo json_encode($itemLists);    
    }

    public function adminpagecontentAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $admin = new Admin();
        $pages = new Pages();
        $errors = [];
        $data = [];

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        //Page ID
        $data['pageid'] = $this->request->getPost('pageid');
        if (!is_string($data['pageid'])) {
            $errors['pageid'] = 'Page ID expected';
        }

        //Error form handling check and Submit Data
        if ($errors) {
            return json_encode(array("status"=>"failed","message"=>implode( ", ", $errors )));
        }

        $pageData=$pages->getPageContent($data['pageid']); // Get Page data from the Database
        $pageid=$pageData->id;
        $pagename=$pageData->pagename;
        $pagebanner=$this->getDi()->getShared('siteurl').'/'.$pageData->pagebanner;
        $pagecode=$pageData->pagecode;
        $lastupdated=$pageData->lastupdated;
        $author=$pageData->author;
        $content=$pageData->content;
        $date=$pageData->date;
        echo json_encode(array("status"=>"success","pageid"=>"$pageid","pagename"=>"$pagename","pagebanner"=>"$pagebanner","pagecode"=>"$pagecode","lastupdated"=>"$lastupdated","author"=>"$author","content"=>"$content","date"=>"$date"));  
    }

    public function listsAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $pages = new Pages();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$pages->getAllPages($resultNum); // Get Pages data from the Database
        foreach($resultData as $pageData) {
            $itemList['pageid']=$pageData->id;
            $itemList['pagename']=$pageData->pagename;
            $itemList['pagebanner']=$pageData->pagebanner;
            $itemList['pagecode']=$pageData->pagecode;
            $itemList['lastupdated']=$pageData->lastupdated;
            $itemList['author']=$pageData->author;
            $itemList['content']=$pageData->content;
            $itemList['date']=$pageData->date;
            $itemLists[] = $itemList;
        } 
        echo json_encode($itemLists);    
    }

    public function contentAction($pageid)
    {
        /** Init Block **/
        $pages = new Pages();

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemList = array();
        $pageData=$pages->getPageContent($pageid); // Get page data from the Database
        $itemList['pageid']=$pageData->id;
        $itemList['pagename']=$pageData->pagename;
        $itemList['pagebanner']=$pageData->pagebanner;
        $itemList['pagecode']=$pageData->pagecode;
        $itemList['lastupdated']=$pageData->lastupdated;
        $itemList['author']=$pageData->author;
        $itemList['content']=$pageData->content;
        $itemList['date']=$pageData->date;
        echo json_encode($itemList);       
    }

    public function removePageAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $pages = new Pages();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $pages->removePage($this->request->getPost('pageid')); //Delete page
        echo json_encode(array("status"=>"success","message"=>"Page deleted successfully"));
    }

    public function addfaqAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $errors = [];
        $data = [];
        $faq = new Faqs();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        /** Validation Block **/
        //Author ID and Name
        $data['authorid'] = $this->request->getPost('userid');
        $data['author'] = $this->request->getPost('username');
        
        //FAQ question
        $data['question'] = $this->request->getPost('question');
        if (!is_string($data['question']) || !preg_match('/^[A-z0-9_-]{3,16}$/', $data['question'])) {
            $errors['question'] = 'FAQ question expected';
        }

        //FAQ answer
        $data['answer'] = $this->request->getPost('answer');
        if (!is_string($data['answer']) || !preg_match('/^[A-z0-9_-]{3,16}$/', $data['answer'])) {
            $errors['answer'] = 'FAQ answer expected';
        }

        //FAQ category
        $data['categoryid'] = "";
        $data['category'] = "";
        if(is_string($this->request->getPost('categoryid'))){
            $data['categoryid'] = $this->request->getPost('categoryid');
            $data['category'] = $generalService->getCategoryName($data['categoryid']);
        }

        //FAQ subcategory
        $data['subcategoryid'] = "";
        $data['subcategory'] = "";
        if(is_string($this->request->getPost('subcategoryid'))){
            $data['subcategoryid'] = $this->request->getPost('subcategoryid');
            $data['subcategory'] = $generalService->getSubCategoryName($data['subcategoryid']);
        }

        //FAQ tags
        $data['tags'] = $this->request->getPost('tags');
        
        //Get current date and time
        $data['date'] = date("d-m-Y");

        //Error form handling check and Submit Data
        if ($errors) {
            return json_encode(array("status"=>"failed","message"=>implode( ", ", $errors )));
        } 

        // Store to Database Model and check for errors
        $faq->addNewEntry($data);
        echo json_encode(array("status"=>"success","message"=>"FAQ entry added successfully"));
    }

    public function faqcontentAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $faq = new Faqs();

        //Business Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$faq->getFAQItems(); // Get FAQ data from the Database
        foreach($resultData as $pageData) {
            $itemList['id']=$pageData->id;
            $itemList['question']=$pageData->question;
            $itemList['answer']=$pageData->answer;
            $itemList['categoryid']=$pageData->categoryid;
            $itemList['category']=$pageData->category;
            $itemList['subcategoryid']=$pageData->subcategoryid;
            $itemList['subcategory']=$pageData->subcategory;
            $itemList['tags']=$pageData->tags;
            $itemList['date']=$pageData->date;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists); 
    }

    public function faqcategorycontentAction($categoryid)
    {
        /** Init Block **/
        $authService = new AuthService();
        $faq = new Faqs();

        //Business Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$faq->getFAQByCategory($categoryid); // Get FAQ data from the Database
        foreach($resultData as $pageData) {
            $itemList['id']=$pageData->id;
            $itemList['question']=$pageData->question;
            $itemList['answer']=$pageData->answer;
            $itemList['categoryid']=$pageData->categoryid;
            $itemList['category']=$pageData->category;
            $itemList['subcategoryid']=$pageData->subcategoryid;
            $itemList['subcategory']=$pageData->subcategory;
            $itemList['tags']=$pageData->tags;
            $itemList['date']=$pageData->date;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists); 
    }

    public function removefaqAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $faq = new Faqs();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $faq->removeEntry($this->request->getPost('faqid')); //Delete page
        echo json_encode(array("status"=>"success","message"=>"FAQ entry deleted successfully"));
    }

    public function addgalleryAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $errors = [];
        $data = [];
        $gallery = new Gallery();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        /** Validation Block **/
        //Author ID and Name
        $data['authorid'] = $this->request->getPost('userid');
        $data['author'] = $this->request->getPost('username');
        
        //Gallery title caption
        $data['caption'] = $this->request->getPost('caption');
        if (!is_string($data['caption']) || !preg_match('/^[A-z0-9_-]{3,16}$/', $data['caption'])) {
            $errors['caption'] = 'Gallery caption expected';
        }

        //Gallery details
        $data['details'] = $this->request->getPost('details');
        if (!is_string($data['details']) || !preg_match('/^[A-z0-9_-]{3,16}$/', $data['details'])) {
            $errors['details'] = 'Gallery details expected';
        }

        //Gallery type
        $data['type'] = $this->request->getPost('type');
        if (!is_string($data['type']) || !preg_match('/^[A-z0-9_-]{3,16}$/', $data['type'])) {
            $errors['type'] = 'Gallery type expected';
        }

        //Gallery postid
        $data['postid'] = $this->request->getPost('postid');

        //Gallery posttype
        $data['posttype'] = $this->request->getPost('posttype');

        //Gallery tags
        $data['tags'] = $this->request->getPost('tags');

        //Gallery category
        $data['categoryid'] = $this->request->getPost('categoryid');
        if (!is_string($data['categoryid']) || !preg_match('/^[A-z0-9_-]{3,16}$/', $data['categoryid'])) {
            $errors['categoryid'] = 'Gallery category expected';
        }
        $data['category'] = $generalService->getCategoryName($data['categoryid']);

        //Gallery subcategory
        $data['subcategoryid'] = $this->request->getPost('subcategoryid');
        $data['subcategory'] = $generalService->getSubCategoryName($data['subcategoryid']);

        //Gallery media
        if($this->request->hasFiles() == true){ 
            $extIMG = array(
                'image/jpeg',
                'image/png',
                'image/gif',
            );
            foreach( $this->request->getUploadedFiles() as $file)
            {              
                // is it a valid extension?
                if ( in_array($file->getType() , $extIMG) && $file->getError() == 0 )
                {
                    $Name          = preg_replace("/[^A-Za-z0-9.]/", '-', $file->getName() );
                    $NewFileName=date("Ymdhis", strtotime("now")).rand(100,1000); //Random file new name
                    $FileExtension = $file->getExtension();
                    $FileName      = "files/" . $NewFileName . "." . $FileExtension;
                    if(!$file->moveTo($FileName)) { // move file to needed path";
                        return json_encode(array("status"=>"failed","message"=>"Error uploading photo"));
                    } else {
                        $data['media'] = "$FileName";
                    }
                } else {
                    return json_encode(array("status"=>"failed","message"=>"Photo must be either JPEG or PNG or GIF file format"));
                }
            }
        }

        //Get current date and time
        $data['date'] = date("d-m-Y");

        //Error form handling check and Submit Data
        if ($errors) {
            return json_encode(array("status"=>"failed","message"=>implode( ", ", $errors )));
        } 

        // Store to Database Model and check for errors
        $gallery->addNewGallery($data);
        echo json_encode(array("status"=>"success","message"=>"Gallery entry added successfully"));
    }

    public function gallerycontentAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $gallery = new Gallery(); 

        //Business Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$gallery->getGalleryItems(); // Get Gallery data from the Database
        foreach($resultData as $pageData) {
            $itemList['id']=$pageData->id;
            $itemList['caption']=$pageData->caption;
            $itemList['type']=$pageData->type;
            $itemList['postid']=$pageData->postid;
            $itemList['posttype']=$pageData->posttype;
            $itemList['media']=$pageData->media;
            $itemList['details']=$pageData->details;
            $itemList['categoryid']=$pageData->categoryid;
            $itemList['category']=$pageData->category;
            $itemList['subcategoryid']=$pageData->subcategoryid;
            $itemList['subcategory']=$pageData->subcategory;
            $itemList['tags']=$pageData->tags;
            $itemList['date']=$pageData->date;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists); 
    }

    public function gallerycategorycontentAction($categoryid)
    {
        /** Init Block **/
        $authService = new AuthService();
        $gallery = new Gallery(); 

        //Business Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$gallery->getGalleryContentByCategory($categoryid); // Get Gallery data from the Database
        foreach($resultData as $pageData) {
            $itemList['id']=$pageData->id;
            $itemList['caption']=$pageData->caption;
            $itemList['type']=$pageData->type;
            $itemList['postid']=$pageData->postid;
            $itemList['posttype']=$pageData->posttype;
            $itemList['media']=$pageData->media;
            $itemList['details']=$pageData->details;
            $itemList['categoryid']=$pageData->categoryid;
            $itemList['category']=$pageData->category;
            $itemList['subcategoryid']=$pageData->subcategoryid;
            $itemList['subcategory']=$pageData->subcategory;
            $itemList['tags']=$pageData->tags;
            $itemList['date']=$pageData->date;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists); 
    }

    public function gallerypostcontentAction($postid)
    {
        /** Init Block **/
        $authService = new AuthService();
        $gallery = new Gallery(); 

        //Business Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$gallery->getGalleryContentByPost($postid); // Get Gallery data from the Database
        foreach($resultData as $pageData) {
            $itemList['id']=$pageData->id;
            $itemList['caption']=$pageData->caption;
            $itemList['type']=$pageData->type;
            $itemList['postid']=$pageData->postid;
            $itemList['posttype']=$pageData->posttype;
            $itemList['media']=$pageData->media;
            $itemList['details']=$pageData->details;
            $itemList['categoryid']=$pageData->categoryid;
            $itemList['category']=$pageData->category;
            $itemList['subcategoryid']=$pageData->subcategoryid;
            $itemList['subcategory']=$pageData->subcategory;
            $itemList['tags']=$pageData->tags;
            $itemList['date']=$pageData->date;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists); 
    }

    public function removegalleryAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $gallery = new Gallery();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $gallery->removeGallery($this->request->getPost('galleryid')); //Delete page
        echo json_encode(array("status"=>"success","message"=>"Gallery entry deleted successfully"));
    }

    public function categoriesAction($type)
    {
        /** Init Block **/
        $categories = new Categories();
        $subcategories = new Subcategories();

        //Logic
        $itemLists = array();
        $itemList = array();
        $itemSubLists = array();
        $itemSubList = array();
        $resultData=$categories->filterCategories($type); // Get type categories data from the Database
        foreach($resultData as $categoryData) {
            $itemList['categoryid']=$categoryData->id;            
            $itemList['category']=$categoryData->category;
            $itemList['categoryimage']=$categoryData->image;
            $itemList['categorytype']=$categoryData->type;
            $itemList['safeurl']=$categoryData->safeurl;
            $itemList['categorydate']=$categoryData->date;
            $categoryid=$categoryData->id;
              //SubCategories
              $subResultData=$subcategories->filterSubcategories($categoryid); // Get type subcategories data from the Database
              foreach($subResultData as $subcategoryData) {
                $itemSubList['subcategoryid']=$subcategoryData->id;            
                $itemSubList['subcategory']=$subcategoryData->subcategory;
                $itemSubList['subcategoryimage']=$subcategoryData->image;
                $itemSubList['subcategorytype']=$subcategoryData->type;
                $itemSubList['safeurl']=$subcategoryData->safeurl;
                $itemSubList['subcategorydate']=$subcategoryData->date;
                $itemSubLists[] = $itemSubList;
              }
            $itemList['subcategories']=$itemSubLists;
            $itemSubLists=[]; $itemSubList=[]; //Clear Sub Vars
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists); 
    }

    public function subcategoriesAction($categoryid=0)
    {
        /** Init Block **/
        $subcategories = new Subcategories();

        //Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$subcategories->filterSubcategories($categoryid); // Get type subcategories data from the Database
        foreach($resultData as $categoryData) {
            $itemList['categoryid']=$categoryData->categoryid;            
            $itemList['subcategoryid']=$categoryData->id;            
            $itemList['subcategory']=$categoryData->subcategory;
            $itemList['subcategoryimage']=$categoryData->image;
            $itemList['subcategorytype']=$categoryData->type;
            $itemList['safeurl']=$categoryData->safeurl;
            $itemList['subcategorydate']=$categoryData->date;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists); 
    }

    public function slidebannersAction()
    {
        /** Init Block **/
        $errors = [];
        $data = [];
        $banners = new Bannersliders();

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$banners->getSlideBannersItems(); // Get data from the Database
        foreach($resultData as $bannersData) {
            $itemList['id']=$bannersData->id;
            $itemList['media']=$this->getDi()->getShared('siteurl').'/'.$bannersData->media;
            $itemList['type']=$bannersData->type;
            $itemList['details']=$bannersData->details;
            $itemList['slidecaption']=$bannersData->slidecaption;
            $itemList['slidecaptiontwo']=$bannersData->slidecaptiontwo;
            $itemList['slidecaptionthree']=$bannersData->slidecaptionthree;
            $itemList['slidetext']=$bannersData->slidetext;
            $itemList['slidebuttonlabel']=$bannersData->slidebuttonlabel;
            $itemList['slidebuttonlink']=$bannersData->slidebuttonlink;
            $itemList['author']=$bannersData->author;
            $itemList['date']=$bannersData->date;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists);  
    }

    public function bannersAction()
    {
        /** Init Block **/
        $errors = [];
        $data = [];
        $banners = new Banners();

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$banners->getBannersItems(); // Get data from the Database
        foreach($resultData as $bannersData) {
            $itemList['id']=$bannersData->id;
            $itemList['image']=$this->getDi()->getShared('siteurl').'/'.$bannersData->image;
            $itemList['link']=$bannersData->link;
            $itemList['position']=$bannersData->position;
            $itemList['author']=$bannersData->author;
            $itemList['date']=$bannersData->date;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists);  
    }

    public function testimonialsAction($resultLimit=50)
    {
        /** Init Block **/
        $authService = new AuthService();
        $clienttestimonials = new Clienttestimonials();

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultNum = (int)$resultLimit;
        $resultData=$clienttestimonials->allApprovedEntries(); // Get posts data from the Database
        foreach($resultData as $entryData) {
            $itemList['id']=$entryData->id;
            $itemList['author']=$entryData->author;
            $itemList['authorphoto']=$this->getDi()->getShared('siteurl').'/'.$entryData->authorphoto;
            $itemList['authorjob']=$entryData->authorjob;
            $itemList['authorlocation']=$entryData->authorlocation;
            $itemList['details']=$entryData->details;
            $itemList['date']=$entryData->date;
            $itemLists[] = $itemList;
        } 
        echo json_encode($itemLists);    
    }

    public function contactformAction()
    {
        /** Init Block **/
        $emailerService = new EmailerService();
        $errors = [];
        $data = [];

        //Full name
        $data['name'] = $this->request->getPost('name');
        if (!is_string($data['name'])) {
            $errors['name'] = 'Full name expected';
        }

        //Email
        $data['email'] = $this->request->getPost('email');
        if (!is_string($data['email'])) {
            $errors['email'] = 'Email expected';
        }

        //Phone
        $data['phone'] = $this->request->getPost('phone');
        if (!is_string($data['phone'])) {
            $errors['phone'] = 'Phone number expected';
        }
        
        //Subject
        $data['subject'] = "Contact Form";

        //Details
        $data['details'] = $this->request->getPost('details');
        if (!is_string($data['details'])) {
            $errors['details'] = 'Details expected';
        }

        if ($errors) {
            return json_encode(array("status"=>"failed","message"=>implode( ", ", $errors )));
        }
        //Send Mail to Webmaster
        $emailerService->sendContactFeedbackEmail($data['name'], $data['email'], $data['phone'], $data['subject'], $data['details']);
        echo json_encode(array("status"=>"success","message"=>"Thanks you for reaching out! You would be responded to soon."));
    }

    public function feedbackformAction()
    {
        /** Init Block **/
        $emailerService = new EmailerService();
        $errors = [];
        $data = [];
        $clienttestimonials = new Clienttestimonials();

        //Author name
        $data['author'] = $this->request->getPost('author');
        if (!is_string($data['author'])) {
            $errors['author'] = 'Author name expected';
        }

        //Author Job
        $data['authorjob'] = $this->request->getPost('authorjob');
        if (!is_string($data['authorjob'])) {
            $errors['authorjob'] = 'Author job expected';
        }

        //Author Location
        $data['authorlocation'] = $this->request->getPost('authorlocation');
        if (!is_string($data['authorlocation'])) {
            $errors['authorlocation'] = 'Author location expected';
        }

        //Details
        $data['details'] = $this->request->getPost('details');
        if (!is_string($data['details'])) {
            $errors['details'] = 'Details expected';
        }

        //Author Photo
        $data['authorphoto']="";
        $data['media']="";
        if($this->request->hasFiles() == true){ 
            $i=1;
            $extIMG = array(
                'image/jpeg',
                'image/png',
                'image/gif',
            );
            foreach( $this->request->getUploadedFiles() as $file)
            {              
                // is it a valid extension?
                if ( in_array($file->getType() , $extIMG) && $file->getError() == 0 )
                {
                    $Name          = preg_replace("/[^A-Za-z0-9.]/", '-', $file->getName() );
                    $NewFileName=date("Ymdhis", strtotime("now")).rand(100,1000); //Random file new name
                    $FileExtension = $file->getExtension();
                    $FileName      = "files/" . $NewFileName . "." . $FileExtension;
                    if(!$file->moveTo($FileName)) { // move file to needed path";
                        return json_encode(array("status"=>"failed","message"=>"Error uploading photo"));
                    } else {
                        if($i == 1) {
                            $data['authorphoto'] = "$FileName";
                            $data['type'] = "text";
                        } elseif($i == 2) {
                            $data['media'] = "$FileName";
                            $data['type'] = "video";
                        }
                        $i++;
                    }
                } else {
                    return json_encode(array("status"=>"failed","message"=>"Photo must be either JPEG or PNG or GIF file format"));
                }
            }
        }

        //Status
        $data['status'] = "pending";

        //Get current date and time
        $data['date'] = date("d-m-Y");

        if ($errors) {
            return json_encode(array("status"=>"failed","message"=>implode( ", ", $errors )));
        }
        //Send Mail to Webmaster
        $clienttestimonials->addNewEntry($data);
        $emailerService->sendAdminNewTestimonialAlertEmail($data['author'], $data['authorjob'], $data['authorlocation'], $data['details']);
        echo json_encode(array("status"=>"success","message"=>"Your feedback testimonial posted successfully"));
    }

    public function newslettersubscribeAction()
    {
        /** Init Block **/
        $emailerService = new EmailerService();
        $errors = [];
        $data = [];

        //Full name
        $data['name'] = $this->request->getPost('name');

        //Email
        $data['email'] = $this->request->getPost('email');
        if (!is_string($data['email'])) {
            $errors['email'] = 'Email expected';
        }

        if ($errors) {
            return json_encode(array("status"=>"failed","message"=>implode( ", ", $errors )));
        }
        //Send Mail to Webmaster
        $emailerService->sendNewsletterSubscriptionEmail($data['email'], $data['name']);
        echo json_encode(array("status"=>"success","message"=>"Thanks you for subscribing!"));
    }

    public function advertorialbannerAction($position="all",$count="1")
    {
        /** Init Block **/
        $advertorials = new Advertorials();
        $errors = [];
        $data = [];

        //Get Banner
        $advertorialsData=$advertorials->singleEntryByPosition("$position"); // Get Advert data from the Database
        $advertbanner=$this->getDi()->getShared('siteurl').'/'.$advertorialsData->image;
        $advertbannerlink='https://localmarket.com.ng/#!/advertoriallink/'.$advertorialsData->id;

        echo json_encode(array("status"=>"success","advertbanner"=>"$advertbanner","advertbannerlink"=>"$advertbannerlink","advertbannerposition"=>"$position"));
    }

    public function advertorialbannerlinkAction($advertid,$source="app")
    {
        /** Init Block **/
        $advertorials = new Advertorials();
        $errors = [];
        $data = [];

        //Update Banner Click Count
        $advertorials->updateEntryClickCount("$advertid"); //Update banner click count
        $advertorialsData=$advertorials->singleEntry("$advertid"); // Get Advert data from the Database
        $advertbannerlink=$advertorialsData->link;

        echo json_encode(array("status"=>"success","advertbannerlink"=>"$advertbannerlink"));
    }

    public function summarystatisticsAction()
    {
        /** Init Block **/
        $games = new Games();
        $gamebets = new Gamebets();
        $users = new Users();
        $transactions = new Transactions();

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemList = array();
        $itemList['gamebets']=$gamebets->allBetsCount();
        $itemList['betwins']=$gamebets->allBetsCount();
        $itemList['users']=$users->sumUsers();
        $itemList['withdrawals']=$transactions->sumWithdrawals();
        echo json_encode($itemList);       
    }
}
?>