function obj(str){
	return document.getElementById(str);
}
function objValue(str){
	return document.getElementById(str).value;
}
function toggle(str){
	if(obj(str).style.display=="none"){
		obj(str).style.display="block";
		return true;
	}
	else if(obj(str).style.display=="block"){
		obj(str).style.display="none";
		return false;
	}
}
function isEmail(str){
	var filter = /^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,})$/;
	return filter.test(str);
}
function isNumber(str){
	var filter = /^[0-9]+$/;
	return filter.test(str);
}
function MM_findObj(n, d) {
	var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
	d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
	if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
	for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
	if(!x && d.getElementById) x=d.getElementById(n); return x;
}
function MM_jumpMenu(targ,selObj,restore){
	eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
	if (restore)
	selObj.selectedIndex=0;
}
function MM_swapImage() {
	var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
	if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}
function MM_swapImgRestore() {
	var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}
function isNumberKey(evt){
	var charCode = (evt.which) ? evt.which : event.keyCode
	if (charCode > 31 && (charCode < 48 || charCode > 57))
		return false;
	
	return true;
}
/*clear input or textarea default value, style1:default style, style2:normal style*/
function clearDefaultText(ele, style1, style2, txt){
	if(ele.value==txt){
		ele.value = '';
		ele.className = style2;
	}
	ele.onblur = function(){
		if(!ele.value==txt || ele.value==''){
			if(typeof txt == "undefined")
				ele.value = '';
			else
				ele.value = txt;
			ele.className = style1;
		}
	}
}
function popUp(webaddy,title,x,y) {
	var features = 'toolbars=0, scrollbars=1, location=0, statusbars=0, menubars=0, resizable=0, width=' + x + ', height=' + y + ', left = 168, top = 118';
	props=window.open(webaddy, title, features);
}
function limitText(limitField, limitCount, limitNum){
	if (limitField.value.length > limitNum)
		limitField.value = limitField.value.substring(0, limitNum);
	else
		limitCount.value = limitNum - limitField.value.length;
}
function colorInputValidationCheck(ob, ob_des, msg){
	ob1 = obj(ob);
	ob2 = obj(ob_des);
	ob1.className = 'redthickborder';
	ob2.innerHTML = '<span class="font_red">'+msg+'</span>';
}
function removeColorInput(ob, ob_des){
	ob1 = obj(ob);
	ob2 = obj(ob_des);
	ob1.className = '';
	ob2.innerHTML = '';
}

