<?
ini_set('log_errors', true);
ini_set('error_log', 'error_main.log'); 
$browser = get_browser($_SERVER['HTTP_USER_AGENT'], true); 
session_start();  
if(!isset($_SESSION['User']['id']) or empty($_SESSION['User']['id'])) {  
  header('Location: /index.php');
  exit();
} else {
  if($_SESSION['User']['flag']==4) {
    header('Location: /tickets.php');
    exit();
  } else {
    $spec_id = $_SESSION['User']['id'];
  }
}

/****************************************/
require_once( 'const.php' );
require_once( 'DB.php' );
$db = new DBWrapper( SQLCONNECTSTRING );
/****************************************/

$tree_plus="<i class=\"fa fa-times\" style=\"padding-top:2px;\"></i>";
$font_awesome = '<link rel="stylesheet" type="text/css" href="/css/font-awesome.css">';
$doctype='';
if($browser['browser']=='Firefox' && $browser['version']<4) {
  $font_awesome = '';
  $tree_plus="X";
}
if($browser['browser']=='Opera' && $browser['version']<12) {
  $font_awesome = '';
  $tree_plus="X";
}  
if($browser['browser']=='IE') {
  $doctype="<!DOCTYPE html>";
} 
function strToRu($str) {
  return iconv("windows-1251", "UTF-8", $str);
} 
//<script type="text/javascript" src="http://code.jquery.com/jquery-1.11.2.js"></script>
?>
<?=$doctype?>
<html>
  <head>      
    <title>Менеджер заданий</title>    
    <link href="/favicon.ico" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" href="/css/style.css" />    
    <script type="text/javascript" src="/js/redirect.js"></script>  
    <script type="text/javascript">redirect("/m/index.php");</script>
    <script type="text/javascript" src="/js/jquery.js"></script> 
    <script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>
    <script type="text/javascript" src="/js/init_main_functions.js"></script>
    <script type="text/javascript" src="/js/socket_new.js"></script>
    <link href='http://fonts.googleapis.com/css?family=Arimo:400,700&subset=latin,cyrillic-ext' rel='stylesheet' type='text/css'>
    <?=$font_awesome?>                                             
    <script>  
      window.spec_id = <?=$_SESSION['User']['id']?>;
      window.spec_flag = <?=$_SESSION['User']['flag']?>;
      window.stat_id = 1;
      window.is_daily = true; 
      window.ord = 1;
      //auto_ckeck_contacts();
      <?
      if(isset($_GET['debug'])) {
        echo "window.DEBUG = true;\r\n      ";
      }
      if(empty($font_awesome)) {
        echo "window.tree_plus='+';\r\n      ";
        echo "window.tree_minus='-';\r\n      ";
        echo "window.close='x';";
      } else {
        echo "window.tree_plus='<i class=\"fa fa-plus-square-o fa-1\"></i>';\r\n      ";
        echo "window.tree_minus='<i class=\"fa fa-minus-square-o fa-1\"></i>';\r\n      ";
        echo "window.close='<i class=\"fa fa-times\" style=\"padding-top:2px;\"></i>';";
      }      
      ?>$( document ).ready(function() {  
        <? 
        if(isset($_GET['show_one_work'])) {
          echo "start_calendar(".(int)$_GET['work_id'].");\r\n";
        } else {
          echo "start_calendar();\r\n";
        } 
        ?>
        $("#selected_stat_td_"+window.stat_id).css("background-color", "#007270");   
        $('#selected_month option:eq('+new Date().getMonth()+')').prop('selected', true).css('color','rgb(220, 0, 0)')        
        $('#selected_year').val(new Date().getFullYear());
        $('#selected_day').val(new Date().getDate()); 
      });    
    </script>
  </head>
<body>
<!--------------ADD TICKET MSG----------------->
<div class="msg_form" style="display:none;"> 
  <input type="hidden" name="ticket_id" id="ticket_id" value=""> 
  <table width=100% height=100% cellspacing=0 cellpadding=0 >
    <tr>
      <td class="pop_head" align=center id="msg_form_title">Переписка</td>
      <td class="pop_head" align=right valign=middle>
      <div class="close_div" onclick="$('.msg_form').hide();" align=center><?=$tree_plus?></div>
      </td>
    </tr>
    <tr>
      <td colspan=2 width=100% height=100% align=center valign=top>    
        <div width=100% height=100% id="popup_main_td4" align=center valign=top>
          <a href="javascript:;" onclick="print_this('msg_form_div');" class="btn">Распечатать</a>
          <table width=100% height=10% id="add_ticket_message_table">
            <tr>
              <td valigh=center align=center width=110>Сообщение:</td>
              <td><textarea id="ticket_description" class="description" name="ticket_description"></textarea><br></td>
            </tr>
            <tr>
              <td align=center id="add_ticket_message_button"></td>
              <td></td>
            </tr>
          </table> 
          <div id="error_msg_div" style="display:none;"></div>
          <div id="msg_form_div"></div>
        </div>
      </td>
    </tr>  
  </table>     
