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
  document.querySelectorAll('pre.inputcode').forEach((el) => {hljs.highlightElement(el);});});
