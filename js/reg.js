
/****************USER REGISTARTION*******************/
function show_reg() {               // show popup
  open_pop();                         
  pop = $("#"+window.e2);
  $("#"+window.e).css('z-index', 10032);
  $("#"+window.e).css('top', ($(window).height()/2) - ($("#"+window.e).height()/2)+'px'); 
  var reg_html = "<br><br><table width=80%>";
  reg_html += "<tr><td width=50%>Логин</td><td width=50%><input style='width:100%;' type='text' id='u_login' placeholder='Логин'></td></tr>";
  reg_html += "<tr><td>Имя</td><td><input style='width:100%;' type='text' id='u_name' placeholder='Имя'></td></tr>";
  reg_html += "<tr><td>Фамилия</td><td><input style='width:100%;' type='text' id='u_sur' placeholder='Фамилия'></td></tr>";
  reg_html += "<tr><td>Отчество</td><td><input style='width:100%;' type='text' id='u_patr' placeholder='Отчество'></td></tr>";
  reg_html += "<tr><td>Пароль</td><td><input style='width:100%;' type='text' id='u_pass1' placeholder='Пароль'></td></tr>";
  reg_html += "<tr><td>Подтверждение</td><td><input style='width:100%;' type='text' id='u_pass2' placeholder='Подтверждение'></td></tr>";
  reg_html += "<tr><td>E-mail</td><td><input style='width:100%;' type='email' id='u_mail' placeholder='E-mail'></td></tr>";
  reg_html += "<tr><td>Защита от роботов</td><td><input style='width:100%;' type='text' id='fool_check' placeholder='Три букы данной организации'></td></tr>";
  reg_html += "<tr><td colspan=2 align=center><br><br><input type='submit' value='Зарегистрироваться' onclick='create_spec();'></td></tr>";
  reg_html += "</table>";                       
  pop.html(reg_html);
  $("#popup_title").html("Регистрация");    //inserp popup title  
}
function create_spec() {         // do reg    
  var retT = validate_spec_reg();
  if(retT!=false) { 
    $("#loading").show();                 
    $.getJSON("/json.php?action=reg&n="+retT['u_name']+"&s="+retT['u_sur']+"&p="+retT['u_patr']+"&l="+retT['u_login']+"&pss="+retT['u_pass1']+"&mail="+retT['u_mail']+"&fc="+retT['fool_check'], function(json){
      //console.log(json);
      $("#loading").hide(); 
      alert(json.msg, json.response);
      if(json.response==100) {
        close_pop();
      }      
    });
  } 
}  
function validate_spec_reg() {   //validating 
  var ret = new Object();
  ret['u_login'] = $( "#u_login" ).val();
  ret['u_name'] = $('#u_name').val();
  ret['u_sur'] = $('#u_sur').val();
  ret['u_patr'] = $('#u_patr').val();
  ret['u_pass1'] = $('#u_pass1').val();
  ret['u_pass2'] = $('#u_pass2').val();  
  ret['u_mail'] = $('#u_mail').val();     
  ret['fool_check'] = $('#fool_check').val();    
  if(ret['u_login']=="" || ret['u_name']=="" || ret['u_sur']=="" || ret['u_patr']=="" || ret['u_pass1']=="" || ret['u_pass2']=="" || ret['fool_check']=="") {
    alert('Пожалуйста, заполните все поля!');
    return false;
  } else {
    if(ret['u_pass1']!=ret['u_pass2']) {
      alert('Пароли не совпадают!');
      return false;
    } else {  
      if(validateEmail(ret['u_mail'])) { 
        //console.log(validateEmail(ret['u_mail']));
        return ret;
      } else {
        alert('Укажите валидный e-mail!');
        return false;
      }
    }  
  }  
}
function validateEmail(email) { 
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
} 
/****************USER REGISTARTION*******************/