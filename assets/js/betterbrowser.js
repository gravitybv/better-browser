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
    jQuery('#betterbrowser').show();
}