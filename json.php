<?
ini_set('log_errors', true);
ini_set('error_log', 'error_json.log');                 
session_start(); 
  
if(!isset($_SESSION['User']['id']) or empty($_SESSION['User']['id']) or !isset($_SESSION['User']['flag'])) {  
  if(!empty($_GET)) {
    /*$arr['msg'] = "Приняты неправильные данные";     
    $arr['response'] = 4000;  
    echo json_encode($arr);
    exit(); */  
  } else { 
    header('Location: /index.php');
  }
} else {
  $spec_id = $_SESSION['User']['id'];
  $spec_fio = $_SESSION['User']['fio'];
}
               
/****************************************/
require_once( 'const.php' );
require_once( 'DB.php' );
$db = new DBWrapper( SQLCONNECTSTRING );

date_default_timezone_set('Etc/GMT-3');

$arr=Array();  
//response 
//100 - all right! 
//200 - already have a timeline in this time isset
//201 - already have a work in this time isset
//202 - adding work - somethign wrong
//203 - updating work error - somethign wrong
//204 - updating timeline datetimes - somethign wrong
//205 - adding postopened work - error
//206 - adding ticket - somethign wrong
//207 - reg - error
//2072 - reg - error: mail not inserted
//208 - getting apporival tickets - error
//2082 - getting apporival tickets - error
//209 - updating tucket stat - error
//210 - updating tucket stat - DB wrong response
//211 - updating tucket hist - DB wrong response
//212 - adding work comment - error
//213 - adding tickets to group - error
//400 - not found action
//401 - not found works
//402 - not found user
//403 - not found ticketS
//4032 - not found SEARCH ticketS
//404 - not found ticket
//405 - not found errors 
//406 - not found places
//407 - not fount session
/****************************************/

          
    /*
    $fp2 = fopen('GETs.log','a');
    $now = date("Y-m-d H:i:s");
    fwrite( $fp2, "======== {$now} ======="."\r\n" );
    if(isset($_GET['action'])) {
      fwrite( $fp2, "======== {$_GET['action']} ======="."\r\n" );
    } 
    foreach($_GET as $k=>$v) {
      fwrite( $fp2, $k ."=". $v ."\r\n" );   
    }
    fclose($fp2); */
    
     
if(!empty($_GET)) {      
  foreach($_GET as $k=>$v) {
    //$_GET[$k]=@iconv("UTF-8", "windows-1251", $v);  
    if(@mb_detect_encoding($_GET[$k], 'UTF-8', true)!==false) {
      $_GET[$k]=@iconv("UTF-8", "windows-1251", $v);  
    } else {
      $_GET[$k]=@urldecode($v);
    }
  } 
} 

if(!empty($_POST)) {      
  foreach($_POST as $k=>$v) {
    //$_POST[$k]=@iconv("UTF-8", "windows-1251", $v);  
    if(@mb_detect_encoding($_POST[$k], 'UTF-8', true)!==false) {
      $_POST[$k]=@iconv("UTF-8", "windows-1251", $v);  
    } else {
      $_POST[$k]=@urldecode($v);
    }
  } 
} 





