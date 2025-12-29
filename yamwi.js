// https://stackoverflow.com/questions/1144783/how-do-i-replace-all-occurrences-of-a-string-in-javascript
function replaceIt(str,find,replace) {
    if(str === null) {return "";}
    else {return str.replace(new RegExp(find,'g'), replace);}}
// convert base64 encoded string to a URL-safe string
// See page 7 of https://www.rfc-editor.org/rfc/rfc4648.txt
function b64toa(b64) {return replaceIt(replaceIt(replaceIt(b64,'=','-'),'\\+','_'),'\\/','~');}
// convert string to base64
function atob64(str) {return replaceIt(replaceIt(replaceIt(str,'-','='),'_','+'),'~','/');}
// decode 'dirty' base64 string
function atou(str) {return decodeURIComponent(escape(atob(atob64(str))));}
// encode string to 'dirty' base64
function utoa(str) {return b64toa(btoa(unescape(encodeURIComponent(str))));}

// utoa("x : π+α;"); // "eCA6IM_AK86xOw--"
// atou("eCA6IM_AK86xOw--" ); // "x : π+α;"
// utoa("x : π+α/𝐁;"); // "eCA6IM_AK86xL~CdkIE7"
// atou("eCA6IM_AK86xL~CdkIE7"); // "x : π+α/𝐁;"
// utoa("x : π+α/𝐁; "); // "eCA6IM_AK86xL~CdkIE7IA--"
// atou("eCA6IM_AK86xL~CdkIE7IA--"); // "x : π+α/𝐁;"

/////////////////////////
// Update the textarea //
/////////////////////////
function updateTextArea(ta) {
    var txta = document.getElementById(ta);
    const thisurl = new URL(location.href);
    const max = thisurl.searchParams.get('max');
    if (max === null) {} else {txta.value = atou(thisurl.searchParams.get('max'));}
}
document.addEventListener("DOMContentLoaded", () => {updateTextArea('max');});

//////////////////////////////////////////////////////////////////////
// HIGHLIGHT.JS                                                     //
// https://highlightjs.readthedocs.io/en/latest/readme.html         //
//////////////////////////////////////////////////////////////////////
document.addEventListener('DOMContentLoaded', (event) => {
    document.querySelectorAll('span.inputcode').forEach((el) => {hljs.highlightElement(el);});
    document.querySelectorAll('pre.enhanced-ascii-art').forEach((el) => {hljs.highlightElement(el);});});


//////////////////////////////////////////////////////////////////////
// Buttons                                                          //
//////////////////////////////////////////////////////////////////////
function make_menu_action(menuId) {
    var menu = document.getElementById(menuId);
    if (!menu) return;

    menu.addEventListener("change", function () {
        if (!this.value) return;

        var textarea = document.getElementById("max");
        // On ajoute toujours une nouvelle ligne si nécessaire, peu importe le contenu précédent
        var maybe_newline = (textarea.value === "" || textarea.value.endsWith("\n")) ? "" : "\n";

        // Plus de replace ! On prend la commande telle quelle
        textarea.value += maybe_newline + this.value;
        textarea.focus();
        this.value = "";
    });
}

document.addEventListener('DOMContentLoaded', (event) => {
    make_menu_action("menu-simplify");
    make_menu_action("menu-solve");
    make_menu_action("menu-deriv");
    make_menu_action("menu-ana");
    make_menu_action("menu-plot");
});

//////////////////////////////////////////////////////////////////////
// Automatically resize the input textarea                          //
//////////////////////////////////////////////////////////////////////
document.addEventListener('DOMContentLoaded', (event) => {
    document.querySelectorAll("textarea").forEach(function(textarea) {
	textarea.style.height = textarea.scrollHeight + "px";
	textarea.style.overflowY = "hidden";

	textarea.addEventListener("input", function() {
	    this.style.height = "auto";
	    this.style.height = this.scrollHeight + "px";
	});
    })});

//////////////////////////////////////////////////////////////////////
// Toggle output format                                             //
//////////////////////////////////////////////////////////////////////
function show_output(n) {
    // see yawmi_display in yamwi.mac
    var types = ["ascii", "ascii-art", "enhanced-ascii-art", "mathml", "tex-mathjax"];
    var type = (n >= 0 && n < types.length) ? types[n] : types[0];
    var turn_on_off = function (t) {
	var stle = (type == t) ? "flex" : "none";
	var e = document.getElementsByClassName("output "+t);
	for (i=0; i<e.length; i++) {
	    e[i].style.display = stle;
	}};
    types.map(turn_on_off);
};
