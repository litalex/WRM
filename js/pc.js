/*****start and repeat autoreload works list****/  
if(window.use_auto_reload==1) {
  update = setTimeout(function(){
    select_works(window.stat_id);
  }, window.auto_reload_ms);
}
function resetTimer() {
  window.clearTimeout(update);
  update = setTimeout(function(){
    select_works(window.stat_id);
  }, window.auto_reload_ms);
}
/*****start and repeat autoreload works list****/
    
function select_works(stat) { 
  /**********set this stat in menu BOLD**********/
  window.stat_id = stat;
  load(1);
  $('#main_stat_selector td').each(function(){
    $(this).css("background-color", "#00aba9");
  });         
  $("#selected_stat_td_"+stat).css("background-color", "#007270");
  /**********set this stat in menu BOLD**********/ 

  var str="";
  var dates=[];  
  var monthSelected = $("#selected_month option:selected" ).val();
  var yearSelected = $("#selected_year").val();
  var day_selected_for_daily = $('#selected_day').val();
  var max_days_inSelected = daysInMonth(monthSelected, yearSelected);   
  monthSelected++;   
  if(monthSelected<10) {
    monthSelected="0"+monthSelected;
  }
  if(window.is_daily) {
    if(day_selected_for_daily<10) {
      day_selected_for_daily="0"+day_selected_for_daily;
    }  
    var from = yearSelected+"-"+monthSelected+"-"+day_selected_for_daily+" 00:00:00";
    var to = yearSelected+"-"+monthSelected+"-"+day_selected_for_daily+' 23:59:59.999'; 
  } else {
    var from = yearSelected+"-"+monthSelected+"-01 00:00:00";
    var to = yearSelected+"-"+monthSelected+"-"+max_days_inSelected+' 23:59:59.999'; 
  }
  $.getJSON('/json.php?action=get_spec_works&w_s='+window.stat_id+'&from='+from+'&to='+to+'&ord='+window.ord, function(json){
    if(json.response!=100) {
      var header_tr_names = $( "#calendar4 tr").eq(1).html();
      $("#calendar4 tbody").remove();
      $("#calendar4").append("<tbody>"+header_tr_names+"</tbody>");
      $("#calendar4 tbody").append('<tr><td colspan=33>'+json.msg+'</td></tr>');
    } else { 
      $.each(json.works, function(i, row) {
        dates.push(row);        
      });   
      Calendar("calendar4",$("#selected_year").val(),$("#selected_month").val(),dates,day_selected_for_daily);      
    } 
    load(0);      
  });
}


function daysInMonth(iMonth, iYear)
{
    return 32 - new Date(iYear, iMonth, 32).getDate();
}