if(isset($_GET['wid']) and !isset($_GET['action'])) {         //   get a work info
  if(!isset($_SESSION['User'])) {
    $arr['msg'] = "Приняты неправильные данные";     
    $arr['response'] = 400;     
    echo @json_encode($arr);
    exit();
  }
  $arr = Array();
  $id = (int)$_GET['wid'];
  @$sql = "call dba.get_work_info(".(int)$id.");";             //get all info aboit work where work id = WID
  @$res_work_info = $db->get( $sql );   
  if(empty($res_work_info)) {
    $arr['msg'] = "Ошибка получения информации о работе!";
  } else {
    $arr['title'] = "Информация о работе № ".$id;  
    @$sql_work_stats = "SELECT id, value FROM dba.work_stats";         //get all work stats
    @$res_work_stats = $db->get( $sql_work_stats );
    if(!empty($res_work_stats)) {
      foreach($res_work_stats as $k3=>$v3) {
          foreach($v3 as $k4=>$v4) {     // for each row parameter
            $arr['work_stats'][$k3][$k4] = strToRu($v4);
          }      
      }
    }   
    foreach($res_work_info[0] as $k=>$v) {
      if($k=="last_update") {
        $v = substr($v, 0, -4); /***del microseconds ***/
      } 
      $arr[$k] = strToRu($v);      
    }
    @$sql2 = "call dba.get_work_history(".(int)$id.");";       //get all work history where work id = WID
    @$res_work_hist = $db->get( $sql2 );    
    if(empty($res_work_hist)) {
      $arr['msg'] = "История работы пуста";
    } else { 
      foreach($res_work_hist as $k1=>$v1) {    // for each hist row     
        foreach($v1 as $k2=>$v2) {     // for each row parameter   
          if($k2=="from_dt" or $k2=="to_dt") {
            $temp_var = explode(".", $res_work_hist[$k1][$k2]);
            $v2 = $temp_var[0];  
          } 
          $arr['hist'][$k1][$k2] = strToRu($v2);
        }       
      }      
    } 
    $sql_ckeck_auto_timelines = 'SELECT * FROM "DBA"."work_hist" WHERE work_hist.work_id = '.(int)$id.' ORDER BY work_hist.from_dt;'; 
    $res_ckeck_auto = $db->get( $sql_ckeck_auto_timelines ); 
    if(empty($res_ckeck_auto)) {
      $arr['msg_auto'] = "AUTO История работы пуста";
    } else { 
      foreach($res_ckeck_auto as $ka1=>$va1) {    // for each hist row     
        foreach($va1 as $ka2=>$va2) {     // for each row parameter   
          if($ka2=="from_dt" or $ka2=="to_dt") {
            $temp_var2 = explode(".", $res_ckeck_auto[$ka1][$ka2]);
            $va2 = $temp_var2[0];  
          } 
          $arr['hist_auto'][$ka1][$ka2] = strToRu($va2);
        }       
      }      
    } 
    @$sql_work_stats = "SELECT id, surname+' '+name+' '+patronymic as fio FROM dba.specialists WHERE exist = 1 and (role_id = 1 or role_id = 5) and id!=".$spec_id." order by surname";         //get all work stats
    @$res_work_stats = $db->get( $sql_work_stats );
    if(!empty($res_work_stats)) {
      foreach($res_work_stats as $k3=>$v3) {
          foreach($v3 as $k4=>$v4) {     // for each row parameter
            $arr['specialists'][$k3][$k4] = strToRu($v4);
          }      
      }
    } 
    @$sql_messages = "SELECT t2.surname+' '+t2.name+' '+t2.patronymic as creator_fio, t1.created, t1.text FROM dba.work_msg t1 LEFT OUTER JOIN dba.specialists t2 ON t1.creator_id = t2.id WHERE t1.work_id=".(int)$id.";";         //get all work stats
    @$res_messages = $db->get( $sql_messages );
    if(!empty($res_messages)) {
      foreach($res_messages as $km=>$vm) {
          foreach($vm as $km1=>$vm1) {     // for each row parameter
            $arr['messages'][$km][$km1] = strToRu($vm1);
          }      
      }
    }   
    @$sql3 = "call dba.get_spec_contacts(".$arr['creator_i'].");";   
    @$res3 = $db->get( $sql3 );    
    if(empty($res3[0][0])) {
      //$arr['msg'] = "Контакты создателя отсутствуют";
    } else { 
      foreach($res3 as $k1=>$v1) {    // for each row     
        foreach($v1 as $k2=>$v2) {     // for each parameter   
          $arr['creator_contacts'][$k1][$k2] = strToRu($v2);
        }       
      }      
    }          
  }
} elseif(isset($_GET['tid']) and !isset($_GET['action'])) { 
  if(!isset($_SESSION['User'])) {
    $arr['msg'] = "Приняты неправильные данные";     
    $arr['response'] = 400;     
    echo @json_encode($arr);
    exit();
  }
  $arr = Array();
  $id = (int)$_GET['tid'];
  @$sql = "call dba.get_ticket_info(".(int)$id.");";             //get all info aboit ticket where ticket id = TID
  @$res_ticket_info = $db->get( $sql );    
  if(empty($res_ticket_info)) {
    $arr['msg'] = "Ошибка получения информации о заявке!";
  } else {      
    $arr['title'] = "Информация о заявке № ".$id;  
    @$sql_ticket_stats = "SELECT id, value FROM dba.ticket_stats";         //get all ticket stats
    @$res_ticket_stats = $db->get( $sql_ticket_stats );
    if(!empty($res_ticket_stats)) {
      foreach($res_ticket_stats as $k3=>$v3) {
          foreach($v3 as $k4=>$v4) {     // for each row parameter
            $arr['ticket_stats'][$k3][$k4] = strToRu($v4);
          }      
      }
    }   
    foreach($res_ticket_info[0] as $k=>$v) {    // all main rows of ticket
      if($k=="created" or $k=="approval" or $k=="spec_selected" or $k=="closed") {
        $v = substr($v, 0, -4);
      }     
      $arr[$k] = strToRu($v);       
    }
    @$sql2 = "call dba.get_ticket_history(".(int)$id.");";       //get all ticket history where ticket id = TID
    @$res_ticket_hist = $db->get( $sql2 );    
    if(empty($res_ticket_hist)) {
      $arr['msg'] = "История заявки пуста";
    } else { 
      foreach($res_ticket_hist as $k1=>$v1) {    // for each hist row     
        foreach($v1 as $k2=>$v2) {     // for each row parameter   
          if($k2=="created") {
            $v2 = substr($v2, 0, -4);
          }        
          $arr['hist'][$k1][$k2] = strToRu($v2);
        }       
      }      
    } 
    //@$sql_specialists = "SELECT id, surname+' '+name+' '+patronymic as fio FROM dba.specialists WHERE exist = 1 and role_id = 1 and id!=".$spec_id;         //get all work stats
    $where_cond = "where error_id = ".$res_ticket_info[0]['error_i'];
    if(isset($arr['cancel_count'])) {
      if($arr['cancel_count']>=3) {
        $where_cond = "";
      }
    }
    //@$sql_specialists = "SELECT id, surname+' '+name+' '+patronymic as fio FROM dba.specialists where exist = 1 and role_id in (1, 5) order by surname;";// and role_id = 5 or id in (SELECT distinct(spec_id) FROM dba.specialist_errors ".$where_cond.");";
    @$sql_specialists = "SELECT t0.id, t0.surname+' '+t0.name+' '+t0.patronymic as fio, count(tt.id) as busy FROM dba.specialists t0 LEFT JOIN dba.tickets tt on tt.spec_id = t0.id and tt.stat_id = 2 and tt.spec_selected>=DATEADD(day, -1, GETDATE()) where t0.exist = 1 and t0.role_id in (1, 5) group by t0.id, fio order by fio;";
    @$res_specialists = $db->get( $sql_specialists );
    if(!empty($res_specialists)) {
      foreach($res_specialists as $k3=>$v3) {
          foreach($v3 as $k4=>$v4) {     // for each row parameter
            $arr['specialists'][$k3][$k4] = strToRu($v4);
          }      
      }
    }  
    if($_SESSION['User']['flag']==5) {  
      if($res_ticket_info[0]['blocked']=='0' or empty($res_ticket_info[0]['blocked'])) {  
        $sql_block = "UPDATE dba.tickets set blocked = ".$spec_id.", blocked_ts = GETDATE() WHERE id = ".$id;
        $res_block = $db->process( $sql_block );
        $arr['blocked'] = $spec_id;
        $arr['blocked_disp'] = $spec_fio;            
      } elseif($res_ticket_info[0]['blocked']!='-1' and (int)$res_ticket_info[0]['blocked_ts']<600000) {    //10 minutes timeout
        $sql_block_fio = "SELECT name+' '+surname+' '+patronymic as fio FROM dba.specialists WHERE id = ".$res_ticket_info[0]['blocked'];
        $res_block_fio = $db->get( $sql_block_fio );
        $arr['blocked_disp'] = strToRu($res_block_fio[0][0]);
        $arr['blocked'] = $res_ticket_info[0]['blocked'];  
      } elseif($res_ticket_info[0]['blocked']!='-1') {
        $arr['blocked'] = $spec_id;
        $arr['blocked_disp'] = $spec_fio;                
      } elseif($res_ticket_info[0]['blocked']=='-1') {
        $arr['blocked'] = -1;
        $arr['blocked_disp'] = 'Блок системой';                
      }
    } else {
      $arr['blocked'] = 0;
      $arr['blocked_disp'] = ''; 
    }      
    @$sql3 = "call dba.get_spec_contacts(".$arr['creator_i'].");";   
    @$res3 = $db->get( $sql3 );    
    if(empty($res3)) {
      $arr['msg'] = "Контакты создателя отсутствуют";
    } else { 
      foreach($res3 as $k1=>$v1) {    // for each row     
        foreach($v1 as $k2=>$v2) {     // for each parameter   
          $arr['creator_contacts'][$k1][$k2] = strToRu($v2);
        }       
      }      
    }     
  }  
} elseif(isset($_GET['action'])) {
  if(!isset($_SESSION['User'])) {
    $arr['msg'] = "Приняты неправильные данные";     
    $arr['response'] = 400;     
    //echo @json_encode($arr);
    //exit();
  }
  if(isset($_SESSION['User'])) {
    if($_SESSION['User']['id'] == 2) {
      $arr['msg'] = "Приняты неправильные данные";     
      $arr['response'] = 333;     
      echo json_encode($arr);  
      exit();
    }
  }
  try {
    switch ($_GET['action']) {
        case "add_new_work":   //add a new work  
            @$sql = "call dba.add_new_work(".$spec_id.",'".$_GET['comm']."');";       //get all work history where work id = WID
            @$res_work_add = $db->get( $sql );
            $db->commit();      
            if(empty($res_work_add)) {
              $arr['msg'] = "Неизвестная ошибка при добавлении в базу!";
              $arr['response'] = 202;  
            } else {
              $arr['new_work_id'] = $res_work_add[0][0];
              $arr['msg'] = "Добавлено!";
              $arr['response'] = 100;
            }
            break;
        case "add_new_ticket":  //add a new ticket 
        
    /*
            $start = microtime(true);
            $fp2 = fopen('ADD_TICKET_MS.log','a');
            fwrite( $fp2, "===START==\r\n" );
            $time = microtime(true) - $start;
            fwrite( $fp2, $time."\r\n" );   
    /*/
    
        
            
         
            $comment = 'Ошибка получения данных POST: ERROR ID 206';
            if(!empty($_GET['comm'])) { 
              $comment = $_GET['comm'];
            } elseif(!empty($_POST['comm'])) { 
              $comment = $_POST['comm'];
            }     
            $comment = str_replace("-||-", "&quot;", $comment);
            
            
    /*
            $time = microtime(true) - $start;
            fwrite( $fp2, $time."\r\n" );
    */             
                 
                 
                  
            @$sql = "call dba.add_new_ticket(".$spec_id.",".(int)$_GET['place'].",".(int)$_GET['error_id'].",'".$_GET['pc']."','".$_GET['ph']."','".$comment."');";       //get all work history where work id = WID
            @$res_work_add = $db->get( $sql );
            
            
    /*        
            $time = microtime(true) - $start;
            fwrite( $fp2, $time."\r\n" );
    */            
                 
                 
                 
                       
            if(empty($res_work_add)) {
              $arr['msg'] = "Неизвестная ошибка при добавлении в базу!";
              $arr['response'] = 206;  
            } else {
              $arr['msg'] = "Ваша заявка добавлена!<br>Не забудьте подтверждать закрытие!";
              $arr['response'] = 100; 
    //        $time = microtime(true) - $start;
    //        fwrite( $fp2, $time."\r\n" );          
              send(1,$res_work_add[0][0]);  // 1 - trigger means this is created and send to dispatcher
    //        $time = microtime(true) - $start;
    //        fwrite( $fp2, $time."\r\n" );                      
            } 
            
    /*      
            $time = microtime(true) - $start;
            fwrite( $fp2, $time."\r\n" );
            fwrite( $fp2, "===STOP==\r\n" );
            fclose($fp2);
    */      
            
            
                    
            break;
        case "get_ticket_message_counter":
            if(!isset($_GET['iss'])) {
              $_GET['iss'] = 0;
            }        
            @$sql_msg = "SELECT count(id) as cc FROM dba.ticket_msg where ticket_id = ".(int)$_GET['tid']." and is_system = ".(int)$_GET['iss'].";";
            @$res_msg = $db->get( $sql_msg );
            $res_msg[0][0]>0?$arr['msg_counter'] = $res_msg[0][0]:$arr['msg_counter']=0;
            $arr['response'] = 100;
            break;
        case "get_approval_tickets_count":
            @$sql = "select count(*) as cc from dba.tickets where stat_id = 3 and creator_id = ".$spec_id.";";       //get all work history where work id = WID
            @$res_count = $db->get( $sql );
            $db->commit();      
            if(empty($res_count)) {
              $arr['msg'] = "Неизвестная ошибка при получении заявок со статусом 'на утверждении'!";
              $arr['response'] = 208; 
              $arr['sql'] = $sql;  
            } else {
              $arr['cc'] = $res_count[0][0];
              $arr['response'] = 100;
            }
            break;
        case "get_created_tickets_count":
            @$sql = "select count(*) as cc from dba.tickets where stat_id = 1;";       //get all work history where work id = WID
            @$res_count = $db->get( $sql );
            $db->commit();      
            if(empty($res_count)) {
              $arr['msg'] = "Неизвестная ошибка при получении заявок со статусом 'создано'!";
              $arr['response'] = 2082;  
            } else {
              $arr['cc'] = $res_count[0][0];
              $arr['response'] = 100;
            }
            break;
        case "save_set_spec":
            $now_temp = date("Y-m-d H:i:s");
            //insert new work for ticket
            @$sql = 'INSERT INTO dba.works ("stat_id", "spec_id", "comments", "ts") SELECT 1, '.(int)$_GET['new_s_id'].', "comments", \''.$now_temp.'\' FROM dba.works WHERE id = '.(int)$_GET['wid'];    
            @$db->process( $sql );
            @$db->commit();      
            @$sql2 = 'SELECT @@IDENTITY';
            @$res_identuty = $db->get( $sql2 );    
            if(empty($res_identuty)) {
              $arr['msg'] = "Неизвестная ошибка добавления новой работы!";
              $arr['response'] = 202;
              break;  
            } 
            
            //insert new work history for new work
            @$sql_add_hist = "INSERT INTO dba.work_hist (\"work_id\", \"comments\", \"from_dt\", \"to_dt\") values (".$res_identuty[0][0].", '".iconv("UTF-8", "windows-1251", "Передал ".$spec_fio)."', '".$now_temp."', '".$now_temp."');";
            @$res_add_hist = $db->process( $sql_add_hist );
            @$db->commit(); 
            if(!$res_add_hist) {
              $arr['msg'] = "Неизвестная ошибка добавления истории к новому заданию!";
              $arr['response'] = 2022; 
              break;
            }  
              
            @$sql_add_msg = "INSERT INTO dba.work_msg (\"text\", \"created\", \"creator_id\", \"work_id\") SELECT \"text\", '".$now_temp."', \"creator_id\", ".$res_identuty[0][0]." FROM dba.work_msg WHERE work_id = ".(int)$_GET['wid'];        
            @$res_add_msg = $db->process( $sql_add_msg );
            @$db->commit(); 
            if(!$res_add_msg) {
              $arr['msg'] = "Неизвестная ошибка добавления комментариев к новому заданию!";
              $arr['response'] = 2023; 
              break;
            } 
                   
            @$sql = 'UPDATE dba.works SET stat_id=3 WHERE id = '.(int)$_GET['wid'];    
            @$res_work_add = $db->process( $sql );  
            @$db->commit();      
            if(empty($res_work_add)) {
              $arr['msg'] = "Неизвестная ошибка закрытия старой работы!";
              $arr['response'] = 202; 
              break; 
            } 
                       
            @$sql = 'SELECT ticket_id FROM dba.ticket_hist WHERE work_id = '.(int)$_GET['wid'];    
            @$res_ticket_id = $db->get( $sql );
            if(empty($res_ticket_id[0][0])) {
              $arr['msg'] = "Задание передано!";
              $arr['response'] = 100;
              break;
            } else {
              @$sql = 'INSERT INTO dba.ticket_hist ("ticket_id", "work_id", "text", "created") values ('.$res_ticket_id[0][0].', '.$res_identuty[0][0].', \''.iconv("UTF-8", "windows-1251", "Передал ".$spec_fio).'\', \''.date("Y-m-d H:i:s").'\');';    
              @$db->process( $sql );
              @$db->commit();  
              @$sql3 = 'SELECT @@IDENTITY';
              @$res_identuty2 = $db->get( $sql3 );       
              if(empty($res_identuty2[0][0])) {
                $arr['msg'] = "Ошибка добавления истории заявки";
                $arr['response'] = 211;
                break;  
              } 
              
              @$sql = 'UPDATE dba.tickets SET spec_id='.(int)$_GET['new_s_id'].' WHERE id = '.$res_ticket_id[0][0];    
              @$res_update_ticket_spec = $db->process( $sql );  
              @$db->commit();   
              if(empty($res_update_ticket_spec)) {
                $arr['msg'] = "Ошибка обновления специалиста у заявки";
                $arr['response'] = 2112;
                break;
              }   
                     
              $arr['msg'] = "Задание передано!";
              $arr['response'] = 100;
            } 
            break;
        case "set_ticket_spec":
            if(!empty($_GET['tid'])) { 
              $sql0 = "SELECT blocked from dba.tickets WHERE id = ".(int)$_GET['tid'].";";
              $res0 = $db->get($sql0);
              if($res0[0][0]=='-1') {
                $arr['msg'] = "Данная заявка принудительно заблокирована!<br>Обновите страницу!";
                $arr['response'] = 2020; 
                break;
              } else {
                $sql1 = "SELECT top 1 work_id from dba.ticket_hist WHERE work_id is not NULL and ticket_id in (SELECT id from dba.tickets WHERE id = ".(int)$_GET['tid']." and spec_id = ".(int)$_GET['new_s_id'].") order by work_id DESC;";
                $res1 = $db->get($sql1);
                if(!empty($res1[0][0])) {
                  $sql2 = "UPDATE dba.tickets SET stat_id = 2 WHERE id = ".(int)$_GET['tid'].";UPDATE dba.works SET stat_id = 1 where id = ".$res1[0][0].";";
                  if($db->process($sql2)) { 
                      $sql4 = 'INSERT INTO "DBA"."ticket_hist" ("ticket_id", "text", "created") values ('.(int)$_GET['tid'].', \''.iconv("UTF-8", "windows-1251", "Заявка и работа возобновлены у того же специалиста.<br>Назначил ".$spec_fio).'\', \''.date("Y-m-d H:i:s").'\');';
                      if(!$db->process($sql4)) { 
                        $arr['msg'] = "Неизвестная ошибка при добавлении истории заявки об возобновлении!";
                        $arr['response'] = 2022;
                        break;
                      }  
                      $arr['msg'] = "Заявка возобновлена!";
                      $arr['response'] = 100;
                      send(2, (int)$_GET['tid']); //2 means mail sending to spec
                      break;                       
                  }
                } else { 
                  @$sql_ckeck = 'SELECT id FROM "DBA"."tickets" WHERE id = '.(int)$_GET['tid'].' and spec_id != '.(int)$_GET['new_s_id'].';';
                  @$res_ckeck = $db->get( $sql_ckeck );
                  if(isset($res_ckeck[0][0]) and $res_ckeck[0][0]>0) {
                    //$arr['msg'] = "Произошла ошибка!<br>Возможно, заявка уже была распределена!";
                    //$arr['response'] = 2025;
                    //break; 
                  }
                
                  @$sql1 = 'SELECT text FROM "DBA"."tickets" WHERE id = '.(int)$_GET['tid'].';';
                  @$res1 = $db->get( $sql1 );
            
                  @$sql_a = "call dba.add_new_work(".(int)$_GET['new_s_id'].",'".$res1[0][0]."');"; 
                  @$res_work_add = $db->get( $sql_a );
          
                  $sql3 = 'INSERT INTO "DBA"."ticket_hist" ("ticket_id", "work_id", "text", "created") VALUES ('.(int)$_GET['tid'].', '.$res_work_add[0][0].', \''.iconv("UTF-8", "windows-1251", "Назначил ".$spec_fio).'\', GETDATE());';
                  $db->process( $sql3 );
          
                  @$sql_i = 'SELECT @@IDENTITY;';
                  if(!$db->get( $sql_i )) {
                    $arr['msg'] = "Неизвестная ошибка при добавлении истории заявки!";
                    $arr['response'] = 2024;
                    break; 
                  }
                  @$sql4 ='UPDATE "DBA"."tickets" SET stat_id = 2, spec_selected = GETDATE(), spec_id = '.(int)$_GET['new_s_id'].' WHERE id = '.(int)$_GET['tid'].';';
                  if(!$db->process( $sql4 )) {   
                    $arr['msg'] = "Неизвестная ошибка при работе с базой!";
                    $arr['response'] = 2023;
                    break;
                  }                                  
                  $arr['msg'] = "Специалист назначен!";
                  $arr['response'] = 100;
                  send(2, (int)$_GET['tid']); //2 means mail sending to spec
                  break;                                                   
                }
              }
            } else {
              $arr['msg'] = "Указан неправильный ID заявки";
              $arr['response'] = 2021;
              break; 
            }  
            break;
            /*
              $sql1 = "SELECT top 1 work_id from dba.ticket_hist WHERE work_id is not NULL and ticket_id in (SELECT id from dba.tickets WHERE id = ".(int)$_GET['tid']." and spec_id = ".(int)$_GET['new_s_id'].") order by work_id DESC;";
              $res1 = $db->get($sql1);
              if(!empty($res1[0][0])) {
                $sql2 = "UPDATE dba.tickets SET stat_id = 2 WHERE id = ".(int)$_GET['tid'].";";
                if($db->process($sql2)) {
                  $sql3 = "UPDATE dba.works SET stat_id = 1 where id = ".$res1[0][0].";";
                  if($db->process($sql3)) {
                    $sql4 = 'INSERT INTO "DBA"."ticket_hist" ("ticket_id", "text", "created") values ('.(int)$_GET['tid'].', \''.iconv("UTF-8", "windows-1251", "Заявка и работа возобновлены у того же специалиста").'\', \''.date("Y-m-d H:i:s").'\');';
                    if($db->process($sql4)) {
                      $arr['msg'] = "Заявка возобновлена!";
                      $arr['response'] = 100;
                    } else {
                      $arr['msg'] = "Неизвестная ошибка при добавлении истории заявки об возобновлении!";
                      $arr['response'] = 202;
                    }
                  } else {
                    $arr['msg'] = "Неизвестная ошибка при возобновлении старого задания!";
                    $arr['response'] = 202;
                  }       
                } else {
                  $arr['msg'] = "Неизвестная ошибка при возобновлении старой заявки!";
                  $arr['response'] = 202; 
                }
              } else {
                @$sql = "call dba.set_spec_to_ticket(".(int)$_GET['tid'].", ".(int)$_GET['new_s_id'].", '".iconv("UTF-8", "windows-1251", $spec_fio)."');";       //get all ticket history where ticket id = TID   
                @$res_work_add = $db->get( $sql );
                @$db->commit();      
                //print_r($res_work_add);
                if(empty($res_work_add) or $res_work_add[0][0] <=0 ) {
                  $arr['msg'] = "Неизвестная ошибка при работе с базой!";
                  $arr['response'] = 202;  
                } else {
                  send(2, (int)$_GET['tid']); //2 means mail sending to spec
                  $arr['msg'] = "Специалист назначен!";
                  $arr['response'] = 100;
                } 
              } */        
        case "add_work_timeline": //add a new work's timeline  
            @$sql_check = "call dba.check_spec_employment(".$spec_id.", 1, '".$_GET['start']."', '".$_GET['stop']."');";      
            @$res_actual_works_check = $db->get( $sql_check );
            if(!empty($res_actual_works_check[0])) {  
              $arr['msg'] = "Ошибка! На данный период уже существует информация!<br>Отложите активные задания на данный период!";
              $arr['response'] = 201; 
              break;          
            }          
            //in ls_spec_id int, in ls_place_id int, in ls_work_type_id int, in ls_comments long varchar
            $sql = "call dba.add_work_timeline(".(int)$_GET['wid'].",'".$_GET['comm']."','".$_GET['start']."','".$_GET['stop']."');";       //get all work history where work id = WID
            $res_work_add = $db->get( $sql );
            $db->commit();     
            if(empty($res_work_add)) {   
              $arr['msg'] = "Неизвестная ошибка при добавлении в базу!";
              $arr['response'] = 202; 
              break; 
            }                
            $arr['msg'] = "Добавлено!";
            $arr['response'] = 100;
            break;
        case "add_work_timeline_auto":  //AUTO add a new work's timeline (without stop)  
            //AUTO STOP FOR ALL
            $sql = "UPDATE dba.work_hist SET \"to_dt\" = '".date("Y-m-d H:i:s")."' WHERE \"to_dt\" is NULL and \"work_id\" in (SELECT id from dba.works where spec_id = ".$spec_id.");";  
            $db->process( $sql );      
            $db->commit();            
            $sql = "call dba.add_work_timeline_auto(".(int)$_GET['wid'].",'".iconv("UTF-8", "windows-1251", "Добавлено автоматически")."','".date("Y-m-d H:i:s")."');";       
            $res_work_add = $db->get( $sql );
            $db->commit();     
            if(empty($res_work_add)) {   
              $arr['msg'] = "Неизвестная ошибка при добавлении в базу!";
              $arr['response'] = 202;  
            } else {               
              $arr['msg'] = "Работа запущена!";
              $arr['response'] = 100;
            }
            break;
        case "freeze_work_timeline_auto": //AUTO add timeline's stop datetime
            @$sql = "UPDATE dba.work_hist SET \"to_dt\" = '".date("Y-m-d H:i:s")."' WHERE \"to_dt\" is NULL and \"work_id\" = ".(int)$_GET['wid'].";";      
            @$res_work_freeze = $db->process( $sql );
            $db->commit();     
            if(!$res_work_freeze) {   
              $arr['msg'] = "Неизвестная ошибка при завершении таймлайна!";
              $arr['response'] = 202;  
            } else {               
              $arr['msg'] = "Работа приостановлена!";
              $arr['response'] = 100;
            }
            break;
        case "update_work_stat":  //edit work stat
            @$sql_check = "select id from dba.works where id = ".(int)$_GET['wid'];
            @$res_check = $db->get( $sql_check );
            if(empty($res_check)) {
              $arr['msg'] = "Ошибка! Запись о данной работе не найдена!";
              $arr['response'] = 2031;
            } else {
              @$sql_upd_stat = "call dba.update_work_stat('".(int)$_GET['wid']."','".(int)$_GET['ws']."', '');";
              @$res_work_upd_stat = $db->get( $sql_upd_stat );
              if($res_work_upd_stat[0][0]==-1) {
                  $arr['msg'] = "Нельзя закрыть роаботу с отметками в будущем!";
                  $arr['response'] = 203;           
              } elseif($res_work_upd_stat[0][0]>0) {
                  $arr['msg'] = "Статус обновлен!";
                  $arr['response'] = 100;
                  if((int)$_GET['ws']==3) {   //if it is closing - let's loock for a append ticket; if it is - let's send message for creator 'close this ticket'
                    $sql_get_tid = 'SELECT top 1 ticket_id as tid FROM "DBA"."ticket_hist" WHERE work_id = '.(int)$_GET['wid'].' ORDER BY ticket_id DESC;';
                    $res_get_tid = $db->get( $sql_get_tid );
                    if(isset($res_get_tid[0][0]) and !empty($res_get_tid[0][0])) {  // if we have a appendet ticket                 
                      $sql_get_mail = 'select value from dba.contacts where contact_type_id = 1 and spec_id = (select creator_id from dba.tickets where id = (SELECT top 1 ticket_id as tid FROM "DBA"."ticket_hist" WHERE work_id = '.(int)$_GET['wid'].' ORDER BY ticket_id DESC));';
                      $res_get_mail = $db->get( $sql_get_mail );
                      if(isset($res_get_mail[0][0]) and !empty($res_get_mail[0][0])) {
                        /***********sending email***********/
                        $mail_to_send = $res_get_mail[0][0]; //for creator       
                        $message = Array(
                          'subject'=>'Подтвердите выполнение заявки #'.$res_get_tid[0][0], 
                          'message'=>"Ваша заявка выполнена и передана Вам на утверждение.<br>Отказать или подтвердить закрытие заявки можно в системе Распределения Рабочих Ресурсов по адресу<br> <a href='http://".SITEADDRESS."/tickets.php?show=".$res_get_tid[0][0]."'>http://".SITEADDRESS."/tickets.php?show=".$res_get_tid[0][0]."</a>",
                          'mail'=>$mail_to_send
                        );
                        send(3,$res_get_tid[0][0],$message); //3 - means that same message body  
                        /***********sending email***********/ 
                      }
                    }
                  }
              } elseif(empty($res_work_upd_stat[0][0])) {
                  $arr['msg'] = "Неизвестная ошибка обновления статуса!";
                  $arr['response'] = 2032;      
              }    
            }                               
            break;
        case "cancel_work": // chancel work with ticket (when spec says "it is not my work!")
            @$sql_check = "select id from dba.works where id = ".(int)$_GET['wid'];
            @$res_check = $db->get( $sql_check );
            if(empty($res_check)) {
              $arr['msg'] = "Ошибка! Запись о данной работе не найдена!";
              $arr['response'] = 203;
            } else {
              @$sql_upd_stat = "call dba.update_work_stat('".(int)$_GET['wid']."',4, '".$_GET['comment']."');";     // 4 = if spec chancel work (close work + set 'not selected spec' for ticket)  
              @$res_work_upd_stat = $db->get( $sql_upd_stat );
              if(empty($res_work_upd_stat[0][0])) {
                  $arr['msg'] = "Произошла ошибка";
                  $arr['response'] = 203;           
              } else {
                  $arr['msg'] = "Статус обновлен!";
                  $arr['response'] = 100;
              }     
            }
            break;
        case "update_ticket_stat":  //edit ticket stat
            @$sql_check = "select id from dba.tickets where id = ".(int)$_GET['tid'];
            @$res_check = $db->get( $sql_check ); 
            if(empty($res_check)) {
              $arr['msg'] = "Ошибка! Данная заявка не найдена!";
              $arr['response'] = 404;
              break;
            } 
            @$sql_upd_stat = "call dba.update_ticket_stat('".(int)$_GET['tid']."','".(int)$_GET['ts']."');";
            @$res_work_upd_stat = $db->get( $sql_upd_stat );                  
            if($res_work_upd_stat[0][0]!=0) {
                $arr['msg'] = "Ошибка обновления статуса: ".$res_work_upd_stat[0][0];
                $arr['response'] = 209;
                break;           
            } elseif($res_work_upd_stat[0][0]==0) { 
              $sql1 = "SELECT top 1 work_id from dba.ticket_hist where ticket_id = ".(int)$_GET['tid']." order by work_id DESC";
              $res1 = $db->get($sql1);      
              if(isset($_GET['is_cancel'])) {    //if user says 'NO!!!DO IT AGAIN!'
                if(isset($res1[0][0]) and !empty($res1[0][0])) {
                  $sql2 = "update dba.works set stat_id = 1 where id = ".$res1[0][0];
                  if($db->process($sql2)) {
                    $sql3 = 'insert into dba.ticket_hist ("ticket_id", "work_id", "text", "created") values ('.(int)$_GET['tid'].', '.$res1[0][0].', \''.iconv("UTF-8", "windows-1251", "Снова активна, т.к. создатель отказал в подтверждении").'\', \''.date("Y-m-d H:i:s").'\');';
                    $res3 = $db->process($sql3);
                    if($res3) {
                      $arr['msg'] = "Статус обновлен!";
                      $arr['response'] = 100;
                      /***********sending email***********/
                      $res = getRes((int)$_GET['tid']);
                      $mail_to_send = $res[0]['spec_mail']; //for spec       
                      $message = Array(
                        'subject'=>'Ваша работа возобнавлена в заявке #'.(int)$_GET['tid'], 
                        'message'=>"Т.к. создатель заявки отказал в закрытии заявки # ".(int)$_GET['tid'].", она снова активна и работа по ней возобновлена. <br>Посмотреть информаци о ней можно по адресу <br> <a href='http://".SITEADDRESS."/tickets.php?show=".(int)$_GET['tid']."'>http://".SITEADDRESS."/tickets.php?show=".(int)$_GET['tid']."</a>",
                        'mail'=>$mail_to_send
                      );
                      send(3,(int)$_GET['tid'],$message); //3 - means that in 'MSSAGING' is new text. and send message for ticket creator
                      /***********sending email***********/                       
                      break;                  
                    }
                  }
                }
              } else {     //if user says 'OK!TICKET IS CLOSED!'
                if((int)$_GET['ts']==4) {
                  $sql4 = 'insert into dba.ticket_hist ("ticket_id", "work_id", "text", "created") values ('.(int)$_GET['tid'].', '.$res1[0][0].', \''.iconv("UTF-8", "windows-1251", "Закрытие подтверждено").'\', \''.date("Y-m-d H:i:s").'\');';
                }
                $arr['msg'] = "Статус обновлен!";
                $arr['response'] = 100;
                break;
              }
            } elseif(empty($res_work_upd_stat[0][0])) {
              $arr['msg'] = "Неизвестная ошибка обновления статуса!<br>БД не ответила!";
              $arr['response'] = 210; 
              break;     
            }    
            break;
        case "update_timelime": //edit timeline date and time      
            @$sql_check = "call dba.check_spec_timeline(".$spec_id.", '".(int)$_GET['tid']."', '".$_GET['start']."', '".$_GET['stop']."');";      
            @$res_actual_timeline_check = $db->get( $sql_check );
            if(!empty($res_actual_timeline_check[0])) {  
              $arr['msg'] = "Ошибка! На данный период уже существует информация!<br>Отложите активные задания на данный период!";
              $arr['response'] = 200; 
            } else {                     
              @$sql_upd_tl = "call dba.update_timelime('".(int)$_GET['tid']."','".$_GET['start']."','".$_GET['stop']."','".$_GET['comm']."');"; 
              $res_work_upd_tl = $db->process( $sql_upd_tl );
              if($res_work_upd_tl>0) {
                $arr['msg'] = "Данные обновлены!";
                $arr['response'] = 100;    
              } else {
                $arr['msg'] = "Ошибка обновления данных!";
                $arr['response'] = 204;
              }  
            } 
            break;
        case "close_now_timeline":
            @$sql_to_check="SELECT from_dt from dba.work_hist WHERE id = '".(int)$_GET['tid']."'";  
            @$res_to_check = $db->get( $sql_to_check );
            $now_dt = date("Y-m-d H:i");
            $from_datetime_ms = strtotime($res_to_check[0][0]) * 1000;
            $new_to_datetime_ms = strtotime($now_dt) * 1000;
            if($from_datetime_ms>=$new_to_datetime_ms) {
              $arr['msg'] = "Проверьте указанные сроки!";
              $arr['response'] = 204;  
            } else {  
              @$sql_check = "call dba.check_spec_timeline(".$spec_id.", '".(int)$_GET['tid']."', '".$res_to_check[0][0]."', '".$now_dt."');";      
              @$res_actual_timeline_check = $db->get( $sql_check );
              if(!empty($res_actual_timeline_check[0])) {  
                $arr['msg'] = "Ошибка! С момента начала по данный момент есть активные задания.<br>Отложите их!";
                $arr['response'] = 200;
              } else {      
                $sql_upd_tl = "UPDATE dba.work_hist SET \"to_dt\" = '".date("Y-m-d H:i")."' WHERE id = '".(int)$_GET['tid']."';"; 
                $res_work_upd_tl = $db->process( $sql_upd_tl );
                if($res_work_upd_tl>0) {
                  $arr['msg'] = "Окончание обновлено!";
                  $arr['response'] = 100;    
                } else {
                  $arr['msg'] = "Ошибка обновления окончания!";
                  $arr['response'] = 204;
                }  
              }    
            }
            break;
        case "delete_timeline": //delete timeline   
            @$sql_upd_stat = "delete from dba.work_hist where id = '".(int)$_GET['tid']."';";
            if($db->process( $sql_upd_stat )) {  
              $arr['msg'] = "Данные удалены!";
              $arr['response'] = 100; 
            } else {
              $arr['msg'] = "Ошибка удаления данных!";
              $arr['response'] = 204;    
            }
            break;
        case "add_work_comment":
            @$sql_add_comm = "INSERT INTO dba.work_msg (\"text\", \"created\", \"creator_id\", \"work_id\") values ('".$_GET['comm']."', '".date("Y-m-d H:i")."', ".$spec_id.", ".(int)$_GET['wid'].");";
            if($db->process( $sql_add_comm )) {  
              $arr['msg'] = "Комментарий добавлен!";
              $arr['response'] = 100;
            } else {
              $arr['msg'] = "Ошибка добавления комментария!";
              $arr['response'] = 212;    
            } 
            break;
        case "get_first_places":
            $sql = 'SELECT id, value, selectable FROM dba.places_all WHERE parent IS NULL and exist = 1';
            $childs = $db->get( $sql );   
            if(isset($childs) && is_array($childs)) { 
              foreach( $childs as &$child ) {  
                $child['childs'] = array();
                get_childs( 'dba.places_all', $child['id'], $db, $child['childs'] );
              }                          
              foreach($childs as $k=>$v) { 
                if(isset($v) && is_array($v)) {    
                  foreach($v as $k2=>$v2) {     // for each row parameter
                    //if(isset($v2) && is_array($v2)) {        
                      $arr['places'][$k][$k2] = strToRu($v2);                          
                      if(is_array($v2)) foreach($v2 as $k3=>$v3) {
                        if(isset($v3) && is_array($v3)) { 
                          $arr['places'][$k][$k2][$k3] = strToRu($v3);      
                          foreach($v3 as $k4=>$v4) {
                            $arr['places'][$k][$k2][$k3][$k4] = strToRu($v4);
                          }   
                        }      
                      }
                    //} 
                  }        
                }      
              }       
              $arr['response'] = 100;
            } else {
              $arr['msg'] = "Ошибка получения списка мест";
              $arr['response'] = 406;      
            } 
            break;
        case "get_first_errors":
            $sql = 'SELECT * FROM dba.errors WHERE parent IS NULL';
            $childs = $db->get( $sql );
            if(isset($childs) && is_array($childs)) {     
              foreach( $childs as &$child ) {
                $child['childs'] = array();
                get_childs( 'dba.errors', $child['id'], $db, $child['childs'] );
              }
              foreach($childs as $k=>$v) {
                if(isset($v) && is_array($v)) {
                  foreach($v as $k2=>$v2) {     // for each row parameter
                    //if(isset($v2) && is_array($v2)) {
                      $arr['errors'][$k][$k2] = strToRu($v2);                   
                      if(is_array($v2)) foreach($v2 as $k3=>$v3) {
                        if(isset($v3) && is_array($v3)) {            
                          $arr['errors'][$k][$k2][$k3] = strToRu($v3);        
                          foreach($v3 as $k4=>$v4) {
                            $arr['errors'][$k][$k2][$k3][$k4] = strToRu($v4);
                          } 
                        }         
                      }
                    //}          
                  }
                }      
              } 
              $arr['response'] = 100;      
            } else {
              $arr['msg'] = "Ошибка получения списка проблем";
              $arr['response'] = 405;      
            } 
            break;
        case "show_profile_stat":
            @$sql_places = "SELECT t0.error_id as id, count(t1.error_id) as count, t2.value as value FROM dba.tickets t0 left join dba.tickets t1 on t1.id = t0.id left join dba.errors t2 on t2.id = t0.error_id where t0.creator_id = ".(int)$_GET['p_id']." group by t0.error_id, t2.value";        
            @$res_places = $db->get( $sql_places );
            foreach($res_places as $k=>$v) {
                foreach($v as $k2=>$v2) {     // for each row parameter
                  $arr['errors'][$k][$k2] = strToRu($v2);
                }      
            }     
            $arr['response'] = 100;
            break;
        case "set_new_pass":
            $password = $_GET['password'] ;                       
            $salt = create_salt();
            $hash = md5( $password.$salt ); 
            @$sql_tmp = 'UPDATE "DBA"."specialists" SET "spec_pass" = \''.$hash.'\',  "salt" = \''.$salt.'\' WHERE id = '.$spec_id.';';   
            if($db->process($sql_tmp)) { 
              $arr['msg'] = "Пароль обновлен!";  
              $arr['response'] = 100;  
            } else {
              $arr['msg'] = "Ошибка обновления пароля!"; 
              $arr['response'] = 4005;  
            }  
            break;
        case "get_places_manager":
            $id = isset( $_REQUEST['id'] ) ? (int)$_REQUEST['id'] : 0;
            $parent_id = isset( $_REQUEST['parent'] ) ? (int)$_REQUEST['parent'] : 0;
            $type = isset( $_POST['type'] ) ? $_POST['type'] : '0,1';
            if( !$parent_id ) {
              $sql = "SELECT id, value, Description, \"outer\", outer_accept FROM dba.places_all WHERE id={$id} AND \"outer\" IN ({$type})";
              $res = $db->get( $sql );
              $db->commit( );
            } else {
              $sql = "SELECT places_all.id, places_all.value, places_all.\"outer\", places_all.outer_accept, count(pl.id) AS childs FROM dba.places_all
                      LEFT JOIN dba.places_all AS pl ON pl.parent=places_all.id
                      WHERE places_all.parent={$parent_id} AND places_all.\"outer\" IN ({$type})
                      GROUP BY places_all.id, places_all.value, places_all.\"outer\", places_all.outer_accept ORDER BY places_all.value asc";
              $res = $db->get( $sql );
              $db->commit( );
            }
            foreach($res as $k=>$v) {
              foreach($v as $k2=>$v2) {  
                $arr[$k][$k2] = strToRu($v2);
              }      
            }   
            $arr['response'] = 100;  
            break;
        case "get_errors":
            @$sql_work_types = "SELECT id, value FROM dba.errors"; 
            @$res_work_types = $db->get( $sql_work_types );
            foreach($res_work_types as $k=>$v) {
                foreach($v as $k2=>$v2) {     // for each row parameter
                  $arr[$k][$k2] = strToRu($v2);
                }      
            }    
            break;
        case "adding_ticket_to_group":
            $sql1 = 'SELECT (IF max(group_id) is NULL THEN 0 ELSE max(group_id) ENDIF) FROM dba.ticket_groups;';
            $res_gr_id = $db->get( $sql1 ); 
            if(isset($_REQUEST['tid'])) { 
              foreach($_REQUEST['tid'] as $k=>$v) {
                  if($v=="add") {
                    $sql_adding_tickets_to_group = "INSERT INTO dba.ticket_groups (\"group_id\", \"ticket_id\", \"exist\") VALUES ('".($res_gr_id[0][0]+1)."', '".$k."', 1)";
                    $db->process( $sql_adding_tickets_to_group );   
                  }   
              }   
              $arr['msg'] = "Группы заявок обновлены";
              $arr['response'] = 100;      
            } else {
              $arr['msg'] = "Ошибка группировки заявок";
              $arr['response'] = 213; 
            }  
            break;
        case "deleting_ticket_from_group":         
            if(isset($_REQUEST['tid'])) { 
              foreach($_REQUEST['tid'] as $k=>$v) {
                  if($v=="del") {        
                    $sql1 = 'SELECT group_id FROM dba.ticket_groups WHERE ticket_id = '.$k.';';                   //get group_id of this ticket
                    $res_gr_id = $db->get( $sql1 );
                    
                    $sql_adding_tickets_to_group = "DELETE FROM dba.ticket_groups WHERE ticket_id = '".$k."';";     //del ticket from group
                    $db->process( $sql_adding_tickets_to_group ); 
                    
                    if(isset($res_gr_id[0][0])) {
                      $sql2 = 'SELECT count(ticket_id) FROM dba.ticket_groups WHERE group_id = '.$res_gr_id[0][0].';';                   //get group_id of this ticket
                      $res_count = $db->get( $sql2 );
                                          
                      if(isset($res_count[0][0])) {
                        if($res_count[0][0]==1) { 
                          $sql_del_tickets_from_group = "DELETE FROM dba.ticket_groups WHERE group_id = '".$res_gr_id[0][0]."';";     //del ticket from group if this ticket is alone
                          $db->process( $sql_del_tickets_from_group ); 
                        }
                      } 
                    }
                                         
                  }   
              }   
              $arr['msg'] = "Заявки разгруппированы!";
              $arr['response'] = 100;      
            } else {
              $arr['msg'] = "Ошибка разгруппировки заявок";
              $arr['response'] = 214; 
            }  
            break;            
        case "toggle_block_ticket":
            @$sql_block = "UPDATE dba.tickets SET blocked=(IF blocked=-1 THEN 0 ELSE -1 ENDIF) WHERE id = ".(int)$_GET['tid']; 
            @$db->process( $sql_block );
            $arr['msg'] = 'Заявка изменена!';
            $arr['response'] = 100;   
            break;
        case "get_ticket_msgs":
            if(!isset($_GET['iss'])) {
              $_GET['iss'] = 0;
            }
            if((int)$_GET['iss']==0) {
              @$sql_msg_upd = "UPDATE dba.ticket_msg SET readed = 1 WHERE ticket_id = ".(int)$_GET['tid']." and creator_id != '".$spec_id."' and is_system = ".(int)$_GET['iss'].";";
              $db->process( $sql_msg_upd );   
              @$sql_msg = "SELECT t1.text, t1.created, t1.readed, t2.surname+' '+t2.name+' '+t2.patronymic as fio FROM dba.ticket_msg t1 LEFT OUTER JOIN dba.specialists t2 ON t1.creator_id = t2.id where t1.ticket_id = ".(int)$_GET['tid']." and is_system = ".(int)$_GET['iss']." order by t1.created;";
              @$res_msg = $db->get( $sql_msg );
              if(!empty($res_msg)) {
                foreach($res_msg as $k3=>$v3) {
                    foreach($v3 as $k4=>$v4) {     // for each row parameter
                      $arr['ticket_msg'][$k3][$k4] = strToRu($v4);
                    }      
                }
              }     
              if(isset($arr['ticket_msg']) and !empty($arr['ticket_msg'])) {
                $arr['msg_counter'] = sizeof($arr['ticket_msg']);
              } else {    
                $arr['msg_counter'] = 0;
              }
              $arr['response'] = 100; 
              break;
            } else if((int)$_GET['iss']==1) { 
              @$sql_msg_upd = "UPDATE dba.ticket_msg SET readed = 1 WHERE ticket_id = ".(int)$_GET['tid']." and creator_id != '".$spec_id."' and is_system=1;";
              $db->process( $sql_msg_upd );   
              @$sql_msg = "SELECT t1.text, t1.created, t1.readed, t2.surname+' '+t2.name+' '+t2.patronymic as fio FROM dba.ticket_msg t1 LEFT OUTER JOIN dba.specialists t2 ON t1.creator_id = t2.id where t1.ticket_id = ".(int)$_GET['tid']." and is_system=1;";
              @$res_msg = $db->get( $sql_msg );
              if(!empty($res_msg)) {
                foreach($res_msg as $k3=>$v3) {
                    foreach($v3 as $k4=>$v4) {     // for each row parameter
                      $arr['ticket_msg'][$k3][$k4] = strToRu($v4);
                    }      
                }
              }     
              if(isset($arr['ticket_msg']) and !empty($arr['ticket_msg'])) {
                $arr['msg_counter'] = sizeof($arr['ticket_msg']);
              } else {    
                $arr['msg_counter'] = 0;
              }
              $arr['response'] = 100; 
              break;
            }
            break;
        case "add_ticket_msgs":
            if(!isset($_GET['iss'])) {
              $_GET['iss'] = 0;
            }          
            @$sql_msg = "INSERT INTO dba.ticket_msg (\"text\", \"creator_id\", \"created\", \"ticket_id\", \"is_system\") VALUES ('".$_GET['msg']."', '".$spec_id."', '".date("Y-m-d H:i:s")."', '".(int)$_GET['tid']."','".(int)$_GET['iss']."');";
            @$res_msg = $db->get( $sql_msg );
            $arr = Array();
            if(!empty($res_msg)) {
              foreach($res_msg as $k3=>$v3) {
                  foreach($v3 as $k4=>$v4) {     // for each row parameter
                    $arr['ticket_msg'][$k3][$k4] = strToRu($v4);
                  }      
              }
            }       
            if(isset($arr['ticket_msg'])) {
              $arr['msg_counter'] = sizeof($arr['ticket_msg']);
            } else {
              $arr['msg_counter'] = 0;    
            }
            $arr['msg'] = "Сообщение добавлено!"; 
            $arr['response'] = 100;
            /***********sending email***********/
            $res = getRes((int)$_GET['tid']);
            if($_SESSION['User']['id']!=$res[0]['creator_id']) {
              $mail_to_send = $res[0]['creator_mail']; //for creator
            } else {
              $mail_to_send = $res[0]['spec_mail']; //for spec
            }          
            $message = Array(
              'subject'=>'Новое сообщение в вашей заявке #'.(int)$_GET['tid'], 
              'message'=>"Просмотреть новое сообщение можно в Системе Распределения Рабочих ресурсов по адресу <br> <a href='http://".SITEADDRESS."/tickets.php?show=".(int)$_GET['tid']."'>http://".SITEADDRESS."/tickets.php?show=".(int)$_GET['tid']."</a>",
              'mail'=>$mail_to_send
            );
            send(3,(int)$_GET['tid'],$message); //3 - means that same message body 
            /***********sending email***********/ 
            break;
        case "add_postopened_work":
            @$sql_add_work = "CALL dba.add_postopened_work('".$spec_id."','".(int)$_GET['w_id']."');"; 
            @$res_add_work = $db->get( $sql_add_work );
            if( $res_add_work[0][0] == "-1" ){
              print_r($res_add_work);
              $arr['msg'] = "Ошибка добавления задания!";
              $arr['response'] = 205;  
            } else {
              $arr['msg'] = "Задание прикреплено!";
              $arr['response'] = 100;  
            }  
            break;
        case "spec_profil_edit":
            if(!empty($_REQUEST['prof'])) {    
              foreach($_REQUEST['prof'] as $k=>$v) {      
                $sql_update = 'UPDATE dba.contacts SET "value" = \''.@iconv("UTF-8", "cp1251", $v).'\' WHERE id='.(int)$k.';';
                $db->process( $sql_update ); 
              }
            }       
            if(!empty($_REQUEST['prof_n'])) {
              foreach($_REQUEST['prof_n'] as $k2=>$v2) {
                $sql_insert = 'INSERT INTO dba.contacts ("spec_id", "contact_type_id", "value") values ('.$spec_id.', '.(int)$k2.', \''.@iconv("UTF-8", "cp1251", $v2).'\');';
                $db->process( $sql_insert ); 
              }
            }
            if(!empty($_REQUEST['prof_del'])) {
              $where_cond = Array();
              foreach($_REQUEST['prof_del'] as $v3) {
                $where_cond[]=$v3;
              }
              $sql_del = 'DELETE FROM dba.contacts WHERE id in ('.implode(",", $where_cond).');';
              $db->process( $sql_del ); 
            }
            $arr['response'] = 100;     
            $arr['msg'] = "Данные сохранены!"; 
            break;
        case "get_all_contact_types":
            @$sql_contact_types = "SELECT * FROM dba.contact_types";         
            @$res_contact_types = $db->get( $sql_contact_types );
            if(!empty($res_contact_types)) {
              foreach($res_contact_types as $k=>$v) {
                  foreach($v as $k2=>$v2) {     // for each row parameter
                    $arr['contact_types'][$k][$k2] = strToRu($v2);
                  }      
              }
            } 
            $arr['response'] = 100; 
            break;
        case "get_spec_info":
            @$sql = "call dba.get_spec_contacts(".$spec_id.");";
            @$res_contacts = $db->get( $sql );
            if(!empty($res_contacts)) {
              foreach($res_contacts as $k=>$v) {
                foreach($v as $k2=>$v2) {     // for each row
                  $arr['cont'][$k][$k2] = strToRu($v2);
                } 
              }   
              $arr['response'] = 100;
            } else {
              $arr['cont'] = "";
              $arr['response'] = 401;
              $arr['msg'] = "Записей не найдено!";  
            }                   
            @$sql_name = 'SELECT name, surname, patronymic, spec_login FROM dba.specialists WHERE id = '.$spec_id.';';
            @$res_name = $db->get( $sql_name );
            if(!empty($res_name)) {
              foreach($res_name[0] as $kN=>$vN) {     // for each row
                $arr['name'][$kN] = strToRu($vN);
              } 
            }      
            $arr['title']=strToRu($res_name[0]['surname']." ".$res_name[0]['name']." ".$res_name[0]['patronymic']); 
            break;
        case "get_spec_works":
            if(isset($_GET['w_s'])) {
              $ws = (int)$_GET['w_s'];  
            } else {
              $ws = 1;
            }     
            if(isset($_GET['first'])) {
              if(!isset($_GET['daily'])) {
                @$sql = "call dba.get_spec_employment(".$spec_id.", ".$ws.", '".date("Y-m-1")."', '".date("Y-m-".date("t", strtotime(date("Y-m-d"))))."', ".(int)$_GET['ord'].");";
              } else {
                @$sql = "call dba.get_spec_employment(".$spec_id.", ".$ws.", '".date("Y-m-d")." 00:00:00', '".date("Y-m-d")." 23:59:59.999', ".(int)$_GET['ord'].");";
              }
            } else {                      
              @$sql = "call dba.get_spec_employment(".$spec_id.", ".$ws.", '".$_GET['from']."', '".$_GET['to']."', ".(int)$_GET['ord'].");";
            }       
            if(isset($_GET['debug'])) {
              echo $sql."<br><br>";
            }
            @$res_actual_works = $db->get( $sql );
            $count=0;
            if(!empty($res_actual_works)) {   
              $arr=parse_work_json($res_actual_works);
              if(empty($arr)) {
                $arr['msg'] = "Записей не найдено!";   
                $arr['response'] = 401;                
              }  else {   
                $arr['response'] = 100;
              }            
            } else {
              $arr['msg'] = "Записей не найдено!";
              $arr['response'] = 401;  
            }
            break; 
        case "show_one_work":
            @$sql = "call dba.get_one_work_on_timeline('".(int)$_GET['work_id']."');";
            @$res_actual_works = $db->get( $sql );
            if(!empty($res_actual_works)) { 
              $arr=parse_work_json($res_actual_works);
              if(empty($arr)) {
                $arr['msg'] = "Записей не найдено!";   
                $arr['response'] = 401;                
              }  else {
                $arr['response'] = 100;
              }            
            } else {
              $arr['msg'] = "Записей не найдено!";
              $arr['response'] = 401;  
            }
            break;
        case "search_ticket":
            $where = Array(); 
            $where_cond = "";
            if($_GET['num']>0) {
              $where[] = "tickets.id = ".(int)$_GET['num'];    
            } else {    
              if($_GET['st']>0) {
                $where[] = "stat_id = ".(int)$_GET['st'];    
              }  
              if($_GET['er']>0) {
                $where[] = "error_id = ".(int)$_GET['er'];
              }  
              if($_GET['pl']>0) {
                $where[] = "place_id = ".(int)$_GET['pl'];
              }  
              if($_GET['sp']>0) {
                $where[] = "spec_id = ".(int)$_GET['sp'];
              } 
              if(!empty($_GET['of'])) {
                $where[] = "created >= '".$_GET['of']."'";
              } 
              if(!empty($_GET['ot'])) {
                $where[] = "created <= '".$_GET['ot']."'";
              }                
            }     
            if(!empty($where)) {
              $where_cond = " where ".implode(" and ", $where);
            }        
            $sql_search_tickets = "SELECT 
                    tickets.id as t_id,
                    ticket_stats.value as stat_v,
                    tickets.stat_id as stat_i,
                    tickets.text as text,
                    tickets.creator_id as creator_i,
                    (t3.surname+' '+t3.name+' '+t3.patronymic) as creator_fio,
                    tickets.place_id as place_i,
                    places_all.value as place_v,
                    tickets.error_id as error_i,
                    errors.value as error_v,
                    tickets.created as created,
                    tickets.spec_selected as spec_selected,
                    tickets.approval as approval,
                    tickets.closed as closed,
                    tickets.spec_id as spec_i,
                    (t2.surname+' '+t2.name+' '+t2.patronymic) as spec_fio
                FROM dba.tickets
                    LEFT OUTER JOIN dba.ticket_stats
                        ON tickets.stat_id = ticket_stats.id
                    LEFT OUTER JOIN dba.places_all
                        ON tickets.place_id = places_all.id
                    LEFT OUTER JOIN dba.errors
                        ON tickets.error_id = errors.id
                    LEFT OUTER JOIN dba.specialists t2
                        ON tickets.spec_id = t2.id
                    LEFT OUTER JOIN dba.specialists t3
                        ON tickets.creator_id = t3.id
                ".$where_cond."
                ORDER BY tickets.id DESC;";  
            @$res_search_tickets = $db->get( $sql_search_tickets );
        
            if(!empty($res_search_tickets)) {  
              foreach($res_search_tickets as $k=>$v) {
                  foreach($v as $k2=>$v2) {     // for each row parameter
                    if($k2=="created") {
                      $v2 = substr($v2, 0, -4);
                    }          
                    $arr['tickets'][$k][$k2] = strToRu($v2);
                  }      
              }
              $arr['response'] = 100;
            } else {
              $arr['msg'] = "Записей не найдено!";
              $arr['response'] = 401;
            }  
            break;
        case "reg":
            if(strToRu($_GET['fc']) != "МДК") {
            //if(false) {
              $arr['msg'] = "Проверка на защиту не удалась!<br>Введите заглавными русскими буквами название огранизации";
              $arr['response'] = 2070; 
            } else { 
              $new_salt = create_salt();
              $hash = md5( $_GET['pss'].$new_salt ); 
        	    @$sql_r1 = 'INSERT INTO "DBA"."specialists" ("Name", "surname", "patronymic", "exist", "spec_login", "role_id", "spec_pass", "salt")	values (\''.$_GET['n'].'\', \''.$_GET['s'].'\', \''.$_GET['p'].'\', 1, \''.$_GET['l'].'\', 4, \''.$hash.'\', \''.$new_salt.'\');';    
              //@$sql_r = "SELECT dba.spec_register('".$_GET['n']."', '".$_GET['s']."', '".$_GET['p']."', '".$_GET['l']."', '".$_GET['pss']."');";
              if($db->process( $sql_r1 )) { 
                @$sql_r = 'SELECT @@IDENTITY;';
                @$res_reg = $db->get( $sql_r ); 
                if(!empty($res_reg[0]) and $res_reg[0]!=-1 and $res_reg[0]>0) {
                  @$sql1 = "INSERT INTO dba.contacts (\"spec_id\", \"contact_type_id\", \"value\") values (".$res_reg[0][0].", 1, '".$_GET['mail']."');";
                  if($db->process($sql1)) {
                    $arr['response'] = 100;
                    $arr['msg'] = "Регистрация прошла успешно!";  
                    $arr['user_id'] = $res_reg[0][0];
                  } else {
                    $arr['msg'] = "Ошибка регистрации!"; 
                    $arr['response'] = 2072;    
                  }         
                } else {
                  $arr['msg'] = "Пальзователь с таким логином уже существует!";  
                  $arr['response'] = 207;  
                  //$arr['user_id'] = -1; 
                }
              } else {
                $arr['msg'] = "Пальзователь с таким логином уже существует!";  
                $arr['response'] = 207;  
                //$arr['user_id'] = -1; 
              }
            } 
            break;
        case "auto_ckeck_contacts":
            $sql = "select value as city_phone from dba.contacts where spec_id = ".$spec_id." and contact_type_id = 2";   //city phone
            $sql1 = "select value as phone from dba.contacts where spec_id = ".$spec_id." and contact_type_id = 5";      //inner phone
            $sql2 = "select value as phone from dba.contacts where spec_id = ".$spec_id." and contact_type_id = 1";      //inner phone            
            @$res = $db->get($sql);
            @$res1 = $db->get($sql1); 
            @$res2 = $db->get($sql2);                
            if(isset($res[0][0]) and !empty($res[0][0])) {
              $arr['auto_ckeck_contacts'][0] = $res[0][0];
            } else {
              $arr['auto_ckeck_contacts'][0] = '';
            }
            if(isset($res1[0][0]) and !empty($res1[0][0])) {
              $arr['auto_ckeck_contacts'][1] = $res1[0][0];
            } else {
              $arr['auto_ckeck_contacts'][1] = '';
            } 
            if(isset($res2[0][0]) and !empty($res2[0][0])) {
              $arr['auto_ckeck_contacts'][2] = $res2[0][0];
            } else {
              $arr['auto_ckeck_contacts'][2] = '';
            }                
            $arr['response'] = 100;
            break;
        case "logout":
            unset($_SESSION['User']);
            header('Location: /index.php?logout=true');
            exit();  
            break; 
        case "login_log":
            $fp = fopen('logging.log','a');
            $now = date("Y-m-d H:i:s");
            fwrite( $fp, "==== LOGIN: ".$_GET['login']."====== LOGGING on {$now} ======="."\r\n" );
            fclose($fp);   
            $arr['response'] = 100;
            break;
        case "check_version":
            $arr['last_version'] = 7;
            $arr['response'] = 100;
            break;
        case "set_free_ticket":
            $sql_block = "UPDATE dba.tickets set blocked = 0 WHERE id = ".(int)$_GET['tid'];
            $res_block = $db->process( $sql_block );
            $arr['msg'] = 'Заявка освобождена от редактирования';    
            $arr['response'] = 100;   
            break;
        case "get_report":
            $arrtemp=Array();
            @$sql_r = "CALL dba.get_spec_employment(-1, 3, '2014-10-01', '2014-11-30')";
            @$res_rep = $db->get( $sql_r );
            if(!empty($res_rep)) {
              //print_r($res_rep);   
              $megaArr = Array();
              foreach($res_rep as $key => $value) {
                $megaArr[$key]['w_id'] = $value['w_id'];
                $tsS = explode("],[", substr(strToRu($value['timestamps']), 1, -1));
                foreach($tsS as $kts => $vts) {
                  $megaArr[$key]['ts'][$kts]=explode(",", $vts);  
                }
              }
                $megaArr2 = Array();
                foreach($megaArr as $k=>$v) {
                  $counter = 0;
                  foreach($v['ts'] as $k2=>$v2) {
                    $to_time = strtotime($v2[0]);
                    $from_time = strtotime($v2[1]);
                    $cc = round(abs($to_time - $from_time) / 60); 
                    $counter += $cc;
                    $notLastArr[$v['w_id']]['peices'][] = $cc;
                  } 
                  $notLastArr[$v['w_id']]['all'] = $counter;
                }
              print_r($notLastArr);
            }
            $arr['response'] = 100;
            break;
        case "get_tickets":
            if(!isset($spec_id)) {
              break;
            }
            $for_who = "-1"; // for all
            $dop_vars = "1";
            if($_SESSION['User']['flag'] != 5) {
              $for_who = $spec_id;
              if((int)$_GET['t_s']==2) {
                if($_SESSION['User']['flag'] != 1) {
                  $dop_vars = "-1";
                }
              }
            } 
            if((int)$_GET['page']>1) {
              $start_at = (((int)$_GET['page']-1)*(int)$_GET['on_page'])+1;
            } else {
              $start_at = 1;
            }
            @$sql_contact_types = "call dba.get_tickets(".(int)$_GET['t_s'].", ".$for_who.", ".$dop_vars.", ".(int)$_GET['on_page'].", ".$start_at.", ".(int)$_GET['ord'].");";         
            @$res_contact_types = $db->get( $sql_contact_types );
            //print_r($res_contact_types);
            if(!empty($res_contact_types)) {
              foreach($res_contact_types as $k=>$v) {
                  foreach($v as $k2=>$v2) {     // for each row parameter
                    if($k2=="created" or $k2=="closed" or $k2=="approval" or $k2=="spec_selected") {
                      $v2 = substr($v2, 0, -4);
                    }
                    $arr['tickets'][$k][$k2] = strToRu($v2);
                  }      
              }
              $arr['response'] = 100;
            } else {
              $arr['msg'] = "Записей не найдено!";
              $arr['response'] = 401;
            }
            break;
        case "get_tickets_pager":
            $for_who = "-1"; // for all
            $dop_vars = "0";
            if($_SESSION['User']['flag'] != 5) {
              $for_who = $spec_id;
              if((int)$_GET['t_s']==2) {
                $dop_vars = "-1";
              }
            } 
            @$sql_count = "call dba.get_tickets_pager(".(int)$_GET['t_s'].", ".$for_who.", ".$dop_vars.");";         
            @$res_count = $db->get( $sql_count );
            if(!empty($res_count)) {
              $arr['count'] = $res_count[0][0];
              $arr['response'] = 100;
            } else {
              $arr['msg'] = "Записей не найдено!";
              $arr['response'] = 401;
            } 
            break;
        case "update_ses":
            //if(isset($_SESSION['User']['id'])) {
              //$arr['msg'] = "session updated";
              //$arr['response'] = 100;
            //} else {
              $arr['msg'] = "Ошибка Сессии! обновите страницу!";
              $arr['response'] = 407;
            //}
            break;                                                                                                
        default:
            $arr['msg'] = "Приняты неправильные данные из ACTION";     
            $arr['response'] = 4000;
    }
    if(isset($arr['response'])) {
      if($arr['response']!=100 and $arr['response']!=401 and $arr['response']!=407 and $arr['response']!=208 and $arr['response']!=400) {
        throw new Exception("CODE: ".$arr['response']." | MSG: ".$arr['msg']." | REFERER URL: ". $_SERVER["HTTP_REFERER"]." TO URL: ".$_SERVER['REQUEST_URI']);
      }
    } else {
      throw new Exception("WITHOUT RESPONSE! REFERER URL: ". $_SERVER["HTTP_REFERER"]." TO URL: ".$_SERVER['REQUEST_URI']);
    }
  } catch (Exception $e) {
      error_log('Error: \''.$e->getMessage()); // at line: ".$e->getLine());
  }
}



