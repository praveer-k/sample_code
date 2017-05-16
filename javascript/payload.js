//payloadObject = { target : "",
//					refreshRate : "",
// 				    preloader : { target : "",
//								  type : "gif/progressBar",
//								  color: ""
//								  background: {color:"", image:""},
//							      image : {src: "", height:"", width:""},
//					          }
//				   }
var Payload = function(payloadObject){
//static variables common to all payload objects
	Payload.requestInProgress = false;
	Payload.requestTimeOut = 0;
	Payload.count = 0;
	Payload.prevPriority = 0;
//reference variables
	var thisObject,dataLoader,refreshInterval;
//payload initiallise [ settings ] variables
	this.name="";
	this.history = [];
	this.state = -1;
	this.maxState = -1;
	this.prevAction = "";
	this.preloader = { target:"body",
					   type:"gif/progressBar",
					   color:"#000000",
					   background: { color:"none",
									 image:"none" },
					   image: { src:"img/preloader.gif",
							    height:"auto",
							    width:"auto" }
					 };
	if(typeof(payloadObject)!=="undefined"){
		this.name = (typeof(payloadObject.name)==="string")?payloadObject.name:null;
		this.target = (typeof(payloadObject.target)==="string")?payloadObject.target:"body";
		this.refreshRate = (typeof(payloadObject.refreshRate)==="number")?payloadObject.refreshRate:null;

		if(typeof(payloadObject.preloader)==="object"){
			this.preloader.target = (typeof(payloadObject.preloader.target)==="string")?payloadObject.preloader.target:"body";
			this.preloader.type = (typeof(payloadObject.preloader.type)==="string")?payloadObject.preloader.type:"";
			this.preloader.color = (typeof(payloadObject.preloader.color)==="string")?payloadObject.preloader.color:"#000000";
			if(typeof(payloadObject.preloader.background)==="object"){
				this.preloader.background.color = (typeof(payloadObject.preloader.background.color)==="string")?payloadObject.preloader.background.color:"none";
				this.preloader.background.image = (typeof(payloadObject.preloader.background.image)==="string")?payloadObject.preloader.background.image:"none";
			}
			if(typeof(payloadObject.preloader.image)==="object"){
				this.preloader.image.src = (typeof(payloadObject.preloader.image.src)==="string" && re_weburl.test(payloadObject.preloader.image.src))?payloadObject.preloader.image.src:"img/preloader.gif";
				this.preloader.image.height = (typeof(payloadObject.preloader.image.height)==="string")?payloadObject.preloader.image.height:"auto";
				this.preloader.image.width = (typeof(payloadObject.preloader.image.width)==="string")?payloadObject.preloader.image.width:"auto";
			}
		}
	}else{
		this.target = "body";
		this.refreshRate = null;
	}
	this.errorMsg = { target: "#popUp",
				  message:  "Error : Either the request failed (file not found) <br/>or<br/> was canceled .",
				  color:"#000000",
				  background: { color:"none",
								 image:"none" },
				  image: { src:"img/robot-network-icon.png",
						    height:"auto",
						    width:"auto" }
				 };
//private utility functions
	//================== DOM General Purpose Functions ====================
	function revomeChildNodes(obj){
		var nodes = obj.childNodes;
		if(nodes!=undefined || nodes!=null){
			for(var i=0; i<nodes.length; i++)
				obj.removeChild(nodes[i]);
		}
	}
	function findBaseName(url) {
	    var fileName = url.substring(url.lastIndexOf('/') + 1);
	    var dot = fileName.lastIndexOf('.');
	    return dot == -1 ? fileName : fileName.substring(0, dot);
	}
	function at(eleName,callback){
		var ele = eleName.substr(1,eleName.length-1);
		//console.log("element : " + ele);
		if(eleName.substr(0,1)=="#"){
			callback(document.getElementById(ele));
		}else if(eleName.substr(0, 1)=="."){
			for(o in document.getElementsByClassName(ele)){
				callback(o);
			}
		}else{
			for(o in document.getElementsByTagName(ele)){
				callback(o);
			}
		}
	}
	function clearDomElement(target){
		at(target,function(o){
			revomeChildNodes(o);
		});
	}
	function doc(target,content){
		at(target,function(o){
			o.innerHTML = content;
		});
	}
	function cancelDefaultAction(e){
	 	var evt = e ? e:window.event;
	 	if(evt.preventDefault) evt.preventDefault();
	 	evt.returnValue = false;
	 	return false;
	}
	function attachErrorMessage(obj){
		var ele = obj.target;
		var msg = (typeof(obj.message)==="string")?obj.message:"";
		clearDomElement(ele);
		var fileref=document.createElement('img');
        fileref.setAttribute('style','color: '+ obj.color +';\
        							  background: '+ obj.background.color +';\
        							  background-image: '+ obj.background.image +';\
        							  height: '+ obj.image.height +';\
        							  width: '+ obj.image.width +';\
        						      border: none;\
        						      margin-top: 20px;\
        						     ');
        fileref.setAttribute('src', obj.image.src);
        if(typeof fileref!='undefined'){
        	at(ele,function(o){
				o.innerHTML = "<center>\
								<div class='shadow' style='background:#eee; border:1px solid #eee; width: 400px; height: 300px; margin: 10% auto;'>\
									<div class='title' style='background:#eee;'>\
									<div style='float:right; margin-top:10px; margin-right:10px; cursor:pointer;' onclick='javascript:{ document.getElementById(\"popUp\").style.display=\"none\"; }'>\
									<img src='img/icon-close.png'/></div></div>\
									<div class='payload_errormsg' style='padding-left:25px; text-align:center;'></div>\
									<br/><label>"+msg+"</label>\
								</div>\
								</center>";
				o.getElementsByClassName("payload_errormsg")[0].appendChild(fileref);
				o.style.display="";
			});
        }else
       		console.log("Error: image to be attached was not found.");
	}
	function dettachImage(obj){
		clearDomElement(obj.target);
	}
	function attachImage(obj){
		var ele = obj.target;
		var msg = (typeof(obj.message)==="string")?obj.message:"";
		clearDomElement(ele);
		var fileref=document.createElement('img');
        fileref.setAttribute('style','color: '+ obj.color +';\
        							  background: '+ obj.background.color +';\
        							  background-image: '+ obj.background.image +';\
        							  height: '+ obj.image.height +';\
        							  width: '+ obj.image.width +';\
        						      border: none;\
        						     ');
        fileref.setAttribute('src', obj.image.src);

        if(typeof fileref!='undefined'){
        	at(ele,function(o){
        		o.appendChild(fileref);
				o.innerHTML += "<br/><label>"+msg+"</label>";
			});
        }else
       		console.log("Error: image to be attached was not found.");
	}
	function toggleRequestProgress(){
		Payload.requestInProgress = (Payload.requestInProgress==true)?false:true;
	}
	function addEventHandler(obj,eventType,handler){
		if(typeof(obj.addEventListener)!=="undefined")
			obj.addEventListener(eventType,handler,false);
		else if(typeof(obj.attachEvent)!=="undefined")
			obj.attachEvent('on'+eventType,handler);
		else
			console.log("No support for event handlers addition on the browser.");
	}
	function removeEventHandler(obj,eventType,handler){
		if(typeof(obj.removeEventListener)!=="undefined")
			obj.removeEventListener(eventType,handler,false);
		else if(typeof(obj.detachEvent)!=="undefined")
			obj.detachEvent('on'+eventType,handler);
		else
			console.log("No support for event handlers removal on the browser.");
	}
	function serialize(form){
	  if (!form || form.nodeName !== 'FORM') {
	    return;
	  }
	  var i,j,q = [];
	  for (i = form.elements.length - 1; i >= 0; i = i - 1) {
	    if (form.elements[i].name === '') {
	      continue;
	    }
	    switch (form.elements[i].nodeName) {
	      case 'INPUT':
	        switch (form.elements[i].type) {
	          case 'text':
	          case 'hidden':
	          case 'password':
	          case 'button':
	          case 'reset':
	          case 'submit':
	            q.push(form.elements[i].name + '=' + encodeURIComponent(form.elements[i].value));
	            break;
	          case 'checkbox':
	          case 'radio':
	            if (form.elements[i].checked) {
	              q.push(form.elements[i].name + '=' + encodeURIComponent(form.elements[i].value))
	            }
	            break;
	          case 'file':
	            break;
	        }
	        break;
	      case 'TEXTAREA':
	        q.push(form.elements[i].name + '=' + encodeURIComponent(form.elements[i].value));
	        break;
	      case 'SELECT':
	        switch (form.elements[i].type) {
	          case 'select-one':
	            q.push(form.elements[i].name + '=' + encodeURIComponent(form.elements[i].value));
	            break;
	          case 'select-multiple':
	            for (j = form.elements[i].options.length - 1; j >= 0; j = j - 1) {
	              if (form.elements[i].options[j].selected) {
	                q.push(form.elements[i].name + '=' + encodeURIComponent(form.elements[i].options[j].value))
	              }
	            }
	            break;
	        }
	        break;
	      case 'BUTTON':
	        switch (form.elements[i].type) {
	          case 'reset':
	          case 'submit':
	          case 'button':
	            q.push(form.elements[i].name + '=' + encodeURIComponent(form.elements[i].value));
	            break;
	        }
	        break;
	    }
	  }
	return q.join('&')
	}
	function createAjaxObject(){
		var httpReq;
		try{
		  httpReq = new XMLHttpRequest();
		  console.log("Http request object XMLHttpRequest created !!!");
		}catch(e){
			var versions=["MSXML2.XmlHttp",
						  "MSXML2.XmlHttp.5.0",
						  "MSXML2.XmlHttp.4.0",
						  "MSXML2.XmlHttp.3.0",
						  "MSXML2.XmlHttp.2.0",
						  "Microsoft.XmlHttp"];
			for(ver in versions){
				try{
					httpReq = new ActiveXObject(ver);
					console.log("Http request object "+ver+" created !!!");
					break;
				}catch(e){
					console.log("Error: http request object could not be created !!!");
					return false;
				}
			} // end for
		}
		return httpReq;
	}
	function AjaxRequest(myuri,priority,callback,preCallBack,handlerType){
		var priority = (typeof(priority)==="number")?priority:0;
		var uri = { action: "", method:"", enctype:"" };
		uri.method = (typeof(myuri.method)==="string" && (myuri.method).toLowerCase()=="post") ? "post" : "get";
		uri.action = (typeof(myuri.action)==="string") ? myuri.action : "";
		uri.enctype = (typeof(myuri.enctype)==="string" && myuri.enctype=="multipart/form-data")? "multipart/form-data" : "application/x-www-form-urlencoded";
		var check = (thisObject.refreshRate==null)?(uri.action!==thisObject.prevAction):true;
		var check = (priority==1)?true:check;
		var validationResponse = true;
		if((uri.method).toLowerCase()=="post"){
			validationResponse = validateForm(myuri);
	    }
		if(check && thisObject!=undefined && validationResponse==true){
			clearTimeout(Payload.requestTimeOut);
			thisObject.prevAction = uri.action;
			//check event priority and on the basis of high or normal (1/0) decide whether to take it forward or not
			// also check if the click is from a button or the same event has been triggered
			//option for above check override.
			if(Payload.requestInProgress==true && (priority!=1 && Payload.prevPriority==1)){
				Payload.requestTimeOut = setTimeout(function(){
											Payload.count = 0;
											Payload.prevPriority = priority;
											try{
												invoke(thisObject,[myuri,priority,callback]);
											}catch(e){
												console.log("Error: "+e.message + "---- object = "+ thisObject);
												//call(thisObject,myuri,priority,callback);
											}
										  },1000);
				Payload.count++;
				console.log("Some Request Still in progress !!! - "+ Payload.count);
			}else{
				if(priority==1 && Payload.prevPriority != 1){
					console.log("Priority Request - discarding previous request !!!");
					Payload.count = 0;
					Payload.prevPriority = priority;
				}
				toggleRequestProgress();
				dataLoader = createAjaxObject();
				if((thisObject.preloader.type).toLowerCase()=="gif"){
					attachImage(thisObject.preloader);
				}
				if(handlerType=="upload"){
					addEventHandler(dataLoader.upload,"progress", updateProgress);
					addEventHandler(dataLoader.upload,"load", transferComplete);
					addEventHandler(dataLoader.upload,"error", transferFailed);
					addEventHandler(dataLoader.upload,"abort", transferCanceled);
					addEventHandler(dataLoader.upload,"loadend", uploadEnd);
				}else{
					addEventHandler(dataLoader,"progress", updateProgress);
					addEventHandler(dataLoader,"load", transferComplete);
					addEventHandler(dataLoader,"error", transferFailed);
					addEventHandler(dataLoader,"abort", transferCanceled);
					addEventHandler(dataLoader,"loadend", downloadEnd);
				}
				if((uri.method).toLowerCase()=="get"){
					dataLoader.open('GET',uri.action,true);
					dataLoader.setRequestHeader('Cache-Control', 'no-cache');
					dataLoader.setRequestHeader('Content-type', uri.enctype);
					dataLoader.send(null);
				}else{
					var parameters = serialize(myuri);
					dataLoader.open('POST', uri.action, true);
					dataLoader.setRequestHeader('Cache-Control', 'no-cache');
					dataLoader.setRequestHeader('Content-type', uri.enctype);
					dataLoader.send(parameters);
				}
				dataLoader.onreadystatechange = function(){
					if (dataLoader.readyState != 4){
						return;
					}else if (dataLoader.status == 404){
						console.log("The requested page was not found !");
						dettachImage(thisObject.preloader);
						thisObject.errorMsg.message = "Error: Something went wrong...<br/> Got this message from dataLoader object.";
						attachErrorMessage(thisObject.errorMsg);
						toggleRequestProgress();
						return;
					}else if (dataLoader.readyState == 0  || dataLoader.status != 200){
						console.log("The ajax request was not initialized !");
						dettachImage(thisObject.preloader);
						thisObject.errorMsg.message = "Error: Something went wrong...<br/>request was could not be initialized.";
						attachErrorMessage(thisObject.errorMsg);
						toggleRequestProgress();
						return;
					}else{
						var data = dataLoader.responseText;
						if(preCallBack && (typeof(preCallBack) === "function" || typeof(preCallBack) === "symbol")){
							data = preCallBack(data);
						}
						if(callback && (typeof(callback) === "function" || typeof(callback) === "symbol")){
							callback(data);
						}
					}
				}
			}
		}else{
			if(uri.action==thisObject.prevAction){
				console.log("Same Request cannot be performed again and again. Be patient !!!");
			}else if(validationResponse!=true){
				console.log("one or more Form Entries are not valid !!!");
			}else{
				console.log("the URI object not defined properly !!!");
			}
		}
	}
// progress on transfers from the server to the client (downloads)
	function updateProgress(e){
	  if(e.lengthComputable){
		var percentComplete = ((e.loaded/e.total) * 100).toFixed(2);
		if(typeof(thisObject.preloader)!=="undefined" && typeof(thisObject.preloader.target)!=="undefined"){
			console.log(thisObject.preloader.target + " - percentage complete : " + percentComplete);
			if((thisObject.preloader.type).toLowerCase()=="progressbar"){
				doc(thisObject.preloader.target,"<div style='width:100%; text-align:center; background:#eee; border:0px solid #ccc;'>\
											 <div style='height:100%; width:"+Math.floor(percentComplete)+"%; background:"+thisObject.preloader.background.color+";'>\
												&nbsp;\
											 </div>\
										   </div>");
			}
		}
	  }else{
		console.log("Unable to compute progress information since the total size is unknown");
		//<!-- div style='position:absolute; width:100%; height:auto; text-align:center; font-size:14px; color:#777;'>"+percentComplete+"%</div -->\
	  }
	}
// downloads and upload events
	function transferComplete(evt) {
	  console.log("The transfer is complete.");
	}
	function transferFailed(evt) {
	    console.log("An error occurred while transfering the file.");
	    thisObject.errorMsg.message = "Error: Something went wrong...<br/> Message from onEvent fail Handler.";
		attachErrorMessage(thisObject.errorMsg);
	}
	function transferCanceled(evt) {
	  	console.log("The transfer has been canceled by the user.");
	  	thisObject.errorMsg.message = "Error: Something went wrong...<br/> Message from onEvent cancel Handler.";
	  	attachErrorMessage(thisObject.errorMsg);
	}
	function uploadEnd(evt){
		console.log("upload process ended. Detaching the event handlers");
		removeEventHandler(dataLoader.upload,"progress", updateProgress);
		removeEventHandler(dataLoader.upload,"load", transferComplete);
		removeEventHandler(dataLoader.upload,"error", transferFailed);
		removeEventHandler(dataLoader.upload,"abort", transferCanceled);
		removeEventHandler(dataLoader.upload,"loadend", uploadEnd);
		if((thisObject.preloader.type).toLowerCase()=="gif" && thisObject.preloader.target!=thisObject.target){
			dettachImage(thisObject.preloader);
		}
	}
	function downloadEnd(evt){
		console.log("download process ended. Detaching the event handlers");
		removeEventHandler(dataLoader,"progress", updateProgress);
		removeEventHandler(dataLoader,"load", transferComplete);
		removeEventHandler(dataLoader,"error", transferFailed);
		removeEventHandler(dataLoader,"abort", transferCanceled);
		removeEventHandler(dataLoader,"loadend", downloadEnd);
		if((thisObject.preloader.type).toLowerCase()=="gif" && thisObject.preloader.target!=thisObject.target){
			dettachImage(thisObject.preloader);
		}
	}
//Public Functions
  //uploads actions - priority always = 1
  	this.DUMP = function(data){
		doc(this.target,data);
	}
	this.SUBMIT = function(form,callback){
		thisObject = this;
		AjaxRequest(form,1,callback,function(data){ return data; },"download");
	}
	this.UPLOAD = function(form,callback){
		thisObject = this;
		AjaxRequest(uri,1,callback,function(data){ return data; },"upload");
	}
  //download actions
	//get info from the server
	//loadObj = {html:"", js:"", css:"" };
/*	this.LOAD = function(loadObj,callback){
		thisObject = this;
		if(typeof(loadObj)!=="object"){ return; }
		if(typeof(loadObj.html)==="undefined" || loadObj.html==null ||  loadObj.html==""){ return; }
		if(typeof(loadObj)!=="undefined"){
 			if(typeof(loadObj.css)==="string"){
				thisObject.CSS(loadObj.css);
 			}
 			if(typeof(loadObj.html)==="string"){
 				//alert(loadObj.html);
 				thisObject.HTML({action: loadObj.html},1);
 			}
 			if(typeof(loadObj.js)==="string"){
 				thisObject.JS(loadObj.js);
 			}
 		}
 		//this.history[++this.state] = {load: loadObj, callback: callback};
	}
	this.back = function(){
		if(this.state>0){
			var loaderObj = this.history[this.state--];
			this.LOAD( loaderObj.loadObj, loaderObj.callback);
		}else{
			console.log("No previous History Present !!!");
		}
	}
	this.forward = function(){
		if(this.state<this.maxState){
			var loaderObj = this.history[this.state++];
			this.LOAD(loaderObj.loadObj, loaderObj.callback);
		}else{
			console.log("No Forward History Present !!!");
		}
	}*/
	this.TEXT = function(uri,priority,callback){
		thisObject = this;
		AjaxRequest(uri,priority,callback,function(data){ return data; },"download");
	}
	this.JSON = function(uri,priority,callback){
		thisObject = this;
		if(typeof(uri)!=="object"){ return; }
		if(typeof(uri.action)==="undefined" || uri.action==null ||  uri.action==""){ return; }
		if(typeof(priority)==="undefined"){ priority=0; }

		AjaxRequest(uri,
					priority,
					callback,
					function(data){
						var json = data;
						try{
							json = JSON && JSON.parse(json) || $.parseJSON(json);
						}catch(e){
							console.log(e.message);
							try{
								json = eval(dataLoader.responseText);
							}catch(e){
								thisObject.errorMsg.message = "ERROR : " + data;
								attachErrorMessage(thisObject.errorMsg);
								json=[];
							}
						}
						return json;
					},"download");
	}
	this.HTML = function(uri,priority,callback){
		thisObject = this;
		if(typeof(uri)!=="object"){ return; }
		if(typeof(uri.action)==="undefined" || uri.action==null ||  uri.action==""){ return; }
		if(typeof(priority)==="undefined"){ priority=0; }
		/*if(typeof(Storage) !== "undefined") {
		    // Code for localStorage/sessionStorage.
		    console.log("Local Storage if Defined. So Storing the history in session storage.");
		    this.storage.history = {uri: {action: uri.action, method: uri.method}, priority: priority, callback: callback};
		    alert(this.storage.history.length);
		} else {
		    // Sorry! No Web Storage support..
		    console.log("No web Storage if Defined. So Storing the history in cookie.")
		}*/
		console.log(this.constructor.name + "-------------" + thisObject.refreshRate);
		if(thisObject.refreshRate!=null && priority==1){
			console.log("A self refreshing payload cannot have its priority 1.so, setting it to 0");
			priority = 0;
		}
		clearInterval(thisObject.refreshInterval);
		AjaxRequest(uri,
					priority,
					callback,
					function(){
							clearDomElement(thisObject.target);
							if(typeof(dataLoader.responseText)!=="undefined")
								doc(thisObject.target,dataLoader.responseText);
							toggleRequestProgress();
					},"download");
		if(thisObject.refreshRate!=null && priority!=1){
			thisObject.refreshInterval = setInterval(function(){
													AjaxRequest(uri,
																0,
																callback,
																function(){
																		clearDomElement(thisObject.target);
																		if(typeof(dataLoader.responseText)!=="undefined")
																			doc(thisObject.target,dataLoader.responseText);
																		toggleRequestProgress();
																},"download")
														},thisObject.refreshRate);
		}
	}
	this.actOn = function(response,callback){
		/* response = { status: "",
						message: "",
	 					load: { html: "",
	 							js: "",
	 							css: ""}
	 				   }
	 	*/
	 	thisObject = this;
	 	if(typeof(response)!=="undefined"){
	 		try{
	 			response = JSON && JSON.parse(response) || $.parseJSON(response);
	 		}catch(e){
	 			response = eval(response);
	 		}finally{
				if(typeof(response.status)==="string" && (response.status=="error" || response.status=="success")){
			 		if(typeof(response.message)==="string"){
			 			clearDomElement("#onerror");
			 			clearDomElement("#onsuccess");
			 			doc("#on"+(response.status).toLowerCase(), response.message);
			 		}
			 		if(typeof(response.load)!=="undefined"){
			 			if(typeof(response.load.css)==="string"){
							thisObject.CSS(response.load.css);
			 			}
			 			if(typeof(response.load.html)==="string"){
			 				thisObject.HTML({action: response.load.html},1,	doc("#on"+(response.status).toLowerCase(), response.message));
			 			}
			 			if(typeof(response.load.js)==="string"){
			 				thisObject.JS(response.load.js);
			 			}
			 		}
			 	}
	 		}
		}
		if(callback && (typeof(callback) === "function" || typeof(callback) === "symbol")){
			callback();
		}
	}
	//attach files
	this.JS = function(url,callback){
		if(url!='' && url!=null && url!=undefined){
			var allsuspects=document.getElementsByTagName('script');
			for (var i=allsuspects.length; i>=0; i--){
				if(allsuspects[i] && allsuspects[i].getAttribute('src')!=null && allsuspects[i].getAttribute('src').indexOf(findBaseName(url))!=-1)
					allsuspects[i].parentNode.removeChild(allsuspects[i]);
			}
			var fileref=document.createElement('script');
			fileref.setAttribute('type','text/javascript');
			fileref.setAttribute('src', url);
			if (typeof(fileref)!='undefined')
			  document.getElementsByTagName('head')[0].appendChild(fileref);
			fileref.onload = function(){
				if(callback && (typeof(callback) === "function" || typeof(callback) === "symbol")){
					callback();
				}
			}
			fileref.onerror = function(){
				console.log("Error: could not load the javascript source file mentioned !!!");
				if(callback && (typeof(callback) === "function" || typeof(callback) === "symbol")){
					callback();
				}
			}
		}else{
			if(callback && (typeof(callback) === "function" || typeof(callback) === "symbol")){
					callback();
			}
		}
	}
	this.CSS = function(url,callback){
		if(url!='' && url!=null && url!=undefined){
			var allsuspects=document.getElementsByTagName('link');
			for (var i=allsuspects.length; i>=0; i--){
				if(allsuspects[i] && allsuspects[i].getAttribute('href')!=null && allsuspects[i].getAttribute('href').indexOf(findBaseName(url))!=-1){
					allsuspects[i].parentNode.removeChild(allsuspects[i]);
				}
			}
			var fileref=document.createElement('link');
			fileref.setAttribute('rel', 'stylesheet');
			fileref.setAttribute('type', 'text/css');
			fileref.setAttribute('href', url);
			if (typeof fileref!='undefined')
			  document.getElementsByTagName('head')[0].appendChild(fileref);
			fileref.onload = function(){
				if(callback && (typeof(callback) === "function" || typeof(callback) === "symbol")){
					callback();
				}
			}
			fileref.onerror = function(){
				console.log("Error: could not link to the css file referred !!!");
				if(callback && (typeof(callback) === "function" || typeof(callback) === "symbol")){
					callback();
				}
			}
		}else{
			if(callback && (typeof(callback) === "function" || typeof(callback) === "symbol")){
					callback();
			}
		}
	}
}
