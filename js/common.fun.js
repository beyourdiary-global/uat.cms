function obj(str) {
  return document.getElementById(str);
}

function objValue(str) {
  return document.getElementById(str).value;
}

function toggle(str) {
  if (obj(str).style.display == "none") {
    obj(str).style.display = "block";
    return true;
  } else if (obj(str).style.display == "block") {
    obj(str).style.display = "none";
    return false;
  }
}

function isEmail(str) {
  var filter =
    /^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,})$/;
  return filter.test(str);
}

function isNumber(str) {
  var filter = /^[0-9]+$/;
  return filter.test(str);
}

function MM_findObj(n, d) {
  var p, i, x;
  if (!d) d = document;
  if ((p = n.indexOf("?")) > 0 && parent.frames.length) {
    d = parent.frames[n.substring(p + 1)].document;
    n = n.substring(0, p);
  }
  if (!(x = d[n]) && d.all) x = d.all[n];
  for (i = 0; !x && i < d.forms.length; i++) x = d.forms[i][n];
  for (i = 0; !x && d.layers && i < d.layers.length; i++)
    x = MM_findObj(n, d.layers[i].document);
  if (!x && d.getElementById) x = d.getElementById(n);
  return x;
}

function MM_jumpMenu(targ, selObj, restore) {
  eval(targ + ".location='" + selObj.options[selObj.selectedIndex].value + "'");
  if (restore) selObj.selectedIndex = 0;
}

function MM_swapImage() {
  var i,
    j = 0,
    x,
    a = MM_swapImage.arguments;
  document.MM_sr = new Array();
  for (i = 0; i < a.length - 2; i += 3)
    if ((x = MM_findObj(a[i])) != null) {
      document.MM_sr[j++] = x;
      if (!x.oSrc) x.oSrc = x.src;
      x.src = a[i + 2];
    }
}

function MM_swapImgRestore() {
  var i,
    x,
    a = document.MM_sr;
  for (i = 0; a && i < a.length && (x = a[i]) && x.oSrc; i++) x.src = x.oSrc;
}

function isNumberKey(evt) {
  var charCode = evt.which ? evt.which : event.keyCode;
  if (charCode > 31 && (charCode < 48 || charCode > 57)) return false;

  return true;
}
/*clear input or textarea default value, style1:default style, style2:normal style*/
function clearDefaultText(ele, style1, style2, txt) {
  if (ele.value == txt) {
    ele.value = "";
    ele.className = style2;
  }
  ele.onblur = function () {
    if (!ele.value == txt || ele.value == "") {
      if (typeof txt == "undefined") ele.value = "";
      else ele.value = txt;
      ele.className = style1;
    }
  };
}

function popUp(webaddy, title, x, y) {
  var features =
    "toolbars=0, scrollbars=1, location=0, statusbars=0, menubars=0, resizable=0, width=" +
    x +
    ", height=" +
    y +
    ", left = 168, top = 118";
  props = window.open(webaddy, title, features);
}

function limitText(limitField, limitCount, limitNum) {
  if (limitField.value.length > limitNum)
    limitField.value = limitField.value.substring(0, limitNum);
  else limitCount.value = limitNum - limitField.value.length;
}

function colorInputValidationCheck(ob, ob_des, msg) {
  ob1 = obj(ob);
  ob2 = obj(ob_des);
  ob1.className = "redthickborder";
  ob2.innerHTML = '<span class="font_red">' + msg + "</span>";
}

function removeColorInput(ob, ob_des) {
  ob1 = obj(ob);
  ob2 = obj(ob_des);
  ob1.className = "";
  ob2.innerHTML = "";
}

