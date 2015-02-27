/*****start and repeat autoreload tickets list****/
if(window.use_auto_reload==1) {
  update = setTimeout(function(){
    if(window.ticket_stat_id!=5) {
      start_tickets();
    }
  }, window.auto_reload_ms);
}
function resetTimer() {
  window.clearTimeout(update);
  update = setTimeout(function(){
    if(window.ticket_stat_id!=5) {
      start_tickets();
    }
  }, window.auto_reload_ms);
}
/*****start and repeat autoreload tickets list****/

/****************FIRST RUN*******************/
function start_tickets(stat) {                
  $("#pager").hide(); // default pager is hidden
  
  /***set status from incoming parameter & default page****/               
  if(stat!=undefined) {
    window.ticket_stat_id = stat;
    window.page=1;
  }                      
  /***set status from incoming parameter & default page****/

  /****set background of selected state*****/
  $('#main_stat_selector td').each(function(){
    $(this).css("background-color", "#00aba9");
  });
  $("#selected_stat_td_"+window.ticket_stat_id).css("background-color", "#007270");
  $("#search_form").hide(); // hide search form and selector 
  $("#search_toggler_btn").html('Поиск');  // default value for search toggle button
  /****set background of selected state*****/

  /****get approval count****/
  if(window.spec_flag==window.sotrudnik_flag_config) { 
    show_approval_count(true);   //true = can't create new ticket if have unclosed tickets
  } 
  if(window.spec_flag==window.dispatcher_flag_config) {
    show_approval_count();
  }
  /****get approval count just for user****/
  
  /****main ajax****/  
  load(1);                           
  $.getJSON('/json.php?action=get_tickets&t_s='+window.ticket_stat_id+'&page='+window.page+'&on_page='+window.max_tickets_on_page+"&spec_id="+window.spec_id+"&ord="+window.ord, function(json){
    load(0);
    if(json.response!=undefined) { 
      $(".main_table").css('height', '100%'); 
      if(json.response==100 && json.tickets!=undefined) {
        show_tickets(json.tickets);
      } else {
        $("#main_tickets").html('<div>'+json.msg+'</div>'); 
      } 
    } else {
      el.html('<div>Приняты или отправлены неправильные данные. Статус ответа неизвестен. </div>');    
    }  
    /*****start and repeat autoreload tickets list****/
    if(window.ticket_stat_id!=5 && window.use_auto_reload==1) {
      resetTimer();
    }
    /*****start and repeat autoreload tickets list****/
  }).error(function() { alert("Ошибка связи с сервером при запросе данных.", 4441); });
  /****main ajax****/
}
/****************FIRST RUN*******************/

//ok, let's go
/****************SHOW TICKETS****************/
function show_tickets(tickets,trigger) {
  var el = $("#main_tickets"),
  pager = $("#pager"),
  str = "",
  pager_str="";
  window.add_tickets_to_group = {};
  window.del_tickets_to_group = {};
  el.html('');
   
  /*******get count of pages *****/ 
  load(1);              
  $.getJSON('/json.php?action=get_tickets_pager&t_s='+window.ticket_stat_id, function(json){        // get count of pages
    load(0);   
    if(Math.ceil(json.count/window.max_tickets_on_page)>1) { 
      pager_str = makePager(json);  //get pager   
    } else {
      trigger = 'reset_pager';      //just hide pager if isset  
    }     
    /*******get count of pages *****/
        
    /****pager hide in search****/
    if(trigger==undefined) {  
      pager.html(pager_str).show();
    } else {
      pager.html('').hide();
      //window.ticket_stat_id = 1;
      window.page = 1;
    }
    /****pager hide in search****/
    
    str += '<table width=100% id="tickets_table"><tr class="table_head"><td width=40 align=center>№';
    if(window.ord!=2) {
      str += '&nbsp;<i class="fa fa-sort-asc" onclick=\'window.ord=2;start_tickets('+window.ticket_stat_id+');\' style="cursor:pointer;"></i></td>';
    } else {
      str += '&nbsp;<i class="fa fa-sort-desc" onclick=\'window.ord=1;start_tickets('+window.ticket_stat_id+');\' style="cursor:pointer;"></i></td>';
    }
    if(window.spec_flag!=window.sotrudnik_flag_config) {
      if(window.ticket_stat_id==1) {
        str += '<td style="padding-left:8px;text-align:center;"><i class="fa fa-users"></i></td>';
      }
      str += '<td width=250>Создатель</td><td width=250>Специалист</td>';
    }
    str += '<td width=140>Тип ошибки</td><td width=210>Путь к месту ошибки</td><td>Создано';
    if(window.ord!=4) {
      str += '&nbsp;<i class="fa fa-sort-asc" onclick=\'window.ord=4;start_tickets('+window.ticket_stat_id+');\' style="cursor:pointer;"></i></td>';
    } else {
      str += '&nbsp;<i class="fa fa-sort-desc" onclick=\'window.ord=3;start_tickets('+window.ticket_stat_id+');\' style="cursor:pointer;"></i></td>';
    }      
    if(window.ticket_stat_id==4) {
      str += '<td>Закрыто</td>';
    }    
    str +='</tr>';
    el.append(str);    
    
    //*****getting tickets in groups*******//
    var groupped_tickets = [], element = {};
    $.each(tickets, function(i, row) { 
      if(row.t_group!=0) {
        if(groupped_tickets[row.t_group]==undefined) {
          groupped_tickets[row.t_group] = [];
        }
        groupped_tickets[row.t_group].push(row);

        return true;     
      } else {
        var append_str = makeRow(row,0);
        $("#tickets_table").append(append_str);  
      }
    });
    //*****getting tickets in groups*******//


    $.each(groupped_tickets, function(i, group) { 
      if(group!=undefined) {
        var group_append_str = '<tr id="group_tr_'+i+'"><td colspan=7 id="group_td_'+i+'" style="padding:0px;">Группа №'+i+'</tr>';
        $("#tickets_table").append(group_append_str);
        $.each(group, function(r, row) {       
          var append_str = makeRow(row,row.t_group);
          $("#group_td_"+i).append(append_str);
        });
      }
    });
                                                   


    el.append('</table>');     
  });  
}

