var stickyNav = document.getElementsByClassName("nav--type-sticky")[0];

window.onscroll = function () {
    "use strict";
    if (pageYOffset > 0 ) {
        stickyNav.classList.add("nav--affix");
    }
    else {
        stickyNav.classList.remove("nav--affix");
    }
};

(function () {

  var nav__toggle = document.getElementsByClassName("nav__toggle")[0],
      navUl = document.getElementsByClassName("nav__ul");

  function toggleMobileMenu() {
    navUl[0].style.transition = "max-height 0.2s ease";
    navUl[0].classList.toggle("nav__ul--hide");
    stickyNav.classList.toggle("nav--open");

  }

  nav__toggle.onclick = toggleMobileMenu;
}());

function setCookie(cname,cvalue,exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires=" + d.toGMTString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/; domain=.belloforwork.com";
};


function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(";");
    for(var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == " ") {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
};

function checkLandingUrlCookie() {
    var landingUrl=getCookie("lurl");
    if (landingUrl != "") {

    } else {
       landingUrl = window.location.href;
       if (landingUrl != "" && landingUrl != null) {
           setCookie("lurl", landingUrl, 7);
       }
    }
};

function incrementTotalPagesCookie() {
    var totalpages=getCookie("totalpages");
    if (totalpages != "") {
      newTotal = parseInt(totalpages) + 1;
      setCookie("totalpages", newTotal, 7);
    } else {
       totalpages = 1;
       if (totalpages != "" && totalpages != null) {
           setCookie("totalpages", totalpages, 7);
       }
    }
};

// Parse the URL
function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
    var regexS = "[\\?&]" + name + "=([^&#]*)";
    var regex = new RegExp(regexS);
    var results = regex.exec(window.location.search);
    if(results == null) {
      return "";
    } else {
      return decodeURIComponent(results[1].replace(/\+/g, " "));
    }
  }



function checkUTMCookie() {

  var referrer = document.referrer;
  var parser = document.createElement("a");
  parser.href = referrer;
  var ref_hostname = parser.hostname;
  var ref_search = parser.search;
  var landingUrl=getCookie("utm_source");
  if (landingUrl != "") {

  } else {
    var utm_source = getParameterByName("utm_source");
    var utm_medium = getParameterByName("utm_medium");
    var utm_campaign = getParameterByName("utm_campaign");
    var utm_content = getParameterByName("utm_content");
    var utm_term = getParameterByName("utm_term");

    if (utm_source.length != 0) {
      setCookie("utm_source", utm_source, 7);
      setCookie("utm_medium", utm_medium, 7);
      setCookie("utm_campaign", utm_campaign, 7);
      setCookie("utm_content", utm_content, 7);
      setCookie("utm_term", utm_term, 7);
    } else if (referrer.length != 0) {
      setCookie("utm_source", "referral", 7);
      setCookie("utm_medium", ref_hostname, 7);
      setCookie("utm_content", referrer, 7);
      setCookie("utm_term", ref_search, 7);
    } else {
      setCookie("utm_source", "direct", 7);
      setCookie("utm_medium", "direct", 7);
    }
  }
};
