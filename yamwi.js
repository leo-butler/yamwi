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
document.addEventListener('DOMContentLoaded', (event) => {
  document.querySelectorAll('pre.output').forEach((el) => {hljs.highlightElement(el);});});


//////////////////////////////////////////////////////////////////////
// Buttons                                                          //
//////////////////////////////////////////////////////////////////////
function make_buttons_action (ba) {
    var id=ba.id, action=ba.action;
    var fun=function() {
        var textarea = document.getElementById("max");
        // On ajoute toujours une nouvelle ligne si nécessaire, peu importe le contenu précédent
	var maybe_newline = (textarea.value === "" || textarea.value.endsWith("\n")) ? "" : "\n";
	textarea.value += maybe_newline+action;
        textarea.focus(); // facultatif : replace le curseur dans la zone de texte
    };
    document.getElementById(id).addEventListener("click",fun);
};

const buttons_action = [
    {id: "expand-btn",	action: "expand((x+1)^2);"},
    {id: "resoudre1",	action: "solve(x^2 - 4 = 0, x);"},
    {id: "resoudre2",	action: "find_root(sin(x), x, 3, 4);"},
    {id: "factoriser",	action: "factor(x^3-1);"},
    {id: "simplifier1", action: "ratsimp((x^2-1)/(x-1));"},
    {id: "simplifier2", action: "trigsimp(cos(x)^2+sin(x)^2);"},
    {id: "simplifier3", action: "radcan(sqrt(8)+log(16));"},
    {id: "simplifier4", action: "trigexpand(sin(x+y));"},
    {id: "simplifier5", action: "fullratsimp( (x^3 - 1)/(x^2 - 1));"},
    {id: "simplifier6", action: "num((x^2 - 1)/(x + 1));"},
    {id: "simplifier7", action: "denom((x^2 - 1)/(x + 1));"},
    {id: "resoudre3",	action: "realroots(x^3 - x - 1);"},
    {id: "resoudre4",	action: "allroots(x^3 - 1);"},
    {id: "resoudre5",	action: "linsolve([x + y = 5, 3*x - y = 1], [x, y]);"},
    {id: "resoudre6",	action: "algsys([x^2 + y = 5, x*y = 2], [x, y]);"},
    {id: "resoudre7",	action: "subst(a + b, x, x^2);"},
    {id: "derive1",	action: "diff(x^3 + 2*x, x);"},
    {id: "derive2",	action: "diff(x^2*exp(-x), x,3);"},
    {id: "derive3",	action: "integrate(x^2+log(x), x);"},
    {id: "derive4",	action: "integrate((x^2-3*x+1)/(x^2+1),x,0,1);"},
    {id: "derive5",	action: "risch(x^2*exp(x^2),x);"},
    {id: "derive6",	action: "romberg(sin(x)+x^2, x, 0, %pi);"},
    {id: "ana1",	action: "limit(sin(x)/x, x, 0);"},
    {id: "ana2",	action: "sum(1/i^2, i, 1, inf), simpsum;"},
    {id: "ana3",	action: "product(k/(k^2+1),k,1,10);"},
    {id: "ana4",	action: "partfrac((x^3+5*x-5)/(x^2-1),x);"},
    {id: "ana5",	action: "taylor(log(1+x),x,0,5);"},
    {id: "ana6",	action: "gcd(2356,128);"},
    {id: "plot1",	action: "draw2d(grid=true,xaxis=true,yaxis=true,xrange=[-5,5],yrange=[-2,12],color=black,line_width=1,explicit(sin(x)*x^3,x,-5,5));"},
    {id: "plot2",	action: "draw2d(parametric(cos(t), sin(t), t, 0, 2*%pi),xrange = [-1.5, 1.5],yrange = [-1.5, 1.5],grid=true,xaxis=true,yaxis=true,line_width=1);"},
    {id: "plot3",	action: "draw2d(grid = true,line_width = 2,color = red,implicit(x^3 - 3*x*y^2 - 1 = 0, x, -2, 2, y, -2, 2),grid=true,xaxis=true,yaxis=true);"},
    {id: "plot4",	action: "draw3d(surface_hide = true,palette = [blue, cyan, green, yellow, orange, red],explicit( sin(sqrt(x^2 + y^2))/(sqrt(x^2 + y^2)), x, -6, 6, y, -6, 6));"},
    {id: "derive7",	action: "f(x):=x+sin(x);"}
];

document.addEventListener('DOMContentLoaded', (event) => {
    buttons_action.forEach(make_buttons_action);});