function makeRow(row,group) {                                                         //********************function for each row********************//
var append_str = '';
  if(group==0) {

  
      append_str += '<tr onclick="ticket_info('+row.t_id+');" style="cursor:pointer;';
      
      /****add attribute if ticket creator - is dispatcher or ticket blocked****/
      if(row.blocked==-1) {
        append_str += 'background-color: #FFCACA;" ticket_blocked=true';
      } else {     
        if(window.spec_flag==window.dispatcher_flag_config && window.spec_id == row.spec_i) {
          append_str += 'background-color: #E8FEFF;" creator_is_dispatcher="true"';
        } else {
          append_str += '"';
        }
      }  
      /****add attribute if ticket creator - is dispatcher or ticket blocked****/
               
      append_str += ' id="row_ticket_'+row.t_id+'" onmouseover="toggle_ticket_row_bg('+row.t_id+', true);" onmouseout="toggle_ticket_row_bg('+row.t_id+', false);">';
      
      
}  else {
  append_str += '<table width="100%" id="tickets_table"><tr>';  
}    
      append_str += '<td align=center width="40">'+row.t_id;
      if(pf(row.msgs)==1) {
        //append_str += ' <i class="fa fa-envelope-o"></i>';
      }
      append_str += '</td>';
      
      /****more columns for dispatcher*****/
      if(window.spec_flag!=window.sotrudnik_flag_config) {
        if(window.ticket_stat_id==1) {
          append_str += '<td onmouseover="toggle_tr_action('+row.t_id+', \'true\');" onmouseout="toggle_tr_action('+row.t_id+', \'false\');" style="cursor:default;padding-left:8px;text-align:center;">';
          if(row.blocked!=-1) {
            if(row.t_group==0) {
              append_str += '<input rel="checkbox_for_groups" type="checkbox" id="adding_ticket_to_group_'+row.t_id+'" onclick="toggler_adding_ticket_to_group('+row.t_id+');" style="cursor:pointer;">';
            } else {
             append_str += 'гр. '+row.t_group+'<br><i class="fa fa-times"></i> <input rel="checkbox_for_groups" type="checkbox" id="adding_ticket_to_group_'+row.t_id+'" onclick="toggler_deleting_ticket_to_group('+row.t_id+');" style="cursor:pointer;">';
            }
          } else {
            append_str += '';
          }
          append_str += '</td>';
        }
        append_str += '<td>';
        if(window.spec_flag==window.dispatcher_flag_config && window.spec_id == row.creator_i && row.stat_i==3) {
          append_str += '<font color="red">'+row.creator_fio+'</font>';
        } else {
          append_str += row.creator_fio;
        }
        append_str += '</td><td>'+row.spec_fio+'</td>';
      }
      /****more columns for dispatcher*****/
      
      append_str += '<td>'+row.error_v+'</td><td>'+row.place_v+'</td><td>'+row.created+'</td>';
      if(window.ticket_stat_id==4) {
        append_str += '<td>'+row.closed+'</td>';
      }       
  if(group==0) {      
      append_str += '</tr>';
  } else {
      append_str += '</tr></table>';  
    }       
      return append_str;
}      
function makePager(json) {
    var pages_count = Math.ceil(json.count/window.max_tickets_on_page),
    temp_count = 1,
    pager_str = '';
    
    for(var cc = 1; cc <=pages_count; cc++) {
          if((cc<10 && pages_count>15 && window.page<9) || pages_count<=10) {
            pager_str += '<div style="width:30px;display:inline;position:absolute;top:98px;left:'+(cc*30)+'px;cursor:pointer;text-align:center;';
            if(cc==window.page) {
              pager_str += 'color:red;font-weight:bold;'       // BOLD this page
            }                    
            pager_str += '" onclick = "window.page='+cc+';start_tickets();">'+cc+'</div>';
          } else if(window.page >= 9) {                                                 
            if((cc>(window.page-5) && cc<(window.page+5)) || cc==1 || cc==pages_count) { 
              if(cc!=1) {
                temp_count++;
              }
              //pager_str += '<div style="width:30px;display:inline;position:absolute;top:98px;left:';
              if(cc!=1 && cc!=pages_count) {
                pager_str += '<div style="width:30px;display:inline;position:absolute;top:98px;left:'+((temp_count+1)*30)+'px;cursor:pointer;text-align:center;';
              } else if(cc==1) {
                pager_str += '<div style="width:30px;display:inline;position:absolute;top:98px;left:'+(temp_count*30)+'px;cursor:pointer;text-align:center;';
              } else if(cc==pages_count) {
                if(window.page<(pages_count-5)) {
                   pager_str += '<div style="width:30px;display:inline;position:absolute;top:98px;left:'+((temp_count+1)*30)+'px;cursor:default;text-align:center;">...</div><div style="width:30px;display:inline;position:absolute;top:98px;left:'+((temp_count+2)*30)+'px;cursor:pointer;text-align:center;';
                } else {
                  pager_str += '<div style="width:30px;display:inline;position:absolute;top:98px;left:'+((temp_count+1)*30)+'px;cursor:pointer;text-align:center;';
                }
              }              
              if(cc==window.page) {
                pager_str += 'color:red;font-weight:bold;'       // BOLD this page
              }                    
              pager_str += '" onclick = "window.page='+cc+';start_tickets();">'+cc+'</div>';
            } else {
              pager_str += '<div style="width:30px;display:inline;position:absolute;top:98px;left:'+(2*30)+'px;cursor:default;text-align:center;">...</div>';
            } 
          } else {
            pager_str += '<div style="width:30px;display:inline;position:absolute;top:98px;left:'+(10*30)+'px;cursor:default;text-align:center;">...</div>';
            if(cc=pages_count) {
              pager_str += '<div style="width:30px;display:inline;position:absolute;top:98px;left:'+(11*30)+'px;cursor:pointer;text-align:center;" onclick = "window.page='+cc+';start_tickets();">'+cc+'</div>';
            }
          } 
    }  
    return pager_str; 
}
/****************SHOW TICKETS****************/

/****************getting count of approval tickets****************/
function show_approval_count(trigger) {    
  $.getJSON('/json.php?action=get_approval_tickets_count', function(json){   
      var app_cc = $("#approval_count");
      if(json.response!=undefined) { 
        if(json.response==100) {
          if(json.cc>0) { 
            app_cc.html(" ("+json.cc+")");
            if(trigger==true) {
              if(json.cc > window.max_unclosed_tickets_count) {
                window.block_creating = true;
              } else {
                window.block_creating = false;
              }
            }
          } else {
            app_cc.html("");
            window.block_creating = false;
          }  
        }  
      } 
    })
}
/****************getting count of approval tickets****************/

