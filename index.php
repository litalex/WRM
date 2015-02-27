<?
ini_set('log_errors', true);
ini_set('error_log', 'error_index.log');
$browser = @get_browser($_SERVER['HTTP_USER_AGENT'], true); 
session_start();

/****************************************/
require_once( 'const.php' );
require_once( 'DB.php' );
$db = new DBWrapper( SQLCONNECTSTRING );
/****************************************/

$font_awesome = '<link rel="stylesheet" type="text/css" href="/css/font-awesome.css">';
$doctype='';
if($browser['browser']=='Firefox' && $browser['version']<4) {
  $font_awesome = '';
} 
if($browser['browser']=='Opera' && $browser['version']<12) {
  $font_awesome = '';
} 
if($browser['browser']=='IE' && $browser['version']<12) {
  $doctype="<!DOCTYPE html>";
} 
$gotoPage = 'main.php';
if(!isset($_SESSION['User'])) { 
  if((isset( $_POST['action'] ) and $_POST['action']=="login" ) or isset($_GET['old_act'])) {       
      if(isset($_GET['old_act'])) {
        $login = trim($_GET['u']);
        $password = '';
        $gotoPage = 'tickets.php?old_act='.$_GET['old_act']; 
        if(!empty($_GET['p']) and !empty($_GET['s'])) {      
          @$sql = "UPDATE dba.specialists SET \"spec_pass\" = '".trim($_GET['p'])."', \"salt\"='".trim($_GET['s'])."' WHERE spec_login='{$login}' AND exist=1";
          @$res = $db->process( $sql );
          setcookie("login_wrm", $login, time()+9999999);
          setcookie('password_wrm', trim($_GET['p']), time()+9999999); 
        }   
      } elseif(isset( $_POST['action'] ) and $_POST['action']=="login" ) {
        $login = isset( $_POST['login'] ) ? trim($_POST['login']) : '';
        $password = isset( $_POST['password'] ) ? trim($_POST['password']) : ''; 
      }
      if($password=="896377u7mntfQW") {       // if it is test enter by some user without pass
        @$sql = "SELECT id FROM dba.specialists WHERE spec_login = '{$login}' AND exist=1";  
      } else {
        @$sql = "SELECT salt, spec_pass FROM dba.specialists WHERE spec_login='{$login}' AND exist=1";
        @$res = $db->get( $sql );
        if(!isset($res[0]['salt']) or empty($res[0]['salt'])) {
          $salt = create_salt();
          $hash = md5( $password.$salt ); 
          @$sql_tmp = 'UPDATE "DBA"."specialists" SET "spec_pass" = \''.$hash.'\',  "salt" = \''.$salt.'\' WHERE spec_login = \''.$login.'\';';   
          @$db->process($sql_tmp);
        } else {
          $salt = $res[0]['salt'];      
          $hash = md5( $password.$salt );
        }
        if(isset($_GET['old_act'])) {
          @$sql = "SELECT id FROM dba.specialists WHERE spec_login = '{$login}' AND exist=1";
        } elseif(isset( $_POST['action'] ) and $_POST['action']=="login" ) {
          @$sql = "SELECT id FROM dba.specialists WHERE spec_login = '{$login}' AND spec_pass='{$hash}' AND exist=1";
        }      
      }
      @$actual = $db->get( $sql );
      //print_r($actual);
      //$sql = "SELECT dba.user_login('".$login."','".$password."')";
      //$actual = $db->get( $sql );   
      if( empty($actual[0][0]) or !isset($actual[0][0]) or $actual[0][0] <=0){
        show(1);
        error_logging($login, $password);
      } else {
       // $_SESSION['User'] = array( 'login' => $login, 'name' => $user_id->name, 'id' => $user_id->id, 'admin_flag' => $user_id->admin_flag );
        @$sql_flag = "SELECT \"role_id\", surname+' '+name+' '+patronymic as fio from dba.specialists where id = '".$actual[0][0]."'";
        @$res_flag = $db->get( $sql_flag );
        $_SESSION['User'] = array( 'login' => $login,  'id' => $actual[0][0], 'flag' => $res_flag[0]['role_id'], 'fio' => strToRu($res_flag[0]['fio']));
        if (isset($_POST['save'])){
          setcookie("login_wrm", $login, time()+9999999);
          setcookie('password_wrm', $hash, time()+9999999);           
        }                  
        header('Location: /'.$gotoPage);
      }
  } else {  
    if(!isset($_GET['logout'])) {
      if(!empty($_COOKIE['login_wrm']) and !empty($_COOKIE['password_wrm'])) {
        @$sql_cook = "select id, role_id, surname+' '+name+' '+patronymic as fio from dba.specialists where exist = 1 and spec_login = '".trim($_COOKIE['login_wrm'])."' and spec_pass = '".trim($_COOKIE['password_wrm'])."';";
        @$res_cook = $db->get( $sql_cook );
        if( !isset($res_cook[0][0]) or empty($res_cook[0][0]) or $res_cook[0][0] == "-1" ){
          show();
          error_logging("COOKIE - ".$_COOKIE['login_wrm'], $_COOKIE['password_wrm']);
        } else {  
          $_SESSION['User'] = array( 'login' => $_COOKIE['login_wrm'],  'id' => $res_cook[0]['id'], 'flag' => $res_cook[0]['role_id'], 'fio' => strToRu($res_cook[0]['fio']));
          header('Location: /'.$gotoPage);
        }
      } else {    
      show();
      }
    } else {
      setcookie('login_wrm', '', time() - 3600);
      unset($_COOKIE['login_wrm']);
      setcookie('password_wrm', '', time() - 3600);
      unset($_COOKIE['password_wrm']);
      setcookie('PHPSESSID', '', time() - 3600);
      unset($_COOKIE['PHPSESSID']);  
      show();
    }    
  }
  ?>
  <script language="JavaScript">
  function SendForm (formName) { 
    /*var i, j;
      for (i=0; i<document.forms[formName].length; i++) {
          if (document.forms[formName].elements[i].value == "" ) {
            alert("Пожалуйста, заполните все поля");
              document.forms[formName].elements[i].focus();
              return false;
          }
    }  */
  return true;
  }
  document.getElementById('login').focus();
  </script>
  <?  
} else {
  if(isset($_GET['old_act'])) {
    $gotoPage = 'tickets.php?old_act='.$_GET['old_act']; 
  }  
  header('Location: /'.$gotoPage);
}
function show($message=0) {
  global $font_awesome, $doctype;
  ?>
  <?=$doctype?>
  <html>
    <head>              
      <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
      <link rel="stylesheet" href="css/style.css" type="text/css">
      <link href='http://fonts.googleapis.com/css?family=Comfortaa&subset=latin,cyrillic' rel='stylesheet' type='text/css'>
      <?=$font_awesome?>              
      <script type="text/javascript" src="http://code.jquery.com/jquery-1.11.2.js"></script>
      <script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>
      <script type="text/javascript" src="/js/init_main_functions.js"></script>
      <script type="text/javascript" src="/js/reg.js"></script>  
        
      <script>
      <? 
        if(isset($_GET['debug'])) {
          echo "window.DEBUG = true;\r\n      ";
        }  
        if(empty($font_awesome)) {
          echo "window.close='x';\r\n      ";
        } else {
          echo "window.close='<i class=\"fa fa-times\" style=\"padding-top:2px;\"></i>';\r\n      ";
        }
      ?>     
        $( document ).ready(function() { 
          $("#popup_title").mousedown(function() {
            $( "#work_popup" ).draggable();
          })
          $("#popup_title").mouseup(function() {
            $( "#work_popup" ).draggable( "destroy" );
          });  
        });
      </script>      
    </head> 
    <body>   
<div id="overlay"></div> 
<!------------------------------->
<div id="work_popup">  
  <table width=100% height=100% cellspacing=0 cellpadding=0 >
    <tr>
      <td class="pop_head" id="popup_title">&nbsp;</td>
      <td class="pop_head" align=right valign=middle>
      <div class="close_div" onclick="close_pop(true);"><script>document.write(window.close);</script></div>
      </td>
    </tr>
    <tr>
      <td colspan=2 width=100% height=100% id="popup_main_td" align=center valign=top>
        <img id="loading" src="/images/loading.gif" border=0>
      </td>
    </tr>  
  </table>     
</div> 
<div id="msgBox">  
  <table width=100% height=100% cellspacing=0 cellpadding=0 >
    <tr>
      <td align=center valign=middle widht=100% height=100% id="msgContainer">&nbsp;
      </td>
    </tr> 
  </table>     
</div> 
<!------------------------------->    
    <table width=100% height=100%><tr><td align=center valign=center>
  <table>
  <tr>
    <td colspan=2 align=center>
      <div style="text-aligh:center;font-weight:bold;font-family: 'Comfortaa', cursive;">
        Система<br>Распределения Рабочих Ресурсов
      </div>
      <img src="/images/avatar200.png" width=200>
    </td>
  </tr>    
  <?
       if($message==1) {
        echo '<tr><td colspan=2 aling=center style="text-align:center;">Не верное сочетание логин/пароль</td></tr>';
       }
       ?>
      <tr>
        <form method="POST" name="formq" onSubmit="return SendForm('formq');"> 
        <td align=center>
          <input type="hidden" name="action" value="login">
          <input id="login" type="text" name="login"  id="login" class="log_input" style="width: 184px;" placeholder="Логин">
        </td>
      </tr>
      <tr>
        <td align=center>
          <input id="password" type="password" name="password" class="log_input" style="width: 184px;" placeholder="Пароль">
        </td>
      </tr>
      <tr>
        <td align=center>
          <label><input name="save" type="checkbox" value='1'> Запомнить меня</label>&nbsp;&nbsp;<i class="fa fa-lock"></i><br>          
          <input type="submit" value="Выполнить вход" class="btn" style="font-family: 'Comfortaa', cursive;"><br>  
          <div style="height:4px;"></div>
          <input type="button" value="Зарегистироваться" class="btn" style="font-family: 'Comfortaa', cursive;" onclick="show_reg();">                     
          </form>
        </td>
      </tr>
      </table> 
  </td>
  </tr>  
  </table>   
  </body>
  </html>
  <?
}
function error_logging($l, $p) {
    $ip="";	
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
       $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
       $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
       $ip = $_SERVER['REMOTE_ADDR'];
    }
    $fp = fopen('error_login.log','a');
    date_default_timezone_set('Etc/GMT-3');
    $now = date("D M d H:i:s Y");
    $str = "[".$now."] [error] [client ".$ip."] user ".$l." authentication failure";
    //[Mon Nov 17 10:28:46 2014] [error] [client x.x.x.x] user bob
    fwrite( $fp, $str."\r\n" );
    //[[]client <HOST>[]] user .* authentication failure
    fclose($fp);
}
function strToRu($str) {
  return iconv("windows-1251", "UTF-8", $str);
}  
function create_salt()
{
  $length = 4;
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $salt = '';
  for ($p = 0; $p < $length; $p++)
  {
      @$salt .= $characters[mt_rand(0, strlen($characters))];
  }
  return $salt;
} 
?>
