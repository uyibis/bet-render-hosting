<?php

use Phalcon\Mvc\Controller;

class BlogController extends \Phalcon\Mvc\Controller {

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
        $admin = new Admin();
        $authService = new AuthService();
        $generalService = new GeneralService();
        $errors = [];
        $data = [];
        $blog = new Blog();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        /** Validation Block **/
        //Author ID and Name
        $data['authorid'] = $this->request->getPost('userid');
        $data['author'] = $this->request->getPost('username');
        $userData=$admin->singleAdmin($this->request->getPost('userid')); // Get User data from the Database
        $data['authorphoto']=$userData->photo;
        $data['authordetails']=$userData->details;
        
        //Blog name
        $data['title'] = $this->request->getPost('title');
        if (!is_string($data['title'])) {
            $errors['title'] = 'Blog title expected';
        }
        $data['safeurl'] = $generalService->getSafeURL($data['title']);

        //Blog views
        $data['views'] = '0';

        //Blog comments
        $data['comments'] = '0';

        //Blog type
        $data['type'] = $this->request->getPost('type');
        if (!is_string($data['type'])) {
            $errors['type'] = 'Blog type expected';
        }

        //Blog category
        $data['categoryid'] = $this->request->getPost('categoryid');
        if (!is_string($data['categoryid'])) {
            $errors['categoryid'] = 'Blog category expected';
        }
        $data['category'] = $generalService->getCategoryName($data['categoryid']);

        //Blog subcategory
        $data['subcategoryid'] = $this->request->getPost('subcategoryid');
        $data['subcategory'] = "";
        //if((string)(int)$data['subcategoryid'] == $data['subcategoryid']) {
        //   $data['subcategory'] = $generalService->getSubCategoryName($data['subcategoryid']);
        //}

        //Blog media
        $data['media'] = $this->request->getPost('media');

        //Blog mediatype
        $data['mediatype'] = $this->request->getPost('mediatype');

        //Blog medialink
        $data['medialink'] = $this->request->getPost('medialink');

