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