function convertSpecialChars() {
  var chars = [
    "Â©",
    "Ã›",
    "Â®",
    "Å¾",
    "Ãœ",
    "Å¸",
    "Ã",
    "Ãž",
    "%",
    "Â¡",
    "ÃŸ",
    "Â¢",
    "Ã ",
    "Â£",
    "Ã¡",
    "Ã€",
    "Â¤",
    "Ã¢",
    "Ã",
    "Â¥",
    "Ã£",
    "Ã‚",
    "Â¦",
    "Ã¤",
    "Ãƒ",
    "Â§",
    "Ã¥",
    "Ã„",
    "Â¨",
    "Ã¦",
    "Ã…",
    "Â©",
    "Ã§",
    "Ã†",
    "Âª",
    "Ã¨",
    "Ã‡",
    "Â«",
    "Ã©",
    "Ãˆ",
    "Â¬",
    "Ãª",
    "Ã‰",
    "Â­",
    "Ã«",
    "ÃŠ",
    "Â®",
    "Ã¬",
    "Ã‹",
    "Â¯",
    "Ã­",
    "ÃŒ",
    "Â°",
    "Ã®",
    "Ã",
    "Â±",
    "Ã¯",
    "ÃŽ",
    "Â²",
    "Ã°",
    "Ã",
    "Â³",
    "Ã±",
    "Ã",
    "Â´",
    "Ã²",
    "Ã‘",
    "Âµ",
    "Ã³",
    "Ã•",
    "Â¶",
    "Ã´",
    "Ã–",
    "Â·",
    "Ãµ",
    "Ã˜",
    "Â¸",
    "Ã¶",
    "Ã™",
    "Â¹",
    "Ã·",
    "Ãš",
    "Âº",
    "Ã¸",
    "Ã›",
    "Â»",
    "Ã¹",
    "Ãœ",
    "@",
    "Â¼",
    "Ãº",
    "Ã",
    "Â½",
    "Ã»",
    "Ãž",
    "â‚¬",
    "Â¾",
    "Ã¼",
    "ÃŸ",
    "Â¿",
    "Ã½",
    "Ã ",
    "â€š",
    "Ã€",
    "Ã¾",
    "Ã¡",
    "Æ’",
    "Ã",
    "Ã¿",
    "Ã¥",
    "â€ž",
    "Ã‚",
    "Ã¦",
    "â€¦",
    "Ãƒ",
    "Ã§",
    "â€ ",
    "Ã„",
    "Ã¨",
    "â€¡",
    "Ã…",
    "Ã©",
    "Ë†",
    "Ã†",
    "Ãª",
    "â€°",
    "Ã‡",
    "Ã«",
    "Å ",
    "Ãˆ",
    "Ã¬",
    "â€¹",
    "Ã‰",
    "Ã­",
    "Å’",
    "ÃŠ",
    "Ã®",
    "Ã‹",
    "Ã¯",
    "Å½",
    "ÃŒ",
    "Ã°",
    "Ã",
    "Ã±",
    "ÃŽ",
    "Ã²",
    "â€˜",
    "Ã",
    "Ã³",
    "â€™",
    "Ã",
    "Ã´",
    "â€œ",
    "Ã‘",
    "Ãµ",
    "â€",
    "Ã’",
    "Ã¶",
    "â€¢",
    "Ã“",
    "Ã¸",
    "â€“",
    "Ã”",
    "Ã¹",
    "â€”",
    "Ã•",
    "Ãº",
    "Ëœ",
    "Ã–",
    "Ã»",
    "â„¢",
    "Ã—",
    "Ã½",
    "Å¡",
    "Ã˜",
    "Ã¾",
    "â€º",
    "Ã™",
    "Ã¿",
    "Å“",
    "Ãš",
  ];
  var codes = [
    "&copy;",
    "&#219;",
    "&reg;",
    "&#158;",
    "&#220;",
    "&#159;",
    "&#221;",
    "&#222;",
    "&#37;",
    "&#161;",
    "&#223;",
    "&#162;",
    "&#224;",
    "&#163;",
    "&#225;",
    "&Agrave;",
    "&#164;",
    "&#226;",
    "&Aacute;",
    "&#165;",
    "&#227;",
    "&Acirc;",
    "&#166;",
    "&#228;",
    "&Atilde;",
    "&#167;",
    "&#229;",
    "&Auml;",
    "&#168;",
    "&#230;",
    "&Aring;",
    "&#169;",
    "&#231;",
    "&AElig;",
    "&#170;",
    "&#232;",
    "&Ccedil;",
    "&#171;",
    "&#233;",
    "&Egrave;",
    "&#172;",
    "&#234;",
    "&Eacute;",
    "&#173;",
    "&#235;",
    "&Ecirc;",
    "&#174;",
    "&#236;",
    "&Euml;",
    "&#175;",
    "&#237;",
    "&Igrave;",
    "&#176;",
    "&#238;",
    "&Iacute;",
    "&#177;",
    "&#239;",
    "&Icirc;",
    "&#178;",
    "&#240;",
    "&Iuml;",
    "&#179;",
    "&#241;",
    "&ETH;",
    "&#180;",
    "&#242;",
    "&Ntilde;",
    "&#181;",
    "&#243;",
    "&Otilde;",
    "&#182;",
    "&#244;",
    "&Ouml;",
    "&#183;",
    "&#245;",
    "&Oslash;",
    "&#184;",
    "&#246;",
    "&Ugrave;",
    "&#185;",
    "&#247;",
    "&Uacute;",
    "&#186;",
    "&#248;",
    "&Ucirc;",
    "&#187;",
    "&#249;",
    "&Uuml;",
    "&#64;",
    "&#188;",
    "&#250;",
    "&Yacute;",
    "&#189;",
    "&#251;",
    "&THORN;",
    "&#128;",
    "&#190;",
    "&#252",
    "&szlig;",
    "&#191;",
    "&#253;",
    "&agrave;",
    "&#130;",
    "&#192;",
    "&#254;",
    "&aacute;",
    "&#131;",
    "&#193;",
    "&#255;",
    "&aring;",
    "&#132;",
    "&#194;",
    "&aelig;",
    "&#133;",
    "&#195;",
    "&ccedil;",
    "&#134;",
    "&#196;",
    "&egrave;",
    "&#135;",
    "&#197;",
    "&eacute;",
    "&#136;",
    "&#198;",
    "&ecirc;",
    "&#137;",
    "&#199;",
    "&euml;",
    "&#138;",
    "&#200;",
    "&igrave;",
    "&#139;",
    "&#201;",
    "&iacute;",
    "&#140;",
    "&#202;",
    "&icirc;",
    "&#203;",
    "&iuml;",
    "&#142;",
    "&#204;",
    "&eth;",
    "&#205;",
    "&ntilde;",
    "&#206;",
    "&ograve;",
    "&#145;",
    "&#207;",
    "&oacute;",
    "&#146;",
    "&#208;",
    "&ocirc;",
    "&#147;",
    "&#209;",
    "&otilde;",
    "&#148;",
    "&#210;",
    "&ouml;",
    "&#149;",
    "&#211;",
    "&oslash;",
    "&#150;",
    "&#212;",
    "&ugrave;",
    "&#151;",
    "&#213;",
    "&uacute;",
    "&#152;",
    "&#214;",
    "&ucirc;",
    "&#153;",
    "&#215;",
    "&yacute;",
    "&#154;",
    "&#216;",
    "&thorn;",
    "&#155;",
    "&#217;",
    "&yuml;",
    "&#156;",
    "&#218;",
  ];

  for (i = 0; i < arguments.length; i++) {
    for (x = 0; x < chars.length; x++) {
      arguments[i].value = arguments[i].value.replace(
        new RegExp(chars[x], "g"),
        codes[x]
      );
    }
  }
}