        //Blog details
        $data['details'] = $this->request->getPost('details');
        if (!is_string($data['details'])) {
            $errors['details'] = 'Blog details content expected';
        }

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
                        $data['photo'] = "$FileName";
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
        $blog->addNewBlog($data);
        echo json_encode(array("status"=>"success","message"=>"Blog post added successfully"));
    }

    public function updateAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $errors = [];
        $data = [];
        $blog = new Blog();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        if($this->request->getPost('title')){ //Title change check
            $data['title'] = $this->request->getPost('title');
            if (is_string($data['title'])) {
                $data['safeurl'] = $generalService->getSafeURL($data['title']);
                $blog->updateBlog($this->request->getPost('postid'), $this->request->getPost('title'), "title"); //Update User account details changes
                $blog->updateBlog($this->request->getPost('postid'), $data['safeurl'], "safeurl"); //Update User account details changes
            } else {
                return json_encode(array("status"=>"failed","message"=>"Blog title must be a string"));
            }
        }

        if($this->request->getPost('details')){ //details change check
            $data['details'] = $this->request->getPost('details');
            if (is_string($data['details'])) {
                $blog->updateBlog($this->request->getPost('postid'), $this->request->getPost('details'), "details"); //Update User account details changes
            } else {
                return json_encode(array("status"=>"failed","message"=>"Blog details must be provided"));
            }
        }

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
                        $blog->updateBlog($this->request->getPost('postid'), $FileName, "image"); //Update blog photo changes
                    }
                } else {
                    return json_encode(array("status"=>"failed","message"=>"Photo must be either JPEG or PNG or GIF file format"));
                }
            }
        }
        
        echo json_encode(array("status"=>"success","message"=>"Blog post updated successfully"));
    }

    public function allAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $blog = new Blog();

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$blog->getAllBlogPosts(); // Get Blog posts data from the Database
        foreach($resultData as $blogData) {
            $itemList['id']=$blogData->id;
            $itemList['title']=$blogData->title;
            $itemList['photo']=$this->getDi()->getShared('siteurl').'/'.$blogData->photo;
            $itemList['safeurl']=$blogData->safeurl;
            $itemList['authorid']=$blogData->authorid;
            $itemList['author']=$blogData->author;
            $itemList['authorphoto']=$this->getDi()->getShared('siteurl').'/'.$blogData->authorphoto;
            $itemList['type']=$blogData->type;
            $itemList['media']=$blogData->media;
            $itemList['mediatype']=$blogData->mediatype;
            $itemList['medialink']=$blogData->medialink;
            $itemList['categoryid']=$blogData->categoryid;
            $itemList['category']=$blogData->category;
            $itemList['subcategoryid']=$blogData->subcategoryid;
            $itemList['subcategory']=$blogData->subcategory;
            $itemList['views']=$blogData->views;
            $itemList['comments']=$blogData->comments;
            $itemList['details']=$generalService->trim_text($blogData->details, '50', true, true); 
            $itemList['date']=$blogData->date;
            $itemLists[] = $itemList;
        } 
        echo json_encode($itemLists);    
    }

    public function listsAction($resultLimit,$category="undefined",$subcategory="undefined",$searchmode="no")
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $blog = new Blog();

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultNum = (int)$resultLimit;
        $resultData=$blog->getBlogPosts($resultNum); // Get Blog posts data from the Database
        foreach($resultData as $blogData) {
            $itemList['id']=$blogData->id;
            $itemList['title']=$blogData->title;
            $itemList['photo']=$this->getDi()->getShared('siteurl').'/'.$blogData->photo;
            $itemList['safeurl']=$blogData->safeurl;
            $itemList['authorid']=$blogData->authorid;
            $itemList['author']=$blogData->author;
            $itemList['authorphoto']=$this->getDi()->getShared('siteurl').'/'.$blogData->authorphoto;
            $itemList['type']=$blogData->type;
            $itemList['media']=$blogData->media;
            $itemList['mediatype']=$blogData->mediatype;
            $itemList['medialink']=$blogData->medialink;
            $itemList['categoryid']=$blogData->categoryid;
            $itemList['category']=$blogData->category;
            $itemList['subcategoryid']=$blogData->subcategoryid;
            $itemList['subcategory']=$blogData->subcategory;
            $itemList['views']=$blogData->views;
            $itemList['comments']=$blogData->comments;
            $itemList['details']=$generalService->trim_text($blogData->details, '50', true, true); 
            $itemList['date']=$blogData->date;
            $itemLists[] = $itemList;
        } 
        echo json_encode($itemLists);    
    }

    public function searchlistsAction($category="undefined",$subcategory="undefined",$searchmode="no",$resultLimit=50)
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $blog = new Blog();

        //Blog category filter search
        $resultNum = (int)$resultLimit;
        $data['categoryid']="";
        $data['subcategoryid']="";
        $data['query']="";
        if($searchmode=="yes"){
            $data['query'] = "$subcategory";
        } else {
            if($category && $category!="undefined"){
                //Product category
                $data['categoryid'] = $generalService->getCategoryID($category);
            } 
            if($subcategory && $subcategory!="undefined"){
                //Product subcategory
                $data['subcategoryid'] = $generalService->getSubCategoryID($subcategory);
            } 
        }

        //Query time
        $itemLists = array();
        $itemList = array();
        if($data['subcategoryid']){
            $resultData=$blog->blogPostsBySubcategory($data['categoryid'],$data['subcategoryid'],$resultNum); // Get products data from the Database
        } elseif($data['categoryid']){
            $resultData=$blog->blogPostsByCategory($data['categoryid'],$resultNum); // Get products data from the Database
        } elseif($data['query']){
            $resultData=$blog->searchBlogPosts($data['query'],$resultNum); // Get products data from the Database
        } else {
            $resultData=$blog->getAllBlogPosts(); // Get products data from the Database
        }
        foreach($resultData as $blogData) {
            $itemList['id']=$blogData->id;
            $itemList['title']=$blogData->title;
            $itemList['photo']=$this->getDi()->getShared('siteurl').'/'.$blogData->photo;
            $itemList['safeurl']=$blogData->safeurl;
            $itemList['authorid']=$blogData->authorid;
            $itemList['author']=$blogData->author;
            $itemList['authorphoto']=$this->getDi()->getShared('siteurl').'/'.$blogData->authorphoto;
            $itemList['type']=$blogData->type;
            $itemList['media']=$blogData->media;
            $itemList['mediatype']=$blogData->mediatype;
            $itemList['medialink']=$blogData->medialink;
            $itemList['categoryid']=$blogData->categoryid;
            $itemList['category']=$blogData->category;
            $itemList['subcategoryid']=$blogData->subcategoryid;
            $itemList['subcategory']=$blogData->subcategory;
            $itemList['views']=$blogData->views;
            $itemList['comments']=$blogData->comments;
            $itemList['details']=$generalService->trim_text($blogData->details, '50', true, true); 
            $itemList['date']=$blogData->date;
            $itemLists[] = $itemList;
        } 
        echo json_encode($itemLists);       
    }

    public function featuredAction($resultLimit)
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $blog = new Blog();

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultNum = (int)$resultLimit;
        $resultData=$blog->getBlogFeaturedPosts($resultNum); // Get Blog posts data from the Database
        foreach($resultData as $blogData) {
            $itemList['id']=$blogData->id;
            $itemList['title']=$blogData->title;
            $itemList['photo']=$this->getDi()->getShared('siteurl').'/'.$blogData->photo;
            $itemList['safeurl']=$blogData->safeurl;
            $itemList['authorid']=$blogData->authorid;
            $itemList['author']=$blogData->author;
            $itemList['authorphoto']=$this->getDi()->getShared('siteurl').'/'.$blogData->authorphoto;
            $itemList['type']=$blogData->type;
            $itemList['media']=$blogData->media;
            $itemList['mediatype']=$blogData->mediatype;
            $itemList['medialink']=$blogData->medialink;
            $itemList['categoryid']=$blogData->categoryid;
            $itemList['category']=$blogData->category;
            $itemList['subcategoryid']=$blogData->subcategoryid;
            $itemList['subcategory']=$blogData->subcategory;
            $itemList['views']=$blogData->views;
            $itemList['comments']=$blogData->comments;
            $itemList['details']=$generalService->trim_text($blogData->details, '50', true, true); 
            $itemList['date']=$blogData->date;
            $itemLists[] = $itemList;
        } 
        echo json_encode($itemLists);    
    }

    public function singleAction($blogid)
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $blog = new Blog();

        //Passed the User Auth Check, Proceed with the Business Logic
        $blog->updateBlogPostViewCount($blogid); //Update blog post views count
        $itemList = array();
        $blogData=$blog->getBlogContent($blogid); // Get Blog ID data from the Database
        $itemList['id']=$blogData->id;
        $itemList['title']=$blogData->title;
        $itemList['photo']=$this->getDi()->getShared('siteurl').'/'.$blogData->photo;
        $itemList['safeurl']=$blogData->safeurl;
        $itemList['authorid']=$blogData->authorid;
        $itemList['author']=$blogData->author;
        $itemList['authorphoto']=$this->getDi()->getShared('siteurl').'/'.$blogData->authorphoto;
        $itemList['authordetails']=$blogData->authordetails;
        $itemList['type']=$blogData->type;
        $itemList['media']=$blogData->media;
        $itemList['mediatype']=$blogData->mediatype;
        $itemList['medialink']=$blogData->medialink;
        $itemList['categoryid']=$blogData->categoryid;
        $itemList['category']=$blogData->category;
        $itemList['subcategoryid']=$blogData->subcategoryid;
        $itemList['subcategory']=$blogData->subcategory;
        $itemList['views']=$blogData->views;
        $itemList['comments']=$blogData->comments;
        $itemList['details']=$blogData->details;
        $itemList['date']=$blogData->date;
        echo json_encode($itemList);       
    }

    public function categoriesAction()
    {
        /** Init Block **/
        $categories = new Categories();
        $subcategories = new Subcategories();

        //Logic
        $itemLists = array();
        $itemList = array();
        $itemSubLists = array();
        $itemSubList = array();
        $resultData=$categories->filterCategories("blog"); // Get product categories data from the Database
        foreach($resultData as $categoryData) {
            $itemList['categoryid']=$categoryData->id;            
            $itemList['category']=$categoryData->category;
            $itemList['categoryimage']=$categoryData->image;
            $itemList['categorytype']=$categoryData->type;
            $itemList['safeurl']=$categoryData->safeurl;
            $itemList['categorydate']=$categoryData->date;
            $categoryid=$categoryData->id;
              //SubCategories
              $subResultData=$subcategories->filterSubcategories($categoryid); // Get product subcategories data from the Database
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
        $resultData=$subcategories->filterSubcategories($categoryid); // Get product subcategories data from the Database
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

    public function addcommentAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $errors = [];
        $data = [];
        $comments = new Comments(); 
        $blog = new Blog();

        /** Validation Block **/
        //postid
        $data['postid'] = $this->request->getPost('postid');
        if (!is_string($data['postid'])) {
            $errors['postid'] = 'Post ID expected';
        }
        
        //commenter ID
        $data['userid'] = $this->request->getPost('userid');
        if (!is_string($data['userid'])) {
            $errors['userid'] = 'User ID expected';
        }
        
        //commenter name
        $data['username'] = $this->request->getPost('username');
        if (!is_string($data['username'])) {
            $errors['username'] = 'Username expected';
        }

        //commenter email
        $data['useremail'] = $this->request->getPost('email');

        //commenter phone
        $data['userphone'] = $this->request->getPost('phone');

        //type
        $data['type'] = "blog";

        //status
        $data['status'] = "approved";
        
        //commenter details
        $data['details'] = $this->request->getPost('details');
        if (!is_string($data['details'])) {
            $errors['details'] = 'Comment expected';
        }       
        
        //commenter photo
        $data['photo'] = "files/avatar.png";
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
                        $data['photo'] = "$FileName";
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
        $comments->addEntry($data);
        $blog->updateBlogPostCommentCount($data['postid']); //Update blog post comment count
        echo json_encode(array("status"=>"success","message"=>"Comment added successfully"));
    }

    public function commentsAction($postid)
    {
        /** Init Block **/
        $comments = new Comments(); 

        /** Logic Block **/
        $itemLists = array();
        $itemList = array();
        $resultData=$comments->allEntriesByPost($postid); // Get post comments data from the Database
        foreach($resultData as $entryData) {
            $itemList['id']=$entryData->id;
            $itemList['postid']=$entryData->postid;
            $itemList['userid']=$entryData->userid;
            $itemList['username']=$entryData->username;
            $itemList['userphoto']=$this->getDi()->getShared('siteurl').'/'.$userData->photo;
            $itemList['photo']=$this->getDi()->getShared('siteurl').'/'.$entryData->photo;
            $itemList['status']=$entryData->status;
            $itemList['details']=$entryData->details;
            $itemList['date']=$entryData->date;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists);
    }

    public function removecommentAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $blog = new Blog();
        $comments = new Comments(); 

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $comments->removeEntry($this->request->getPost('postid')); //Delete comment
        $blog->reduceBlogPostCommentCount($this->request->getPost('postid')); //Reduce blog post comments count
        echo json_encode(array("status"=>"success","message"=>"Comment deleted successfully"));
    }

    public function removeBlogAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $blog = new Blog();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $blog->removeBlog($this->request->getPost('blogid')); //Delete product
        echo json_encode(array("status"=>"success","message"=>"Blog post deleted successfully"));
    }

}
?>