function convertSpecialChars(){
	var chars = ["Â©","Ã›","Â®","Å¾","Ãœ","Å¸","Ã","Ãž","%","Â¡","ÃŸ","Â¢","Ã ","Â£","Ã¡","Ã€","Â¤","Ã¢","Ã","Â¥","Ã£","Ã‚","Â¦","Ã¤","Ãƒ","Â§","Ã¥","Ã„","Â¨","Ã¦","Ã…","Â©","Ã§","Ã†","Âª","Ã¨","Ã‡","Â«","Ã©","Ãˆ","Â¬","Ãª","Ã‰","Â­","Ã«","ÃŠ","Â®","Ã¬","Ã‹","Â¯","Ã­","ÃŒ","Â°","Ã®","Ã","Â±","Ã¯","ÃŽ","Â²","Ã°","Ã","Â³","Ã±","Ã","Â´","Ã²","Ã‘","Âµ","Ã³","Ã•","Â¶","Ã´","Ã–","Â·","Ãµ","Ã˜","Â¸","Ã¶","Ã™","Â¹","Ã·","Ãš","Âº","Ã¸","Ã›","Â»","Ã¹","Ãœ","@","Â¼","Ãº","Ã","Â½","Ã»","Ãž","â‚¬","Â¾","Ã¼","ÃŸ","Â¿","Ã½","Ã ","â€š","Ã€","Ã¾","Ã¡","Æ’","Ã","Ã¿","Ã¥","â€ž","Ã‚","Ã¦","â€¦","Ãƒ","Ã§","â€ ","Ã„","Ã¨","â€¡","Ã…","Ã©","Ë†","Ã†","Ãª","â€°","Ã‡","Ã«","Å ","Ãˆ","Ã¬","â€¹","Ã‰","Ã­","Å’","ÃŠ","Ã®","Ã‹","Ã¯","Å½","ÃŒ","Ã°","Ã","Ã±","ÃŽ","Ã²","â€˜","Ã","Ã³","â€™","Ã","Ã´","â€œ","Ã‘","Ãµ","â€","Ã’","Ã¶","â€¢","Ã“","Ã¸","â€“","Ã”","Ã¹","â€”","Ã•","Ãº","Ëœ","Ã–","Ã»","â„¢","Ã—","Ã½","Å¡","Ã˜","Ã¾","â€º","Ã™","Ã¿","Å“","Ãš"]; 
	var codes = ["&copy;","&#219;","&reg;","&#158;","&#220;","&#159;","&#221;","&#222;","&#37;","&#161;","&#223;","&#162;","&#224;","&#163;","&#225;","&Agrave;","&#164;","&#226;","&Aacute;","&#165;","&#227;","&Acirc;","&#166;","&#228;","&Atilde;","&#167;","&#229;","&Auml;","&#168;","&#230;","&Aring;","&#169;","&#231;","&AElig;","&#170;","&#232;","&Ccedil;","&#171;","&#233;","&Egrave;","&#172;","&#234;","&Eacute;","&#173;","&#235;","&Ecirc;","&#174;","&#236;","&Euml;","&#175;","&#237;","&Igrave;","&#176;","&#238;","&Iacute;","&#177;","&#239;","&Icirc;","&#178;","&#240;","&Iuml;","&#179;","&#241;","&ETH;","&#180;","&#242;","&Ntilde;","&#181;","&#243;","&Otilde;","&#182;","&#244;","&Ouml;","&#183;","&#245;","&Oslash;","&#184;","&#246;","&Ugrave;","&#185;","&#247;","&Uacute;","&#186;","&#248;","&Ucirc;","&#187;","&#249;","&Uuml;","&#64;","&#188;","&#250;","&Yacute;","&#189;","&#251;","&THORN;","&#128;","&#190;","&#252","&szlig;","&#191;","&#253;","&agrave;","&#130;","&#192;","&#254;","&aacute;","&#131;","&#193;","&#255;","&aring;","&#132;","&#194;","&aelig;","&#133;","&#195;","&ccedil;","&#134;","&#196;","&egrave;","&#135;","&#197;","&eacute;","&#136;","&#198;","&ecirc;","&#137;","&#199;","&euml;","&#138;","&#200;","&igrave;","&#139;","&#201;","&iacute;","&#140;","&#202;","&icirc;","&#203;","&iuml;","&#142;","&#204;","&eth;","&#205;","&ntilde;","&#206;","&ograve;","&#145;","&#207;","&oacute;","&#146;","&#208;","&ocirc;","&#147;","&#209;","&otilde;","&#148;","&#210;","&ouml;","&#149;","&#211;","&oslash;","&#150;","&#212;","&ugrave;","&#151;","&#213;","&uacute;","&#152;","&#214;","&ucirc;","&#153;","&#215;","&yacute;","&#154;","&#216;","&thorn;","&#155;","&#217;","&yuml;","&#156;","&#218;"];
	
	for (i=0; i<arguments.length; i++){
		for(x=0; x<chars.length; x++){
			arguments[i].value = arguments[i].value.replace(new RegExp(chars[x], 'g'), codes[x]);
		}
	}
}
function isScrolledVisible(elem){
    var docViewTop = jQuery(window).scrollTop();
	var elemTop = jQuery(elem).offset().top+jQuery(elem).height();
	if(elemTop<docViewTop) //scroll to elem
		return (docViewTop-elemTop<0?true:false);
	else //!scroll to elem
		return (elemTop<jQuery(window).height()+docViewTop?true:false); //elem not within browser window
}
function showStickybar(elem){
	if(jQuery('#stickybar').length==1){
		if(!isScrolledVisible(elem)) //elem not visible on load
			jQuery('#stickybar').css('display','block');
		jQuery(window).scroll(function(){		
			if(!isScrolledVisible(elem)) //elem not visible after scrolling
				jQuery('#stickybar').css('display','block');
			else
				jQuery('#stickybar').css('display','none');
		});
		jQuery(window).resize(function(){
			if(!isScrolledVisible(elem)) //elem not visible after scrolling
				jQuery('#stickybar').css('display','block');
			else
				jQuery('#stickybar').css('display','none');
		});
	}
}


var tooltipsfun = function( sensorele, tooltipID ) {
	jQuery(sensorele).css('cursor', 'pointer');
	jQuery(sensorele).mouseenter(function(){
		timer = setTimeout(function(){
			jQuery("#"+tooltipID).show();
		},700);
	}).mouseleave(function(){
		clearTimeout(timer);
		setTimeout(function(){
			jQuery("#"+tooltipID).hide();
		},700);
	});
};


