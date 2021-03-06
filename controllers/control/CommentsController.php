<?php 

class CommentsController extends BaseController{
    
    public function actionSearch(){
    $user = new User();
    if(!$user->isAuth()){
        header("Location: /control/index");
    }
    $getUserPrfile = $user->getProfile();
    if($getUserPrfile['role'] != 'admin')  parent::ShowError(404, "Страница не найдена!");
    
    $getSettings = $this->db->query('SELECT * FROM ga_settings');
    $settings = $getSettings->fetch();
    
    $title = "Поиск комментарии";
    
          
    $filter = [];
    $sql = '';
       
    if(isset($_GET['status']) && $_GET['status'] != ''){
    $filter['status'] = $_GET['status'];
    $moderation = strip_tags($_GET['status']);
    if(stristr($sql, "WHERE") == false){ 
      $sql .= "WHERE moderation = '$moderation'";   
    }else $sql .= " AND moderation = '$moderation'"; 
    }else $filter['status'] = '';

    $countComments = $this->db->query("SELECT * FROM ga_comments $sql");
    $count = $countComments->rowCount();
    
    $filter['count'] = $count;
    
    $pagination = new Pagination();
    $per_page = 15;
    $result = $pagination->create(array('per_page' => $per_page, 'count' => $count, 'no_rgp' => ''));   
    
    $getComments = $this->db->query("SELECT * FROM ga_comments $sql ORDER BY id DESC LIMIT ".$result['start'].", ".$per_page."");
    $countComments = $countComments->fetchAll();
    
    
     $content = $this->view->renderPartial("control/comments/index", ['ViewPagination' => $result['ViewPagination'], 'filter' => $filter, 'comments' => $countComments]);
    
    
    $this->view->render("control/main", ['content' => $content, 'title' => $title]);   
    }
    
    
    public function actionIndex(){
    $user = new User();
    if(!$user->isAuth()){
        header("Location: /control/index");
    }
    $getUserPrfile = $user->getProfile();
    if($getUserPrfile['role'] != 'admin')  parent::ShowError(404, "Страница не найдена!");
    
    $getSettings = $this->db->query('SELECT * FROM ga_settings');
    $settings = $getSettings->fetch();
    
    $title = "Комментарии";
    
    if(parent::isAjax()){
        
        
    }else{
    
    $filter = [];
    
    $filter['address'] = '';
    $filter['status'] = '';
    
    
    $countComments = $this->db->query('SELECT * FROM ga_comments');
    $count = $countComments->rowCount();
    

    $pagination = new Pagination();
    $per_page = 15;
    $result = $pagination->create(array('per_page' => $per_page, 'count' => $count));

    $getComments = $this->db->query('SELECT * FROM ga_comments ORDER BY id DESC LIMIT '.$result['start'].', '.$per_page.'');
    $getComments = $getComments->fetchAll();
    $filter['count'] = count($getComments);
    $content = $this->view->renderPartial("control/comments/index", ['filter' => $filter, 'comments' => $getComments, 'ViewPagination' => $result['ViewPagination']]);
 
    $this->view->render("control/main", ['content' => $content, 'title' => $title]);   
    }
    
    }
    
    
    public function actionEdit(){
    $getSettings = $this->db->query('SELECT * FROM ga_settings');
    $settings = $getSettings->fetch();    
        
    $user = new User();
    if(!$user->isAuth()){
        header("Location: /control/index");
    }
    
    $getUserPrfile = $user->getProfile();
    if($getUserPrfile['role'] != 'admin')  parent::ShowError(404, "Страница не найдена!");

    if(isset($_GET['id'])) $id = (int)$_GET['id']; else $id = '';
    
    $title = "Изменение комментария #$id";
     
    $getInfoComments = $this->db->prepare('SELECT * FROM ga_comments WHERE id = :id');
    $getInfoComments->execute(array(':id' => $id));
    $getInfoComments = $getInfoComments->fetch();
    if(empty($getInfoComments)) parent::ShowError(404, "Страница не найдена!");
    
    
    if(parent::isAjax()){
        
    $status = (int)$_POST['status'];
    $moderation = (int)$_POST['moderation'];
    
    
    $game = $_POST['game'];
    $ip = $_POST['ip'];
    $port = $_POST['port'];
    
    $rating = $_POST['rating'];
    

    $sql = "UPDATE ga_servers SET status = :status, moderation =:moderation, game = :game, ip = :ip, port = :port, rating = :rating, top_enabled = :top_enabled, top_expired_date = :top_expired_date, vip_enabled = :vip_enabled, vip_expired_date = :vip_expired_date, color_enabled = :color_enabled, color_expired_date = :color_expired_date, ban = :ban, ban_couse = :ban_couse WHERE id= :id";
    $update = $this->db->prepare($sql);                                        
    $update->bindParam(':status', $status);   
    $update->bindParam(':moderation', $moderation); 
    $update->bindParam(':game', $game); 
    $update->bindParam(':ip', $ip);
    $update->bindParam(':color_expired_date', $color_expired_date);
    $update->bindParam(':ban', $ban);
    $update->bindParam(':ban_couse', $ban_couse);
    $update->bindParam(':id', $id); 
    $update->execute();     
    
    $answer['status'] = "success";
    $answer['success'] = "Сервер успешно изменен";
    exit(json_encode($answer)); 
        
    }else{
        

    $content = $this->view->renderPartial("control/comments/edit", ['data' => $getInfoComments]);
 
    $this->view->render("control/main", ['content' => $content, 'title' => $title]);   
    
    }
    }
    
    
    public function actionModeration(){
    $user = new User();
    if(!$user->isAuth()){
        header("Location: /control/index");
    }
    $getUserPrfile = $user->getProfile();
    if($getUserPrfile['role'] != 'admin')  parent::ShowError(404, "Страница не найдена!");

    if(isset($_GET['id'])) $id = (int)$_GET['id']; else $id = '';
    $moderation = 1;
    $sql = "UPDATE ga_comments SET moderation =:moderation WHERE id= :id";
    $update = $this->db->prepare($sql);                                          
    $update->bindParam(':moderation', $moderation); 
    $update->bindParam(':id', $id); 
    $update->execute();    
    
    header("Location: /control/comments");
  
    
    
        
    }
    
    public function actionRemove(){
    $user = new User();
    if(!$user->isAuth()){
        header("Location: /control/index");
    }
    $getUserPrfile = $user->getProfile();
    if($getUserPrfile['role'] != 'admin')  parent::ShowError(404, "Страница не найдена!");
    
    if(parent::isAjax()){
    if(isset($_GET['id'])) $id = (int)$_GET['id']; else $id = '';
    $sql = "DELETE FROM ga_comments WHERE id =  :id";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);   
    $stmt->execute();     

    }
    
        
    }
    
    
    
    
}