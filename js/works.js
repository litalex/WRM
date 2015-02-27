/****************FIRST RUN*******************/
function start_calendar(show_one_work) {
try{ 
  load(1);
  var dates=[];
  $("#selected_day").hide(); //hide day selector     
                              
  /******set default size for popups and tables*****/
  if(window.is_mobile!=undefined) {
    $("#calendar4").css('max-width', pf(window.innerWidth)-4+'px');
    $("#main_m").css('max-width', pf(window.innerWidth)-4+'px'); 
    $("#work_popup").css('max-width', pf(window.innerWidth)+'px');
    $("#work_popup").css('max-height', pf(window.innerHeight)-20+'px'); 
  }
  /******set default size for popups and tables*****/  
  
  /******set default variable for mobile view*****/
  if(show_one_work!=undefined) {
    window.show_one_work = true;
  } else {
    window.show_one_work = false;
  } 
  /******set default variable for mobile view*****/
                
  /****set background of selected day/month state*****/
  if(window.is_daily) {
    $("#dayly_selector_d").css("background-color", "#007270");
    $("#dayly_selector_m").css("background-color", "#00aba9");
  } else { 
    $("#dayly_selector_d").css("background-color", "#00aba9");    
    $("#dayly_selector_m").css("background-color", "#007270");    
  }
  /****set background of selected day/month state*****/
   
  /****getting right url for ajax****/  
  if(window.show_one_work == false) {
    if(window.is_daily) {
      $("#selected_day").show(); 
      var json_url = '/json.php?action=get_spec_works&w_s='+window.stat_id+'&first=true&daily=true&ord=1';
    } else {
      var json_url = '/json.php?action=get_spec_works&w_s='+window.stat_id+'&first=true&ord='+window.ord;
    }  
  } else {
    var json_url = '/json.php?action=show_one_work&work_id='+show_one_work; 
  } 
  /****getting right url for ajax****/   
  
  /****get approval count just for user****/    
  if(window.spec_flag==window.dispatcher_flag_config) {
    show_created_tickets_count();
  } 
  /****get approval count just for user****/ 
         
  /****main ajax****/ 
  $.getJSON(json_url, function(json){    
      if(json.response==100) {
        $.each(json.works, function(i, row) {
          dates.push(row);        
        });   
      } 
      Calendar("calendar4",new Date().getFullYear(),new Date().getMonth(),dates,new Date().getDate());      //let's show calendar!
      if(json.response!=100) {
        if(window.is_mobile) {
          $("#main_m").html(json.msg);
        } else {
          $("#calendar4 tbody").append('<tr><td colspan=33>'+json.msg+'</td></tr>');
        }  
      }  
      load(0);
      $(".main_table").css('height', '100%');
  }).error(function() { alert("Ошибка связи с сервером"); });
  /****main ajax****/
} catch(e) {
  alert('Error in start_calendar()<br> look at console',0001);
  console.log(e.message);
  console.log(e.stack);
  //throw new Error("Неправильный формат данных: ");
}   
}
/****************FIRST RUN*******************/

