      !function(e,n,a){e.behaviors.omega_samhsa={attach:function(e,o){function i(){var e=n(a).width();e<l&&n("body").find(".block-mobile-hamburger-block .menu-toggle").length?n(".main-menu").addClass("collapsible"):e>=l&&n(".main-menu").removeClass("collapsible open animating")}function s(){var e=n(a).width();e<l&&!n("body").find(".side-menu .menu-toggle").length?(n(".side-menu > h2").prepend('<a class="menu-toggle" href="#" rel=”nofollow”><span class="menu-icon"></span>Menu</a>'),n(".side-menu").addClass("collapsible")):e>=l&&(n("body").find(".side-menu .menu-toggle").remove(),n(".side-menu").removeClass("collapsible open animating"))}function t(){var e=n(a).width();e<l&&n("body").find(".site-search .search-form-wrapper").length?n(".search-form-wrapper").appendTo(".region--preface-first"):e>=l&&n("body").find(".region--preface-first .search-form-wrapper").length&&n(".search-form-wrapper").prependTo(".head-search-wrapper")}var l=624;n(".field--name-field-section-link").hover(function(){n(".mega-menu-item .active").removeClass("active"),n(this).parent().addClass("active")}),n(".main-menu .paragraph--type--megamenu-data:nth-child(1)").addClass("active"),n("html").hasClass("touchevents")&&n(".main-menu .menu-item > a").once().click(function(e){!n(this).parent().hasClass("open")&&n(this).parent().find(".mega-menu-wrapper").length&&(e.preventDefault(),n(".main-menu .menu-item").removeClass("open"),n(this).parent().addClass("open"))}),n(".block-mobile-hamburger-block .menu-toggle").once().click(function(e){e.preventDefault(),n(".block-mobile-hamburger-block .menu-toggle, .main-menu").addClass("animating"),n(".main-menu").slideToggle().promise().done(function(){n(".main-menu.collapsible").removeAttr("style"),n(".block-mobile-hamburger-block .menu-toggle, .main-menu").removeClass("animating").toggleClass("open")})}),n("body").once().on("click",".side-menu .menu-toggle",function(e){e.preventDefault(),n(this).parents(".collapsible").addClass("animating").find("> .menu").slideToggle().promise().done(function(){n(this).parents(".collapsible").find("> .menu").removeAttr("style"),n(this).parents(".collapsible").removeClass("animating").toggleClass("open")})}),n("body").once("openfootermenus").on("click","#quicklinks-toggle-button",function(e){e.preventDefault(),n(".region--postscript-second").addClass("animating"),n(".postscript-menu-wrapper").slideToggle().promise().done(function(){n(".postscript-menu-wrapper").removeAttr("style"),n(".region--postscript-second").removeClass("animating").toggleClass("open")})}),n("#search-toggle").once("opensearch").click(function(e){e.preventDefault(),n(".search-form-wrapper").slideToggle().promise().done(function(){n(".search-form-wrapper").removeAttr("style").toggleClass("open")})}),n(e).find("body").once("mobileSearchPlacement").each(function(){i(),s(),t()}),n(a).on("load",function(){}),n(a).resize(function(){i(),s(),t()}),n(a).scroll(function(){})}}}(Drupal,jQuery,this);

      !function(e,n,a){e.behaviors.omega_samhsa={attach:function(e,o){function i(){var e=n(a).width();e<l&&n("body").find(".block-mobile-hamburger-block .menu-toggle").length?n(".main-menu").addClass("collapsible"):e>=l&&n(".main-menu").removeClass("collapsible open animating")}function s(){var e=n(a).width();e<l&&!n("body").find(".side-menu .menu-toggle").length?(n(".side-menu > h2").prepend('<a class="menu-toggle" href="#" rel=”nofollow”><span class="menu-icon"></span>Menu</a>'),n(".side-menu").addClass("collapsible")):e>=l&&(n("body").find(".side-menu .menu-toggle").remove(),n(".side-menu").removeClass("collapsible open animating"))}function t(){var e=n(a).width();e<l&&n("body").find(".site-search .search-form-wrapper").length?n(".search-form-wrapper").appendTo(".region--preface-first"):e>=l&&n("body").find(".region--preface-first .search-form-wrapper").length&&n(".search-form-wrapper").prependTo(".head-search-wrapper")}var l=768;n(".field--name-field-section-link").hover(function(){n(".mega-menu-item .active").removeClass("active"),n(this).parent().addClass("active")}),n(".main-menu .paragraph--type--megamenu-data:nth-child(1)").addClass("active"),n("html").hasClass("touchevents")&&n(".main-menu .menu-item > a").once().click(function(e){!n(this).parent().hasClass("open")&&n(this).parent().find(".mega-menu-wrapper").length&&(e.preventDefault(),n(".main-menu .menu-item").removeClass("open"),n(this).parent().addClass("open"))}),n(".block-mobile-hamburger-block .menu-toggle").once().click(function(e){e.preventDefault(),n(".block-mobile-hamburger-block .menu-toggle, .main-menu").addClass("animating"),n(".main-menu").slideToggle().promise().done(function(){n(".main-menu.collapsible").removeAttr("style"),n(".block-mobile-hamburger-block .menu-toggle, .main-menu").removeClass("animating").toggleClass("open")})}),n("body").once().on("click",".side-menu .menu-toggle",function(e){e.preventDefault(),n(this).parents(".collapsible").addClass("animating").find("> .menu").slideToggle().promise().done(function(){n(this).parents(".collapsible").find("> .menu").removeAttr("style"),n(this).parents(".collapsible").removeClass("animating").toggleClass("open")})}),n("body").once("openfootermenus").on("click","#quicklinks-toggle-button",function(e){e.preventDefault(),n(".region--postscript-second").addClass("animating"),n(".postscript-menu-wrapper").slideToggle().promise().done(function(){n(".postscript-menu-wrapper").removeAttr("style"),n(".region--postscript-second").removeClass("animating").toggleClass("open")})}),n("#search-toggle").once("opensearch").click(function(e){e.preventDefault(),n(".search-form-wrapper").slideToggle().promise().done(function(){n(".search-form-wrapper").removeAttr("style").toggleClass("open")})}),n(e).find("body").once("mobileSearchPlacement").each(function(){i(),s(),t()}),n(a).on("load",function(){}),n(a).resize(function(){i(),s(),t()}),n(a).scroll(function(){})}}}(Drupal,jQuery,this);
