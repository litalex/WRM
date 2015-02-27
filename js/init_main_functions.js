//*************************config   
window.DEBUG=false;
window.e = "work_popup";
window.e2 = "popup_main_td";    
window.spec_flag_config = 1;
window.god_flag_config = 2;
window.admin_flag_config = 3;
window.sotrudnik_flag_config = 4;
window.dispatcher_flag_config = 5; 
window.use_socket = true;
window.socket_server = 'ADDRESS';
window.comment_length_small = 12;   // for opened 
window.comment_length_big = 21;     // for else
window.comment_length_megabig = 43; // for daily
window.max_tickets_on_page = 20;
window.block_creating = false;      // block creating tickets if have unclosed tickets
window.use_auto_reload = 1;
window.auto_reload_ms = 300000;     //5 minutes
window.browser_is_old=false;
window.max_unclosed_tickets_count = 0;
//reload_session();
$(document)
.keyup(function(e) {
  if (e.keyCode == 27) { close_pop();$('#places_popup').hide();$('.msg_form').hide(); } //close ALL windows if pressed ESC
})
.ready(function() { 
  /***draggable for main popup***/
  $("#popup_title").mousedown(function() {
    $( "#work_popup" ).draggable();
  })
  $("#popup_title").mouseup(function() {
    $( "#work_popup" ).draggable( "destroy" );
  }); 
  /***draggable for main popup***/
  
  /***draggable for messages popup***/
  $("#msg_form_title").mousedown(function() {
    $( ".msg_form" ).draggable();
  })
  $("#msg_form_title").mouseup(function() {
    $( ".msg_form" ).draggable( "destroy" );
  }); 
  /***draggable for messages popup***/
        
  if(window.use_socket==true) {     
    connectToWebsocket({
      onopen: function(event) {
        //alert("CONNECTED");
        //console.log("Connected to websocket");
        $("#socket_stat").html('<font color="green">socket connected</font>');
        sendMessage("Hello World!", {sockets: ["*"], tags: null})
      },
      onmessage: function(event) {
        //console.log("Received message: " + event.data)
      }
    });
  } else {
    $("#socket_stat").html('');
  }
  $( window ).resize(function() {
    if($("#work_popup").is(':hidden')==false) {      
      $("#"+window.e2).css('max-height', (parseFloat(window.innerHeight)-95-50)+'px'); //main insert + set max-width  
      $("#work_popup").css('max-height', (parseFloat(window.innerHeight)-95)+'px'); //main insert + set max-width  
    }         
  });  
});        
//*************************config

