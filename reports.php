<?
header('Content-Type: text/html; charset=utf-8');
ini_set('log_errors', true);
ini_set('error_log', 'error_json.log');                 
session_start();
if(empty($_SESSION['User']['id'])) {  
  header('Location: /'); 
  exit();
} else {
  $spec_id = $_SESSION['User']['id'];
  $spec_fio = $_SESSION['User']['fio'];
}

 
/****************************************/
require_once( 'const.php' );
require_once( 'DB.php' );
$db = new DBWrapper( SQLCONNECTSTRING );

$sitePREFIX = ""; 
date_default_timezone_set('Etc/GMT-3');

$show_busy_in_hours = true;

function to_tuple($x) {
      global $show_busy_in_hours;
      $Fh = floor($x / (60*60*1000));          // целых часов
      $t1 = $x - ($Fh*(60*60*1000));           // остатот от целых часов
      $Fm = floor($t1 / (60*1000));            // целых минут
      if($Fh<24 or $show_busy_in_hours==true) { 
        return  $Fh."ч, ".$Fm."м";
      } else {
        $Fd = floor($Fh/24);                   // целых дней
        $newX = ($x - ($Fd*24*60*60*1000));
        $Fh = floor($newX / (60*60*1000));     // целых часов
        $t1 = $newX - ($Fh*(60*60*1000));      // остатот от целых часов
        $Fm = floor($t1 / (60*1000));          // целых минут
        return  $Fd."д, ".$Fh."ч, ".$Fm."м";
      }
      
}    
?>