function isScrolledVisible(elem) {
  var docViewTop = jQuery(window).scrollTop();
  var elemTop = jQuery(elem).offset().top + jQuery(elem).height();
  if (elemTop < docViewTop)
    //scroll to elem
    return docViewTop - elemTop < 0 ? true : false;
  //!scroll to elem
  else return elemTop < jQuery(window).height() + docViewTop ? true : false; //elem not within browser window
}

function showStickybar(elem) {
  if (jQuery("#stickybar").length == 1) {
    if (!isScrolledVisible(elem))
      //elem not visible on load
      jQuery("#stickybar").css("display", "block");
    jQuery(window).scroll(function () {
      if (!isScrolledVisible(elem))
        //elem not visible after scrolling
        jQuery("#stickybar").css("display", "block");
      else jQuery("#stickybar").css("display", "none");
    });
    jQuery(window).resize(function () {
      if (!isScrolledVisible(elem))
        //elem not visible after scrolling
        jQuery("#stickybar").css("display", "block");
      else jQuery("#stickybar").css("display", "none");
    });
  }
}

var tooltipsfun = function (sensorele, tooltipID) {
  jQuery(sensorele).css("cursor", "pointer");
  jQuery(sensorele)
    .mouseenter(function () {
      timer = setTimeout(function () {
        jQuery("#" + tooltipID).show();
      }, 700);
    })
    .mouseleave(function () {
      clearTimeout(timer);
      setTimeout(function () {
        jQuery("#" + tooltipID).hide();
      }, 700);
    });
};

var vmoreHLnews = function (boxwidth, totalitems, nodata) {
  var n = jQuery(".hlitem").length,
    width = boxwidth,
    newwidth = width * n;

  jQuery("#hlstage, .hlitem").css("width", width);
  jQuery("#hlslide-holder").css({
    width: newwidth,
  });

  jQuery(".hlitem").each(function (i) {
    var thiswid = 730;
    jQuery(this).css({
      left: thiswid * i,
    });
  });

  jQuery("#hlprev").click(function () {
    var hlprev = jQuery("#hlslide-holder .active").prev();
    var curIndex =
      jQuery(".active").index() - 1 < 0 ? 0 : jQuery(".active").index() - 1;
    if (hlprev.length) {
      getHLpaging(curIndex, totalitems, n, nodata);
      jQuery("#hlstage").animate(
        {
          scrollLeft: hlprev.position().left,
        },
        1000
      );
    }
  });
  /* on right button click scroll to the next sibling of the current visible slide */
  jQuery("#hlnext").click(function () {
    var hlnext = jQuery("#hlslide-holder .active").next();
    var curIndex =
      jQuery(".active").index() + 1 > n ? n : jQuery(".active").index() + 1;
    if (hlnext.length) {
      getHLpaging(curIndex, totalitems, n, nodata);
      jQuery("#hlstage").animate(
        {
          scrollLeft: hlnext.position().left,
        },
        1000
      );
    }
  });

  /*on scroll move the indicator 'shown' class to the
    most visible slide on viewport
    */
  jQuery("#hlstage").scroll(function () {
    var scrollLeft = jQuery(this).scrollLeft();
    jQuery(".hlitem").each(function (i) {
      var posLeft = jQuery(this).position().left;
      var w = jQuery(this).width();

      if (scrollLeft >= posLeft && scrollLeft < posLeft + w) {
        jQuery(this).addClass("active").siblings().removeClass("active");
      }
    });
  });
};