/****************OPEN WORK INFO POPUP*******************/
function ticket_info(t_id) {                                   ////////// open popup with all work info 
  open_pop();                             // create pop function vith loading image 
  pop = $("#"+window.e2);                 // main td in popup           
  $.getJSON('/json.php?tid='+t_id, function(json){
    $.each(json, function(id, json_row) {   
      if(json[id]==undefined || json[id]=="") {  
        json[id] = " ";
      }
    }); 
    
    var st = "";                          // var of string
    var head_str = "";                    // var of string
    var color = "";                       // status color
    var separator = " - ";                // separatof for start & stop text
    var divHeight = 24;                   // height for datetime div  
    var stat_name_string = json.stat_v;   // name of status  
      
    /*****TICKETS ROW SELECTOR COLOR******/
    $.each($("#tickets_table tr").not($("#tickets_table tr.table_head")), function(t_row_id, t_row_val) {
      if($(this).attr('creator_is_dispatcher')!=undefined) {
        $(this).css('background-color','#E8FEFF');
      } else if($(this).attr('ticket_blocked')!=undefined) {
        $(this).css('background-color','#FFCACA');
      } else {
        $(this).css("background-color","white");
      }     
    });      
    $("#row_ticket_"+t_id).css("background-color","rgb(0, 171, 169)");  
    /*****TICKETS ROW SELECTOR COLOR******/
      
    /*******GETTING STAT COLOR*******/ 
    switch (json.stat_i) {
        case '1':
          color = "red";
          break
        case '2':
          color = "red";
          break
        case '3':
          color = "blue";
          break
        case '4':
          color = "green";
          break          
        default:
          color = "black";
          break
    }
    if(window.spec_flag==window.sotrudnik_flag_config) { 
      switch (json.stat_i) {
          case '1':
            stat_name_string = "В работе у специалиста";
            break
          case '2':
            stat_name_string = "В работе у специалиста";
            break
          case '3':
            stat_name_string = "Требует подтверждения";
            break
          case '4':
            stat_name_string = "Закрыта";
            break          
          default:
            stat_name_string = "Получен неправильный статус";
            break
      }
    } 
    /*******GETTING STAT COLOR*******/ 

  
    /**************head and ticket state, place, error, creator, comment*************/  
      head_str += "<div style='text-align:left;' id='head_div'>";
      head_str += "<h3><b>ЗАЯВКА № "+t_id+"</b></h3><br>";
      head_str += "<b>Статус заявки:</b> <font style='color:"+color+";'>"+stat_name_string+"</font></b>&nbsp;&nbsp;&nbsp;<a href='javascript:;' onclick='print_this(\"popup_main_td2\");' class='btn'>Распечатать</a>";
      if( json.stat_i==1 && window.spec_flag == window.dispatcher_flag_config && (pf(json.blocked)==0 || json.blocked==window.spec_id)) { 
        head_str += "&nbsp;&nbsp;&nbsp;<a href='javascript:;' onclick='toggle_block_ticket("+t_id+");' class='btn'>Заблокировать</a>"; 
        window.set_free_ticket = t_id;
      } else if( json.stat_i==1 && window.spec_flag == window.dispatcher_flag_config && pf(json.blocked)==-1 ) {
        head_str += "&nbsp;&nbsp;&nbsp;<a href='javascript:;' onclick='toggle_block_ticket("+t_id+");' class='btn'>Разблокировать</a>"; 
      } else if( json.stat_i==1 && window.spec_flag == window.dispatcher_flag_config && json.blocked>0 ) {
        if(json.blocked==window.spec_id) {
          window.set_free_ticket = t_id;
        }
        if(json.blocked!=window.spec_id) {
          head_str += "<br><br><font color='red'>Обрабатывается пользователем "+json.blocked_disp+"</font>";
        }       
      }
      if(json.blocked==-1) {
        head_str += "<br><br><font color='red'>Заявка принудительно заблокирована</font>";
      }       
      if(window.spec_flag!=window.sotrudnik_flag_config) {
        head_str += "<br><br><b>Информация о создателе заявки:</b>";// "+json.creator_fio+"<br>";
        //head_str += "<b>Контакты:</b><br>";
        head_str += "<table width=100% id='ticket_dates_table'>";
        head_str += "<tr><td width=200>ФИО</td><td><a href='javascript:;' onclick='show_profile_stat("+json.creator_i+");'>"+json.creator_fio+"</a></td></tr>";
        if(json.creator_contacts) {
          $.each(json.creator_contacts, function(ic, row_c){
            head_str += "<tr><td>"+json.creator_contacts[ic].type+"</td><td>"+json.creator_contacts[ic].value+"</td></tr>";
          }); 
        }
        head_str += "</table><br>";               
      } else {
        head_str += "<br><br>";
      }           
      head_str += "<b>Путь к месту ошибки:</b> "+json.place_v+"<br><br>\
      <b>Тип ошибки:</b> "+json.error_v+"<br><br>\
      <b>Имя компьютера:</b> "+json.pc_name+"<br><br>";
      if(json.phone!=undefined && json.phone!='' && json.phone!=' ') {
        head_str += "<b>Контактный тел.:</b> "+json.phone+"<br><br>";
      }
      head_str += "<b>Пояснение к ошибке:</b> "+text_explode(json.text)+"<br><br>"; 
      
      
      
    /**************set new spec*************/
    if(json.stat_i==1 && (window.spec_flag == window.god_flag_config || window.spec_flag == window.dispatcher_flag_config) ) { // if it is dispatcher or GODOFWORLD
      if( window.spec_flag == window.dispatcher_flag_config && json.blocked>0 && json.blocked!=window.spec_id ) {
        head_str += "<font color='red'>Назначение специалиста недоступно, пока заявка открыта другим диспетчером.</font>";
      } else if( window.spec_flag == window.dispatcher_flag_config && json.blocked==-1 ) {
        head_str += "<font color='red'>Назначение специалиста недоступно, пока заявка принудительно заблокирована.</font>";
      } else {       
        head_str += '<input type="submit" value="Назначить специалиста" onclick="show_set_spec();" id="set_spec" style="margin-bottom:10px;">';
        head_str += '<div id="set_spec_selector_div" style="display:none;border:1px solid black;"><table width=100%><tr><td>Выберите специалиста: </td>';
        head_str += '<td><select id="set_spec_selector"><option value="0">Выберите</option>';
        $.each(json.specialists, function(is, rows) {  
          head_str += "<option value='"+rows.id+"' style='background-color:";
          if(rows.busy>0 && rows.busy<=3) {
            head_str += "#98DF92";
          } else if(rows.busy>3 && rows.busy<=6) {
            head_str += "#E8EF8B";
          } else if(rows.busy>6) {
            head_str += "#FCA4A4";
          } else {
            head_str += "white";
          }
          head_str +=";'>"+rows.fio+" | выполняемых заявок: "+rows.busy+"</option>";
        });
        head_str += '</select></td></tr><tr><td colspan=2 align=center><input type="submit" value="Назначить!" onclick="save_set_spec('+t_id+');" id="set_spec" style="margin-top:10px;margin-bottom:10px;"></td></tr></table></div>';                  
      }
    }
    /**************set new spec*************/
          
            
      if( window.spec_flag == window.dispatcher_flag_config) {      
        //if(json.msg_counter<1 || json.creator_i==window.spec_id) {
        //  json.msg_counter = "0"; 
        var show_adding = false;                
        if(window.spec_id == json.creator_i || window.spec_id == json.spec_i || window.spec_flag == window.dispatcher_flag_config) {                                    
          show_adding = true;
        }                               
          head_str += "<br><br><div style='display:inline;width:200px'><a href='javascript:;' onclick='show_add_ticket_msg("+t_id+", "+json.stat_i+", "+show_adding+");' class='btn'><i class='fa fa-comment-o'></i>&nbsp;&nbsp;Переписка<div id='ticket_message_counter' style='display:inline;color:white;'></div></a></div>";
          if(window.spec_flag!=window.sotrudnik_flag_config) {
            head_str += "<div style='margin-left:30px;display:inline;width:200px'><a href='javascript:;' onclick='show_add_ticket_sys_msg("+t_id+");' class='btn'><i class='fa fa-comment-o'></i>&nbsp;&nbsp;Служебные комментарии<div id='ticket_sys_message_counter' style='display:inline;'></div></a></div>";
          }
          head_str += "<br><br>";
        //}
      } else {
        if(window.spec_id == json.creator_i || window.spec_id == json.spec_i) {
        // && json.stat_i!=4
          if(json.msg_counter<1) {
            json.msg_counter = "0";
          }     
          head_str += "<br><br><a href='javascript:;' onclick='show_add_ticket_msg("+t_id+", "+json.stat_i+");' class='btn'><i class='fa fa-comment-o'></i>&nbsp;&nbsp;Переписка<div id='ticket_message_counter' style='display:inline;color:white;'></div></a>";
          if(window.spec_flag!=window.sotrudnik_flag_config) {
            head_str += "<div style='margin-left:30px;display:inline;width:200px'><a href='javascript:;' onclick='show_add_ticket_sys_msg("+t_id+");' class='btn'><i class='fa fa-comment-o'></i>&nbsp;&nbsp;Служебные комментарии<div id='ticket_sys_message_counter' style='display:inline;'></div></a></div>";
          }
          head_str += "<br><br>";          
        } 
      }  
    /**************head and ticket state, place, error, creator, comment*************/



    /**************status selector*************/    
    if((window.spec_flag==window.sotrudnik_flag_config && json.stat_i==3) || (window.spec_flag==window.dispatcher_flag_config && window.spec_id == json.creator_i && json.stat_i==3)) {      // if it is sotrudnik and need approval
      head_str += "<div align=center><input type='submit' onclick=\"update_ticket_stat("+t_id+",4);\" value='Подтвердить закрытие' style='margin-bottom:10px;' ></div>";
      head_str += "<div align=center><input type='submit' onclick=\"update_ticket_stat("+t_id+",2, true);\" value='Отказать в подтверждении' style='margin-bottom:10px;' >\
        <div style='width:80%;display:none;' id='update_ticket_stat_comment'><textarea style='width:100%; height:100px;' id='update_ticket_stat_textarea'>Создатель отказался от подтверждени</textarea><div style='margin-top:8px;text-align:left;'><input type='button' value='Отказаться' class='blue_btn' onclick='update_ticket_stat("+t_id+",2,true);'></div></div>";      
    }
    /**************status selector*************/
    
    
    
    /**************dates: created, specialist selected, on approval, closed*************/  
    if(window.spec_flag!=window.sotrudnik_flag_config) {                        //if it is NOT sotrudnik       
      head_str += "<div style='margin-top:10px;text-align: center;'><b>Штампы времени:</b></div><table width=100% id='ticket_dates_table'><tr><td width=200>Создано</td><td>"+json.created+"</td></tr>"; 
      head_str += "<tr><td >Назначен специалист</td><td>"+json.spec_selected+"</td></tr>"; 
      head_str += "<tr><td >Передано на утверждение</td><td>"+json.approval+"</td></tr>"; 
      head_str += "<tr><td >Закрыто</td><td>"+json.closed+"</td></tr></table><br>";  
    }   
    /**************dates: created, specialist selected, on approval, closed*************/

    
     head_str += "</div>";
    
    
    /**************ticket history*************/
    if(window.spec_flag != window.sotrudnik_flag_config) { // if it is not user     
      if(json.hist != undefined) {
        if(window.is_mobile==true) {     // if it is mobile
          separator = "<br>"; 
          divHeight = 50;
        }    
        head_str += "<div style='margin-top:10px;text-align: center;'><b>Детальная история заявки:</b></div><div id='pop_work_hist'></div>";     ///main template for workhist
        $.each(json.hist, function(i, val) {   //load hist 
          st += "<div class='row_info' style='border: 1px solid black;'><table id='ticket_hist_row_table'>";
          if(val.created!='') { 
            st += "<tr><td width=200>Время</td><td>"+val.created+"</td></tr>";
          }          
          if(val.spec_id!='') { 
            st += "<tr><td width=200>Специалист</td><td>"+val.spec_fio+"</td></tr>";
          }
          if(val.work_text!='') {           
            //st += "<tr><td>Пояснение к работе</td><td>"+val.work_text+"</td></tr>";
          }
          if(val.ticket_hist_text!='') {           
            st += "<tr><td>Пояснение к истории</td><td>"+val.ticket_hist_text+"</td></tr>";
          }
          if(val.work_stat_v!='') {           
            st += "<tr><td>Статус работы</td><td>"+val.work_stat_v+"</td></tr>";
          }
          if(val.work_id!='') { 
            st += "<tr><td>id работы</td><td><a href='main.php?show_one_work=true&work_id="+val.work_id+"' target='_popup' class='work_link'>"+val.work_id+"</a></td></tr>";
            //st += "<tr><td>id работы</td><td>"+val.work_id+"</td></tr>";
          }
          st += "</table></div><br>"; 
        }); 
      } else {
        head_str += "<div class='row_info' style='text-align:center;'>" + json.msg + "</div>";
      }   
    }
    /**************ticket history*************/

    
    pop.html(head_str);
    $("#pop_work_hist").html(st);
    $("#popup_title").html(json.title);    //inserp popup title
    pop.css('max-height', (parseFloat(window.innerHeight)-95-50)+'px'); //main insert + set max-width  
    //$("#"+window.e).css('max-height', (parseFloat(window.innerHeight)-95-50)+'px'); //main insert + set max-width 
    get_ticket_message_counter(t_id); 
    if(window.spec_flag!=window.sotrudnik_flag_config) {
      get_ticket_message_counter(t_id, 1);
    }   
  });   
}
/****************OPEN WORK INFO POPUP*******************/