function parse_work_json($res_actual_works){
  $count=0;
  foreach($res_actual_works as $key=>$res) {
                  foreach($res as $k=>$v) {
                    if(!is_integer($k)) {
                      if($k=="comments" ) {
                        if(!empty($res_actual_works[$key][$k])) {
                          $arr['works'][$key][$k] = explode("],[", substr(strToRu($v), 1, -1));
                        } else {
                          $arr['works'][$key][$k][] = Array();
                        }
                      } else if($k=="timestamps") {
                        if(!empty($res_actual_works[$key][$k])) {
                          $temp_var = substr($v, 1, -1);
                          $temp_arr = explode("],[", $temp_var);
                          foreach($temp_arr as $k_t=>$v_t) {
                            $temp_var2 = explode(",", $v_t);
                            $last_temp_arr = Array();
                            $cc=0;
                            foreach($temp_var2 as $k_l=>$v_l) {
                              $var_is_empty=0;
                              $v1_1 = explode(" ", $v_l);
                              if($cc==0) {
                                $last_temp_arr['start']['date'] = explode("-", $v1_1[0]);
                                $last_temp_arr['start']['time'] = explode(":", $v1_1[1]); 
                                $cc++;
                              } else {
                                if(!empty($v1_1[0])) {
                                  $last_temp_arr['stop']['date'] = explode("-", $v1_1[0]);
                                } else {
                                  $var_is_empty = 1;
                                  $last_temp_arr['stop']['date'] = explode("-", date("Y-m-d"));
                                }
                                if(!empty($v1_1[1])) {
                                  $last_temp_arr['stop']['time'] = explode(":", $v1_1[1]);
                                } else {
                                  $var_is_empty = 1;
                                  $last_temp_arr['stop']['time'] = explode(":", date("H:i:s"));
                                }
                                //date("Y-m-d H:i:s");
                                $cc=0;
                              }
                              if($var_is_empty==0) {
                                $arr['works'][$key]['wothout_stop'] = false;
                              } else {
                                $arr['works'][$key]['wothout_stop'] = true;
                              }                             
                            }
                            $arr['works'][$key][$k][] = $last_temp_arr;
                          }
                        } else {
                          $arr['works'][$key][$k][] = Array('start' => Array(Array(0,0,0),Array(0,0,0)), 'stop' => Array(Array(0,0,0),Array(0,0,0)));
                        }
                      } else if($k=="timestamp_ids") {
                        if(!empty($res_actual_works[$key][$k])) {
                          $temp_var = substr($v, 1, -1);
                          $temp_arr = explode("],[", $temp_var);
                          $arr['works'][$key][$k] = $temp_arr;
                        } else {
                          $arr['works'][$key][$k][] = 0;
                        }
                      } else {
                        $arr['works'][$key][$k] = strToRu($v);
                      }
                    }
                  }
                  
  }
  return $arr;
}