var vmoreHLnews = function(boxwidth, totalitems, nodata) {
    var n = jQuery(".hlitem").length,
        width = boxwidth,
        newwidth = width * n;
	
	jQuery('#hlstage, .hlitem').css('width',width);	
    jQuery('#hlslide-holder').css({
        'width': newwidth
    });

    jQuery(".hlitem").each(function (i) {
        var thiswid = 730;
        jQuery(this).css({
            'left': thiswid * i
        });

    });
	
	jQuery('#hlprev').click(function () {
        var hlprev = jQuery('#hlslide-holder .active').prev();
		var curIndex = jQuery('.active').index()-1<0?0:jQuery('.active').index()-1;
	    if (hlprev.length) {
			getHLpaging(curIndex,totalitems,n,nodata);
            jQuery('#hlstage').animate({
                scrollLeft: hlprev.position().left
            }, 1000);
        }
    });
    /* on right button click scroll to the next sibling of the current visible slide */
    jQuery('#hlnext').click(function () {
        var hlnext = jQuery('#hlslide-holder .active').next();
		var curIndex = jQuery('.active').index()+1>n?n:jQuery('.active').index()+1;
        if (hlnext.length) {
			getHLpaging(curIndex,totalitems,n,nodata);
            jQuery('#hlstage').animate({
                scrollLeft: hlnext.position().left
            }, 1000);
        }
    });

    /*on scroll move the indicator 'shown' class to the
    most visible slide on viewport
    */
    jQuery('#hlstage').scroll(function () {
        var scrollLeft = jQuery(this).scrollLeft();
        jQuery(".hlitem").each(function (i) {
            var posLeft = jQuery(this).position().left
            var w = jQuery(this).width();
           
            if (scrollLeft >= posLeft && scrollLeft < posLeft + w) {
              jQuery(this).addClass('active').siblings().removeClass('active');
            }
        });
    });	
};

function getHLpaging(curIndex, totalitems, totaldivitems, totaldata){
	jQuery('a.hlnavleft').removeClass('inactiveleft');
	jQuery('a.hlnavright').removeClass('inactiveright');
	pagingIndex = curIndex==(totaldivitems-1)?totaldata:(curIndex+1)*totalitems;
	pagingText = ((curIndex*totalitems)+1)+'-'+pagingIndex+' of '+totaldata;
	jQuery('.hlpaging').text(pagingText);
	if(curIndex==0){
		jQuery('a.hlnavleft').addClass('inactiveleft');
		jQuery('a.hlnavright').removeClass('inactiveright');
	}
	else if(curIndex==(totaldivitems-1)){
		jQuery('a.hlnavright').addClass('inactiveright');
		jQuery('a.hlnavleft').removeClass('inactiveleft');
	}	
}

function validCaptcha(formname,group) {
	var v = grecaptcha.getResponse();
	if(v.length == 0){
		alert('Please Complete The Captcha');
		return false;
	}
	else
	{
		if(typeof(formname)!='undefined' && formname!=''){
			try{
				let hiddenInput = document.createElement("input");
				hiddenInput.setAttribute("type", "hidden");
				hiddenInput.setAttribute("name", 'g-recaptcha-response');
				let gresponse = document.querySelector('.g-recaptcha-response').value;
				hiddenInput.setAttribute("value", gresponse);
				document[formname].appendChild(hiddenInput);
				document.forms[formname].submit();
			}catch(e){
				document.forms[formname].submit();
			}
		}
		else
		{ 
			return true;
		}
	}
}

function checkValidDate(inDate, futurecheck) {
	/***get today date****/
	var today = new Date();
	var todaydd = today.getDate();
	var todaymm = today.getMonth()+1; //January is 0!
	var todayyyyy = today.getFullYear();
	todayyyyy = todayyyyy.toString();
	
	if(todaydd<10)
		var todaydd = pad(todaydd, 2); 
	if(todaymm<10)
		var todaymm = pad(todaymm, 2); 

	if (inDate=='')
		return true;
	var d="312831303130313130313031";
	var yr;

	/* For invalid dates, return false */
	if (inDate.length>0 && inDate.length<8) return false;

	// Expected inDate format: dd.mm.yyyy
	dd = inDate.substring(0,2);
	mm = inDate.substring(2,4);
	yy = inDate.substring(4,8);

	/* Now, convert the string yr1 into a numeric and test for leap year.
	If it is, change the end of month day string for Feb to 29  */

	var isLeap = false;
	yy=yy*1;
	if (yy%400==0) isLeap = true
	else if (yy%100==0) isLeap = false
	else if (yy%4==0) isLeap = true;
	if (isLeap) d=d.substring(0,2)+"29" + d.substring(4,d.length);

	/* Pick the end of month day from the d string for this month. */
	pos=(mm*2)-2;
	ld=d.substring(pos,pos+2)+0;
	if (dd<1||dd>ld)
		return false;
	else if (mm<1||mm>12)
		return false;
	else if (yy<1900)
		return false;
	else if(typeof futurecheck !== 'undefined' && parseInt(yy+mm+dd)>parseInt(todayyyyy+todaymm+todaydd))
		return false;

	return true;
}