<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <title>Общая статистика</title>
    <script type='text/javascript' src='//code.jquery.com/jquery-1.9.1.js'></script>
    <script type='text/javascript' src="http://ajax.aspnetcdn.com/ajax/globalize/0.1.1/globalize.min.js"></script>
    <script type='text/javascript' src="http://cdn3.devexpress.com/jslib/13.1.5/js/dx.chartjs.js"></script>

    <script type='text/javascript'>
    $(window).load(function(){ 
         
      var dataSourcePlaces = [<?$sql = "SELECT e2.value as name, (SELECT COUNT(*) FROM dba.tickets i WHERE i.place_id = j.id) AS count FROM dba.places_all j left outer join dba.places_all e2 on e2.id = j.id group by name, count order by count DESC"; 
      $res = $db->get($sql);
      $countsPlace = Array();
      $allPlaces = Array();
      $cc = 0 ;
      foreach($res as $k=>$v) {
        $countsPlace[] = "\r\n".'{ category: "'.iconv("windows-1251", "UTF-8", $v['name']).'", number: '.$v['count'].' }';
        $allPlaces[] = Array(iconv("windows-1251", "UTF-8", $v['name']), $v['count']);       
      }    
      echo implode(",", $countsPlace);
      unset($countsPlace);
      ?>];
     /* $("#chartPlaceContainer").dxPieChart({
          dataSource: dataSourcePlaces,
          series: {
              type: "donut",
              argumentField: "category",
              valueField: "number",
              innerRadius: 0.5,
              hoverMode: "none"
          },
          legend: {
              verticalAlignment: "bottom",
              horizontalAlignment: "center",
              equalColumnWidth: true
          },
          tooltip: {
              enabled: true,
              customizeText: function () {
                  return this.argumentText + "<br/>"
                  + this.percentText + " (" + this.valueText + ")";
              }
          }    
      }); */
      
      
      
      
      var dataSourceErrors = [<?
      $sql = "SELECT e2.value as name, (SELECT COUNT(*) FROM dba.tickets i WHERE i.error_id = j.error_id) AS count FROM dba.tickets j left outer join dba.errors e2 on e2.id = j.error_id group by name, count order by count DESC"; 
      $res = $db->get($sql);
      $countsError = Array();
      $allErrors = Array();
      foreach($res as $k=>$v) {
        $countsError[] = "\r\n".'{ category: "'.iconv("windows-1251", "UTF-8", $v['name']).'", number: '.$v['count'].' }';
        $allErrors[] = Array(iconv("windows-1251", "UTF-8", $v['name']), $v['count']);
      }
      echo implode(",", $countsError);
      unset($countsError);
      ?>];             
    
    var dataSourceCreators = [<?
      $sql = "SELECT (e2.surname+' '+e2.name+' '+e2.patronymic) as creator_fio, (SELECT COUNT(*) FROM dba.tickets i WHERE i.creator_id = j.creator_id) AS count FROM dba.tickets j left outer join dba.specialists e2 on e2.id = j.creator_id group by creator_fio, count order by count DESC"; 
      $res = $db->get($sql);
      $countsCreators = Array();
      $allCreators = Array();
      foreach($res as $k=>$v) {
        $countsCreators[] = "\r\n".'{ category: "'.iconv("windows-1251", "UTF-8", $v['creator_fio']).'", number: '.$v['count'].' }';
        $allCreators[] = Array(iconv("windows-1251", "UTF-8", $v['creator_fio']), $v['count']);
      }
      echo implode(",", $countsCreators);
      unset($countsCreators);
      ?>];      
      
    var dataSourceSpecs = [<?
      //$sql = "SELECT (j.surname+' '+j.name+' '+j.patronymic) as spec_fio, (SELECT COUNT(*) FROM dba.tickets i WHERE i.spec_id = j.id) AS count FROM dba.specialists j left outer join dba.specialists e2 on e2.id = j.id where j.role_id = 1 or j.role_id = 5 group by spec_fio, count order by count DESC";
      $sql = "SELECT (j.surname+' '+j.name+' '+j.patronymic) as spec_fio, (SELECT count(distinct(t0.ticket_id)) FROM dba.ticket_hist t0 where t0.work_id in (SELECT t1.id FROM dba.works t1 where t1.spec_id = j.id)) AS count FROM dba.specialists j left outer join dba.specialists e2 on e2.id = j.id where j.role_id = 1 or j.role_id = 5 group by spec_fio, count order by count DESC"; 
      $res = $db->get($sql);
      $countsSpecs = Array();
      $allSpecsTickets = Array();
      foreach($res as $k=>$v) {
        $countsSpecs[] = "\r\n".'{ category: "'.iconv("windows-1251", "UTF-8", $v['spec_fio']).'", number: '.$v['count'].' }';
        $allSpecsTickets[] = Array(iconv("windows-1251", "UTF-8", $v['spec_fio']), $v['count']);
      }
      echo implode(",", $countsSpecs);
      unset($countsSpecs);
      ?>];           

    var dataSourceSpecTimes = [<?
      $from = '2015-02-01 00:00:00';
      //$to = '2015-02-20 23:59:59';
      $to = date("Y-m-d H:i:s");
      $sql_wt = "SELECT dba.get_work_time('".$from."','".$to."');";
      $res_wt = $db->get($sql_wt);                               
      $sql = "SELECT 
                (j.surname+' '+j.name+' '+j.patronymic) as spec_fio, 
                (select sum(cast(DATEDIFF(millisecond, 
                  (
                      CASE
                          WHEN from_dt between '".$from."' and '".$to."' THEN from_dt 
                          WHEN from_dt > '".$to."' THEN '".$to."' 
                          WHEN from_dt < '".$from."' THEN '".$from."' END
                  ), 
                  (
                      CASE
                          WHEN to_dt between '".$from."' and '".$to."' THEN to_dt 
                          WHEN to_dt > '".$to."' THEN '".$to."' 
                          WHEN to_dt < '".$from."' THEN '".$from."' END
                  )
                ) as bigint)) as ss 
                from dba.work_hist 
                where work_id in (SELECT id FROM dba.works where spec_id = j.id) 
                and to_dt is not null) AS count,
                e2.id as s_id 
              FROM dba.specialists j 
              left outer join dba.specialists e2 on e2.id = j.id where j.role_id = 1 or j.role_id = 5 
              group by spec_fio, count, s_id 
              order by count DESC"; 
              
      $res = $db->get($sql);
      $countsSpecTimes = Array();
      $allSpecsTimes = Array();
      foreach($res as $k=>$v) {
        if(!isset($v['count']) or empty($v['count'])) {
          $v['count'] = 0;
        } 
        $countsSpecTimes[] = "\r\n".'{ category: "'.iconv("windows-1251", "UTF-8", $v['spec_fio']).'", number: '.$v['count'].' }';
        $allSpecsTimes[] = Array(iconv("windows-1251", "UTF-8", $v['spec_fio']), $v['count'], $v['s_id']);
      }
      echo implode(",", $countsSpecTimes);
      unset($countsSpecTimes);  
      ?>];           
                    
    }); 
        
    function toggle_empty_rows(table_id, rel) {
      $('#'+table_id+" tr[rel="+rel+"]").each(function(){
        if($(this).is(':hidden')) {
          $(this).show();  
        } else {
          $(this).hide();
        }
      });
      if($("#toggle_empty_rows_button_"+table_id).html()=="Показать все") {
        $("#toggle_empty_rows_button_"+table_id).html('Свернуть');
      } else {
        $("#toggle_empty_rows_button_"+table_id).html('Показать все');
      }      
    }
    </script>
    <style>
    .info_table {
      border-collapse: collapse;
    }
    .info_table td{
      border: 1px solid #7EACB1;
    }
    .info_table_big {
      border-collapse: collapse;
    }   
    </style>
