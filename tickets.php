<?             
ini_set('log_errors', true);
ini_set('error_log', 'error_tickets.log');
$browser = get_browser($_SERVER['HTTP_USER_AGENT'], true); 
session_start();
if(!isset($_SESSION['User']['id']) or empty($_SESSION['User']['id'])) {  
  header('Location: /index.php');
  exit();
} else {
  $spec_id = $_SESSION['User']['id'];
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
if($browser['browser']=='IE' && $browser['version']<12) {
  $doctype="<!DOCTYPE html>";
  $tree_plus="X"; 
}      
function strToRu($str) {
  return iconv("windows-1251", "UTF-8", $str);
} 

//    <script type="text/javascript" src="http://code.jquery.com/jquery-1.9.0.js"></script>
?>
<?=$doctype?> 
<html>
  <head>          
    <title>Менеджер заявок</title>
    <link href="/favicon.ico" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" href="/css/style.css" />    
    <script type="text/javascript" src="/js/redirect.js"></script>  
    <script type="text/javascript">redirect("/m/tickets.php");</script>    
    <script type="text/javascript" src="/js/jquery.js"></script>  
    <script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>      
    <script type="text/javascript" src="/js/init_main_functions.js"></script>
    <script type="text/javascript" src="/js/tickets.js"></script>       
    <script type="text/javascript" src="/js/calendar.js"></script> 
    <script type="text/javascript" src="/js/socket_new.js"></script> 
    <script type="text/javascript" src="http://ajax.aspnetcdn.com/ajax/globalize/0.1.1/globalize.min.js"></script>
    <script type="text/javascript" src="http://cdn3.devexpress.com/jslib/13.1.5/js/dx.chartjs.js"></script>           
    <link href='http://fonts.googleapis.com/css?family=Arimo:400,700&subset=latin,cyrillic-ext' rel='stylesheet' type='text/css'> 
    <?=$font_awesome?> 
    <script>       
      window.spec_id = <?=$_SESSION['User']['id']?>;
      window.spec_flag = <?=$_SESSION['User']['flag']?>; 
      window.ticket_stat_id = <?if($_SESSION['User']['flag']==2 or $_SESSION['User']['flag']==5) {?>1<?}else{?>2<?}?>;
      window.page=1;
      window.set_free_ticket = 0;
      window.ord = 1;
      auto_ckeck_contacts();
      <? 
        if(isset($_GET['debug'])) {
          echo "window.DEBUG = true;\r\n      ";
        }  
        if(empty($font_awesome)) {
          echo "window.tree_plus='+';\r\n      ";
          echo "window.tree_minus='-';\r\n      ";
          echo "window.close='x';\r\n      ";
        } else {
          echo "window.tree_plus='<i class=\"fa fa-plus-square-o fa-1\"></i>';\r\n      ";
          echo "window.tree_minus='<i class=\"fa fa-minus-square-o fa-1\"></i>';\r\n      ";
          echo "window.close='<i class=\"fa fa-times\" style=\"padding-top:2px;\"></i>';\r\n      ";
        }
      ?>
      $( document ).ready(function() {                      
        $("#selected_stat_td_"+window.ticket_stat_id).css("background-color", "#007270"); 
        <?
        $standart_link='';
        if(isset($_GET['old_act'])) {
          $standart_link='document.location=\'tickets.php\';';
          if($_GET['old_act']=='create_ticket') {
            echo "start_tickets();";
            //echo "show_create_ticket();";
          }
          if($_GET['old_act']=='start_tickets_1') {
            echo "start_tickets(1);";
          }  
          if($_GET['old_act']=='start_tickets_2') {
            echo "start_tickets(2);";
          }  
          if($_GET['old_act']=='start_tickets_3') {
            echo "start_tickets(3);";
          }  
          if($_GET['old_act']=='start_tickets_4') {
            echo "start_tickets(4);";
          }        
          if($_GET['old_act']=='search_tickets') {
            echo "search_toggler();";
          } 
        } else if(isset($_GET['show'])) {
          echo "start_tickets();";
          echo "ticket_info(".(int)$_GET['show'].");";
          //echo "show_add_ticket_msg(".(int)$_GET['show'].", 2, true);";
        } else {
          echo "start_tickets();";
        }                    
        ?> 
      });    
    </script>
  </head>
<body>
<!--------------DATESELECTOR CALENDAR POPUP----------------->
<div id="calendar" onclick="if(event.stopPropagation) event.stopPropagation(); else event.cancelBubble = true;" style="position:fixed;display:none;top:30px;left:250px;"></div>
<!--------------/DATESELECTOR CALENDAR POPUP----------------->
            
<!--------------ADD TICKET MSG----------------->
<div class="msg_form" style="display:none;"> 
  <input type="hidden" name="ticket_id" id="ticket_id" value=""> 
  <table width=100% height=100% cellspacing=0 cellpadding=0>
    <tr>
      <td class="pop_head" align=center id="msg_form_title">Переписка</td>
      <td class="pop_head" align=right valign=middle>
      <div class="close_div" onclick="$('.msg_form').hide();<?=$standart_link?>" align=center><?=$tree_plus?></div>
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

<!--------------PLACE & ERROR SELECTOR----------------->
<div id="places_popup" class="popup" style="width:360px;">
  <div class="close_div_small" onclick="close_popup( 'places_popup' ); return false;<?=$standart_link?>"><?=$tree_plus?></div>
  <div class="cb"></div>
  <div id="tree">
    <div class="toplevel" id="toplevel">
      &nbsp;
    </div>
  </div>
</div>
<!--------------/PLACE & ERROR SELECTOR----------------->

<!--------------MAIN LOADING----------------->
<img id="main_loading" src="/images/loading.gif" border=0 >
<!--------------/MAIN LOADING----------------->

<!--------------MAIN POPUP-----------------> 
<div id="overlay"></div>   
<div id="work_popup">  
  <table width=100% height=100% cellspacing=0 cellpadding=0 >
    <tr>
      <td class="pop_head" id="popup_title">&nbsp;</td>
      <td class="pop_head" align=right valign=middle>
      <div class="close_div" onclick="close_pop();<?=$standart_link?>"><?=$tree_plus?></div>
      </td>
    </tr>
    <tr>
      <td colspan=2 width=100% height=100% id="popup_main_td2" align=center valign=top>
        <div width=100% height=100% id="popup_main_td" align=center valign=top>&ndsp;</div>
      </td>
    </tr>  
  </table>     
</div> 
<!--------------/MAIN POPUP----------------->

<!---------------MESSAGES BOX---------------->
<div id="msgBox">  
  <table width=100% height=100% cellspacing=0 cellpadding=0 >
    <tr>
      <td align=center valign=middle widht=100% height=100% id="msgContainer">&nbsp;
      </td>
    </tr> 
  </table>     
</div> 
<!---------------/MESSAGES BOX---------------->
<!---------main---------->
<table width=100% height=100% cellspacing=0 cellpadding=0 class="main_table" style="height:1px;">
  <!---------menu---------->
  <tr>
    <td width=100% height=25 class="menu_td" >
      <div class="main_row_div mdk" style="margin-top:5px;background-color:#1aa1e1;height:50px;color:white;"> 
        <table class="menu_table2" cellspacing=0 cellpadding=0 height=100% width=100%>
          <tr>
            <?if($_SESSION['User']['flag']==1 or $_SESSION['User']['flag']==5) {?> <td width=180 style="cursor:pointer;" onclick="document.location='/main.php';" onmouseover="this.style.backgroundColor='#00abfd';" onmouseout="this.style.backgroundColor='#1aa1e1';"  align=center ><a href="/main.php"><i class="fa fa-tasks fa-lg"></i>&nbsp;&nbsp;Показать задания</a></td>  <?}?>            
            <td width=180 style="cursor:pointer;" onclick="show_create_ticket();return false;" onmouseover="this.style.backgroundColor='#00abfd';" onmouseout="this.style.backgroundColor='#1aa1e1';" align=center ><a href="javascript:;"><i class="fa fa-pencil-square-o fa-lg"></i>&nbsp;&nbsp;Создать заявку</a></td>                    
            <td width=180 style="cursor:pointer;" onclick="spec_profil();return false;" onmouseover="this.style.backgroundColor='#00abfd';" onmouseout="this.style.backgroundColor='#1aa1e1';"  align=center ><a href="javascript:;"><i class="fa fa-user fa-lg"></i>&nbsp;&nbsp;Профиль</a></td>        
            <?if($_SESSION['User']['flag']==4) {?><td width=120 style="cursor:pointer;" style="cursor:pointer;" onmouseover="this.style.backgroundColor='#00abfd';" onmouseout="this.style.backgroundColor='#1aa1e1';"  align=center ><a href="http://docs.google.com/viewer?url=http://ADRESS/docs/manual_sotrudnik.pdf&embedded=true" target="_blank"><i class="fa fa-question-circle fa-lg"></i>&nbsp;&nbsp;Помощь</a></td><?}?>
            <?if($_SESSION['User']['flag']==1) {?><td width=120 style="cursor:pointer;" style="cursor:pointer;" onmouseover="this.style.backgroundColor='#00abfd';" onmouseout="this.style.backgroundColor='#1aa1e1';"  align=center ><a href="http://docs.google.com/viewer?url=http://ADRESS/docs/manual_spec.pdf&embedded=true" target="_blank"><i class="fa fa-question-circle fa-lg"></i>&nbsp;&nbsp;Помощь</a></td><?}?>
            <?if($_SESSION['User']['flag']==5) {?><td width=120 style="cursor:pointer;" style="cursor:pointer;" onmouseover="this.style.backgroundColor='#00abfd';" onmouseout="this.style.backgroundColor='#1aa1e1';"  align=center ><a href="http://docs.google.com/viewer?url=http://ADRESS/docs/manual_dispatcher.pdf&embedded=true" target="_blank"><i class="fa fa-question-circle fa-lg"></i>&nbsp;&nbsp;Помощь</a></td><?}?>
            <?if($_SESSION['User']['flag']==5) {?><td width=120 style="cursor:pointer;" style="cursor:pointer;" onmouseover="this.style.backgroundColor='#00abfd';" onmouseout="this.style.backgroundColor='#1aa1e1';"  align=center ><a href="/reports.php" target="_blank"><i class="fa fa-signal fa-lg"></i>&nbsp;&nbsp;Статистика</a></td><?}?>            
            <td width=120 style="cursor:pointer;" onclick="document.location='/json.php?action=logout';" style="cursor:pointer;" onmouseover="this.style.backgroundColor='#00abfd';" onmouseout="this.style.backgroundColor='#1aa1e1';"  align=center ><a href="/json.php?action=logout" ><i class="fa fa-sign-out fa-lg"></i>&nbsp;&nbsp;Выход</a></td>
            <td width=* height=50 style="color:white;font-size:20px;font-family: 'Arimo', sans-serif;padding-right:30px;" align=right><div style="display:inline;padding-top:14px;padding-bottom:13px;font-size:12px;" id="socket_stat">socket connecting...</div> <div style="display:inline;padding-top:14px;padding-bottom:13px;background-image: url(/images/avatar50.png);background-repeat:no-repeat;background-position: center center;">Менеджер заявок</div></td>        
          </tr>
        </table>
      </div>      
    </td>
  </tr>
  <!---------/menu---------->
  <tr>
    <!---------status selector--------->
    <td width=100% height=25 class="menu_td">
      <div class="left_row_div mdk" style="margin-top:5px;"> 
        <table class="menu_table" cellspacing=0 cellpadding=0 id="main_stat_selector">
          <tr>            
            <?if($_SESSION['User']['flag']==2 or $_SESSION['User']['flag']==5) {?><td width=180 onclick="start_tickets(1);return false;" align=center id="selected_stat_td_1"><a href="javascript:;">Нераспределенные</a></td><?}?>
            <td width=180 onclick="start_tickets(2);return false;" align=center id="selected_stat_td_2"><a href="javascript:;">В работе</a></td>
            <td width=180 onclick="start_tickets(3);return false;" align=center id="selected_stat_td_3"><a href="javascript:;">На утверждении <div id="approval_count"></div></a></td>
            <td width=180 onclick="start_tickets(4);return false;" align=center id="selected_stat_td_4"><a href="javascript:;">Закрытые</a></td>
            <?if($_SESSION['User']['flag']!=4) {?><td width=180 onclick="search_toggler();return false;" align=center id="search_toggler"><a href="javascript:;" id="search_toggler_btn">Поиск</a></td><?}?>           
          </tr>
        </table>
      </div>    
    </td> 
  </tr> 
  <!---------/status selector--------->
<?if($_SESSION['User']['flag']!=4) {
  $sql_e1 = "SELECT id, value from dba.errors WHERE selectable=1";
  $res_e1 = $db->get($sql_e1);
  $sql_e2 = "SELECT id, value from dba.places_all order by value";
  $res_e2 = $db->get($sql_e2);
  $sql_e3 = "SELECT id, surname+' '+name+' '+patronymic as fio from dba.specialists WHERE exist = 1 and role_id != 4 order by surname";
  $res_e3 = $db->get($sql_e3);  
  ?>   
  <!---------ticket search form--------->
  <tr id="search_form" style="display:none;">
    <td width=100% height=25 class="menu_td">
      <div style="margin-top:5px;">         
      <table class="menu_table search_table" cellspacing=0 cellpadding=0 width=450>
        <tr>
          <td>Номер</td>
          <td>
            <input type="number" id="s_num" style="width:200px;" onchange="toggle_search_number(<?= $_SESSION['User']['flag']==1 ? "'for_spec_1'" : "" ?>);"><a href="javascript:;" onclick="toggle_search_number(<?= $_SESSION['User']['flag']==1 ? "'for_spec_2'" : "'true'" ?>);" class="btn" style="margin-left:10px">Очистить</a>
          </td>
        </tr>
        <tr>
          <td>Статус</td>
          <td>
            <select id="s_stat" style="width:200px;" onchange="toggle_search_stat();">
              <option value='0'>Все</option>
              <option value='1'>Нераспределенные</option>
              <option value='2'>В работе</option>
              <option value='3'>На утверждении</option>
              <option value='4'>Завершенные</option>
            </select><a href="javascript:;" onclick="toggle_search_stat(true);" class="btn" style="margin-left:10px">Очистить</a>
          </td>
        </tr>        
        <tr>
          <td>Тип ошибки</td>
          <td>
            <select id="s_error" style="width:200px;">
              <option value='0'>Все</option>
              <?
              foreach($res_e1 as $k=>$v) {
                echo "<option value='".$v['id']."'>".strToRu($v['value'])."</option>";
              }
              ?>
            </select><a href="javascript:;" onclick="$('#s_error :nth-child(1)').prop('selected', true);" class="btn" style="margin-left:10px">Очистить</a>            
          </td>
        </tr>
        <tr>
          <td>Место ошибки</td>
          <td>
            <select id="s_place" style="width:200px;">
              <option value='0'>Все</option>
              <?
              foreach($res_e2 as $k=>$v) {
                echo "<option value='".$v['id']."'>".strToRu($v['value'])."</option>";
              }
              ?>
            </select><a href="javascript:;" onclick="$('#s_place :nth-child(1)').prop('selected', true);" class="btn" style="margin-left:10px">Очистить</a>
          </td>
        </tr>
        <tr>
          <td>Специалист</td>
          <td>     
            <select id="s_spec" style="width:200px;" <?= $_SESSION['User']['flag']==1 ? " disabled='disabled'" : "" ?> >        
              <option value='0'>Все</option>
              <?
              foreach($res_e3 as $k=>$v) {
                echo "<option value='".$v['id']."' ";
                if($_SESSION['User']['flag']==1 && $v['id']==$_SESSION['User']['id']) {
                  echo "selected";
                }                
                echo ">".strToRu($v['fio'])."</option>"; 
              } 
              ?>
            </select><?if($_SESSION['User']['flag']!=1) {?><a href="javascript:;" onclick="$('#s_spec :nth-child(1)').prop('selected', true);" class="btn" style="margin-left:10px">Очистить</a><?}?>
          </td>
        </tr>
        <tr>        
          <td>Дата создания</td>
          <td>от 
          <input type="hidden" name="date" id="this_date" value="<?=date("d.m.Y")?>" />
          <input class="date" id="open_date_0" type="text" name="open_date[]" onclick="toggle_calendar( this, event )" style="width:79px;" value="<?=date("Y-m-d")?>"> до
          <input class="date" id="open_date_1" type="text" name="open_date[]" onclick="toggle_calendar( this, event )" style="width:78px;"><a href="javascript:;" onclick="$('#open_date_0').val('');$('#open_date_1').val('');" class="btn" style="margin-left:10px">Очистить</a>
          </td>
        </tr>
        <tr>
          <td colspan=2 align=center>
            <br><a href="javascript:;" onclick="do_search();" class="btn">Поиск</a>
          </td>
        </tr>                                               
      </table>
      </div>         
    </td>
  </tr>
  <!---------/ticket search form--------->
<?}?>
  <!---------content--------->     
  <tr>
    <td valign=top><br> 
    
      <!--------page navigator------->          
      <div id="pager"></div>
      <!--------/page navigator-------> 
      <!--------tickets-------> 
      <div id="main_tickets"></div>  
      <!--------/tickets------->  
      
    </td>
  </tr>
  <!---------/content--------->
</table>
<!---------/main---------->
</body>
</html>