/************SET NEW SPEC OF WORK************/
function show_set_spec() {
  if($("#set_spec_selector_div").is(':hidden')) {
    $("#set_spec").val('Отменить назначение');
    $("#set_spec_selector_div").show();    
  } else {
    $("#set_spec").val('Назначить специалиста');
    $("#set_spec_selector_div").hide();
  }
}

function save_set_spec(t_id) {
  var new_spec = $("#set_spec_selector").val();
  if(new_spec==0) {
    alert('Быверите специалиста!', 21100);  
  } else {
    if(t_id!=undefined && t_id!=0 && new_spec!=undefined && new_spec!=0) {
      load(1);
      $("#set_spec").hide();    
      $.getJSON("/json.php?action=set_ticket_spec&tid="+t_id+"&new_s_id="+new_spec, function(json){ 
        load(0);      
        alert(json.msg, json.response);
        close_pop();    
        if(json.response==100) {
          $("#set_spec").val('Назначить специалиста');
          $("#set_spec_selector_div").hide();
          start_tickets();
        }      
      });
    } else {
      alert('Ошибка получения данных!<br>перезагрузите страницу', 21101);
    }
  }
}        
/************SET NEW SPEC OF WORK************/
     
     
function toggle_block_ticket(t_id) {
  load(1);           
  $.getJSON("/json.php?action=toggle_block_ticket&tid="+t_id, function(json){ 
    load(0);      
    alert(json.msg, json.response);
    window.set_free_ticket = 0;
    close_pop();    
    if(json.response==100) {
      start_tickets();
    }      
  });
} 

       
/****************UPDATING TICKET STATUS*******************/
function update_ticket_stat(t_id,val,trigger) {   //ajax updating status of work   
  if (confirm("Подтвердите действие")) {
    var temp_str='';  
    if(trigger!=undefined) {                // if it - the refusal of confirmation 
      temp_str = '&is_cancel=true';
    } 
    //$.getJSON('/json.php?action=update_ticket_stat&tid='+t_id+'&ts='+val+'&comment='+$("#update_comment").val(), function(json){
    $.getJSON('/json.php?action=update_ticket_stat&tid='+t_id+'&ts='+val+temp_str+'&comment='+$("#update_ticket_stat_textarea").val(), function(json){      
        alert(json.msg, json.response);
        if(json.response==100) {
          close_pop();
          start_tickets();
        } 
    });
  }
}
/****************UPDATING TICKET STATUS*******************/

