// https://stackoverflow.com/questions/1144783/how-do-i-replace-all-occurrences-of-a-string-in-javascript
function replaceIt(str,find,replace) {return str.replace(new RegExp(find,'g'), replace);}
// convert base64 encoded string to a URL-safe string
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