</div>         
<!--------------/ADD TICKET MSG----------------->

<!--------------MAIN LOADING----------------->
<img id="main_loading" src="/images/loading.gif" border=0 >
<!--------------/MAIN LOADING----------------->

<!--------------MAIN POPUP----------------->
<div id="overlay"></div>  
<div id="work_popup">  
  <table width=100% height=100% cellspacing=0 cellpadding=0 >
    <tr>
      <td class="pop_head" id="popup_title"></td>
      <td class="pop_head" align=right valign=middle>
      <div class="close_div" onclick="close_pop();" align=center><?=$tree_plus?></div>
      </td>
    </tr>
    <tr>
      <td colspan=2 width=100% height=100% id="popup_main_td2" align=center valign=top>
        <div width=100% height=100% id="popup_main_td" align=center valign=top></div>
      </td>
    </tr>  
  </table>     
</div>
<!--------------/MAIN POPUP----------------->

<!---------------MESSAGES BOX---------------->
<div id="msgBox">  
  <table width=100% height=100% cellspacing=0 cellpadding=0 >
    <tr>
      <td align=center valign=middle widht=100% height=100% id="msgContainer"></td>
    </tr> 
  </table>     
</div> 
<!---------------/MESSAGES BOX---------------->

<!---------------TOOLTIP---------------->
<div id="mess"></div> 
<!---------------/TOOLTIP---------------->