/****************SHOW 'POPUP ADDING NEW TICKET*******************/
function show_create_ticket() {                                 ////////// open popup with ticket creating
  /*****utocheck to close all opened tickets and input all contacts*****/
  if(window.block_creating == true) {
    alert("Сначала подтвердите закрытие заявок!", 4006);
    return false;
  }
  auto_ckeck_contacts();
  /*****utocheck to close all opened tickets and input all contacts*****/ 
  open_pop();
  pop = $("#"+window.e2);                         // main td in popup 
  var str1 = "<br><table width=90%><tr><td width=160 align=left>Путь к месту ошибки<font color='red'>*</font></td><td style='padding-left:10px;' align=left><input type='hidden' id='c_place' value=''>";
    str1 += '<a href="javascript:;" id="place_title" onclick="get_places_selector(-1); return false;" class="selector">Не выбрано</a></td></tr>';
    str1 += "<tr><td width=100 align=left>Тип ошибки<font color='red'>*</font></td><td style='padding-left:10px;' align=left><input type='hidden' id='c_error_id' value=''>";
    str1 += '<a href="javascript:;" id="error_title" onclick="get_errors_selector(-1); return false;" class="selector">Не выбрано</a></td></tr>';
    str1 += "<tr><td width=100 align=left>Название компьютера<font color='red'>*</font></td><td style='padding-left:10px;' align=left><input type='text' id='c_pc_name' value=''></td></tr>";
    str1 += "<tr><td width=100 align=left>Контактный телефон</td><td style='padding-left:10px;' align=left><input type='text' id='c_phone' value=''></td></tr>";    
    str1 += '<tr><td valign=center align=left>Описание<font color="red">*</font></td><td style="padding-left:10px;" align=left><textarea name="comments" id="c_comments"></textarea></td></tr>\
      <tr><td colspan=2 align=left><a id="create_ticket_button" class="btn" href="javascript:;" onclick="create_ticket();return true;"><i class="fa fa-floppy-o"></i>&nbsp;&nbsp;Сохранить</a></td></tr></table>';
    pop.html(str1);
    $("#popup_title").html("Создание заявки");    //inserp popup title    
}
 