function get_childs( $table, $parent_id, &$db, &$childs ) {
  $sql = "SELECT {$table}.id, {$table}.value, count(t1.id) AS childs,
          ( IF {$table}.selectable IS NOT NULL THEN {$table}.selectable ELSE 1 ENDIF ) AS selectable
          FROM {$table}
          LEFT JOIN {$table} AS t1 ON t1.Parent={$table}.id
          WHERE {$table}.parent={$parent_id} GROUP BY {$table}.id, {$table}.value, {$table}.selectable";
  $childs = $db->get( $sql );   
}

function strToRu($str) {    
 if(is_array($str)) {
    $tmp = Array();
    foreach($str as $kt=>$vt) {
      $tmp[$kt] = strToRu($vt);
    }
    return $tmp;
  } else {
    return @iconv("windows-1251", "UTF-8", $str);
  }        
} 
function ruToStr($str) {    
 if(is_array($str)) {
    $tmp = Array();
    foreach($str as $kt=>$vt) {
      $tmp[$kt] = strToRu($vt);
    }
    return $tmp;
  } else {
    return @iconv("UTF-8", "windows-1251", $str);
  }        
} 

function create_salt() {
  $length = 4;
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $salt = '';
  for ($p = 0; $p < $length; $p++)
  {
      @$salt .= $characters[mt_rand(0, strlen($characters))];
  }
  return $salt;
} 