</head>
<body>
  <center><h2>Общая статистика по специалистам, пользователям, заявкам и ошибкам</center></h2><br>
  <table style="width:100%;">
    <tr>
      <td width=100% valign=top style="padding-left:10px;" colspan=3>
        
        <!-----------------------------------> 
        <div style="width:100%;height:30px;line-height:30px;background-color:#a2d9f7;text-align:center;">Статистика специалистов</div>
        <table style="width:100%;" class="info_table_big" id="creators_table">
          <tr>
            <td>
            
              <table style="width:100%;" class="info_table" id="creators_table">
                <tr style="background-color:#D9EDF9;">
                  <td colspan=2 align=center>Общее количество заявок</td>
                </tr>              
                <tr style="background-color:#D9EDF9;">
                  <td>ФИО</td><td>Кол-во</td>
                </tr>  
                <?
                  foreach($allSpecsTickets as $v) {
                    echo "<tr><td>".$v[0]."</td><td>".$v[1]."</td></tr>";
                  }            
                ?>     
              </table>
                     
            </td>
            <td>
            
              <table style="width:100%;" class="info_table" id="creators_table">
                <tr style="background-color:#D9EDF9;">
                  <td colspan=4 align=center>Рабочих часов: <b><?=floor($res_wt[0][0]/60/60/1000)?></b> с <?=$from?> по <?=$to?></td>
                </tr>              
                <tr style="background-color:#D9EDF9;">
                  <td>ФИО</td><td>всего</td><td>в рабочее</td><td>сверхурочно</td>
                </tr>              
                <?
                
                
                $sql1 = "select id from dba.specialists where role_id!=4";
                $res1 = $db->get($sql1);
                //print_r($res1);
                $all_timelines = Array();
                foreach($res1 as $k1=>$v1) {
                  $all_timelines[$res1[$k1]['id']]['w'] = 0;
                  $all_timelines[$res1[$k1]['id']]['a'] = 0;
                  $sqlT = "select (CASE
                                      WHEN from_dt between '".$from."' and '".$to."' THEN from_dt 
                                      WHEN from_dt > '".$to."' THEN '".$to."' 
                                      WHEN from_dt < '".$from."' THEN '".$from."' END
                              ) as from_dt, 
                              (
                                  CASE
                                      WHEN to_dt between '".$from."' and '".$to."' THEN to_dt 
                                      WHEN to_dt > '".$to."' THEN '".$to."' 
                                      WHEN to_dt < '".$from."' THEN '".$from."' END
                              ) as to_dt FROM dba.work_hist where work_id in (select id from dba.works where spec_id = ".$res1[$k1]['id'].") and to_dt is not null order by from_dt";
                  $resT = $db->get($sqlT);
                  foreach($resT as $k2=>$v2) {
                    if($resT[$k2]['from_dt']!=$resT[$k2]['to_dt']) { 
                      $sql_wt_t = "SELECT dba.get_work_time('".$resT[$k2]['from_dt']."','".$resT[$k2]['to_dt']."');";
                      $res_wt_t = $db->get($sql_wt_t);
                      $all_timelines[$res1[$k1]['id']]['w'] += $res_wt_t[0][0];       //just in work time
                      //$all_timelines[$res1[$k1]['id']]['a'] += floor((((strtotime($resT[$k2]['to_dt'])*1000) - (strtotime($resT[$k2]['from_dt'])*1000))/1000/60));    //ALL TIME in minutes
                      $all_timelines[$res1[$k1]['id']]['a'] += floor((strtotime($resT[$k2]['to_dt'])*1000) - (strtotime($resT[$k2]['from_dt'])*1000));    //ALL TIME in MILLISECONDS
                    }                    
                  }
                }
                //asort($all_timelines);
                ///print_r($all_timelines);
                
                
                
                  //foreach($allSpecsTickets as $v) {
                   // echo "<tr><td>".$v[0]."</td><td>".$v[1]."</td></tr>";
                  //}   
                  
                  
                  
                                  
                
                
                
                  foreach($allSpecsTimes as $v) {
                    //echo "<tr><td>".$v[0]."</td><td>".to_tuple($v[1])."</td></tr>";
                    echo "<tr><td>".$v[0]."</td><td>";
                    //.to_tuple($v[1]).
                    
                    if((floor($v[1] / (60*60*1000))) > ($res_wt[0][0]/60/60/1000)) {
                      echo  "<font color=green>";
                    } else {
                      echo  "<font color=red>";
                    }
                    echo to_tuple($v[1])."</font>";
                    
                   // echo "</td><td>".to_tuple($all_timelines[$v[2]]['a'])."</td><td>".to_tuple($all_timelines[$v[2]]['w'])."</td></tr>";
                   
                    echo "</td><td>";
                    if($all_timelines[$v[2]]['w'] > $res_wt[0][0]) {
                      echo  "<font color=green>";
                    } else {
                      echo  "<font color=red>";
                    }                    
                    echo to_tuple($all_timelines[$v[2]]['w'])."</font></td><td>".to_tuple($all_timelines[$v[2]]['a']-$all_timelines[$v[2]]['w'])."</td></tr>";
                  }            
                ?>     
              </table> 
              
            </td>
            <!--<td>
            
              <table style="width:100%;" class="info_table" id="creators_table">
                <tr style="background-color:#D9EDF9;">
                  <td>ФИО</td><td>время</td>
                </tr>
                <tr><td><pre>              
                <?
         
                ?> 
                </pre></td><td></td></tr>    
              </table>          
            </td>   -->
          </tr>
        </table>
        <!-----------------------------------> 
        <br><br>
      </td>
    </tr>  
    <tr>
      <td width=33% valign=top style="padding-left:10px;">
        <!----------------------------------->  
        <div style="width:100%;height:30px;line-height:30px;background-color:#a2d9f7;text-align:left;">&nbsp;&nbsp;Места ошибок</div>
        <div style="width:100%;">
          <table style="width:100%;" class="info_table" id="places_table">
            <tr style="background-color:#D9EDF9;">
              <td>Наименование</td><td>Кол-во</td>
            </tr>
            <? 
            $all_count = 0;
            foreach($allPlaces as $v) {
              if($all_count<20) {                          
                echo "<tr><td>".$v[0]."</td><td>".$v[1]."</td></tr>";
                $all_count++;
              } else {
                echo "<tr style='display:none' rel='more'><td>".$v[0]."</td><td>".$v[1]."</td></tr>";
              }
            }
            if($all_count<sizeof($allPlaces)) {
              echo "<tr><td>Скрытые элементы<br><a id='toggle_empty_rows_button_places_table' href='javascript:;' onclick='toggle_empty_rows(\"places_table\", \"more\");'>Показать все</a></td><td>".(sizeof($allPlaces)-$all_count)."</td></tr>";  
            }
            ?>
          </table>                   
          <div id="chartPlaceContainer" style="width:100%;height:100%"></div>
        </div>
       <!-----------------------------------> 

      </td>
      <td width=33% valign=top style="padding-left:10px;">
      
        <!-----------------------------------> 
        <div style="width:100%;height:30px;line-height:30px;background-color:#a2d9f7;text-align:left;">&nbsp;&nbsp;Типы ошибок</div>
        <div style="width:100%;">
          <table style="width:100%;" class="info_table">
            <tr style="background-color:#D9EDF9;">
              <td>Наименование</td><td>Кол-во</td>
            </tr>
            <?
            foreach($allErrors as $v) {
              echo "<tr><td>".$v[0]."</td><td>".$v[1]."</td></tr>";
            }
            ?>
          </table>                   
          <div id="chartErrorContainer" style="width:100%;height:100%"></div>
        </div>
        <!-----------------------------------> 
        
      </td>
      <td width=33% valign=top style="padding-left:10px;">
      
        <!-----------------------------------> 
        <div style="width:100%;height:30px;line-height:30px;background-color:#a2d9f7;text-align:left;">&nbsp;&nbsp;Создатели заявок</div>
        <div style="width:100%;">
          <table style="width:100%;" class="info_table" id="creators_table">
            <tr style="background-color:#D9EDF9;">
              <td>Наименование</td><td>Кол-во</td>
            </tr>
            <?
            $all_count = 0;
            foreach($allCreators as $v) {
              if($all_count<20) {                          
                echo "<tr><td>".$v[0]."</td><td>".$v[1]."</td></tr>";
                $all_count++;
              } else {
                echo "<tr style='display:none' rel='more'><td>".$v[0]."</td><td>".$v[1]."</td></tr>";
              }
            }
            if($all_count<sizeof($allCreators)) {
              echo "<tr><td>Скрытые элементы<br><a id='toggle_empty_rows_button_creators_table' href='javascript:;' onclick='toggle_empty_rows(\"creators_table\", \"more\");'>Показать все</a></td><td>".(sizeof($allCreators)-$all_count)."</td></tr>";  
            }
            ?>
          </table>                   
          <div id="chartPlaceContainer" style="width:100%;height:100%"></div>
        </div> 
        <!-----------------------------------> 
        
      </td>     
    </tr>     
  </table>
          
</body>


</html>