function Calendar(id, year, month, dates, day) {

  /*****start and repeat autoreload works list****/
  if(window.use_auto_reload==1) { 
    resetTimer();
  }
  /*****start and repeat autoreload works list****/ 
     
  if(day==undefined) {
    day = new Date().getDate();
  }                                        
  $('#'+id+' tbody').html("");
  var calendar="<tr>";
  calendar+="<td class='name_of_col_works'><b>Задание ";
  if(window.ord!=2) {
    calendar += '&nbsp;<i class="fa fa-sort-asc" onclick=\'window.ord=2;select_works('+window.stat_id+');\' style="cursor:pointer;"></i></td>';
  } else {
    calendar +=  '&nbsp;<i class="fa fa-sort-desc" onclick=\'window.ord=1;select_works('+window.stat_id+');\' style="cursor:pointer;"></i></td>';
  }  
  calendar+="</b></td><td width=40 class='name_of_col_works' onmouseover=\"tooltip(this,'Номер прикрепленной заявки');\" onmouseout=\"hide_info(this);\"><i class=\"fa fa-clipboard\"></i>";
  if(window.ord!=3) {
    calendar += '&nbsp;<i class="fa fa-sort-asc" onclick=\'window.ord=3;select_works('+window.stat_id+');\' style="cursor:pointer;"></i></td>';
  } else {
    calendar +=  '&nbsp;<i class="fa fa-sort-desc" onclick=\'window.ord=4;select_works('+window.stat_id+');\' style="cursor:pointer;"></i></td>';
  }    
    
  if(window.is_daily) {
    /**************FOR DAY***************/   
    for(var  i = 0; i <= 23; i++) {
      if(i<10) {
        i = "0"+i;
      }
      if (i == new Date().getHours() && day == new Date().getDate() && year == new Date().getFullYear() && month == new Date().getMonth()) {
        calendar += '<td width=33 class="today">' + i + ':00</td>';               // now hour
      }else{  
        calendar += '<td width=33 style="color:black;font-size:12px;">' + i + ':00</td>';
      }
    }
    /**************FOR DAY***************/
  } else {
    /**************FOR MONTH***************/
    var WeekDay = ["<span style='color:red;'>Вс</span>","Пн","Вт","Ср","Чт","Пт","<span style='color:red;'>Сб</span>"];
    Date.prototype.getWeek = function () {
        var target  = new Date(this.valueOf());
        var dayNr   = (this.getDay() + 6) % 7;
        target.setDate(target.getDate() - dayNr + 3);
        var firstThursday = target.valueOf();
        target.setMonth(0, 1);
        if (target.getDay() != 4) {
            target.setMonth(0, 1 + ((4 - target.getDay()) + 7) % 7);
        }
        return 1 + Math.ceil((firstThursday - target) / 604800000);
    }
    
    
    var Dlast = new Date(year,parseFloat(month)+1,0).getDate(),
        D = new Date(year,month,Dlast),
        DNlast = D.getDay(),
        DNfirst = new Date(D.getFullYear(),D.getMonth(),1).getDay();
        
           
    if (new Date(D.getFullYear(),D.getMonth(),1).getWeek() < 10) {
      weekNum = '0' + new Date(D.getFullYear(),D.getMonth(),1).getWeek();
    }else{
      weekNum = new Date(D.getFullYear(),D.getMonth(),1).getWeek();
    }   
    for(var  i = 1; i <= Dlast; i++) {
    
      currD = new Date(year,month,i);  //  получаем пн, вт, ср, чт, пт, сб, вс
      dOfW = WeekDay[currD.getDay()];  //  получаем пн, вт, ср, чт, пт, сб, вс
      
      if (i == new Date().getDate() && D.getFullYear() == new Date().getFullYear() && D.getMonth() == new Date().getMonth()) {
        calendar += '<td width=33 class="today">'+dOfW+'<br><b>' + i + '</b>';               // сегодня
      }else{
        if (
            (i == 1 && D.getMonth() == 0 && ((D.getFullYear() > 1897 && D.getFullYear() < 1930) || D.getFullYear() > 1947)) ||
            (i == 2 && D.getMonth() == 0 && D.getFullYear() > 1992) ||
            ((i == 3 || i == 4 || i == 5 || i == 6 || i == 8) && D.getMonth() == 0 && D.getFullYear() > 2004) ||
            (i == 7 && D.getMonth() == 0 && D.getFullYear() > 1990) ||
            (i == 23 && D.getMonth() == 1 && D.getFullYear() > 2001) ||
            (i == 8 && D.getMonth() == 2 && D.getFullYear() > 1965) ||
            (i == 1 && D.getMonth() == 4 && D.getFullYear() > 1917) ||
            (i == 9 && D.getMonth() == 4 && D.getFullYear() > 1964) ||
            (i == 12 && D.getMonth() == 5 && D.getFullYear() > 1990) ||
            (i == 7 && D.getMonth() == 10 && (D.getFullYear() > 1926 && D.getFullYear() < 2005)) ||
            (i == 8 && D.getMonth() == 10 && (D.getFullYear() > 1926 && D.getFullYear() < 1992)) ||
            (i == 4 && D.getMonth() == 10 && D.getFullYear() > 2004)
           ) {
          calendar += '<td width=33 style="color:red;">'+dOfW+'<br>' + i;      // гос праздники
        }else{
          calendar += '<td width=33 style="color:black;">'+dOfW+'<br>' + i;    // обычные дни
        }
      }
      calendar += '</td>';
    }
    /**************FOR MONTH***************/
  }
  calendar += '</tr>';  
  for(var  w = 0; w <= (dates.length-1); w++) {
      var color = "";
      switch (dates[w].stat_int) {
        case '1':
          color = "rgb(192, 53, 50)";
          break
        case '2':
          color = "blue";
          break
        case '3':
          color = "green";
          break
        default:
          color = "black";
          break         
      }     
      var new_comment = dates[w].main_comment;
      var comment_length = 0; 
      if(window.is_daily) {
        comment_length = window.comment_length_megabig;
      } else {
        if(dates[w].stat_int==1) {
          comment_length = window.comment_length_small;
        } else {
          comment_length = window.comment_length_big;
        }
      }
      if(dates[w].main_comment.length>comment_length) {
        new_comment = dates[w].main_comment.substring(0, comment_length)+"...";
      }
      calendar += '<tr>';
      calendar += '<td height=30 id="name_of_col_work_'+dates[w].w_id+'" class="name_of_row" onmouseover="toggle_work_row_bg('+dates[w].w_id+', true);" onmouseout="toggle_work_row_bg('+dates[w].w_id+', false);">';
      //toggle_ticket_row_bg('+row.t_id+', true);
      /**********FAST STAT**********/
      calendar += '<table width=100% height=100%><tr>';
      if(dates[w].stat_int==1 && window.show_one_work==false) {
        if(dates[w].wothout_stop==false || dates[w].wothout_stop == undefined) {
          calendar += '<td width=25 style="border:0px;width:25px;padding-left:3px;"><a href="javascript:;" onclick="add_timeline_auto('+dates[w].w_id+');" style="color:#1ba1e2;" onmouseover="tooltip(this,\'Приступить\');" onmouseout="hide_info(this);"><i class="fa fa-play"></i></a></td>';
        }
        if(dates[w].wothout_stop==true) {
          calendar += '<td width=25 style="border:0px;width:25px;padding-left:3px;"><a href="javascript:;" onclick="freeze_work('+dates[w].w_id+');" style="color:#1ba1e2;" onmouseover="tooltip(this,\'Приостановить\');" onmouseout="hide_info(this);"><i class="fa fa-pause"></i></a></td>';
        }
        if(dates[w].wothout_stop != undefined) {
          calendar += '<td width=25 style="border:0px;width:25px;"><a href="javascript:;" onclick="close_work('+dates[w].w_id+');" style="color:#1ba1e2;" onmouseover="tooltip(this,\'Завершить\');" onmouseout="hide_info(this);"><i class="fa fa-stop"></i></a></td>';
        }
      }
      /**********FAST STAT**********/
      calendar += '<td style="border:0px;text-align:center;" onclick="work_info('+dates[w].w_id+', -1);return true;" onmouseover="tooltip(this,\''+escape(dates[w].main_comment)+'\');" onmouseout="hide_info(this);">'+new_comment+'</td>';
      /*******if this work have a ticket, show icon*******/
      //calendar += '<td width=25 style="border:0px;width:25px;" onclick="work_info('+dates[w].w_id+', -1);return true;" ';
      if(dates[w].ticket_id) {    //if this work have a ticket
        //calendar += '>';
      } else {                    //if this work DON'T have a ticket
        //calendar += ' onmouseover="tooltip(this,\''+escape(dates[w].main_comment)+'\');" onmouseout="hide_info(this);">&nbsp;';
      }
      
      if(pf(dates[w].msgs)==1) {
        calendar += '<td style="border:0px;padding:0px;margin:0px;"><i class="fa fa-envelope-o" style="color: E1052A;display:inline;" onmouseover="tooltip(this,\'Непрочитанное сообщение в переписке заявки\');" onmouseout="hide_info(this);"></i></td>';
      }      
      //<i class="fa fa-clipboard"></i></td>';
      
      /*******if this work have a ticket, show icon*******/
      calendar += '</td></tr></table>';      
      calendar += '</td>';
      
      /*******ticket number column*******/
      if(dates[w].ticket_id && dates[w].ticket_id!='' && dates[w].ticket_id!=' ' && dates[w].ticket_id>0) {
        calendar += '<td height=30 width=40 class="name_of_row" id="name_of_col_work2_'+dates[w].w_id+'" onclick="work_info('+dates[w].w_id+', -1);return true;" onmouseover="toggle_work_row_bg('+dates[w].w_id+', true);tooltip(this,\'<b>'+dates[w].place+'</b><br>Прикреплена заявка № '+dates[w].ticket_id+'\');" onmouseout="toggle_work_row_bg('+dates[w].w_id+', false);hide_info(this);">'+dates[w].ticket_id+'</td>';
      } else {
        calendar += '<td height=30 width=40 class="name_of_row" id="name_of_col_work2_'+dates[w].w_id+'" onclick="work_info('+dates[w].w_id+', -1);return true;" onmouseover="toggle_work_row_bg('+dates[w].w_id+', true);tooltip(this,\''+escape(dates[w].main_comment)+'\');" onmouseout="toggle_work_row_bg('+dates[w].w_id+', false);hide_info(this);"></td>';
      }
      /*******ticket number column*******/
      
      if(window.is_daily) {
        /**************FOR DAY***************/      
        for(var  hour = 0; hour <= 23; hour++) { 
          var is_event = false; 
          var event_int = '';
          if(dates[w].timestamps[0].start.date!=undefined) {
            for(var  t = 0; t <= (dates[w].timestamps.length-1); t++) { 
            
            otherdatesW=new Date(
              dates[w].timestamps[t].start.date[0],
              (dates[w].timestamps[t].start.date[1]-1),
              dates[w].timestamps[t].start.date[2], 
              dates[w].timestamps[t].start.time[0], 
              dates[w].timestamps[t].start.time[1], 
              dates[w].timestamps[t].start.time[2]
            );
            otherDate2W=new Date(
              dates[w].timestamps[t].stop.date[0],
              (dates[w].timestamps[t].stop.date[1]-1),
              dates[w].timestamps[t].stop.date[2], 
              dates[w].timestamps[t].stop.time[0], 
              dates[w].timestamps[t].stop.time[1], 
              dates[w].timestamps[t].stop.time[2]
            );
            iDateW=new Date(year, month, day, hour, 0, 0);
            delta1W=iDateW.getTime()-otherdatesW.getTime();
            delta2W=iDateW.getTime()-otherDate2W.getTime(); 
            //console.log(delta1W + "|" + hour +"|"+iDateW.getTime()+"|"+otherdatesW.getTime()); 
              if((delta1W>=0 || delta1W>-3599999) && delta2W<=0) {         //поправка на неполный час для его закрашивания
                is_event = true;
                event_int = t;   
              } 
            } 
          } 
          if(is_event) {
            calendar += '<td height=30 width=30 style="background-color:'+color+';cursor:pointer;" onclick="work_info('+dates[w].w_id+', '+dates[w].timestamp_ids[event_int]+');return true;">&nbsp;</td>';
          }  else {
            if (hour == new Date().getHours() && day == new Date().getDate() && year == new Date().getFullYear() && month == new Date().getMonth()) {
              calendar += '<td height=30 width=30 style="background-color:#D9F0FD;">&nbsp;</td>';
            } else {
              calendar += '<td height=30 width=30 >&nbsp;</td>';
            }
          }
        }
        /**************FOR DAY***************/
      } else {
        /**************FOR MONTH***************/
        for(var  day = 1; day <= Dlast; day++) { 
          var is_event = false; 
          var event_int = '';
          if(dates[w].timestamps[0].start.date!=undefined) {
            for(var  t = 0; t <= (dates[w].timestamps.length-1); t++) { 
                   
            otherdatesW=new Date(
              dates[w].timestamps[t].start.date[0],
              (dates[w].timestamps[t].start.date[1]-1),
              dates[w].timestamps[t].start.date[2]
            );
            otherDate2W=new Date(
              dates[w].timestamps[t].stop.date[0],
              (dates[w].timestamps[t].stop.date[1]-1),
              dates[w].timestamps[t].stop.date[2]
            );
            iDateW=new Date(year,month,day);
            delta1W=iDateW.getTime()-otherdatesW.getTime();
            delta2W=iDateW.getTime()-otherDate2W.getTime();    
              if(delta1W>=0 && delta2W<=0) {
                is_event = true;
                event_int = t;
                   
              } 
            } 
          } 
          if(is_event) {
            calendar += '<td height=30 width=30 style="background-color:'+color+';cursor:pointer;" onclick="work_info('+dates[w].w_id+', '+dates[w].timestamp_ids[event_int]+');return true;">&nbsp;</td>';
          }  else {
            if (day == new Date().getDate() && D.getFullYear() == new Date().getFullYear() && D.getMonth() == new Date().getMonth()) {
              calendar += '<td height=30 width=30 style="background-color:#D9F0FD;">&nbsp;</td>';
            } else {
              calendar += '<td height=30 width=30 >&nbsp;</td>';
            }
          }
        }   
        /**************FOR MONTH***************/
      }
      calendar += '</tr>';
  }
  $('#'+id+' tbody').html(calendar); 
}

/*******CLICK LISTENERS*******/    
$("#selected_year").change(function() {
  select_works(window.stat_id);
});
$("#selected_month").change(function() {
  select_works(window.stat_id);
});
$("#selected_day").change(function() {
  select_works(window.stat_id);
});
/*******CLICK LISTENERS*******/

function toggle_work_row_bg(id, trigger) {
  if($("#popup_title").is(':hidden')) {
    if (trigger==true) {
      $('#name_of_col_work_'+id).css('background-color','#83DBDB');
      $('#name_of_col_work2_'+id).css('background-color','#83DBDB');
    } else {
      $('#name_of_col_work_'+id).css('background-color','#D9F0FD');
      $('#name_of_col_work2_'+id).css('background-color','#D9F0FD');
    }  
  } 
}