if(isset($_GET['s_m'])) {
  if($spec_id==1 and $_GET['word']=='go') {
    send(3,(int)$_GET['s_m']);
  }
}

function send($trigger,$new_id,$sample='') {
  global $db;
  $mail = '';
  $message = '';
  switch ($trigger) {
    case 1:   // for dispatchers - grlobalview=1 in specialists table 
        $res = getRes($new_id);    
        $subject = ruToStr('Новая заявка #').$new_id;
        $mails = Array();
        $sql_global_view_mails = "select 
          value as dispatcher_mail
          from dba.contacts t1 
          where 
          t1.contact_type_id = 1 
          and t1.spec_id in (SELECT id FROM dba.specialists where global_view = 1 and exist = 1)";
        $res_global_view_mails = $db->get($sql_global_view_mails);
        foreach($res_global_view_mails as $v) {
          $mails[] = $v[0];
        }
        $mail = implode(";", $mails);  //$mail = 'jerico@inbox.ru';    //dispatcher main ftp1421@mail.ru;umashev@gmail.com;
        $message = getMessageBody(1, $res);
        break;
    case 2:   // for selected in ticket spec      
        $res = getRes($new_id);    
        $subject = ruToStr('Вам поступила заявка #'.$new_id);
        $mail = $res[0]['spec_mail']; //spec mail 
        $message = getMessageBody(1, $res);     //1 - means with all info - error, placve etc...
        break;
    case 3:  // for ticket creator
        $subject = ruToStr($sample['subject']);
        $mail = $sample['mail'];
        $message = getMessageBody(2, $sample['message']);  //2 - means without all info, just text in $sample['message']
        break;
  }  
  if(!empty($mail)) {
      $sql_main = 'CALL "DBA"."auto_send_mail"(\''.$mail.'\', \''.$subject.'\', \''.$message.'\');';
      $db->process($sql_main);
  }    
}