function pad (str, max) {
	str = str.toString();
	return str.length < max ? pad("0" + str, max) : str;
}

function gInputNumbersDotOnly(myfield, e){
	var key;
	var keychar;
	if (window.event)
	   key = window.event.keyCode;
	else if (e)
	   key = e.which;
	else
	   return true;
	keychar = String.fromCharCode(key);
	keychar = keychar.toLowerCase();
	// control keys
	if ((key==null) || (key==0) || (key==8) || 
		(key==9) || (key==13) || (key==27) )
	   return true;
	// numbers
	else if ((("0123456789.").indexOf(keychar) > -1))
	   return true;
	else
	   return false;
}

function gAddLoadEvent(func) {
	var oldonload = window.onload;
	if (typeof window.onload != 'function') {
		window.onload = func;
	} else {
		window.onload = function() {
	  		oldonload();
	  	func();
		}
	}
}

function gDestroycatfish() { /* catfish closer function */
	jQuery("#catfish").remove(); /* clip catfish off the tree */
	document.getElementsByTagName('html')[0].style.padding= '0'; /* reset the padding at the bottom */
	return false; /* disable the link's 'linkiness' -- so it won't jump you up the top of the page */
}

function gCloselink() { /* attach the catfish closer function to the link */ 
	if(document.getElementById("closeme")){
		var closelink = document.getElementById('closeme'); /* find the 'close this' link */
		closelink.onclick = gDestroycatfish; /* attach the destroy function to it's 'onclick' */
	}
}

function gSetCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+ d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function convertDate(date,current_Date){// format: DD/MM/YYYY (Check valid date and get date)
	var valid_date = '';
	
	var todayDate = new Date();
	var todayDD = todayDate.getDate().toString();
	var todayMM = (todayDate.getMonth()+1).toString();
	var todayYY = todayDate.getFullYear().toString();
	
	if(todayDD<10)
		var todayDD = pad(todayDD, 2); 
	if(todayMM<10)
		var todayMM = pad(todayMM, 2); 
	
	if(date){
		var splitDate = date.split(/[-\/.]/);
		var inputDD = splitDate[0]?splitDate[0]:'';
		inputDD = inputDD.toString();
		var inputMM = splitDate[1]?splitDate[1]:'';
		inputMM = inputMM.toString();
		var inputYY = splitDate[2]?splitDate[2]:'';
		inputYY = inputYY.toString();
		
		if(inputDD<10 && inputDD.length <2){
			inputDD = pad(inputDD, 2); 	
		}
		if(inputMM<10 && inputMM.length <2){
			inputMM = pad(inputMM, 2); 	
		}

		if(inputDD.length != 2 || inputMM.length != 2 || inputYY.length !=4)
			return valid_date;

		if(checkValidDate(inputDD+inputMM+inputYY)){
			if(typeof current_Date !== 'undefined')
				return valid_date = [inputYY+inputMM+inputDD, todayYY+todayMM+todayDD];
			else
				return valid_date = [inputYY+inputMM+inputDD];
		}
		else
			return valid_date;
	}
	else
		return valid_date;
}

function createSortingTable(tableid) {
	let table = new DataTable('#'+tableid, {
		paging: $('#'+tableid+' tbody tr').length>10,
		searching: $('#'+tableid+' tbody tr').length>10,
		info: false,
	})
}