function getHLpaging(curIndex, totalitems, totaldivitems, totaldata) {
  jQuery("a.hlnavleft").removeClass("inactiveleft");
  jQuery("a.hlnavright").removeClass("inactiveright");
  pagingIndex =
    curIndex == totaldivitems - 1 ? totaldata : (curIndex + 1) * totalitems;
  pagingText =
    curIndex * totalitems + 1 + "-" + pagingIndex + " of " + totaldata;
  jQuery(".hlpaging").text(pagingText);
  if (curIndex == 0) {
    jQuery("a.hlnavleft").addClass("inactiveleft");
    jQuery("a.hlnavright").removeClass("inactiveright");
  } else if (curIndex == totaldivitems - 1) {
    jQuery("a.hlnavright").addClass("inactiveright");
    jQuery("a.hlnavleft").removeClass("inactiveleft");
  }
}

function validCaptcha(formname, group) {
  var v = grecaptcha.getResponse();
  if (v.length == 0) {
    alert("Please Complete The Captcha");
    return false;
  } else {
    if (typeof formname != "undefined" && formname != "") {
      try {
        let hiddenInput = document.createElement("input");
        hiddenInput.setAttribute("type", "hidden");
        hiddenInput.setAttribute("name", "g-recaptcha-response");
        let gresponse = document.querySelector(".g-recaptcha-response").value;
        hiddenInput.setAttribute("value", gresponse);
        document[formname].appendChild(hiddenInput);
        document.forms[formname].submit();
      } catch (e) {
        document.forms[formname].submit();
      }
    } else {
      return true;
    }
  }
}

function checkValidDate(inDate, futurecheck) {
  /***get today date****/
  var today = new Date();
  var todaydd = today.getDate();
  var todaymm = today.getMonth() + 1; //January is 0!
  var todayyyyy = today.getFullYear();
  todayyyyy = todayyyyy.toString();

  if (todaydd < 10) var todaydd = pad(todaydd, 2);
  if (todaymm < 10) var todaymm = pad(todaymm, 2);

  if (inDate == "") return true;
  var d = "312831303130313130313031";

  /* For invalid dates, return false */
  if (inDate.length > 0 && inDate.length < 8) return false;

  // Expected inDate format: dd.mm.yyyy
  dd = inDate.substring(0, 2);
  mm = inDate.substring(2, 4);
  yy = inDate.substring(4, 8);

  /* Now, convert the string yr1 into a numeric and test for leap year.
  If it is, change the end of month day string for Feb to 29  */

  var isLeap = false;
  yy = yy * 1;
  if (yy % 400 == 0) isLeap = true;
  else if (yy % 100 == 0) isLeap = false;
  else if (yy % 4 == 0) isLeap = true;
  if (isLeap) d = d.substring(0, 2) + "29" + d.substring(4, d.length);

  /* Pick the end of month day from the d string for this month. */
  pos = mm * 2 - 2;
  ld = d.substring(pos, pos + 2) + 0;
  if (dd < 1 || dd > ld) return false;
  else if (mm < 1 || mm > 12) return false;
  else if (yy < 1900) return false;
  else if (
    typeof futurecheck !== "undefined" &&
    parseInt(yy + mm + dd) > parseInt(todayyyyy + todaymm + todaydd)
  )
    return false;

  return true;
}

function pad(str, max) {
  str = str.toString();
  return str.length < max ? pad("0" + str, max) : str;
}

function gInputNumbersDotOnly(myfield, e) {
  var key;
  var keychar;
  if (window.event) key = window.event.keyCode;
  else if (e) key = e.which;
  else return true;
  keychar = String.fromCharCode(key);
  keychar = keychar.toLowerCase();
  // control keys
  if (key == null || key == 0 || key == 8 || key == 9 || key == 13 || key == 27)
    return true;
  // numbers
  else if ("0123456789.".indexOf(keychar) > -1) return true;
  else return false;
}

function gAddLoadEvent(func) {
  var oldonload = window.onload;
  if (typeof window.onload != "function") {
    window.onload = func;
  } else {
    window.onload = function () {
      oldonload();
      func();
    };
  }
}

function gDestroycatfish() {
  /* catfish closer function */
  jQuery("#catfish").remove(); /* clip catfish off the tree */
  document.getElementsByTagName("html")[0].style.padding =
    "0"; /* reset the padding at the bottom */
  return false; /* disable the link's 'linkiness' -- so it won't jump you up the top of the page */
}

function gCloselink() {
  /* attach the catfish closer function to the link */
  if (document.getElementById("closeme")) {
    var closelink =
      document.getElementById("closeme"); /* find the 'close this' link */
    closelink.onclick =
      gDestroycatfish; /* attach the destroy function to it's 'onclick' */
  }
}