function getMessageBody($trigger, $res) {
  switch ($trigger) {
    case 1:   //default message body
        $contact_person = 'Контактное лицо: '.strToRu($res[0]['creator_fio']).'<br>';
        $creator_in_phone = '';
        $creator_city_phone = '';
        $creator_mob_phone = '';
        $contact_computer = '';
        $inputed_phone = '';    
        if(!empty($res[0]['creator_in_phone'])) {$creator_in_phone = 'Внутренний телефон: '.$res[0]['creator_in_phone'].'<br>';}
        if(!empty($res[0]['creator_city_phone'])) {$creator_city_phone = 'Городской телефон: '.$res[0]['creator_city_phone'].'<br>';}
        if(!empty($res[0]['creator_mob_phone'])) {$creator_mob_phone = 'Мобильный телефон: '.$res[0]['creator_mob_phone'].'<br>';}
        if(!empty($res[0]['pc_name'])) {$contact_computer = 'Имя компьютера: '.strToRu($res[0]['pc_name']).'<br>';}
        if(!empty($res[0]['phone'])) {$inputed_phone = 'Указанный доп. телефон: '.strToRu($res[0]['phone']).'<br>';}    
        $place_v = 'Место: '.strToRu($res[0]['place']).'<br>'; 
        $error_v = 'Тип ошибки: '.strToRu($res[0]['error']).'<br>';
        $description = $contact_person.$creator_in_phone.$creator_city_phone.$creator_mob_phone.$contact_computer.$inputed_phone.$place_v.$error_v.'<br>'.str_replace('"', '\'', strToRu($res[0]['text'])); 
        $description = ruToStr($description);       
        break;
    case 2:   //sample message body with text in $res
        $description = ruToStr($res);
        break;
  }
  $message = 'Content-type: text/html; charset=windows-1251'."\n";
  $message .= "Content-Transfer-Encoding: base64\n\n";
  $message .= chunk_split( base64_encode( $description ) )."\n";
  return $message; 
}

