function json2url(json){
	json.t=Math.random();
	
	var arr=[];
	for(var name in json){
		arr.push(name+'='+json[name]);
	}
	return arr.join('&');
}
function ajax(json){
	var timer=null;
	json=json || {};
	if(!json.url){
		alert('url不存在');
		return;	
	}
	
	json.type=json.type || 'get';
	json.time=json.time ||  3;
	json.data=json.data || {};
	
	if(window.XMLHttpRequest){
		var oAjax=new XMLHttpRequest();
	}else{
		var oAjax=new ActiveXObject('Microsoft.XMLHTTP');	
	}
	
	
	switch(json.type.toLowerCase()){
		case 'get':
			oAjax.open('GET',json.url+'?'+json2url(json.data),true);
			
			oAjax.send();
			break;
		case 'post':
			oAjax.open('POST',json.url,true);
			oAjax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
			oAjax.send(json2url(json.data));
			break;
	}
	
	json.loadingFn && json.loadingFn();
	
	oAjax.onreadystatechange=function(){
		if(oAjax.readyState==4){
			if(oAjax.status>=200 && oAjax.status<300 || oAjax.status==304){
				clearTimeout(timer);
				json.success && json.success(oAjax.responseText);	
			}else{
				clearTimeout(timer);
				json.error && json.error(oAjax.status);
			}
		}	
	}
	
	//超时
	timer=setTimeout(function(){
		alert('网络超时');
		oAjax.onreadystatechange=null;
	},json.time*1000);
}
