function gSetCookie(cname, cvalue, exdays) {
  var d = new Date();
  d.setTime(d.getTime() + exdays * 24 * 60 * 60 * 1000);
  var expires = "expires=" + d.toUTCString();
  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function convertDate(date, current_Date) {
  // format: DD/MM/YYYY (Check valid date and get date)
  var valid_date = "";

  var todayDate = new Date();
  var todayDD = todayDate.getDate().toString();
  var todayMM = (todayDate.getMonth() + 1).toString();
  var todayYY = todayDate.getFullYear().toString();

  if (todayDD < 10) var todayDD = pad(todayDD, 2);
  if (todayMM < 10) var todayMM = pad(todayMM, 2);

  if (date) {
    var splitDate = date.split(/[-\/.]/);
    var inputDD = splitDate[0] ? splitDate[0] : "";
    inputDD = inputDD.toString();
    var inputMM = splitDate[1] ? splitDate[1] : "";
    inputMM = inputMM.toString();
    var inputYY = splitDate[2] ? splitDate[2] : "";
    inputYY = inputYY.toString();

    if (inputDD < 10 && inputDD.length < 2) {
      inputDD = pad(inputDD, 2);
    }
    if (inputMM < 10 && inputMM.length < 2) {
      inputMM = pad(inputMM, 2);
    }

    if (inputDD.length != 2 || inputMM.length != 2 || inputYY.length != 4)
      return valid_date;

    if (checkValidDate(inputDD + inputMM + inputYY)) {
      if (typeof current_Date !== "undefined")
        return (valid_date = [
          inputYY + inputMM + inputDD,
          todayYY + todayMM + todayDD,
        ]);
      else return (valid_date = [inputYY + inputMM + inputDD]);
    } else return valid_date;
  } else return valid_date;
}

function createSortingTable(tableid) {
  let table = new DataTable("#" + tableid, {
    paging: $("#" + tableid + " tbody tr").length > 10,
    searching: $("#" + tableid + " tbody tr").length > 10,
    /* info: false, */
    order: [[1, "asc"]], // 0 = db id column; 1 = numbering column
    /* responsive: true, */
    autoWidth: false
  });
}

function setWidth(id, id2) {
  var one = document.getElementById(id);
  var two = document.getElementById(id2);
  style = window.getComputedStyle(one);
  wdt = style.getPropertyValue("width");
  two.style.width = wdt;
}

function datatableAlignment(elementID) {
  $(window).on("load resize", () => {
    var lengthElement = $("#" + elementID + "_length");
    var filterElement = $("#" + elementID + "_filter");
    var tableElement = $("#" + elementID);
    var tableParentElement = tableElement.parent();
    var infoElement = $("#" + elementID + "_paginate");
    var paginateElement = $("#" + elementID + "_paginate");

    // show entries and length row
    if (window.matchMedia("(max-width: 769px)").matches) {
      lengthElement.addClass("d-flex justify-content-left mb-3");
      filterElement.addClass("d-flex justify-content-left mb-3");
    } else {
      lengthElement.removeClass("d-flex justify-content-left");
      filterElement.removeClass("d-flex justify-content-left");
    }

    // paginate
    if (window.matchMedia("(max-width: 361px)").matches) {
      paginateElement.children().addClass("d-flex flex-column");
    } else {
      paginateElement.children().removeClass("d-flex flex-column");
    }

    // table
    if (!tableParentElement.parent().hasClass("table-responsive"))
      tableParentElement.parent().addClass("table-responsive");

    if (!tableParentElement.hasClass("p-0")) tableParentElement.addClass("p-0");

    // info
    if (!infoElement.hasClass("mb-3")) infoElement.addClass("mb-3 pb-3");

    // paginate
    if (!paginateElement.hasClass("mb-3"))
      paginateElement.addClass("mb-3 pb-3");
  });
}

function centerAlignment(elementID) {
  $(window).on("load resize", () => {
    var form = $("#" + elementID);

    if (window.matchMedia("(max-height: 950px)").matches) {
      if (form.hasClass("centered")) form.removeClass("centered");

      form.css("overflow", "auto");
    } else {
      form.addClass("centered");

      form.css("overflow", "visible");
    }
  });
}

function floatInput(element) {
  $(element).on("input", function () {
    let actualValue = $(this).val().replace(".", "");
    console.log(actualValue);
    $(this).val((parseInt(actualValue) / 100).toFixed(2));

    if ($(this).val() == "0" || $(this).val() == "0.00") $(this).val("");
  });
}

function previewImage(input, output) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();

    reader.onload = function (e) {
      $("#" + output).attr("src", e.target.result);
    };

    reader.readAsDataURL(input.files[0]);
  }
}