function getRes($new_id) {
  global $db;
  $sql = "select
          t0.pc_name, 
          t0.text,
          t0.phone,
          t0.creator_id as creator_id,          
          t1.surname+' '+t1.name+' '+t1.patronymic as creator_fio, 
          t2.value as creator_in_phone, 
          t3.value as creator_mail, 
          t4.value as spec_mail,
          t5.value as place, 
          t6.value as error,
          t7.value as creator_city_phone,         
          t8.value as creator_mob_phone         
      from dba.tickets t0 
      LEFT OUTER JOIN dba.specialists t1
          ON t0.creator_id = t1.id    
      LEFT OUTER JOIN dba.contacts t2 
          ON t2.spec_id = t1.id and t2.contact_type_id = 5 
      LEFT OUTER JOIN dba.contacts t3 
          ON t3.spec_id = t1.id and t3.contact_type_id = 1
      LEFT OUTER JOIN dba.contacts t4 
          ON t4.spec_id = t0.spec_id and t4.contact_type_id = 1
      LEFT OUTER JOIN dba.places_all t5
          ON t5.id = t0.place_id
      LEFT OUTER JOIN dba.errors t6
          ON t6.id = t0.error_id 
      LEFT OUTER JOIN dba.contacts t7 
          ON t7.spec_id = t1.id and t7.contact_type_id = 2
      LEFT OUTER JOIN dba.contacts t8 
          ON t8.spec_id = t1.id and t8.contact_type_id = 3               
      where t0.id = ".$new_id;
  $res = $db->get($sql);
  return $res;
}


if(empty($arr)) {
  $arr['msg'] = "Приняты неправильные данные";     
  $arr['response'] = 400; 
}       
echo @json_encode($arr);
//fclose($fp);
?>