//ok, let's go
/****************OPEN WORK INFO POPUP*******************/
function work_info(w_id, ts_id) {                                   ////////// open popup with all work info 
  open_pop();                          // create pop function vith loading image 
  var pop = $("#"+window.e2),                         // main td in popup     
  ticket_info = "",
  selected_name_of_row = $("#name_of_col_work_"+w_id),
  head_str = "";                                 
  $('#calendar4 .name_of_row').each(function(){
    $(this).css({"background-color": "#D9F0FD;","color":"2c567a"});
  });
  selected_name_of_row.css({"background-color": "#00aba9", "color":"white"});       
  $.getJSON('/json.php?wid='+w_id, function(json){   
    $.each(json, function(id, json_row) {   
      if(json[id]==undefined || json[id]=="") {  
        json[id] = " ";
      }
    }); 
    /*******GETTING WORK STAT COLOR*******/ 
    var color = "";
      switch (json.stat_life) {
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
    /*******GETTING WORK STAT COLOR*******/
           
    /**************main head and work state, comment*************/        
    head_str += "<div id='div_for_ticket_info'";
    if(json.ticket_id!=undefined && json.ticket_id>0) {
      head_str += " style='border:1px solid black; padding:10px;margin-bottom:10px;'"; 
    }
    head_str += "></div>";


    /**************IF HAVE A TICKET show info of this ticket*************/
    if(json.ticket_id!=undefined && json.ticket_id>0) {
      load(1);
      $.getJSON('/json.php?tid='+json.ticket_id, function(json_ticket){
        load(0);
        /*******GETTING TICKET STAT COLOR*******/ 
        var color_t = "";
          switch (json_ticket.stat_i) {
            case '1':
              color_t = "rgb(192, 53, 50)";
              break
            case '2':
              color_t = "rgb(192, 53, 50)";
              break
            case '3':
              color_t = "#D5E923;";
              break
            case '4':
              color_t = "green";
              break            
            default:
              color_t = "black";
              break
        }
        /*******GETTING TICKET STAT COLOR*******/ 
        /**************head and ticket state, place, error, creator, comment, DATES of creating, set spec etc...*************/
        //ticket_info += "<div style='text-align:left;' id='head_div'>\
//              <b>Информация о прикрепленной заявке № "+json.ticket_id+"</b></div><br>\
        
        ticket_info += "<div style='text-align:left;' id='head_div'>\
              <b>Информация о прикрепленной заявке № "+json.ticket_id+"</b>&nbsp;&nbsp;&nbsp;<a href='javascript:;' onclick='print_this(\"div_for_ticket_info\");' class='btn'>Распечатать</a><br><br>\
              <b>Статус заявки:</b> <font style='color:"+color_t+";'>"+json_ticket.stat_v+"</font><br><br>\
              <b>Создатель:</b>";
              // json_ticket.creator_fio
        if(window.spec_flag!=window.sotrudnik_flag_config) {
          //ticket_info += "<b>Контакты:</b><br>";
          ticket_info += "<table width=100% id='ticket_dates_table'>";
          ticket_info += "<tr><td width=200>ФИО</td><td>"+json_ticket.creator_fio+"</td></tr>";
          if(json_ticket.creator_contacts) {
            $.each(json_ticket.creator_contacts, function(ic, row_c){
              ticket_info += "<tr><td>"+json_ticket.creator_contacts[ic].type+"</td><td>"+json_ticket.creator_contacts[ic].value+"</td></tr>";
            });       
          } 
        }              
        ticket_info += "</table><br><b>Путь к месту ошибки:</b> "+json_ticket.place_v+"<br><br>\
              <b>Вид ошибки:</b> "+json_ticket.error_v+"<br><br>\
              <b>Имя компьютера:</b> "+json_ticket.pc_name+"<br><br>";
        if(json_ticket.phone && json_ticket.phone!="" && json_ticket.phone!=" ") {                    
          ticket_info += "<b>Контактный тел.:</b> "+json_ticket.phone+"<br><br>";
        }                
        ticket_info += "<b>Пояснение к ошибке:</b> "+json_ticket.text+"<br><br>";                  
        ticket_info += "<div style='display:inline;width:200px'><a href='javascript:;' onclick='show_add_ticket_msg("+json.ticket_id+", "+json_ticket.stat_i+");' class='btn'><i class='fa fa-comment-o'></i>&nbsp;&nbsp;Переписка<div id='ticket_message_counter' style='display:inline;'></div></a></div>";
        if(window.spec_flag!=window.sotrudnik_flag_config) {
          ticket_info += "<div style='margin-left:30px;display:inline;width:200px'><a href='javascript:;' onclick='show_add_ticket_sys_msg("+json.ticket_id+");' class='btn'><i class='fa fa-comment-o'></i>&nbsp;&nbsp;Служебные комментарии<div id='ticket_sys_message_counter' style='display:inline;'></div></a></div>";
        }
        ticket_info += "<br><br>";
        /**************dates: created, specialist selected, on approval, closed*************/      
          ticket_info += "<table width=100% id='ticket_dates_table'><tr><td width=200>Создано</td><td>"+json_ticket.created+"</td></tr>"; 
          ticket_info += "<tr><td >Назначен специалист</td><td>"+json_ticket.spec_selected+"</td></tr>"; 
          ticket_info += "<tr><td >Передано на утверждение</td><td>"+json_ticket.approval+"</td></tr>"; 
          ticket_info += "<tr><td >Закрыто</td><td>"+json_ticket.closed+"</td></tr></table>";  
        /**************dates: created, specialist selected, on approval, closed*************/
                                                              
        /**************head and ticket state, place, error, creator, comment, DATES of creating, set spec etc...*************/
        $("#div_for_ticket_info").html(ticket_info);
        //if((window.spec_id == json_ticket.creator_i || window.spec_id == json_ticket.spec_i) || json_ticket.stat_i==2) {
          get_ticket_message_counter(json.ticket_id);  
          get_ticket_message_counter(json.ticket_id, 1);          
        //}
      });
    }
    /**************IF HAVE A TICKET show info of this ticket*************/
    //head_str +="<h2>UNDER CONSTRUCTION</h2>";      
    //head_str += "</div>"; //closed #head_div


    head_str += "<div style='text-align:left;' id='head_div'>";
    head_str += "<b>Статус задания:</b> <font style='color:"+color+";'>"+json.stat+"</font>";
    if(json.last_update!='' && json.last_update!=' ' && json.last_update!=undefined) {
      head_str += ", <b>обновлен: </b>"+json.last_update;
    }
    head_str += "<br><br>";
    /*<b>Расположение:</b> "+json.value_place+"<br><b>Ошибка:</b> "+json.value_wtype+"<br>*/   
    if(json.ticket_id==undefined || json.ticket_id<=0) {
      head_str += "<b>Описание:</b> "+json.comments+"<br><br>";
    }    
    /**************main head and work state, comment*************/ 
    
    /**************who is postopened work if stat = 2 and it is not you*************/ 
    if(json.spec_id != window.spec_id && json.stat_life == 2) {   //if it is NOT owner of work AND it is postopened work (status = 2)
      head_str += "Отложено специалистом <b>";
      if(window.is_mobile==true) {
        head_str += "<br>";
      }
      head_str += json.spec_fio+"</b><br><br>";
      //head_str += json.spec_fio+"</b><br><br><input type='submit' value='Взять работу на выполнение' onclick = 'add_postopened_work("+w_id+");' style='margin-bottom:10px;'><br>";
      //LAST :head_str += json.spec_fio+"</b><br><br><a class='btn' href='javascript:;' onclick='add_postopened_work("+w_id+");' style='margin-bottom:10px;'><i class='fa fa-plus'></i>&nbsp;&nbsp;Взять работу на выполнение</a><br>";    
    } 
    /**************who is postopened work if stat = 2 and it is not you*************/ 
   head_str += "</div>"; //closed #head_div

    
    var info_str = '<ul class="work_menu">';
    if(window.show_one_work==true) { 
      info_str += '<li onclick="show_div_info(\'work_comments_div\');"style="width:115px;display: inline-block;"><a href="javascript:;"><i class="fa fa-comment"></i> Комментарии</a></li>';
      info_str += '<li onclick="show_div_info(\'work_hist_div\');" style="width:97px;display: inline-block;"><a href="javascript:;"><i class="fa fa-history"></i> История</a></li>';
    } else {
      if(json.stat_life!=3) {
        info_str += '<li onclick="show_div_info(\'work_stats_div\');" style="width:97px;display: inline-block;" class="active_work_menu"><a href="javascript:;"><i class="fa fa-wrench"></i> Действия</a></li>';            
      }
      info_str += '<li onclick="show_div_info(\'work_comments_div\');"style="width:115px;display: inline-block;"><a href="javascript:;"><i class="fa fa-comment"></i> Комментарии</a></li>';
      if(json.spec_id == window.spec_id && json.stat_life==1) { 
        info_str += '<li onclick="show_div_info(\'set_spec_div\');" style="width:97px;display: inline-block;"><a href="javascript:;"><i class="fa fa-share"></i> Передать</a></li>';
      }                                                                                                                                                       
      info_str += '<li onclick="show_div_info(\'work_hist_div\');" style="width:97px;display: inline-block;"><a href="javascript:;"><i class="fa fa-history"></i> История</a></li>';
    }
    info_str += '</ul>';
    
    
    
    info_str += '<div id="info_div">'; //START INFO DIV   
    /*******************START INFO DIV*******************/
      /**************statuses and actions with work*************/
      var display_stats = 'none';       
      var trigger_need_start = false;      // TRIGGER = NEED TO AUTO START TIMELINE???
      if((json.spec_id == window.spec_id && json.stat_life!=3) || json.stat_life==2) {
        display_stats = 'block';
      }       
/**/  info_str += "<div id='work_stats_div' class='now_hidden' style='display:"+display_stats+";'></div>";
      var stats_str = '<br><div style="text-align:center;">';
      if(json.stat_life==2) {
        if(json.spec_id == window.spec_id) {
          stats_str += '<a href="javascript:;" onclick="update_work_stat('+w_id+',1);" class="btn"><i class="fa fa-play"></i>&nbsp;&nbsp;&nbsp;Приступить</a>&nbsp;&nbsp;&nbsp;';
        } else {
          stats_str += '<a href="javascript:;" onclick="add_postopened_work('+w_id+',1);" class="btn"><i class="fa fa-plus"></i>&nbsp;&nbsp;&nbsp;Взять на выполнение</a>&nbsp;&nbsp;&nbsp;';
        }          
      } else {  
        if(json.hist_auto!=undefined)  {
        //console.log(json.hist_auto);      
            if(json.hist_auto.length>0) {
              var last_hist_auto = myTrim(json.hist_auto[(json.hist_auto.length-1)].to_dt); 
              //if(json.hist_auto!=undefined && last_hist_auto[4]!=undefined || last_hist_auto[4]!="" || last_hist_auto[4]!=" " || last_hist_auto[4]!=0) {
              if(last_hist_auto) {
                stats_str += '<a href="javascript:;" onclick="add_timeline_auto('+w_id+');" class="btn"><i class="fa fa-play"></i>&nbsp;&nbsp;&nbsp;Приступить</a>&nbsp;&nbsp;&nbsp;';
              } else {
                stats_str += '<a href="javascript:;" onclick="freeze_work('+w_id+');" class="btn"><i class="fa fa-pause"></i>&nbsp;&nbsp;&nbsp;Приостановить</a>&nbsp;&nbsp;&nbsp;';
              }      
            } else {
              stats_str += '<a href="javascript:;" onclick="add_timeline_auto('+w_id+');" class="btn"><i class="fa fa-play"></i>&nbsp;&nbsp;&nbsp;Приступить</a>&nbsp;&nbsp;&nbsp;';
            }
            stats_str += '<a href="javascript:;" onclick="close_work('+w_id+');" class="btn"><i class="fa fa-stop"></i>&nbsp;&nbsp;&nbsp;Завершить</a>&nbsp;&nbsp;&nbsp;';
        } else {
          stats_str += '<a href="javascript:;" onclick="add_timeline_auto('+w_id+');" class="btn"><i class="fa fa-play"></i>&nbsp;&nbsp;&nbsp;Приступить</a>&nbsp;&nbsp;&nbsp;';
        }
      }  
      if(json.stat_life==1) {
        stats_str += '<a href="javascript:;" onclick="update_work_stat('+w_id+',2);" class="btn"><i class="fa fa-share-alt"></i>&nbsp;&nbsp;&nbsp;Отложить и опубликовать</a>&nbsp;&nbsp;&nbsp;';
        if(json.ticket_id!=undefined && json.ticket_id>0) {
          //var trigger_need_end = false;
          /*if(json.hist_auto!=undefined)  {
            console.log(json.hist_auto);      
            if(json.hist_auto.length>0) {
              var last_hist_auto = json.hist_auto[(json.hist_auto.length-1)]; 
              if(last_hist_auto[4]==undefined || last_hist_auto[4]=="" || last_hist_auto[4]==" " || last_hist_auto[4]==0) {
                trigger_need_end = true;
              }
            }
          } */
          if(json.hist == undefined) {        
            trigger_need_start = true;        // TRIGGER = NEED TO AUTO START TIMELINE???
          }          
          //stats_str += '<a href="javascript:;" onclick="cancel_work('+w_id+', '+trigger_need_start+', '+trigger_need_end+');" class="btn"><i class="fa fa-times"></i>&nbsp;&nbsp;&nbsp;Отказаться</a>&nbsp;&nbsp;&nbsp;';
          stats_str += '<a href="javascript:;" id="cancel_work_button" onclick="cancel_work_toggler('+w_id+', '+trigger_need_start+');" class="btn"><i class="fa fa-times"></i>&nbsp;&nbsp;&nbsp;Отказаться</a>&nbsp;&nbsp;&nbsp;';
          stats_str += "<div style='display:none;margin-bottom:10px;' id='update_div'><br><font color='red'>Укажите причину:</font><br><textarea id='cancel_work_textarea' style='width:100%;height:55px;'></textarea><br><br><input type='button' value='Отказаться от выполнения' onclick=\"cancel_work("+w_id+", "+trigger_need_start+");\"></div>";   // comment for update - default disabled
        }
      }        
      stats_str += '</div>';     
      /**************statuses and actions with work*************/
      /**************set new spec*************/
/**/  info_str += '<br><div id="set_spec_div" class="now_hidden" style="display:none;"><table width=100%><tr><td>Выберите специалиста: </td>';
      info_str += '<td><select id="set_spec_selector"><option value="0">Выберите</option>';
      $.each(json.specialists, function(is, rows) {  
        info_str += "<option value='"+rows.id+"'>"+rows.fio+"</option>";
      });
      info_str += '</select></td></tr><tr><td colspan=2 align=center><input type="submit" value="Назначить!" onclick="save_set_spec('+w_id+', '+trigger_need_start+');" style="margin-top:10px;margin-bottom:10px;"></td></tr></table></div>';
      /**************set new spec*************/
      /**************hostory*************/ 
/**/  info_str += "<div id='work_hist_div' class='now_hidden' style='display:none;'></div>";     ///main template for workhist          
      var st = "";
      var separator = " - ";           // separatof for start & stop text
      var divHeight = 24;                 // height for datetime div
      if(json.hist != undefined) {
        if(window.is_mobile==true) {     // if it is mobile
          separator = "<br>"; 
          divHeight = 50;
        }    
        //info_str += "<div style='margin-top:10px;'><a href='javascript:;' class='btn' onclick='show_work_hist();'><i class='fa fa-history'></i>&nbsp;&nbsp;История работы</a></div><div id='work_hist_div' style='display:none;margin-top:8px;'></div>";     ///main template for workhist
        $.each(json.hist, function(i, val) {   //load hist 
          st += "<div class='row_info ";       /// start div for each history
          if(ts_id==val.id) {         // if this row is selected then border=red
            st += "selected_ts";
          } else {
            st += "ts";
          }
          st += "'>";
          
          datetimeStart = val.from_dt.split(" ");
          datetimeStop = val.to_dt.split(" ");      
          
          /*******inputs for editing datetime*********/
          var newhtmlHidden = "<input type='hidden' value='"+datetimeStart[0]+" "+datetimeStart[1]+"' id='start_date_in_history_reserve_"+val.id+"'>\
                          <input type='date' value='"+datetimeStart[0]+"' id='start_date_in_history_"+val.id+"'>\
                          <input type='time' value='"+datetimeStart[1]+"' id='start_time_in_history_"+val.id+"'>\
                          "+separator+"<input type='date' value='"+datetimeStop[0]+"'  id='stop_date_in_history_"+val.id+"'>\
                          <input type='time' value='"+datetimeStop[1]+"'  id='stop_time_in_history_"+val.id+"'>";          
          st += "<div style='padding-left:0px; display:none; height:"+divHeight+"px;' id='htmlHidden_"+val.id+"'>"+newhtmlHidden+"</div>";
          st += "<div style='padding-left:0px;margin-top:25px;margin-bottom:10px;display:none;' id='edit_history_hidden_"+val.id+"'><textarea id='history_comment_"+val.id+"'>"+val.comments+"</textarea></div>";
          /*******inputs for editing datetime*********/
          
          /*******noteditable datetime*********/
          var newhtmlNotEditable = val.from_dt+separator+val.to_dt;      
          st += "<div style='padding-left:18px; height:"+divHeight+"px;'  id='htmlNotEditable_"+val.id+"'>"+newhtmlNotEditable+"</div>";       
          /*******noteditable datetime*********/
           
          if(val.comments!="" && val.comments!=" " && val.comments!=undefined) {   //if timeline comment is not empty
            st += "<div style='padding-left:18px;margin-top:10px;margin-bottom:10px;' id='edit_history_"+val.id+"'>"+val.comments+"</div>";    //show timeline comment
          }
          /******* buttons 'change' and 'del' and 'end' ********/
          if(json.spec_id == window.spec_id && json.stat_life==1) {       // if it is owner of work
            st += "<table width=100%><tr><td width=50% align=center><!--<input type='button' value=' изменить ' id='change_dates_timeline_button_"+val.id+"' onclick=\"change_dates_timeline("+val.id+");\">-->\
                <!--<input type='button' style='margin-top:10px;' value=' закрыть сейчас ' id='close_now_timeline_button_"+val.id+"' onclick=\"close_now_timeline("+val.id+");\">--></td>\
                <td width=50% align=center><input type='button' value=' отменить ' style='display:none;' id='changel_editing_dates_timeline_button_"+val.id+"' onclick=\"changel_editing_dates_timeline_button("+val.id+");\">";
            if(window.is_mobile==true) {
            st += "<br>";
            }      
            st += "&nbsp;<input type='button' style='margin-top:10px;' value=' удалить ' id='delete_timeline_button' onclick='delete_timeline("+val.id+");'></td></tr></table>";
          }
          st += "</div>";       /// stop div for each history
          /******* buttons 'change' and 'del' and 'end' ********/ 
        });  
      } else {
        st += "<div class='row_info' style='text-align:center;margin-top:10px;'><b>" + json.msg + "</b></div>";
      } 
      /**************hostory*************/
     
      /**************comments*************/
/**/  info_str += "<div id='work_comments_div' class='now_hidden' style='display:none;'></div>";
      c_str = "<div style='width:80%;'>";   //start main div for comments with paddings
      if(window.show_one_work==false && json.stat_life!=2 && json.stat_life!=3) {
        c_str = "<textarea style='width:100%; height:100px;' id='work_comment_textarea'></textarea><div style='margin-top:8px;text-align:left;'><input type='button' value='Добавить' class='blue_btn' onclick='add_work_comment("+w_id+");'></div>";
      }
      if(json.messages!=undefined && json.messages.length>0) {
        c_str += "<table width=100% class='small_table' style='margin-top:8px;'><tr class='table_head'><td>Автор</td><td>Дата</td><td>сообщение</td><!--<td>Действия</td>--></tr>";
        $.each(json.messages, function(im, msg) {  
          c_str += "<tr><td>"+msg.creator_fio+"</td><td>"+msg.created+"</td><td>"+msg.text+"</td><!--<td><i class='fa fa-pencil'></i> <i class='fa fa-trash-o'></i></td>--></tr>";
        });
      } else {
        c_str += '<div class="row_info" style="text-align:center;margin-top:10px;"><b>Комментарии отсутствуют</b></div>';
      }
      c_str += "<table>";  
      c_str += "</div>";  //stop main div for comments 
      /**************comments*************/
      
    /*******************STOP INFO DIV*******************/
    info_str += '</div>'; //STOP INFO DIV
    var all_include = head_str + info_str;  //make all html: head+info
    pop.html(all_include).css('max-height', (pf(window.innerHeight)-150)+'px'); //main insert + set max-width
    $("#work_stats_div").html(stats_str);
    $("#work_hist_div").html(st);          //insert all history in 'main insert'
    $("#work_comments_div").html(c_str);
    $("#popup_title").html(json.title);    //inserp popup title   
    
    /**********work_menu style selector********/
    $('.work_menu li').each(function(){
      $(this)
      .mouseover(function() {
        $(this).addClass("hovered_work_menu");
      })
      .mouseout(function() {
        $(this).removeClass("hovered_work_menu");
      })
      .click(function() {
        if($(this).hasClass("active_work_menu")!=true) { 
          $('.work_menu li').each(function(){
            $(this).removeClass("active_work_menu");
          });            
          $(this).addClass("active_work_menu");
        } else {
          $(this).removeClass("active_work_menu");
        }
      })
      .css("cursor", "pointer");
    });
    if(ts_id!=-1) {
      //show_div_info('work_hist_div'); // show work history and set red border in selected history, if opened cron timeline     
    }
    /**********work_menu style selector********/
  });   
}
/****************OPEN WORK INFO POPUP*******************/

/***************MAIN DIV INFO SWITCHER IN WORK INFO************/
function show_div_info(div_name) {
  if(div_name==undefined) {
    div_name='';
  }
  $('#info_div div.now_hidden').each(function(){
    if($(this).attr("id")==div_name) {
      if($("#"+div_name).is(':visible')==true) {
        $(this).hide();
      } else {
        $(this).show();
      }
    } else {
      $(this).hide();
    }
  });
  var wtf    = $("#popup_main_td");
  var height = wtf[0].scrollHeight;
  wtf.scrollTop(height);  
}
/***************MAIN DIV INFO SWITCHER IN WORK INFO************/

/****************ADDING NEW COMMENT TO WORK*******************/
function add_work_comment(wid) {
  load(1); 
  $.getJSON("/json.php?action=add_work_comment&wid="+wid+"&comm="+$("#work_comment_textarea").val(), function(json){
    load(0);      
    alert(json.msg, json.response);
    if(json.response==100) {          
      close_pop();
      //start_calendar();
    } 
  });
}
/****************ADDING NEW COMMENT TO WORK*******************/

/****************ACTIONS WITH TIMELINES*******************/
function change_dates_timeline(id) {
  $("#change_dates_timeline_button_"+id).val('сохранить');
  if($("#htmlNotEditable_"+id).is(':visible')) {
    $("#htmlNotEditable_"+id).hide();
    $("#htmlHidden_"+id).show();
    $("#changel_editing_dates_timeline_button_"+id).show();
    $("#edit_history_hidden_"+id).show();
    $("#edit_history_"+id).hide();
  } else {
    do_change_dates_timeline(id);
  }
}

function close_now_timeline(id) {
  load(1); 
  $.getJSON("/json.php?action=close_now_timeline&tid="+id, function(json){
    load(0);      
    alert(json.msg, json.response);
    if(json.response==100) {          
      close_pop();
      start_calendar();
    } 
  });
}

function changel_editing_dates_timeline_button(id) {
  $("#change_dates_timeline_button_"+id).val('изменить');
  $("#htmlNotEditable_"+id).show();
  $("#htmlHidden_"+id).hide();
  $("#changel_editing_dates_timeline_button_"+id).hide();
  $("#edit_history_hidden_"+id).hide();
  $("#edit_history_"+id).show();
}

function do_change_dates_timeline(id) {
  var datetimesStart = $("#start_date_in_history_"+id).val()+" "+$("#start_time_in_history_"+id).val();
  var datetimesStop = $("#stop_date_in_history_"+id).val()+" "+$("#stop_time_in_history_"+id).val();
  var reserveDatetimeStart = $("#start_date_in_history_reserve_"+id).val(); // if this field < nex start datetime, then AHTUNG!
  var dt_check = $("#start_date_in_history_"+id).val().split('-');
  var t_check = $("#start_time_in_history_"+id).val().split(':');  
  var check_start_ms = new Date(dt_check[0], dt_check[1], dt_check[2], t_check[0], t_check[1], 0, 0).getTime();
  
  var dt_check2 = $("#stop_date_in_history_"+id).val().split('-');
  var t_check2 = $("#stop_time_in_history_"+id).val().split(':');  
  var check_stop_ms = new Date(dt_check2[0], dt_check2[1], dt_check2[2], t_check2[0], t_check2[1], 0, 0).getTime();
  
  var dt_reserve = reserveDatetimeStart.split(' ');   
  var dt_reserveD = dt_reserve[0].split('-');
  var dt_reserveT = dt_reserve[1].split(':');  
  var dt_reserve_ms = new Date(dt_reserveD[0], dt_reserveD[1], dt_reserveD[2], dt_reserveT[0], dt_reserveT[1], 0, 0).getTime();
  if(dt_reserve_ms>check_start_ms) { 
    alert('Машина времени сломалась!\nСтарт не может быть перенесен назад!', -1);
  } else {
    if(check_start_ms<check_stop_ms) {
        load(1);  
        $.getJSON("/json.php?action=update_timelime&tid="+id+"&start="+datetimesStart+"&stop="+datetimesStop+"&comm="+$("#history_comment_"+id).val(), function(json){
          load(0);      
          alert(json.msg, json.response);
          if(json.response==100) {        
            close_pop();
            start_calendar(); 
          } 
        }); 
    } else {
      alert('Окончание не должно быть раньше начала!', -1);
      return false;
    }
  }   
}

function delete_timeline(id) {
  if (confirm("Подтвердите удаление")) { 
    load(1); 
    $.getJSON("/json.php?action=delete_timeline&tid="+id, function(json){
      load(0);
      alert(json.msg, json.response);
      if(json.response==100) {
        close_pop();
        //start_calendar();
      }                                   
    });
  }
}
/****************ACTIONS WITH TIMELINES*******************/

/****************SHOW 'ADD TIMELINE'*******************/
function show_add_timeline() { 
  if($("#add_timeline").is(':hidden')) {
    $("#show_add_timeline").val('Отменить добавление');
    var tsM = new String((new Date().getMinutes()+2)); //поправка на пару минут вперед
    if(tsM<10) {
      tsM = "0"+tsM;
    }
    var tsH = new String(new Date().getHours());
    if(tsH<10) {
      tsH = "0"+tsH;
    }      
    $('#c_date_start').val(new Date().toDateInputValue());   //////////     SET DEFAULT VALUE
    $('#c_date_stop').val(new Date().toDateInputValue());   //////////     SET DEFAULT VALUE
    $('#c_time_start').val(tsH+":"+tsM);                    //////////     SET DEFAULT VALUE
    //$('#c_time_stop').val(new Date().getHours()+":"+new Date().getMinutes());  //////////     SET DEFAULT VALUE
    var TempMinutes = ((new Date().getMinutes()+2)+30);
    var TempHours = new Date().getHours();
    if(TempMinutes>59) {
      TempHours = TempHours+1;
      TempMinutes = TempMinutes-60; 
    }
    if(TempMinutes<10 && TempMinutes>=0) {
      TempMinutes = String("0"+TempMinutes); 
    }
    $('#c_time_stop').val(TempHours+":"+TempMinutes);  
    $("#add_timeline").show();  
  } else {
    $("#show_add_timeline").val('Добавить сроки выполнения');
    $("#add_timeline").hide();
  }
}
Date.prototype.toDateInputValue = (function() {
    var local = new Date(this);
    local.setMinutes(this.getMinutes() - this.getTimezoneOffset());
    return local.toJSON().slice(0,10);
});
/****************SHOW 'ADD TIMELINE'*******************/

/****************getting count of approval tickets****************/
function show_created_tickets_count() {
  $.getJSON('/json.php?action=get_created_tickets_count', function(json){
      var app_cc = $("#tickets_count");
      if(json.response!=undefined) { 
        if(json.response==100) {
          if(json.cc>0) { 
            app_cc.html(" ("+json.cc+")");
          } else {
            app_cc.html("");
          }  
        }  
      } 
    })
}
/****************getting count of approval tickets****************/

/************SET NEW SPEC OF WORK************/
function save_set_spec(w_id,trigger_start) {
  if(trigger_start==undefined) {
    trigger_start = false;
  }
  if($("#set_spec_selector").val()!="" && $("#set_spec_selector").val()!=" " && $("#set_spec_selector").val()!=0) {
    load(1);
    if(trigger_start==true) {   
      add_timeline_auto(w_id);
    }   
    freeze_work(w_id,true);     // TRUE = don't show message 'work freezed'    
    $.getJSON("/json.php?action=save_set_spec&wid="+w_id+"&new_s_id="+$("#set_spec_selector").val(), function(json){
      load(0);
      alert(json.msg, json.response);
      if(json.response==100) {
        close_pop();
        start_calendar(); 
      }      
    });
  } else {
    alert("Выберите специалиста!");
  }
}
/************SET NEW SPEC OF WORK************/

/****************ADDING TIMELINE*******************/
function freeze_work(w_id, trigger) {
  load(1);    
  $.getJSON("/json.php?action=freeze_work_timeline_auto&wid="+w_id, function(json){
    load(0);
    if(trigger==undefined) {
      alert(json.msg, json.response);
      if(json.response==100) {
        close_pop();
        start_calendar();     
      }
    }      
  });
}

function cancel_work_toggler() {
  if ($("#update_div").is(':hidden')) {
    $("#cancel_work_button").html('<i class="fa fa-reply"></i>&nbsp;&nbsp;&nbsp;Отмена');
    $("#update_div").show();
    $( "#cancel_work_textarea" ).focus();
  } else { 
    $("#cancel_work_button").html('<i class="fa fa-times"></i>&nbsp;&nbsp;&nbsp;Отказаться');
    $("#update_div").hide();
  }
}
function cancel_work(w_id, trigger_start) { //, trigger_end) {
  if ($.trim($('#cancel_work_textarea').val()).length < 3 || $.trim($('#cancel_work_textarea').val()) == "") {  
    alert('Укажите причину отказа!');
  } else { 
    $("#cancel_work").html('<i class="fa fa-times"></i>&nbsp;&nbsp;&nbsp;Отказаться');
    load(1);
    if(trigger_start==true) {   
      add_timeline_auto(w_id);
    }   
    freeze_work(w_id,true);     // TRUE = not show message 'work freezed'
    $.getJSON("/json.php?action=cancel_work&wid="+w_id+"&comment="+$('#cancel_work_textarea').val(), function(json){
        load(0);
        alert(json.msg, json.response);
        if(json.response==100) {
          close_pop();
          start_calendar(); 
        }      
    });
  } 
}

function add_timeline(w_id) {  
  var ret = validate_creating_timeline();
  if(ret!=false) { 
    load(1);         
    $.getJSON("/json.php?action=add_work_timeline&wid="+w_id+"&start="+ret['start']+"&stop="+ret['stop']+"&comm="+ret['comm'], function(json){
      load(0); 
      alert(json.msg, json.response);
      if(json.response==100) {
        close_pop();
        start_calendar();  
      }      
    });
  } 
}

function add_timeline_auto(w_id) {
  load(1);
  $.getJSON("/json.php?action=add_work_timeline_auto&wid="+w_id, function(json){
      load(0);
      alert(json.msg, json.response);
      if(json.response==100) {
        if($("#work_popup").is(":visible")) {  // update popup if popup is opened 
          close_pop();
          work_info(w_id, '-1');
        }
        start_calendar();  
      }      
  });
}

function validate_creating_timeline() { 
  var ret = new Object();
  ret['comm'] = $('#c_comments_timeline').val();
  ret['date_start'] = $( "#c_date_start" ).val();
  ret['time_start'] = $('#c_time_start').val();
  ret['date_stop'] = $('#c_date_stop').val();
  ret['time_stop'] = $('#c_time_stop').val();
  ret['start']=ret['date_start']+" "+ret['time_start'];  
  ret['stop']=ret['date_stop']+" "+ret['time_stop'];  
  if(ret['comm']=="" || ret['date_start']=="" || ret['time_start']=="" || ret['date_stop']=="" || ret['time_stop']=="") { 
    alert('Пожалуйста, заполните все поля!'); 
    return false;
  }   
  var dt_check = ret['date_start'].split('-');
  var t_check = ret['time_start'].split(':');
  var check_start_ms = new Date(dt_check[0], (dt_check[1]-1), dt_check[2], t_check[0], t_check[1], 0, 0).getTime();
  var dt_check2 = ret['date_stop'].split('-');
  var t_check2 = ret['time_stop'].split(':');  
  var check_stop_ms = new Date(dt_check2[0], (dt_check2[1]-1), dt_check2[2], t_check2[0], t_check2[1], 0, 0).getTime();
  if(check_start_ms<check_stop_ms) {
    var tmp_now = new Date().getTime();
    if(tmp_now<check_start_ms) {
      return ret;
    } else {
      alert('Машина времени сломалась!\nНачало не должно быть в прошлом!', -1);
      return false;
    } 
  } else {
    alert('Машина времени сломалась!\nОкончание не должно быть раньше начала!', -1); 
    return false;
  }  
}
/****************ADDING TIMELINE*******************/  

/****************ADDING POSTOPENED WORK FROM SOMEBODY*******************/
function add_postopened_work(w_id) {
  if (confirm("Уверены, что хотите прикрепить данную работу?")) {   
    load(1);
    $.getJSON("/json.php?action=add_postopened_work&w_id="+w_id, function(json){
      load(0);
      alert(json.msg, json.response);
      if(json.response==100) {
        close_pop();
        start_calendar(); 
      }      
    });
  }
}                       
/****************ADDING POSTOPENED WORK FROM SOMEBODY*******************/

/****************UPDATING WORK STATUS*******************/
function update_work_stat(w_id,value) {   //ajax updating status of work   
      load(1);
      if(value==1) {
        add_timeline_auto(w_id);  // FIRSTLY CREATE TIMELINES
      } else if(value==2 || value==3) {
        freeze_work(w_id);       // FIRSTLY CLOSE ALL ELSE TIMELINES
      }
      //$.getJSON('/json.php?action=update_work_stat&wid='+w_id+'&ws='+value+'&comment='+$("#update_comment").val(), function(json){
      $.getJSON('/json.php?action=update_work_stat&wid='+w_id+'&ws='+value, function(json){
        alert(json.msg, json.response);
        if(json.response==100) {
          load(0);
          close_pop();
          start_calendar(); 
        } 
      });
}

function close_work(w_id) {
  if (confirm("Уверены, что хотите завершить данную работу?")) {  
  load(1);
  freeze_work(w_id);
  $.getJSON('/json.php?action=update_work_stat&wid='+w_id+'&ws=3&comment=Автоматическое закрытие', function(json){
    alert(json.msg, json.response);
    if(json.response==100) {
      load(0);
      close_pop();
      start_calendar();
    } 
  });
  }
}

/****************UPDATING WORK STATUS*******************/

/****************SHOW 'POPUP ADDING NEW WORK'*******************/
function create_work() {                                 ////////// open popup with work creating
  open_pop();
  pop = $("#"+window.e2);                         // main td in popup 
  var str1 = "<br><br><table width=80%>";
  str1 += '<tr><td valign=top width=15%>Описание</td><td><textarea name="comments" id="c_comments"></textarea></td></tr><tr><td colspan=2 align=left>';
  str1 += '<br><a class="btn" href="javascript:;" onclick="create(\'create_timeline\');"><i class="fa fa-floppy-o"></i>&nbsp;&nbsp;Сохранить</a>';
  pop.html(str1);
  $("#popup_title").html("Создать информацию о занятости");
  show_pop();             
}
/****************SHOW 'POPUP ADDING NEW WORK'*******************/

/****************ADDING NEW WORK*******************/
function create(trigger) {                                           ////////// close popup with work creating and send json to sql create work
  if(trigger==undefined) {
    trigger="";
  }
  var ret = validate_creating();
  if(ret!=false) {      
    load(1);
    $.getJSON("/json.php?action=add_new_work&error_type=1&place=1&comm="+ret['comm'], function(json){
      load(0);
      alert(json.msg, json.response);
      if(json.response==100) {
        if(trigger=="create_timeline") {
          //LET S AUTO ADD TIMELINE;
          add_timeline_auto(json.new_work_id);
        } 
        close_pop();
        start_calendar();
      }   
    });
  } 
}

function validate_creating() { 
  var ret = new Object();
  ret['place'] = $( "#c_place" ).val();
  ret['error_type'] = $('#c_error_type').val();
  ret['comm'] = $('#c_comments').val();
  if(ret['place']=="" || ret['error_type']=="" || ret['comm']=="" ) {
    alert('Пожалуйста, заполните все поля!');
    return false;
  }   
  return ret;
}
/****************ADDING NEW WORK*******************/        

/****************smart trim() function*******************/        
function myTrim(x) {
    return x.replace(/^\s+|\s+$/gm,'');
}
/****************smart trim() function*******************/                      