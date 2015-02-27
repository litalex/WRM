function redirect(MobileURL){
	try {
		// avoid loops within mobile site
		if(document.getElementById("dmRoot") != null)
		{
			return;
		}
		var noredirect = document.location.search;
		if (noredirect.indexOf("no_redirect=true") < 0){
			if ((navigator.userAgent.match(/^[^\[]*(iPhone|iPod|BlackBerry|Android.*Mobile|BB10.*Mobile|webOS|Windows CE|IEMobile|Opera Mini|Opera Mobi|HTC|LG-|LGE|SAMSUNG|Samsung|SEC-SGH|Symbian|Nokia|PlayStation|PLAYSTATION|Nintendo DSi).*$/im)) ) {
				//location.replace(MobileURL);
			}
		}	
	}
	catch(err){}
}