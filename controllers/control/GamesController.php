<?php 

class GamesController extends BaseController{
     
    public function actionIndex(){
    $user = new User();
    if(!$user->isAuth()){
        header("Location: /control/index");
    }
    $getUserPrfile = $user->getProfile();
    if($getUserPrfile['role'] != 'admin')  parent::ShowError(404, "Страница не найдена!");
    
    $getSettings = $this->db->query('SELECT * FROM ga_settings');
    $settings = $getSettings->fetch();
    
    $title = "Игры";
    
    $getGames = $this->db->query('SELECT * FROM ga_games WHERE status ="1"');
    $getGames = $getGames->fetchAll();
    
     
    $content = $this->view->renderPartial("control/games/index", ['games' => $getGames]);
 
    $this->view->render("control/main", ['content' => $content, 'title' => $title]);
    }
    
    public function actionAdd(){
    $user = new User();
    if(!$user->isAuth()){
        header("Location: /control/index");
    }
    $getUserPrfile = $user->getProfile();
    if($getUserPrfile['role'] != 'admin')  parent::ShowError(404, "Страница не найдена!");
    
    $getSettings = $this->db->query('SELECT * FROM ga_settings');
    $settings = $getSettings->fetch();
    
    $title = "Добавление новой игры";
    
    if(parent::isAjax()){
    
    $status = 1;
    $id = $_POST['game'];
    
    $sql = "UPDATE ga_games SET status = :status WHERE id= :id";
    $update = $this->db->prepare($sql);                                   
    $update->bindParam(':status', $status);       
    $update->bindParam(':id', $id); 
    $update->execute();     
    
    $answer['status'] = "success";
    $answer['success'] = "Новая игра успешно добавлена";
    exit(json_encode($answer)); 
        
    }else{
        
        
    $getGames = $this->db->query('SELECT * FROM ga_games WHERE status = "0"');
    $getGames = $getGames->fetchAll();
     
    $content = $this->view->renderPartial("control/games/add", ['games' => $getGames]);
 
    $this->view->render("control/main", ['content' => $content, 'title' => $title]);   
    }
    
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
    $status = 0;
    $sql = "UPDATE ga_games SET status = :status WHERE id = :id";
    $update = $this->db->prepare($sql);                                  
    $update->bindParam(':status', $status);      
    $update->bindParam(':id', $id); 
    $update->execute();  

    }
    
        
    }
    
    
    public function actionEdit(){
    $user = new User();
    if(!$user->isAuth()){
        header("Location: /control/index");
    }
    $getUserPrfile = $user->getProfile();
    if($getUserPrfile['role'] != 'admin')  parent::ShowError(404, "Страница не найдена!");

    if(isset($_GET['id'])) $id = (int)$_GET['id']; else $id = '';
    
    $title = "Изменение платежной системы #$id";
     
    $getInfoPaymethods = $this->db->prepare('SELECT * FROM ga_pay_methods WHERE id = :id');
    $getInfoPaymethods->execute(array(':id' => $id));
    $getInfoPaymethods = $getInfoPaymethods->fetch();
    if(empty($getInfoPaymethods)) parent::ShowError(404, "Страница не найдена!");
    
    
    if(parent::isAjax()){
        
    $status = (int)$_POST['status'];
    if($getInfoPaymethods['typeCode'] == 'unitpay'){
    $content = json_encode(['public_key' => $_POST['public_key'], 'secret_key' => $_POST['secret_key']]);
    }elseif($getInfoPaymethods['typeCode'] == 'robokassa'){
    $content = json_encode(['login' => $_POST['login'], 'password1' => $_POST['password1'], 'password2' => $_POST['password2']]);    
    }

    $sql = "UPDATE ga_pay_methods SET status = :status, content = :content WHERE id= :id";
    $update = $this->db->prepare($sql);                                        
    $update->bindParam(':status', $status);   
    $update->bindParam(':content', $content); 
    $update->bindParam(':id', $id); 
    $update->execute();     
    
    $answer['status'] = "success";
    $answer['success'] = "Настройки успешно изменены";
    exit(json_encode($answer)); 
        
    }else{
        
    $system = new System();
    $url = $system->getUrl();
    $params = json_decode($getInfoPaymethods['content'], true);
    $content = $this->view->renderPartial("control/paymethods/edit", ['data' => $getInfoPaymethods, 'params' => $params, 'url' => $url]);
 
    $this->view->render("control/main", ['content' => $content, 'title' => $title]);   
    
    }
    }

}