/****************POPUP FUNCTIONS*******************/
function close_pop () {                                 ////////// close popup
  $("#overlay").hide();
  $('#'+window.e).hide() ;
  $("#"+window.e2).html('');
  $("#popup_title").html(''); 
  /*****WORKS ROW SELECTOR COLOR******/
  $('#calendar4 .name_of_row').each(function(){
    $(this).css({"background-color": "#D9F0FD","color":"2c567a"});
  });
  /*****WORKS ROW SELECTOR COLOR******/
  /*****TICKETS ROW SELECTOR COLOR******/
  if($("#tickets_table tr")) {
    $.each($("#tickets_table tr").not($("#tickets_table tr.table_head")), function(t_row_id, t_row_val) {
      if($(this).attr('creator_is_dispatcher')!=undefined) {
        $(this).css('background-color','#E8FEFF');
      } else if($(this).attr('ticket_blocked')!=undefined) {
        $(this).css('background-color','#FFCACA');
      } else {
        $(this).css("background-color","white");
      }     
    });
  } 
  /*****TICKETS ROW SELECTOR COLOR******/
  if(window.set_free_ticket>0) { 
    $.getJSON("/json.php?action=set_free_ticket&tid="+window.set_free_ticket, function(json){
      window.set_free_ticket = 0;   
    });
  }
}
function open_pop() {                                         ////////// open popup
  $("#popup_title").html('');
  $("#overlay").show();
  el = $( "#"+window.e );                         // all popup
  pop = $("#"+window.e2);                         // main td in popup  
  pop.html('<img id="loading" src="/images/loading.gif" border=0>');
  //$( "#loading" ).css('margin-top', ((el.height()/2) - 30)+'px'); // --   minus height of loading image
  $( "#loading" ).css({
    "left":(pf($(window).width())/2)-(pf($("#main_loading").width())/2)+"px",
    "top":"120px",
    "position":"fixed"
  });               
  
  //el.css('top', '11px');
  if(window.is_mobile==undefined) {
    el.css('top', '35px');
    el.css('left', ($(window).width()/2) - (el.width()/2)+'px');
  } else {
    el.css('top', '0px');
    el.css('left', '0px');
  }  
  el.css('z-index', '10032'); 
  el.css('max-height', (parseFloat(window.innerHeight)-20)+"px");          
  show_pop();                              // now pop is visible     
}
function show_pop() {
  el = $( "#"+window.e );                         // all popup
  if(window.is_mobile==undefined) {    
    el.css('left', ($(window).width()/2) - (el.width()/2)+'px');  
  } else {
    el.css('left', '0px');
  }         
  el.show(); 
}
/******msg box*****/
function alert(str, resp) { 
  wait = 600;
  if(resp==undefined) {   
    resp = 100;
  } else {
    if(resp!=100) {
      if(window.DEBUG==true) {
        str += "<br><font color='red'>Код ошибки "+resp+"</font>";
      }    
      wait = 3000;
    }
  }
  msgBox(str, wait);
}
function msgBox(str, wait) {
  elmsg = $("#msgBox");                       
  pop = $("#work_popup"); 
  p_h = pf(pop.height());    
  $("#msgContainer").html(str); 
  elmsg.css({
      'left': (pf($(window).width())/2) - (elmsg.width()/2)+'px', 
      'top': pf(pop.css('top')) + (p_h/2) - (pf(elmsg.css('height'))/2) +'px', 
      'z-index': '60052'})
    .show(); 
  function funcMsg() {
    elmsg.fadeOut(1000);
  }          
  setTimeout(funcMsg, wait);  
}
function load(action) {
  $("#main_loading").css({
    "left":(pf($(window).width())/2)-(pf($("#main_loading").width())/2)+"px",
    "top":"120px"
  });
  if(action==1) {
    $("#main_loading").show();
  }
  if(action==0) {
    $("#main_loading").hide();
  }
}
function pf(el) {
  return parseFloat(el);
}
/******msg box*****/
/****************POPUP FUNCTIONS*******************/

/****************ONMOUSEOVER TOOLTIP*******************/
var op;
function tooltip(el,txt) {
  op = 0.1;
  $("#mess").html(unescape(txt));
  $("#mess").css('opacity', op);
  $("#mess").show();
	//el.onmousemove=positiontip;
  el.onmousemove = function(e) {
    e = e || window.event;
    var target = e.target || e.srcElement,
        offsetX = e.clientX,
        offsetY = e.clientY - 45;
    $("#mess").css({top:(offsetY+66)+'px',left:offsetX+'px'});     
  };
  appear();
}
function hide_info(el) {
  $("#mess").hide();
	el.onmousemove='';
}
function appear() {
	if(op < 1) {
		op += 0.1;
    $("#mess").css({"opacity":op, "filter":'alpha(opacity='+op*100+')'});
		t = setTimeout('appear()', 30);
	}
}
/*function positiontip() {
  $("#mess").css({top:(event.pageY+22)+'px',left:event.pageX+'px'});
} */
/****************ONMOUSEOVER TOOLTIP*******************/ 

/************MESSAGE FROM SOCKET************/
function socket_info(msg) {
  //console.log("Socket says: "+msg); 
  if(msg=="new_ticket_added") {
    if(window.spec_flag==window.dispatcher_flag_config) {
      alert("Появилась новая заявка!",400);
      start_tickets(1);
    } 
  }
}
/************MESSAGE FROM SOCKET************/