async function confirmationDialog(id, msg, pagename, path, pathreturn, act) {
    switch(act)
    {
        case 'I':   var title = "Insert " + pagename;
                    var title2 = "Are you sure want to insert?";
                    var btn = "Insert";
                    break;
        case 'E':   var title = "Edit " + pagename;
                    var title2 = "Are you sure want to edit?";
                    var btn = "Edit";
                    break;
        case 'D':   var title = "Delete " + pagename;
                    var title2 = "Are you sure want to delete?";
                    var btn = "Delete";
                    break;
        default:    var title = "Error";
    }

    var message = '';
    if (msg.length >= 1)
    {
        for(let i=0; i<msg.length; i++) 
            message += `<p class="mt-n3" style="text-align:center; font-weight:bold;">${msg[i]}</p>`;
    }

    const modalElem = document.createElement('div')
    modalElem.id = "modal-confirm"
    modalElem.className = "modal fade"
    modalElem.innerHTML = `
        <div class="modal-dialog modal-dialog-centered " style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
        <div class="modal-content">             
            <div class="modal-body fs-6 mt-3">
            <p style="text-align:center; font-weight:bold; font-size:25px;">${title}</p>
            <p class="mt-n2" style="text-align:center;">${title2}</p>
            ${message}
        </div>
        <div class="modal-footer d-flex justify-content-center mt-n3" style="border-top:0px">             
            <button id="acceptBtn" type="button" class="btn" 
            style="border:1px solid #FF9B44; background-color:#FFFFFF; color:#FF9B44; box-shadow: 0 0 !important; border-radius: 24px; text-transform:none;">${btn}</button>
            <button id="rejectBtn" type="button" class="btn" 
            style="border: 1px solid #FF9B44; background-color:#FFFFFF; color:#FF9B44; box-shadow: 0 0 !important; border-radius: 24px; text-transform:none;">Cancel</button>
        </div>
        </div>
    </div>
    `;

    const modelResult = document.createElement('div')
    modelResult.id = "modal-confirm"
    modelResult.className = "modal fade"
    modelResult.innerHTML = `
        <div class="modal-dialog modal-dialog-centered " style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
        <div class="modal-content">             
            <div class="modal-body fs-6 mt-3">
            <p style="text-align:center; font-weight:bold; font-size:25px;">Successful ${title}</p>
        </div>
        <div class="modal-footer d-flex justify-content-center mt-n3" style="border-top:0px">             
            <button id="contBtn" type="button" class="btn" 
            style="border:1px solid #FF9B44; background-color:#FFFFFF; color:#FF9B44; box-shadow:0 0 !important; border-radius: 24px; text-transform:none;">Continue</button>
        </div>
        </div>
    </div>
    `;

	if(act == 'D')
	{
		const myModal = new bootstrap.Modal(modalElem, {
			keyboard: false,
			backdrop: 'static'
		})
		myModal.show()
	
		const result = await new Promise((resolve, reject) => {
			document.body.addEventListener('click', response)
	
			function response(e) {
				let bool = false
				if (e.target.id == 'rejectBtn') bool = false
				else if (e.target.id == 'acceptBtn') bool = true
				else return
				document.body.removeEventListener('click', response);
				document.body.querySelector('.modal-backdrop').remove();
				modalElem.remove();
				resolve(bool);
			}
		})
	
		if(result) 
		{
			$.ajax({
				type: 'POST',
				url: path,
				data: {
					id: id,
					act: act
				},
				cache: false,
				success: (result) => {	
						const myModal2 = new bootstrap.Modal(modelResult, {
							keyboard: false,
							backdrop: 'static'
						})
						myModal2.show()
	
						return new Promise((resolve, reject) => {
							document.body.addEventListener('click', response)
	
							var myTimeout = setTimeout(() => {
								document.body.removeEventListener('click', response)
								document.body.querySelector('.modal-backdrop').remove()
								modelResult.remove()
								resolve(true)
								location.reload();
							},5000);
	
							function response(e) {           
								let bool = false
								let timeOut = false
	
								if (e.target.id == 'contBtn') {
									bool = true;
									clearTimeout(myTimeout);
								}
								else return
	
								document.body.removeEventListener('click', response)
								document.body.querySelector('.modal-backdrop').remove()
								modelResult.remove()
								resolve(bool)
								location.reload();
							}
						})
				}
			})
		} else console.log("Operation Cancelled.");
	}

	if(act == 'I' || act == 'E')
	{
		const myModal2 = new bootstrap.Modal(modelResult, {
			keyboard: false,
			backdrop: 'static'
		})
		myModal2.show()

		return new Promise((resolve, reject) => {
			document.body.addEventListener('click', response)

			var myTimeout = setTimeout(() => {
				document.body.removeEventListener('click', response)
				document.body.querySelector('.modal-backdrop').remove()
				modelResult.remove()
				resolve(true)
				window.location.href = pathreturn
			},5000);

			function response(e) {           
				let bool = false
				let timeOut = false

				if (e.target.id == 'contBtn') {
					bool = true;
					clearTimeout(myTimeout);
				}
				else return

				document.body.removeEventListener('click', response)
				document.body.querySelector('.modal-backdrop').remove()
				resolve(bool)
				window.location.href = pathreturn
			}
		})
	}
    
}