async function confirmationDialog(id, msg, pagename, path, pathreturn, act) {
  switch (act) {
    case "I":
      var title = "Successful Insert " + pagename;
      var title2 = "Are you sure want to insert?";
      var btn = "Insert";
      break;
    case "E":
      var title = "Successful Edit " + pagename;
      var title2 = "Are you sure want to edit?";
      var btn = "Edit";
      break;
    case "D":
      var title = "Successful Delete " + pagename;
      var title2 = "Are You Sure Want To Delete This " + pagename + " ?";
      var btn = "Delete";
      break;
    case "F":
      var title = "Error Occurred,Please Try Again Later";
      break;
    case "MO":
      var title = msg + " Successful Place";
      break;
    case "ErrMO":
      var title = msg;
      break;
    case "NC":
      var title = "No changes were made.";
      break;
    case "PC":
      var title = "Successful Change " + pagename;
      break;
    default:
      var title = "Error";
  }

  if (act !== 'ErrMO') {
    localStorage.clear();
  }

  var message = "";
  if (msg.length >= 1) {
    for (let i = 0; i < msg.length; i++)
      message += `<p class="mt-n3" style="text-align:center; font-weight:bold;">${msg[i]}</p>`;
  }

  if (act == 'D') {
    var firstContent = title2;
  } else {
    var firstContent = title;
  }

  const modalElem = document.createElement("div");
  modalElem.id = "modal-confirm";
  modalElem.className = "modal fade";
  modalElem.innerHTML = `
  <div class="modal-dialog modal-dialog-centered" style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
      <div class="modal-content">             
          <div class="modal-body fs-6 mt-3">
              <p style="text-align:center; font-weight:bold; font-size:25px;">${firstContent} </p>
              ${message}
          </div>
          <div class="modal-footer d-flex justify-content-center mt-n3" style="border-top:0px">             
              <button id="acceptBtn" type="button" class="btn" 
                  style="border:1px solid #FF9B44; background-color:#FFFFFF; color:#FF9B44; box-shadow: 0 0 !important; border-radius: 24px; text-transform:none;">
                  ${btn}
              </button>
              <button id="rejectBtn" type="button" class="btn" 
                  style="border: 1px solid #FF9B44; background-color:#FFFFFF; color:#FF9B44; box-shadow: 0 0 !important; border-radius: 24px; text-transform:none;">
                  Cancel
              </button>
          </div>
      </div>
  </div>
`;

  const modelResult = document.createElement("div");
  modelResult.id = "modal-confirm";
  modelResult.className = "modal fade";
  modelResult.innerHTML = `
        <div class="modal-dialog modal-dialog-centered " style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
        <div class="modal-content">             
            <div class="modal-body fs-6 mt-3">
            <p style="text-align:center; font-weight:bold; font-size:25px;">${title}</p>
        </div>
        <div class="modal-footer d-flex justify-content-center mt-n3" style="border-top:0px">             
            <button id="contBtn" type="button" class="btn" 
            style="border:1px solid #FF9B44; background-color:#FFFFFF; color:#FF9B44; box-shadow:0 0 !important; border-radius: 24px; text-transform:none;">Continue</button>
        </div>
        </div>
    </div>
    `;

  if (act == "D") {
    const myModal = new bootstrap.Modal(modalElem, {
      keyboard: false,
      backdrop: "static",
    });
    myModal.show();

    const result = await new Promise((resolve, reject) => {
      document.body.addEventListener("click", response);

      function response(e) {
        let bool = false;
        if (e.target.id == "rejectBtn") bool = false;
        else if (e.target.id == "acceptBtn") bool = true;
        else return;
        document.body.removeEventListener("click", response);
        document.body.querySelector(".modal-backdrop").remove();
        modalElem.remove();
        resolve(bool);
      }
    });

    if (result) {
      $.ajax({
        type: "POST",
        url: path,
        data: {
          id: id,
          act: act,
        },

        cache: false,
        success: (result) => {
          console.log(path);
          const myModal2 = new bootstrap.Modal(modelResult, {
            keyboard: false,
            backdrop: "static",
          });
          myModal2.show();

          return new Promise((resolve, reject) => {
            document.body.addEventListener("click", response);

            var myTimeout = setTimeout(() => {
              document.body.removeEventListener("click", response);
              document.body.querySelector(".modal-backdrop").remove();
              modelResult.remove();
              resolve(true);
              location.reload();
            }, 5000);

            function response(e) {
              let bool = false;
              let timeOut = false;

              if (e.target.id == "contBtn") {
                bool = true;
                clearTimeout(myTimeout);
              } else return;

              document.body.removeEventListener("click", response);
              document.body.querySelector(".modal-backdrop").remove();
              modelResult.remove();
              resolve(bool);
              location.reload();
            }
          });
        },
      });
    } else console.log("Operation Cancelled.");
  }

  if (act == "I" || act == "E" || act == "MO" || act == "NC" || act == "PC" || act == "F" || act == "ErrMO") {
    const myModal2 = new bootstrap.Modal(modelResult, {
      keyboard: false,
      backdrop: "static",
    });
    myModal2.show();

    return new Promise((resolve, reject) => {
      document.body.addEventListener("click", response);

      var myTimeout = setTimeout(() => {
        document.body.removeEventListener("click", response);
        document.body.querySelector(".modal-backdrop").remove();
        modelResult.remove();
        resolve(true);
        window.location.href = pathreturn;

      }, 5000);

      function response(e) {
        let bool = false;
        let timeOut = false;

        if (e.target.id == "contBtn") {
          bool = true;
          clearTimeout(myTimeout);
        } else return;

        document.body.removeEventListener("click", response);
        document.body.querySelector(".modal-backdrop").remove();
        resolve(bool);
        window.location.href = pathreturn;

      }
    });
  }
}