(function (Drupal, $, window) {
  Drupal.behaviors.omega_samhsa_pep = {
    attach: function (context, settings) {
      $('.qty-justification').hide();
      let searchParams = new URLSearchParams(window.location.search);
      var pathArray = window.location.pathname.split( '/' );
      var hostname = window.location.hostname
      if(searchParams.has('quantity')) {
        $('.qty-justification').show().css('color','red');
        $('#edit-checkout-justification-justification-text').css('border', '1px solid red');
      }

      if ($('.checkout-pane-checkout-review-justification--text .field__item').text().length == 0) {
        if(window.location.href.indexOf("/review") >= 0) {
          //window.location.href = '/checkout/' + pathArray[2] + '/order_information?quantity=max';
        }
      }
      // Execute code once the DOM is ready.
      //----------------------------------------------------------------------------------------------


      // Execute code once the window is fully loaded.
      //----------------------------------------------------------------------------------------------
      $(window).on('load', function () {
        !function(t,e,a){"use strict";var r={};t.ARIAaccordion=r,r.NS="ARIAaccordion",r.AUTHOR="Scott O'Hara",r.VERSION="3.2.1",r.LICENSE="https://github.com/scottaohara/accessible_accordions/blob/master/LICENSE";var i="accordion",l=i+"__trigger",n=i+"__panel",o="[data-aria-accordion-heading]",d="[data-aria-accordion-panel]",c=0;r.create=function(){var t,a,s,u,A,g,h="none",b=e.querySelectorAll("[data-aria-accordion]");for(c+=1,g=0;g<b.length;g++){var f;if((t=b[g]).hasAttribute("id")||(t.id="acc_"+c+"-"+g),t.classList.add(i),e.querySelectorAll("#"+t.id+"> li").length?(a=e.querySelectorAll("#"+t.id+" li > "+d),s=e.querySelectorAll("#"+t.id+" li > "+o)):(a=e.querySelectorAll("#"+t.id+" > "+d),s=e.querySelectorAll("#"+t.id+" > "+o)),t.hasAttribute("data-default")&&(h=t.getAttribute("data-default")),A=t.hasAttribute("data-constant"),t.hasAttribute("data-multi"),t.hasAttribute("data-transition")){var y=t.querySelectorAll(d);for(f=0;f<y.length;f++)y[f].classList.add(n+"--transition")}for(r.setupPanels(t.id,a,h,A),r.setupHeadingButton(s,A),u=e.querySelectorAll("#"+t.id+"> li").length?e.querySelectorAll("#"+t.id+" li > "+o+" ."+l):e.querySelectorAll("#"+t.id+" > "+o+" ."+l),f=0;f<u.length;f++)u[f].addEventListener("click",r.actions),u[f].addEventListener("keydown",r.keytrolls)}},r.setupPanels=function(t,e,a,r){var i,l,o,d,c;for(i=0;i<e.length;i++)o=t+"_panel_"+(i+1),d=a,c=r,(l=e[i]).setAttribute("id",o),s(e[0],!0),l.classList.add(n),"none"!==d&&NaN!==parseInt(d)&&(d<=1?s(e[0],!1):d-1>=e.length?s(e[e.length-1],!1):s(e[d-1],!1)),(c&&"none"===d||NaN===parseInt(d))&&s(e[0],!1)},r.setupHeadingButton=function(t,a){var r,i,n,o,d,c;for(c=0;c<t.length;c++)i=(r=t[c]).nextElementSibling.id,n=e.getElementById(i).getAttribute("aria-hidden"),o=e.createElement("button"),d=r.textContent,r.innerHTML="",r.classList.add("accordion__heading"),o.setAttribute("type","button"),o.setAttribute("aria-controls",i),o.setAttribute("id",i+"_trigger"),o.classList.add(l),"false"===n?(u(o,!0),g(o,!0),a&&o.setAttribute("aria-disabled","true")):(u(o,!1),g(o,!1)),r.appendChild(o),o.appendChild(e.createTextNode(d))},r.actions=function(t){var a,i=this.id.replace(/_panel.*$/g,""),n=e.getElementById(this.getAttribute("aria-controls"));a=e.querySelectorAll("#"+i+"> li").length?e.querySelectorAll("#"+i+" li > "+o+" ."+l):e.querySelectorAll("#"+i+" > "+o+" ."+l),t.preventDefault(),r.togglePanel(t,i,n,a)},r.togglePanel=function(t,a,r,i){var l,n,o=t.target;if("true"!==o.getAttribute("aria-disabled")&&(l=o.getAttribute("aria-controls"),g(o,"true"),"true"===o.getAttribute("aria-expanded")?(u(o,"false"),s(r,"true")):(u(o,"true"),s(r,"false"),e.getElementById(a).hasAttribute("data-constant")&&A(o,"true")),e.getElementById(a).hasAttribute("data-constant")||!e.getElementById(a).hasAttribute("data-multi")))for(n=0;n<i.length;n++)o!==i[n]&&(g(i[n],"false"),l=i[n].getAttribute("aria-controls"),A(i[n],"false"),u(i[n],"false"),s(e.getElementById(l),"true"))},r.keytrolls=function(t){if(t.target.classList.contains(l)){var a,r=t.keyCode||t.which,i=this.id.replace(/_panel.*$/g,"");switch(a=e.querySelectorAll("#"+i+"> li").length?e.querySelectorAll("#"+i+" li > "+o+" ."+l):e.querySelectorAll("#"+i+" > "+o+" ."+l),r){case 35:t.preventDefault(),a[a.length-1].focus();break;case 36:t.preventDefault(),a[0].focus()}}},r.init=function(){r.create()};var s=function(t,e){t.setAttribute("aria-hidden",e)},u=function(t,e){t.setAttribute("aria-expanded",e)},A=function(t,e){t.setAttribute("aria-disabled",e)},g=function(t,e){t.setAttribute("data-current",e)};r.init()}(window,document);

      });


      // Execute code when the window is resized.
      //----------------------------------------------------------------------------------------------
      $(window).resize(function () {

      });


      // Execute code when the window scrolls.
      //----------------------------------------------------------------------------------------------
      $(window).scroll(function () {

      });

    }
  };

} (Drupal, jQuery, this));

(function ($) {

// execute mobile menu
    $(document).ready(function(){
        $(function() {
            $('#dl-menu').dlmenu({
                animationClasses : { classin : 'dl-animate-in-2', classout : 'dl-animate-out-2' }
            });
            $( ".mobile-only" ).parent().addClass( "mobile-only" );
        });
// execute menu active path
        var path = window.location.pathname.split('/');
        path = path[path.length - 1];
        if (path !== undefined) {
            $("ul.sf-menu, ul.sf-menu ul")
                .find("a[href$='" + path + "']") // gets all links that match the href
                .parents('li') // gets all list items that are ancestors of the link
                .children('a') // walks down one level from all selected li's
                .addClass('active');
        }
// More Like this section responsive tabs
    });

var coll = document.getElementsByClassName("collapse-region");
var i;

for (i = 0; i < coll.length; i++) {
  coll[i].addEventListener("click", function() {
    this.classList.toggle("active");
    var content = this.nextElementSibling;
    content.classList.toggle("active");
  });
}

})(jQuery);
