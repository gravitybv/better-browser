jQuery(document).ready(function ($) {
    $('#betterbrowser').on('click', '.js-show-browserlist', function (e) {
        e.preventDefault();
        $('.js-betterbrowser-list').toggleClass('active');
    });
});

var browser = bowser.getParser(window.navigator.userAgent);
//console.log(browser);
console.log("You are using " + browser.parsedResult.browser.name +
    " v" + browser.parsedResult.browser.version +
    " on " + browser.parsedResult.os.name);

var isValidBrowser = browser.satisfies(betterBrowser);

console.log(isValidBrowser);
if(isValidBrowser){
    betterbrowserOffset('0');
  jQuery('#betterbrowser').show();
  jQuery('body').addClass('need-better-browser');
}

function betterbrowserOffset(timeoutTime, pxOffset) {
  if (!timeoutTime) {
    timeoutTime = 1000;
  }
  setTimeout(function() {
    if (!pxOffset) {
      pxOffset = jQuery('#betterbrowser').outerHeight(true);
    }
    jQuery('html').css('padding-top', pxOffset);
    //jQuery('html').animate({'padding-top': pxOffset}, 'slow');
    //console.log('betterbrowser offset van ' + pxOffset +' is gedaan na:' + timeoutTime);
  }, timeoutTime);
}