/* Rate Checking */
var getUrlParameter = function getUrlParameter(sParam) {
  var sPageURL = window.location.search.substring(1),
    sURLVariables = sPageURL.split("&"),
    sParameterName,
    i;

  for (i = 0; i < sURLVariables.length; i++) {
    sParameterName = sURLVariables[i].split("=");

    if (sParameterName[0] === sParam) {
      return sParameterName[1] === undefined
        ? true
        : decodeURIComponent(sParameterName[1]).replace(/\+/g, " ");
    }
  }
  return false;
};

/* fix issue of dropdown menu display inside table responsive */
function dropdownMenuDispFix() {
  const dropdowns = document.querySelectorAll(".dropdown-toggle");
  const dropdown = [...dropdowns].map(
    (dropdownToggleEl) =>
      new bootstrap.Dropdown(dropdownToggleEl, {
        popperConfig(defaultBsPopperConfig) {
          return { ...defaultBsPopperConfig, strategy: "fixed" };
        },
      })
  );
}

//autocomplete
function searchInput(param,siteURL) { 
  var elementID = param["elementID"];
  var hiddenElementID = param["hiddenElementID"];
  var search = param["search"];
  var type = param["searchType"];
  var dbTable = param["dbTable"];
  if (param["addSelection"]) {
    var addSelection = param["addSelection"];
  }

  if (search != "") {
    $.ajax({
      url: siteURL + "/getSearch.php",
      type: "post",
      data: {
        searchText: search,
        searchType: type,
        tblname: dbTable,
      },
      dataType: "json",
      success: (result) => {
        // create div
        if (
          !(
            ($("#searchResult_" + elementID).length &&
              $("#clear_" + elementID).length) > 0
          )
        )
          $("#" + elementID).after(
            '<ul class="searchResult" id="searchResult_' +
            elementID +
            '"></ul>',
            '<div id="clear_' + elementID + '" class="clear"></div>'
          );

        // set width same as input
        setWidth(elementID, "searchResult_" + elementID);

        var dataArr = [];

        // loop result
        var len = result.length;
        $("#searchResult_" + elementID).empty();
        for (var i = 0; i < len; i++) {
          if (result[i]["desc"] != undefined) {
            var desc = result[i]["desc"];
            var value = result[i]["val"];
            $("#searchResult_" + elementID).append(
              "<li value='" + value + "'>" + desc + "</li>"
            );
          } else {
            var id = result[i]["id"];
            var name = result[i][type];
            $("#searchResult_" + elementID).append(
              "<li value='" + id + "'>" + name + "</li>"
            );
            dataArr[id] = result[i];
          }
        }

        if (addSelection) {
          $("#searchResult_" + elementID).append(
            "<li value='" + addSelection + "'>" + addSelection + "</li>"
          );
        }

        // binding click event to li
        $("#searchResult_" + elementID + " li").bind("click", function () {
          setText(this, "#" + elementID, "#" + hiddenElementID);
          $("#" + elementID).change();
          $("#searchResult_" + elementID).empty();
          $("#searchResult_" + elementID).remove();
          $("#clear_" + elementID).remove();
        });
      },
    });
  } else {
    $("#searchResult_" + elementID).empty();
    $("#searchResult_" + elementID).remove();
    $("#clear_" + elementID).remove();
  }

}

function retrieveDBData(param, siteURL, callback) {
  var search = param["search"];
  var type = param["searchType"];
  var dbTable = param["dbTable"];
  var col = param["searchCol"];

  if (search != "") {
    $.ajax({
      url: siteURL + '/searchData.php',
      type: 'post',
      data: {
          searchText: search,
          searchType: type,
          tblname: dbTable,
          searchCol: col
      },
      dataType: 'json',
      success: (result) => {   
          callback(result);      
      },
      error: function (xhr, status, error) {
        console.error('Error fetching data:', error);
        console.log('XHR Status:', status);
        console.log('XHR Response Text:', xhr.responseText);
        console.log('XHR Response JSON:', xhr.responseJSON);
    }
    });
  }
}

function retrieveJSONData(search, type, tblname) {
  return $.ajax({
    url: "getSearch.php",
    type: "post",
    data: {
      searchText: search,
      searchType: type,
      tblname: tblname,
    },
    dataType: "json",
    success: (result) => {
      /* console.log(result[0]); */
      /* return result; */
    },
  });
}

function setText(element, val, val2) {
  var text = $(element).text();
  var value = $(element).attr("value");

  if (value != "emptyValue") {
    $(val).val(text);
    $(val2).val(value).trigger("input"); // to trigger input event from package page
  } else {
    $(val).val("");
    $(val2).val("").trigger("input"); // to trigger input event from package page
  }
}