function create_ticket() {                                           ////////// close popup with ticket creating and send json
  var ret = validate_creating();
  if(ret!=false) {  
    $("#create_ticket_button").hide();    
    load(1);
    $.getJSON("/json.php?action=add_new_ticket&error_id="+ret.error_id+"&place="+ret.place+"&pc="+ret['pc_name']+"&ph="+ret['phone']+"&comm="+ret['comm'], function(json){
      load(0); 
      alert(json.msg, 999);
      if(json.response==100) {
        if(window.use_socket==1) {
          //console.log(JSON.stringify("new_ticket_added"));
          conn.send(JSON.stringify("new_ticket_added"));
        }
        close_pop();
        start_tickets();
      } else {
        $("#create_ticket_button").show();
      }   
    });
  } 
}             

function validate_creating() {  
  var ret = new Object();
  ret['place'] = $( "#c_place" ).val();
  ret['error_id'] = $('#c_error_id').val();
  ret['pc_name'] = $('#c_pc_name').val(); 
  ret['phone'] = $('#c_phone').val();  
  ret['comm'] = text_explode($('#c_comments').val().replace(/["']/g, '-||-').replace(/\n/g, "<br>"));
  if(ret['place']=="" || ret['error_id']=="" || ret['comm']=="" || ret['pc_name']=="") {
    alert('Пожалуйста, заполните все поля!');
    return false;
  }   
  return ret;
}
/****************SHOW 'POPUP ADDING NEW TICKET*******************/


/****************show places and errors selector*******************/
function get_places_selector() {
    var params = "";
    load(1);
    $.getJSON("/json.php?action=get_first_places", function(json){
      load(0);  
      if(json.response==100) {
      var places_str="";
      $.each(json.places, function(i, place) {
        places_str += '<div class="sublevel">\
                <div class="sublevel_row">\
                  <a id="toggler_places_'+place.id+'" href="javascript:;" class="toggler" ';
                  if(place.childs!=undefined) {
                    //places_str += 'onclick="toggle_places(\''+place.id+'\'); return false;"';
                    places_str += 'style="cursor:default;"';
                  }
                  places_str += '><i class="fa fa-folder-o"></i> </a><a href="javascript:;" id="title_places_'+place.id+'" ';
                  if(place.selectable==0) {
                    places_str += 'style="cursor:default;"';
                  }
                  places_str += ' class="branch" onclick="select_place(\''+place.id+'\', \''+place.selectable+'\'); return false;">'+place.value+'</a>\
                </div>\
                <div id="childs_places_'+place.id+'" class="cb">';
                $.each(json.places[i].childs, function(i2, itemp) {
                      places_str += '<div class="sublevel">\
                      <div class="sublevel_row">';
                        if(itemp.childs!=undefined && pf(itemp.childs)>0) {
                          places_str += '<a id="toggler_places_'+itemp.id+'" href="javascript:;" class="toggler" onclick="toggle_places(\''+itemp.id+'\'); return false;">'+window.tree_plus+'</a> ';
                        }
                        places_str += '<a href="javascript:;" id="title_places_'+itemp.id+'" class="branch" onclick="';
                        if(itemp.selectable==0) {
                          places_str += 'toggle_places(\''+itemp.id+'\')';
                        } else {
                          places_str += 'select_place(\''+itemp.id+'\', \''+itemp.selectable+'\')';
                        } 
                        places_str += ';return false;">'+itemp.value+'</a>\
                      </div>\
                      <div id="childs_places_'+itemp.id+'" class="cb" style="display: none"></div>\
                      </div>';
                 });                
                places_str += '</div></div>';
         }); 
         $("#toplevel").html(places_str);
         $(".close_div_small").css({
            'left':"-25px",
            'top':'-25px'});
         $("#places_popup")
            .css({
              'left': ($(window).width()/2) - ((pf($("#places_popup").width()+95))/2)+'px', 
              'max-height': (parseFloat(window.innerHeight)-90)+'px'})
            .show();
                     
      }   
    }); 
}

function get_errors_selector() {
    var params = "";
    load(1);
    $.getJSON("/json.php?action=get_first_errors", function(json){
      load(0);  
      if(json.response==100) {
      var places_str="";
      $.each(json.errors, function(i, place) {
        places_str += '<div class="sublevel">\
                <div class="sublevel_row">\
                  <a id="toggler_places_'+place.id+'" href="javascript:;" class="toggler" ';
                  if(place.childs!=undefined) {
                    //places_str += 'onclick="toggle_errors(\''+place.id+'\'); return false;"';
                    places_str += 'style="cursor:default;"';
                  }
                  places_str += '><i class="fa fa-folder-o"></i> </a>\
                  <a href="javascript:;" id="title_errors_'+place.id+'" class="branch" onclick="select_error(\''+place.id+'\', \''+place.selectable+'\'); return false;">'+place.value+'</a>\
                </div>\
                <div id="childs_places_'+place.id+'" class="cb">';
                $.each(json.errors[i].childs, function(i2, itemp) {
                      places_str += '<div class="sublevel">\
                      <div class="sublevel_row">';
                        if(itemp.childs!=undefined && pf(itemp.childs)>0) {
                          places_str += '<a id="toggler_places_'+itemp.id+'" href="javascript:;" class="toggler" onclick="toggle_errors(\''+itemp.id+'\'); return false;">'+window.tree_plus+'</a> ';
                        }
                        places_str += '<a href="javascript:;" id="title_errors_'+itemp.id+'" class="branch" onclick="select_error(\''+itemp.id+'\', \''+itemp.selectable+'\'); return false;">'+itemp.value+'</a>\
                      </div>\
                      <div id="childs_places_'+itemp.id+'" class="cb" style="display: none"></div>\
                      </div>';
                 });                
                places_str += '</div></div>';
         }); 
         $("#toplevel").html(places_str);
         $(".close_div_small").css({
            'left':"-25px",
            'top':'-25px'});         
         $("#places_popup")
            .css({
              'left': ($(window).width()/2) - ((pf($("#places_popup").width()+95))/2)+'px', 
              'max-height': (parseFloat(window.innerHeight)-70)+'px'})
            .show();
      }   
    });
}

function create_html_places(json)
{
  var html = '';
  i = 0;
  while( json[i] ) {
    html += '<div class="sublevel">';
    html += '<div class="sublevel_row">';
    if( json[i].childs > 0 )   {
      html += '<a id="toggler_places_'+json[i].id+'" href="javascript:;" class="toggler" onclick="toggle_places('+json[i].id+'); return false;">'+window.tree_plus+'</a> ';
    }
    if( json[i].selectable ) {
      html += '<a href="javascript:;" id="title_places_'+json[i].id+'" class="branch" onclick="select_place('+json[i].id+', '+json[i].selectable+'); return false;">'+json[i].value+'</a>';
    } else {
      html += '<a href="javascript:;" id="title_places_'+json[i].id+'" class="branch unselectable" onclick="select_place('+json[i].id+', '+json[i].selectable+'); return false;">'+json[i].value+'</a>';
    }
    html += '</div><div id="childs_places_'+json[i].id+'" class="cb" style="display: none"></div></div>';
    ++i;
  }
  return html;
}

var current_id = 0;
var current_select = null;
var current_select_places = null;
var current_select_error = null;

function close_popup( id )
{
  document.getElementById(id).style.display = 'none';
}

function toggle_places( parent )
{
  current_id = parent;
  var container = $('#childs_places_'+current_id);
  var toggler = $('#toggler_places_'+current_id); 
  if( typeof container.attr('havedata') !== 'undefined' ) {
    if( container.css('display') == 'block' ) {
      toggler.html(window.tree_plus);
      container.hide();
    } else {
      toggler.html(window.tree_minus);
      container.show()
    }
  } else {  
    load_places_branch();
  }
}

function load_places_branch()
{      
  load(1);            
  $.getJSON('/json.php?action=get_places_manager&parent='+current_id, function(json){
    load(0);
    show_places_branch(json);
  });                                    
}

function show_places_branch(json)
{
  var container = $('#childs_places_'+current_id);
  var toggler = $('#toggler_places_'+current_id);
  container.html(create_html_places( json ));
  container.attr('havedata',true);
  toggler.html(window.tree_minus);  
  container.show();
}

function select_place( id, selectable )
{
  if( selectable == 0 )
   { return;}
  if( current_select_places != null )  {
    $('#title_places_'+current_select_places).css('color', '#000');
  }
  current_select_places = id;
  $('#title_places_'+current_select_places).css('color', '#f00');
  $("#place_title").html($('#title_places_'+ id).html());
  $("#c_place").val(id);
  $("#places_popup").hide();
}

function select_error( id, selectable )
{
  if( selectable == 0 )
   { return;}

  if( current_select_error != null )  {
    $('#title_errors_'+current_select_error).css('color', '#000');
  }
  current_select_error = id;
  $('#title_errors_'+current_select_error).css('color', '#f00');
  $("#error_title").html($('#title_errors_'+ id).html());
  $("#c_error_id").val(id);
  $("#places_popup").hide();
}
/****************show places and errors selector*******************/

/***************ALL FOR SEARCH TICKETS*****************/
function search_toggler() {                                //toggle search form
  window.ticket_stat_id = 5;
  $('#main_stat_selector td').each(function(){
    $(this).css("background-color", "#00aba9");
  });
  $("#search_toggler").css("background-color", "#007270"); 
  if($("#search_form").is(':hidden')) {
    ////$("#search_toggler_btn").html('Скрыть поиск');
    $("#search_form").show();
    //$( "#search_form" ).show().animate({height: "31px"}, 2000);       //not working(
    $("#pager").hide(); 
    $("#main_tickets").html('');    
  } else {
    ////$("#search_toggler_btn").html('Поиск');
    ////$("#search_form").hide();
    ////$("#pager").show();
  }
}

function toggle_search_number(trigger) {                         //toggle rows in form when NUMBER isset/not isset/cleared
  if(trigger==undefined && ($("#s_num").val()>0 || $("#s_num").val().length>0)) {
    $("#s_stat").attr('disabled', true);
    $('#s_stat :nth-child(1)').prop('selected', true);
    $("#s_error").attr('disabled', true);
    $('#s_error :nth-child(1)').prop('selected', true);
    $("#s_place").attr('disabled', true);
    $('#s_place :nth-child(1)').prop('selected', true);
    $("#s_spec").attr('disabled', true);
    $('#s_spec :nth-child(1)').prop('selected', true);  
    $('#open_date_0').val('').attr('disabled', true);
    $('#open_date_1').val('').attr('disabled', true);                   
  } else if(trigger=='true') { 
    $("#s_num").val(''); 
    $("#s_stat").attr('disabled', false);
    $("#s_error").attr('disabled', false);
    $("#s_place").attr('disabled', false);
    $("#s_spec").attr('disabled', false);
    $('#open_date_0').attr('disabled', false);
    $('#open_date_1').attr('disabled', false);                                                   
  } else if(trigger=='for_spec_1') { 
    $("#s_stat").attr('disabled', true);
    $('#s_stat :nth-child(1)').prop('selected', true);
    $("#s_error").attr('disabled', true);
    $('#s_error :nth-child(1)').prop('selected', true);
    $("#s_place").attr('disabled', true);
    $('#s_place :nth-child(1)').prop('selected', true);
    $('#open_date_0').val('').attr('disabled', true);
    $('#open_date_1').val('').attr('disabled', true); 
  } else if(trigger=='for_spec_2') { 
    $("#s_num").val(''); 
    $("#s_stat").attr('disabled', false);
    $("#s_error").attr('disabled', false);
    $("#s_place").attr('disabled', false);
    $('#open_date_0').attr('disabled', false);
    $('#open_date_1').attr('disabled', false); 
  }
} 

function toggle_search_stat(trigger) {     //on stat change - toggle SPEC SELECTOR when selected 2nd stat     trigger=clear
  if(trigger==undefined && $("#s_stat").val()==1) {
    $("#s_spec").attr('disabled', true);
    $('#s_spec :nth-child(1)').prop('selected', true);
  } else {
    $("#s_spec").attr('disabled', false);
    if(trigger!=undefined) { 
      $('#s_stat :nth-child(1)').prop('selected', true);
    }
  }
} 

function do_search() {                   //main func - let's search
  load(1);
  var number = $("#s_num").val(),
  stst = $("#s_stat").val(),
  error = $("#s_error").val(),
  place = $("#s_place").val(),
  spec = $("#s_spec").val(),
  open_from = $("#open_date_0").val(),
  open_to = $("#open_date_1").val();
  if(open_from!="") {
    open_from += " 00:00:00";
  }
  if(open_to!="") {
    open_to += " 23:59:59";
  }              
  $.getJSON("/json.php?action=search_ticket&num="+number+"&st="+stst+"&er="+error+"&pl="+place+"&sp="+spec+"&of="+open_from+"&ot="+open_to, function(json){
    if(json.response==100 && json.tickets!=undefined) {
      show_tickets(json.tickets,'reset_pager');             //insert finded rows
    } else {
      $("#main_tickets").html('<div>'+json.msg+'</div>'); 
    }
    load(0);
  }).error(function() { alert("Ошибка связи с сервером при запросе данных."); });  
}
/***************ALL FOR SEARCH TICKETS*****************/

/***************toggle ticket background color*****************/
function toggle_ticket_row_bg(id, trigger) {
  if($("#popup_title").is(':hidden')) {
    if (trigger==true) {
      $('#row_ticket_'+id).css('background-color','#83DBDB');
    } else {
      if($('#row_ticket_'+id).attr('creator_is_dispatcher')!=undefined) {
        $('#row_ticket_'+id).css('background-color','#E8FEFF');
      } else if($('#row_ticket_'+id).attr('ticket_blocked')!=undefined) {
        $('#row_ticket_'+id).css('background-color','#FFCACA');
      } else {
        $('#row_ticket_'+id).css('background-color','#f4fdff');
      }
    }  
  } 
}
/***************toggle ticket background color*****************/

/***************ALL STAT OF USER******************/
function show_profile_stat(id) {
  open_pop();                             // create pop function vith loading image 
  pop = $("#"+window.e2);                 // main td in popup 
  load(1);          
  $.getJSON('/json.php?action=show_profile_stat&p_id='+id, function(json){
    load(0);

    pop.html('<div id="chartContainer" style="height:400px;max-width:600px;margin: 0px auto"></div>');
    $("#popup_title").html('Статистика профиля');    //inserp popup title
    pop.css('max-height', (parseFloat(window.innerHeight)-95-50)+'px'); //main insert + set max-width  
  
    var dataSource = [];
    $.each(json.errors, function(i, row) { 
      var o = {};
      o['category'] = row.value;
      o['number'] = parseInt(row.count);
      dataSource.push(o);
    });

    $("#chartContainer").dxPieChart({
        dataSource: dataSource,
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
    });
    
  });  
}
/***************ALL STAT OF USER******************/                                                                                       


function toggle_tr_action(id, trigger) {
  if(trigger=='false') {
    $('#row_ticket_'+id).click(function() {
      ticket_info(id);    
    });  
  } else {
    $('#row_ticket_'+id).attr('onclick','').unbind('click');  
  }        
}

/***************GROUP******************/
function toggler_adding_ticket_to_group(tid) { 
  if($('#adding_ticket_to_group_'+tid).is(':checked')==true) { 
    window.add_tickets_to_group[tid] = true;
  } else {
    delete window.add_tickets_to_group[tid];
  }              
  var ObjCount = Object.keys(window.add_tickets_to_group).length;          
  $("#msgContainer").html('<font color="red">Финкция на стадии разработки!<br>Не юзать!</font><br>Группировка выбранных заявок<br><br><a href="javascript:;" onclick="do_adding_ticket_to_group();" class="btn">Группировать</a>&nbsp;&nbsp;<a href="javascript:;" onclick="cancel_adding_ticket_to_group();" class="btn">Отменить</a>'); 
  $("#msgBox").css({
      'cursor': 'move',
      'top': (event.pageY-130)+'px',
      'left': (event.pageX+20)+'px'});
  if(ObjCount>1) {
    $("#msgBox").show();
    $("#msgBox").draggable();
  } else {
    $("#msgBox").hide();
    if($("#msgBox").is(':hidden')==false) {      //correct undestroing
      $("#msgBox").draggable("destroy");
    }
  }
}

function do_adding_ticket_to_group() {
  var ret = '';
  $.each(window.add_tickets_to_group, function(tid, row) {
    ret += '&tid['+tid+']=add';
  }); 
  $.getJSON('/json.php?action=adding_ticket_to_group'+ret, function(json){      
    $('#msgBox').hide();  
    alert(json.msg, json.response);
    if(json.response==100) {
      //close_pop();
      start_tickets();
    } 
  }); 
}

function cancel_adding_ticket_to_group() {
  $('input[rel="checkbox_for_groups"]').filter(function() {         //uncheck all selected group checkboxes
    $(this).attr('checked', false);
  });  
  $.each(window.add_tickets_to_group, function(id, val) {         //del all data in group window array
    delete window.add_tickets_to_group[id];    
  });
  $('#msgBox').hide();
}       
/***************GROUP******************/

/***************UNGROUP******************/
function toggler_deleting_ticket_to_group(tid) { 
  if($('#adding_ticket_to_group_'+tid).is(':checked')==true) { 
    window.del_tickets_to_group[tid] = true;
  } else {
    delete window.del_tickets_to_group[tid];
  }              
  var ObjCount = Object.keys(window.del_tickets_to_group).length;          
  $("#msgContainer").html('Разгруппировка выбранных заявок<br><br><a href="javascript:;" onclick="do_deleting_ticket_to_group();" class="btn">Разгруппировать</a>&nbsp;&nbsp;<a href="javascript:;" onclick="cancel_deleting_ticket_to_group();" class="btn">Отменить</a>'); 
  $("#msgBox").css({
      'cursor': 'move',
      'top': (event.pageY-130)+'px',
      'left': (event.pageX+20)+'px'});
  if(ObjCount>1) {
    $("#msgBox").show();
    $("#msgBox").draggable();
  } else {
    $("#msgBox").hide();
    if($("#msgBox").is(':hidden')==false) {      //correct undestroing
      $("#msgBox").draggable("destroy");
    }
  }
}

function do_deleting_ticket_to_group() {
  var ret = '';
  $.each(window.del_tickets_to_group, function(tid, row) {
    ret += '&tid['+tid+']=del';
  }); 
  $.getJSON('/json.php?action=deleting_ticket_from_group'+ret, function(json){      
    $('#msgBox').hide();  
    alert(json.msg, json.response);
    if(json.response==100) {
      //close_pop();
      start_tickets();
    } 
  }); 
}

function cancel_deleting_ticket_to_group() {
  $('input[rel="checkbox_for_groups"]').filter(function() {         //uncheck all selected group checkboxes
    $(this).attr('checked', false);
  });  
  $.each(window.del_tickets_to_group, function(id, val) {         //del all data in group window array
    delete window.del_tickets_to_group[id];    
  });
  $('#msgBox').hide();
}
/***************UNGROUP******************/ 