<!---------main---------->
<table width=100% height=100% cellspacing=0 cellpadding=0 class="main_table"  style="height:1px;">
  <? if(!isset($_GET['show_one_work'])) { ?>
  <!---------menu---------->
  <tr>
    <td width=100% height=25 class="menu_td" >
      <div class="main_row_div mdk" style="margin-top:5px;background-color:#1aa1e1;height:50px;color:white;"> 
        <table class="menu_table2" cellspacing=0 cellpadding=0 height=100% width=100%>
          <tr>
            <?if($_SESSION['User']['flag']==1 or $_SESSION['User']['flag']==5) {?> <td width=180 style="cursor:pointer;" onclick="document.location='/tickets.php';" onmouseover="this.style.backgroundColor='#00abfd';" onmouseout="this.style.backgroundColor='#1aa1e1';"  align=center ><a href="/tickets.php"><i class="fa fa-clipboard fa-lg"></i>&nbsp;&nbsp;Показать заявки <div id="tickets_count"></div></a></td>  <?}?>            
            <td width=180 style="cursor:pointer;" onclick="create_work();return true;" onmouseover="this.style.backgroundColor='#00abfd';" onmouseout="this.style.backgroundColor='#1aa1e1';" align=center><a href="javascript:;" ><i class="fa fa-pencil-square-o fa-lg"></i>&nbsp;&nbsp;Создать задание</a></td>                    
            <td width=180 style="cursor:pointer;" onclick="spec_profil();return true;" onmouseover="this.style.backgroundColor='#00abfd';" onmouseout="this.style.backgroundColor='#1aa1e1';"  align=center ><a href="javascript:;"><i class="fa fa-user fa-lg"></i>&nbsp;&nbsp;Профиль</a></td>      
            <?if($_SESSION['User']['flag']==4) {?><td width=120 style="cursor:pointer;" style="cursor:pointer;" onmouseover="this.style.backgroundColor='#00abfd';" onmouseout="this.style.backgroundColor='#1aa1e1';"  align=center ><a href="http://docs.google.com/viewer?url=http://ADRESS/docs/manual_sotrudnik.pdf&embedded=true" target="_blank"><i class="fa fa-question-circle fa-lg"></i>&nbsp;&nbsp;Помощь</a></td><?}?>
            <?if($_SESSION['User']['flag']==1) {?><td width=120 style="cursor:pointer;" style="cursor:pointer;" onmouseover="this.style.backgroundColor='#00abfd';" onmouseout="this.style.backgroundColor='#1aa1e1';"  align=center ><a href="http://docs.google.com/viewer?url=http://ADRESS/docs/manual_spec.pdf&embedded=true" target="_blank"><i class="fa fa-question-circle fa-lg"></i>&nbsp;&nbsp;Помощь</a></td><?}?>
            <?if($_SESSION['User']['flag']==5) {?><td width=120 style="cursor:pointer;" style="cursor:pointer;" onmouseover="this.style.backgroundColor='#00abfd';" onmouseout="this.style.backgroundColor='#1aa1e1';"  align=center ><a href="http://docs.google.com/viewer?url=http://ADRESS/docs/manual_dispatcher.pdf&embedded=true" target="_blank"><i class="fa fa-question-circle fa-lg"></i>&nbsp;&nbsp;Помощь</a></td><?}?>                                       
            <td width=120 style="cursor:pointer;" onclick="document.location='/json.php?action=logout';" style="cursor:pointer;" onmouseover="this.style.backgroundColor='#00abfd';" onmouseout="this.style.backgroundColor='#1aa1e1';"  align=center ><a href="/json.php?action=logout" ><i class="fa fa-sign-out fa-lg"></i>&nbsp;&nbsp;Выход</a></td>
            <td width=* height=50 style="color:white;font-size:20px;font-family: 'Arimo', sans-serif;padding-right:30px;" align=right><div style="display:inline;padding-top:14px;padding-bottom:13px;font-size:12px;" id="socket_stat">socket connecting...</div> <div style="display:inline;padding-top:14px;padding-bottom:13px;background-image: url(/images/avatar50.png);background-repeat:no-repeat;background-position: center center;">Менеджер заданий</div></td>            
          </tr>
        </table>
      </div>      
    </td>
  </tr>
  <!---------/menu---------->
  <tr>
    <td width=150 height=25 class="menu_td">
      <div class="left_row_div mdk" style="margin-top:5px;"> 
        <table  class="menu_table" cellspacing=0 cellpadding=0 id="main_stat_selector">
          <tr>
            <? 
              $sql_w_s = "SELECT id, value FROM dba.work_stats";
              $res_w_s = $db->get( $sql_w_s );
              foreach($res_w_s as $r_w_s) {
                  ?>
                    <td width=180 onclick="select_works(<?=$r_w_s['id']?>);return true;"  align=center id="selected_stat_td_<?=$r_w_s['id']?>"><a href="javascript:;"><?=strToRu($r_w_s['value'])?></a></td>
                  <?
              }
            ?>  
            <!--<td width=130 onclick="select_works(-1);return true;" id="selected_stat_td_-1"><a href="javascript:;">Все</a></td>-->
          </tr>
        </table>
      </div>    
    </td>
  </tr>
  <? } ?>
  <tr>
    <td width=150 height=25 class="menu_td">
      <div class="left_row_div mdk" style="margin-top:5px;"> 
        <table  class="menu_table" cellspacing=0 cellpadding=0 >
          <tr>
            <td width="180" onclick="window.is_daily=true;start_calendar();" id="dayly_selector_d"  align=center ><a href="javascript:;">День</a></td>
            <td width="180" onclick="window.is_daily=false;start_calendar();" id="dayly_selector_m"  align=center ><a href="javascript:;">Месяц</a></td>
          </tr>
        </table>
      </div>    
    </td>
  </tr>   
  
  <tr>
    <td valign=top style="border:0px;">        
            <!--------calendar planer------->
            <table id="calendar4" class="tickets_table" cellspacing=0 cellpadding=0 style="border:0px;">
            <thead>
              <tr>
                <td colspan=33 style="padding-top:6px;padding-bottom:7px;border:0px;"> 
                <select id="selected_month">
                  <option value="0">Январь</option>
                  <option value="1">Февраль</option>
                  <option value="2">Март</option>
                  <option value="3">Апрель</option>
                  <option value="4">Май</option>
                  <option value="5">Июнь</option>
                  <option value="6">Июль</option>
                  <option value="7">Август</option>
                  <option value="8">Сентябрь</option>
                  <option value="9">Октябрь</option>
                  <option value="10">Ноябрь</option>
                  <option value="11">Декабрь</option>
                  </select>
                  <input type="number" id="selected_year" value="" min="2014" max="3000" size="16" style="margin-left:20px;">
                  <input type="number" id="selected_day" min="1" max="31" style="display:none;margin-left:20px;">
                </td>
              </tr>
            </thead>
            <tbody>
               
            </tbody>           
            </table>      
            <script type="text/javascript" src="/js/works.js"></script>  
            <script type="text/javascript" src="/js/pc.js"></script>                 
            <!--------/calendar planer------->
    </td>
  </tr>
</table>
<!---------/main---------->
</table>
</body>
</html>