document.addEventListener("DOMContentLoaded", function () {
  var actionBtn = document.getElementById("actionBtn");

  // Retrieve data from localStorage on page load
  retrieveDataFromLocalStorage();

  // Attach input event listener to each input field
  var inputFields = document.querySelectorAll("input, textarea ,select");
  inputFields.forEach(function (input) {
    if (!input.readOnly) {
      input.addEventListener("input", function () {
        // Save form data to localStorage when user types
        saveFormDataToLocalStorage();
      });
    }
  });

  if (actionBtn) {
    actionBtn.addEventListener("click", function (event) {
      if (!validateForm()) {
        event.preventDefault();
        displayPreviousData();
      } else {
        // Save form data to localStorage when validation passes
        saveFormDataToLocalStorage();
      }
    });
  }

  function retrieveDataFromLocalStorage() {
    var inputFields = document.querySelectorAll("input, textarea ,select");
    var page = localStorage.getItem("page");

    if (page !== 'invalid') {
      inputFields.forEach(function (input) {
        // Check if the input is not readonly and has stored data
        if (!input.readOnly && localStorage.getItem(input.id) && input.id && input.type !== 'file') {
          input.value = localStorage.getItem(input.id);
        }
      });
    }
  }

  function saveFormDataToLocalStorage() {
    var inputFields = document.querySelectorAll("input, textarea ,select");
    var page = localStorage.getItem("page");

    if (page !== 'invalid') {
      inputFields.forEach(function (input) {
        if (!input.readOnly && input.id && !input.multiple && input.type !== 'file') {
          localStorage.setItem(input.id, input.value);
        }
      });
    }
  }

  function displayPreviousData() {
    // Loop through input fields and restore previous data
    var inputFields = document.querySelectorAll("input, textarea,select");
    inputFields.forEach(function (input) {
      // Check if the input is not readonly and has previous data
      if (!input.readOnly && localStorage.getItem(input.id) && input.type !== 'file') {
        input.value = localStorage.getItem(input.id);
      }
    });
  }

  function validateForm() {
    var alertMessages = document.querySelectorAll('span[role="alert"]');
    alertMessages.forEach(function (alert) {
      alert.parentNode.removeChild(alert);
    });

    checkRequiredInputs();

    return document.querySelectorAll('span[role="alert"]').length === 0;
  }

  function checkRequiredInputs() {
    var requiredInputs = document.querySelectorAll(
      "input[required], select[required]"
    );

    requiredInputs.forEach(function (input) {
      if (input.value.trim() === "") {
        var labelContent = document.querySelector(
          'label[for="' + input.id + '"]'
        ).textContent;

        var alertMessage = document.createElement("span");
        alertMessage.textContent = labelContent + " is required!";
        alertMessage.style.color = "red";
        alertMessage.setAttribute("role", "alert");

        input.parentNode.appendChild(alertMessage);

        // Save the current value as the previous value
        input.setAttribute("data-previous-value", input.value);
      }
    });
  }
});

// Wait for the DOM to be ready
document.addEventListener("DOMContentLoaded", function () {
  // Get the input field and error message elements
  var currentDataNameInput = document.getElementById("currentDataName");
  var errorSpan = document.getElementById("errorSpan");

  if (currentDataNameInput) {
    // Function to toggle error message visibility
    function toggleErrorMessage() {
      var inputValue = currentDataNameInput.value.trim();
      errorSpan.style.display =
        inputValue !== "" &&
          inputValue !== localStorage.getItem("currentDataName")
          ? "none"
          : "block";
    }

    // Attach an input event listener to the input field
    currentDataNameInput.addEventListener("input", toggleErrorMessage);

    // Initial toggle to set the initial state
    toggleErrorMessage();
  }
});

function setCookie(cname, cvalue, exMins) {
  var d = new Date();
  d.setTime(d.getTime() + exMins * 60 * 1000);
  var expires = "expires=" + d.toUTCString();
  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function checkCurrentPage(page, action) {
  var previousPage = localStorage.getItem("page");
  var perviousAction = localStorage.getItem("action");

  if (previousPage != page || perviousAction != action) {
    localStorage.clear();
    localStorage.setItem("page", page);
    localStorage.setItem("action", action);
  }
}

function preloader(additionalDelay, action) {
  document.addEventListener("DOMContentLoaded", function () {
    setTimeout(function () {
      document.querySelector(".preloader").style.display = "none";
      document.querySelector(".pre-load-center").style.display = "none";
      document.querySelector(".page-load-cover").style.display = "block";
      setAutofocus(action);
    }, additionalDelay);
  });
}

function setAutofocus(action) { 
  if (action === "I" || action === "E") {
    var firstInput = $("input[type='text']:visible:enabled:not(:checkbox,:radio,:hidden,[readonly]), textarea:visible:enabled:not(:hidden,[readonly]), input[type='number']:visible:enabled:not(:hidden,[readonly])").filter(function() {
      return $.trim($(this).val()) === '';
    }).first();    if (firstInput.length > 0) {

      firstInput.focus();

      var inputValue = firstInput.val();
      var lastSpaceIndex = inputValue.lastIndexOf(" ");

      if (lastSpaceIndex !== -1) {
        var input = firstInput.get(0);
        var lastWordIndex = inputValue.indexOf(" ", lastSpaceIndex + 1);
        var cursorPosition =
          lastWordIndex !== -1 ? lastWordIndex : inputValue.length;
        input.setSelectionRange(cursorPosition, cursorPosition);
      } else {
        firstInput.get(0).selectionStart = firstInput.get(0).selectionEnd =
          inputValue.length;
      }
    }
  }
}
