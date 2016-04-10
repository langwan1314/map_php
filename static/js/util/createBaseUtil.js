/**
 * 创建base和title标签的内容（多用于load加载页面的情况）
 */
var PROJECT_NAME;
function createBase() {
	var l = (window.location+'').split('/');
	var basehrefURL = (l[0]+'//'+l[2]+'/');
	if(l[3] && l[3] == 'cecgw'){
		basehrefURL += (l[3]+'/');
	}
	if(document.getElementsByTagName("base").length == 0){
		var base = document.createElement('base');
		base.href = basehrefURL;
		document.getElementsByTagName('head')[0].appendChild(base);
	}else{
		document.getElementsByTagName("base")[0].href = basehrefURL;
	}
}
function createTitle() {
	var title = loadProjectName();
	
	var scripts = document.getElementsByTagName("meta");
	var scriptLength = scripts.length;
	if(scriptLength > 0){
		for(var i=0; i < scriptLength; i++){
			if(scripts[i].getAttribute("name") && scripts[i].getAttribute("name") == "title"){
				if(scripts[i].getAttribute("content") != ""){
					title += "-"+scripts[i].getAttribute("content");
					break;
				}
			}
		}
	}
	
	if(document.getElementsByTagName("title").length == 0){
		var titleTag = document.createElement('title');
		document.getElementsByTagName('head')[0].appendChild(titleTag);
	}
	var titles = document.getElementsByTagName("title");
	var titleLength = titles.length;
	if(titleLength > 0){
		for(var i=0; i < titleLength; i++){
			titles[i].innerHTML = title;
		}
	}
}

function loadProjectName(){
	var proName = "实训平台";
      var xmlhttp;
      try {
          xmlhttp = new XMLHttpRequest();
      } catch (e) {
          xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
      }

      xmlhttp.onreadystatechange = function() {
          if (4 == xmlhttp.readyState) {
              if (200 == xmlhttp.status) {
                  var data = xmlhttp.responseText;
                  proName = data.split(",")[2].split(":")[1];
                  proName = proName.substring(1,proName.length-1);
                  
              }
          }
      }

      xmlhttp.open("post", "global/projectname.json", false);
      xmlhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
      xmlhttp.send("");
      PROJECT_NAME = proName;
	return proName;
}


createBase();
createTitle();