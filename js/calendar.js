//var args = window.dialogArguments;
var elem = 0;
var WeekTitles=new Array('По','Вт','Ср','Чт','Пт','Сб', 'Вс');
function showallweektitles()
{
	var i, answer="<tr>\n"
	for(i=0;i<WeekTitles.length;i++)
  {
    if( i == 6 )
      answer+="<th "+'class="sunday"'+">";
  else
      answer+="<th>";
    answer+=WeekTitles[i]+"</th>\n";
  }
	answer+="</tr>\n"
	return answer
}

//function calendar_show(m, d, y, element)
function calendar_show(date, element)
{
  var m_names = new Array("Январь", "Февраль", "Март", 
  "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", 
  "Октябрь", "Ноябрь", "Декабрь");

  elem = element;
  // get calendar position;
  var curleft = curtop = 0;
  var offset = element.offsetHeight;
	if (element.offsetParent)
  {
		curleft = element.offsetLeft
		curtop = element.offsetTop
		while (element = element.offsetParent) {
			curleft += element.offsetLeft
			curtop += element.offsetTop
		}
	}
  var calendar = document.getElementById('calendar');
  calendar.style.top = curtop + offset;
  calendar.style.left = curleft;
  if( date )
  {
    var date_arr = date.split('.');
    var y = date_arr[2];
    var m = date_arr[1];
    if( m != "10") m = m.replace("0", "");
    m = parseInt(m);
    var d = parseInt(date_arr[0]);

/*    var date_arr = date.split('-');
    var y = date_arr[0];
    var m = date_arr[1];
    if( m != "10") m = m.replace("0", "");
    m = parseInt(m);
    var d = parseInt(date_arr[2]);*/
  }
  else
  {
    var currentTime = new Date();
    var y = currentTime.getFullYear();
    var m = currentTime.getMonth() + 1;
//    m = parseInt(m);
    var d = currentTime.getDate();
  }
	var sdate= new Date(m+'/1/'+y);
	var mdate= new Date(m+'/'+d+'/'+y);
	var todaydate= new Date();
	var days=dayonmonth(m,y);
	var iday=0, day, answer='';
  //alert(mdate);
	answer+='<div class="calendar_head">'
	answer+='<span onClick="calendar_move(\'-\',\'y\','+m+','+d+','+y+')" CLASS="calendar_button_0_1" onMouseOver="this.className=\'calendar_button_1_1\'" onMouseOut="this.className=\'calendar_button_0_1\'" ><<</span>'
	answer+='<span onClick="calendar_move(\'-\',\'m\','+m+','+d+','+y+')" CLASS="calendar_button_0_2" onMouseOver="this.className=\'calendar_button_1_2\'" onMouseOut="this.className=\'calendar_button_0_2\'"><</span>'
	answer+=m_names[m-1]+' '+y
	answer+='<span onClick="calendar_move(\'+\',\'m\','+m+','+d+','+y+')" CLASS="calendar_button_0_2" onMouseOver="this.className=\'calendar_button_1_2\'" onMouseOut="this.className=\'calendar_button_0_2\'">></span>'
	answer+='<span onClick="calendar_move(\'+\',\'y\','+m+','+d+','+y+')" CLASS="calendar_button_0_1" onMouseOver="this.className=\'calendar_button_1_1\'" onMouseOut="this.className=\'calendar_button_0_1\'">>></span>'
	answer+='</div>'
	answer+='<table class="calendar">'
	answer+=showallweektitles();
  answer+='<tr>';
  
	var SuDay = sdate.getDay();
  if(SuDay == "0") {
    SuDay = 7;
  }
  
  for(day=1;day<SuDay;day++){
		iday++;
		answer+="    <td></td>" ;
	}
  //alert(days); // количество дней в месяце  (31)
	for(day=1;day<=days;day++)
  {  
		iday++;   // порядковый номер дня недени (0-6)
    //alert((todaydate.getMonth()+1));
//			answer+="<td onClick=\"close_calendar('"+y+"-"+m+"-"+day+"', '"+elem.id+"');\" class=\"calendar_today "+( iday == 7 ? 'sunday': '' )+"\">"+day+"</td>";
		if((todaydate.getMonth()+1)==m && todaydate.getDate()==day && todaydate.getFullYear()==y)
			answer+="<td onClick=\"close_calendar('"+y+"-"+m+"-"+day+"', '"+elem.id+"');\" class=\"calendar_today "+( iday == 7 ? 'sunday': '' )+"\">"+day+"</td>";
		else
			answer+="<td onClick=\"close_calendar('"+y+"-"+m+"-"+day+"', '"+elem.id+"');\" class=\"calendar "+( iday==7 ? 'sunday': '' )+"\">"+day+"</td>";
//			answer+="<td onClick=\"close_calendar('"+y+"-"+m+"-"+day+"', '"+elem.id+"');\" class=\"calendar "+( iday==7 ? 'sunday': '' )+"\">"+day+"</td>";

		if(iday==7)
    {
			answer+="</tr><tr>";
			iday=0;
		}
	}
	answer+="  </tr>";
	answer+='</table><div class="calendar_foot"></div>';
  calendar.innerHTML = answer;
}
function dayonmonth(m, y){ // Функция, для определения количества дней в месяце
	var answer;
	if(m!=2){ // Если не "Февраль"
		var date1=new Date(m+'/31/'+y);
		var mm=(m<12)?(m+1):1;
		var yy=(m<12)?y:(y+1);
		var date2=new Date(mm+'/1/'+yy);
		answer=(date1.getDay()==date2.getDay())?'30':'31';
	}else{
		var date1=new Date(m+'/29/'+y);
		var mm=(m<12)?(m+1):1;
		var yy=(m<12)?y:(y+1);
		var date2=new Date(mm+'/1/'+yy);
		answer=(date1.getDay()==date2.getDay())?'28':'29';
	}
	return answer
}
function calendar_move(to, index, m, d, y)
{
	switch(to){
		case('-'):
			switch(index){
				case('m'):
					if(m>1){
						m--
					}else{
						m=1
						y--
					}
					break
				case('d'):
					var mm=(m>1)?--m:1
					var yy=(m>1)?y:--y
					var days=dayonmonth(mm, yy)
					if(d>1){
						d--
					}else{
						d=days
						m=mm
						y=yy
					}
					break
				case('y'):
					y--
					break
			}
			break
		case('+'):
			switch(index){
				case('m'):
					if(m<12){
						m++
					}else{
						m=1
						y++
					}
					break
				case('d'):
					var mm=(m<12)?++m:1
					var yy=(m<12)?y:++y
					var days=dayonmonth(m, y)
					if(d<days){
						d++
					}else{
						d=1
						m=mm
						y=yy
					}
					break
				case('y'):
					y++
					break
				}
			break
	}
//  var date = y+'-'+m+'-'+d;
  var date = d+'.'+m+'.'+y;
	calendar_show(date, elem)
}
function toggle_calendar( elem, e )
{
  var form = document.getElementById('selection_form');
  date = $("#this_date").val();


  if(!e) var e = window.event;
  if(e.stopPropagation) e.stopPropagation();
  else e.cancelBubble = true;
  if(e.preventDefault) e.preventDefault();
  else e.returnValue = false;

  var calendar = document.getElementById('calendar');
  
  //calendar
  
    
  if( calendar.style.display == 'block' )
  {
    calendar.style.display = 'none';
  }
  else
  {
    calendar.style.display = 'block';
    calendar_show( date, elem );
  }
}
function close_calendar( date, elem_id )
{
  var calendar = $( '#calendar' ).hide();
  $("#"+elem_id).val(date);
}