/************ADD TICKET MESSAGE************/
function show_add_ticket_msg(tid, stat, show_adding, is_system) {
  if(is_system==undefined) {
    var is_system = 0;
  }
  $("#add_ticket_message_button").html('<a class="btn" href="javascript:;" onclick="add_ticket_msg('+is_system+');" >Добавить</a>');
  $("#error_msg_div").hide();
  if(tid==-1) {
    $(".msg_form").hide();
    //$(".msg_form").draggable("destroy");
    $("#ticket_description").val('');
    $("#ticket_id").val('');
  } else {  
    if($(".msg_form").is(':hidden')) {
      $("#ticket_id").val(tid);
      load(1);
      var json_url = "/json.php?action=get_ticket_msgs&tid="+tid;
      if(is_system==1) {
        json_url = "/json.php?action=get_ticket_msgs&tid="+tid+"&iss=1";
      }
      $.getJSON(json_url, function(json){
        load(0); 
        if(json.response==100) {
          var msgs = "";  
          if(is_system==1) {    // if it is system messages - we can add msg!
            $("#add_ticket_message_table").show(); 
          } else {               
            if(stat==4 || window.show_one_work==true || show_adding==false || stat==3) {
              if(stat == 3 && window.spec_flag==window.sotrudnik_flag_config) {
                $("#error_msg_div").html('<br><font color="red">Для возможности добавления сообщения, необходимо отказать в подтверждении закрытия заявки, тем самым сделав ее снова активной</font>').show();
              } 
              $("#add_ticket_message_table").hide();
            } else {
              $("#add_ticket_message_table").show();
            } 
          }      
          if(json.msg_counter>0) {
            msgs += "<br><br><table width=100% id='tickets_msg_table' style='border: 1px solid black;border-collapse: collapse;'><tr class='table_head'><td>Автор</td><td>Создано</td><td>Сообщение</td><td>Статус</td></tr>";          
            $.each(json.ticket_msg, function(i, row) {   
              if(row.readed==0) {
                row.readed = "Непрочитано";  
              } else {
                row.readed = "Прочитано";
              }
              msgs += "<tr><td>"+row.fio+"</td><td>"+row.created+"</td><td>"+row.text+"</td><td>"+row.readed+"</td></tr>";
            }); 
            msgs += "</table>";
          } else {
            msgs += "<br><br>Переписка пуста";  
          }         
          $("#msg_form_div").html(msgs);
          $(".msg_form").css({
              "left":(pf($(window).width())/2)-345+"px",
              "top":"90px"})
            .show();
          //$( ".msg_form" ).draggable();
          $("#msg_form_title").html("Переписка");
        }      
      });
    } else {
      $(".msg_form").hide();
      //$(".msg_form").draggable("destroy");
      $("#ticket_description").val('');
      $("#ticket_id").val('');
    }
  }
}
function add_ticket_msg(is_system) {
  if(is_system==undefined) {
    var is_system = 0;
  }    
  if($("#ticket_description").val()) {    
    $(".msg_form").hide();    
    load(1);
    $.getJSON("/json.php?action=add_ticket_msgs&tid="+$("#ticket_id").val()+"&iss="+is_system+"&msg="+$("#ticket_description").val().replace(/["']/g, ''), function(json){
        load(0);
        alert(json.msg, json.response);
        if(json.response==100) {
          get_ticket_message_counter($("#ticket_id").val(),is_system);     
          show_add_ticket_msg(-1);   
        }  
    });  
  } else {
    alert('Заполните комментарий');
  }
}
function get_ticket_message_counter(ticket_id,is_system) {
  if(is_system==undefined) {
    var is_system = 0;
  }   
  $.getJSON("/json.php?action=get_ticket_message_counter&tid="+ticket_id+"&iss="+is_system, function(json){
    if(json.response==100 && json.msg_counter>0) {      
    
      if(is_system==1) {
        $("#ticket_sys_message_counter").html(" <font color=white>("+json.msg_counter+")</font>");
      } else {
        $("#ticket_message_counter").html(" <font color=white>("+json.msg_counter+")</font>");
      }
    } 
  });
}
/************ADD TICKET MESSAGE************/

/************ADD TICKET SYSTEM MESSAGE************/
function show_add_ticket_sys_msg(tid, stat) {
 show_add_ticket_msg(tid, 1, true, 1);
}
/************ADD TICKET SYSTEM MESSAGE************/

/****************SHOW 'POPUP SPEC PROFIL'*******************/
function spec_profil() {                                 ////////// open popup with spec info  
  open_pop(); 
  pop = $("#"+window.e2);                         // main td in popup 
  var str1='<br><br><table width=80% border=0 id="profil_table"><tr><td id="rows_to_del"></td></tr>'; 
  $.getJSON('/json.php?action=get_spec_info', function(json){
    if(json.response==100) {
      var have_ids = [];
      $.each(json.cont, function(i, row) { 
        have_ids.push(row.id);  
        str1 += "<tr id='tr_"+row.row_id+"'><td width=";
        if(window.is_mobile==true) {
          str1 += "140";
        } else {
          str1 += "320";
        }
        str1 += ">"+row.type+"</td><td width=254 align=left><input type='text' name='"+row.row_id+"' id='profil' value='"+row.value+"' style='width:200px;'>";     
        str1 +=" <input type='button' value=' - ' onclick='del_exist_profil_row("+row.row_id+");' stle='size:20px;font-size:20px;'></td></tr>"; 
      }); 
      str1 += "</table><table width=80% border=0>";
      str1 += '<tr><td align=left style="width:';
      if(window.is_mobile==true) {
        str1 += "140";
      } else {
        str1 += "320";
      }
      str1 += 'px;"><input type="button" value="Сохранить" onclick="spec_profil_save();return true;" ></td><td width=254 align=left><input style="width:200px;" type="button" value=" + " onclick="add_profil_row(\'';
      //str1 += have_ids;
      str1 += '\');return true;" ></td></tr><tr><td><br><a href="javascript:;" onclick="show_new_pass();" class="btn">Сменить пароль</a></td><td></td></tr></table><br>';
      pop.html(str1); 
      $("#popup_title").html(json.title);    //inserp popup title    
      show_pop();
    } else {
      alert(json.msg, json.response);
    }    
  });                     
}
/****************SHOW 'POPUP SPEC PROFIL'*******************/

/****************EDITING 'POPUP SPEC PROFIL'*******************/
function add_profil_row(need_ids) {               //add one row
  var need_ids = eval('['+need_ids+']');
  $.getJSON('/json.php?action=get_all_contact_types', function(json){ 
    if(need_ids!=undefined && need_ids.length>0) {
      $.each(need_ids, function( index, value ) {
      var rot_id_rand = Math.floor((Math.random() * (99999999 - 10000000)) + 10000000);  
      var select = "<select id='select_"+rot_id_rand+"' style='width:140px;' readonly>"; 
      $.each(json.contact_types, function(i, row) {
        if(pf(row.id)==value) {
          select += "<option value='"+row.id+"' >"+row.value+"</option>";  
        }               
      }); 
      select += "</select>";  
      $("#profil_table").append("<tr id='tr_"+rot_id_rand+"'><td>"+select+"<font color='red'>*</font></td><td><input type='text' id='profil_new' name='"+rot_id_rand+"' value='' style='width:200px;'> <input type='button' value=' - ' onclick='del_profil_row("+rot_id_rand+");' stle='size:20px;font-size:20px;'></td></tr>");
      });
    } else {
      var rot_id_rand = Math.floor((Math.random() * (99999999 - 10000000)) + 10000000);  
      var select = "<select id='select_"+rot_id_rand+"' style='width:140px;'>"; 
      $.each(json.contact_types, function(i, row) {
        select += "<option value='"+row.id+"' >"+row.value+"</option>";  
      }); 
      select += "</select>";  
      $("#profil_table").append("<tr id='tr_"+rot_id_rand+"'><td>"+select+"<font color='red'>*</font></td><td><input type='text' id='profil_new' name='"+rot_id_rand+"' value='' style='width:200px;'> <input type='button' value=' - ' onclick='del_profil_row("+rot_id_rand+");' stle='size:20px;font-size:20px;'></td></tr>");
    }    
  }); 
}
function del_profil_row(id) {
  $("#tr_"+id).remove();  
}

function del_exist_profil_row(id) {
  del_profil_row(id); 
  var rot_id_rand = Math.floor((Math.random() * (99999999 - 10000000)) + 10000000);  
  $("#rows_to_del").append("<input type='hidden' id='profil_del' name='"+rot_id_rand+"' value='"+id+"'>");      
}

function spec_profil_save() {                                           ////////// save all data in spec profil
  var ret = validate_spec_profil_str();
  if(ret!=false && ret!=undefined) {                            
    $.getJSON('/json.php?action=spec_profil_edit'+ret, function(json){  
      alert(json.msg, json.response);
      if(json.response==100) {
        close_pop();
      } 
    });
  } else {
    alert("Ошибка введенных данных");
  }
}
function validate_spec_profil_str() { 
  var str = '';
  $('input[type=text][id=profil]').each(function(){
      str += "&prof["+$(this).attr("name")+"]="+$(this).val();
  });
  $('input[type=text][id=profil_new]').each(function(){
    if($(this).val()!='' && $(this).val()!=' ' && $(this).val()!=undefined) {
      str += "&prof_n["+$("#select_"+$(this).attr("name")+" :selected").val()+"]="+$(this).val();
    }
  }); 
  $('input[type=hidden][id=profil_del]').each(function(){
      str += "&prof_del["+$(this).attr("name")+"]="+$(this).val();
  });   
  if(window.DEBUG) {
    console.log(str);
  } 
  if(str!="" && str!=" ") {   
    return str;
  } else {
    return false;
  }
}                       
/****************EDITING 'POPUP SPEC PROFIL'*******************/

/****************Auto check for contacts phone and email*******************/
function auto_ckeck_contacts() {
  $.getJSON("/json.php?action=auto_ckeck_contacts", function(json){        
    if(json.response==100) {
      var ErrTxt = '';    
      if(json.auto_ckeck_contacts[0]=="" || json.auto_ckeck_contacts[0]==" " || json.auto_ckeck_contacts[0]==undefined) {  
        ErrTxt+="<br>- Укажите городской телефон";                                                                        
      }
      if(json.auto_ckeck_contacts[1]=="" || json.auto_ckeck_contacts[1]==" " || json.auto_ckeck_contacts[1]==undefined) {
        ErrTxt+="<br>- Укажите внутренний телефон";
      } 
      if(json.auto_ckeck_contacts[2]=="" || json.auto_ckeck_contacts[2]==" " || json.auto_ckeck_contacts[2]==undefined) {
        ErrTxt+="<br>- Укажите e-mail адрес";
      }            
      if(ErrTxt!='') {
        alert('Заполните свой профиль:'+ErrTxt,411);      
        spec_profil();
        if(json.auto_ckeck_contacts[0]=="" || json.auto_ckeck_contacts[0]==" " || json.auto_ckeck_contacts[0]==undefined) {  
          add_profil_row('2');
        }
        if(json.auto_ckeck_contacts[1]=="" || json.auto_ckeck_contacts[1]==" " || json.auto_ckeck_contacts[1]==undefined) {
          add_profil_row('5');
        }
        if(json.auto_ckeck_contacts[2]=="" || json.auto_ckeck_contacts[2]==" " || json.auto_ckeck_contacts[2]==undefined) {
          add_profil_row('1');
        }
                                      
      }  
    }      
  });
}
/****************Auto check for contacts phone and email*******************/
/****************explode mega row to rows*******************/
function text_explode(txt) {
  var max_letters = 70, tmp = '', counter=0;;
  var arr = txt.split(',');
  $.each(arr, function( index, value ) {
    if(counter>max_letters) {
      //counter = 0;
      //tmp += "<br>";
    }
    counter += value.length;
    tmp += value;
    if(index<(arr.length-1)) {
     tmp += ", ";
    } 
  });      
  return tmp;  
}
/****************explode mega row to rows*******************/

/***********print ticket function*********/
function print_this(id) {
  var txt = $("#"+id).html();
  var win = window.open("", "Title", "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=800, height=600, top=0, left=0");
  win.document.body.innerHTML = txt;
  win.focus();
  win.print();   
}
/***********print ticket function*********/

/***********change pass*********/
function show_new_pass() {
  open_pop(); 
  pop = $("#"+window.e2);                         // main td in popup 
  var str1 = "<br><br><table width=300><tr><td>Пароль</td><td><input type='text' name='pss1' value='' id='new_pss1'></td></tr><tr><td>Подтверждение</td><td><input type='text' name='pss2' value='' id='new_pss2'></td></tr><tr><td colspan=2 align=center><br><a href='javascript:;' onclick='set_new_pass();' class='btn'>Сохранить</a>&nbsp;&nbsp;&nbsp;<a href='javascript:;' onclick='spec_profil();' class='btn'>отменить</a></td></tr></table>";
  
  pop.html(str1); 
  $("#popup_title").html('Смена пароля');    //inserp popup title    
  show_pop(); 
}

function set_new_pass() {
  var p1 = $("#new_pss1").val();
  var p2 = $("#new_pss2").val();
  if(p1!=p2) {
    alert("Пароли не совпадают!");
  } else if(p1.length<3) {
    alert("Пароль слишком маленький!");
  } else {
    $.getJSON('/json.php?action=set_new_pass&password='+p1, function(json){  
      alert(json.msg, json.response);
      if(json.response==100) {
        close_pop();
      } 
    });  
  }
}
/***********change pass*********/

/*****autoreload tickets ses****/
/*    not used coz isset autoreload
function reload_session() {
  setTimeout(function(){
      $.ajax({
        url: "/json.php?action=update_ses",
        cache: false
      });
      reload_session();
  }, window.auto_reload_ms);
}             */
/*****autoreload tickets ses****/                        

function pr(variable, comment) {
	if (console.log) {
		if (comment) {
			console.log(comment+':',variable);
		} else {
			console.log(variable);
		}
	} else {
		alert(variable);
	}
}                                                           