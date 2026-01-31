<!doctype html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
  <link rel="icon" type="image/x-icon" href="favicon.ico">
  <link rel="stylesheet" media="all" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/default.min.css">
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/maxima.min.js"></script>
  <script type="text/javascript" src="yamwi.js"></script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.1/MathJax.js?config=TeX-AMS_HTML"></script>
  <title>Maxima on line</title>
</head>




<body>

<h1><a href="index.php" class="homepage"><i>Maxima Online</i></a></h1>

<p class="small-right">Help: 
<a href="help/help_es.html" target="_blank">Español</a>, 
<a href="help/help_en.html" target="_blank">English</a>
<a href="help/help_gl.html" target="_blank">Galego</a>
</p>

<div class="menu-row">

<select id="menu-simplify" class="cmd-menu">
<option value="">Simplify...</option>
<option value="expand((x+1)^2);">Expand</option>
<option value="factor(x^3-1);">Factor</option>
<option value="gfactor(x^2+1);">Complex factorization </option>
<option value="ratsimp((x^2-1)/(x-1));">Simplify fraction</option>
<option value="trigsimp(cos(x)^2+sin(x)^2);">Simplify trig</option>
<option value="radcan(sqrt(8)+log(16));">Simplify log/roots</option>
<option value="trigexpand(sin(x+y));">Expand trig</option>
<option value="fullratsimp((x^3-1)/(x^2-1));">Full simplify</option>
<option value="num((x^2-1)/(x+1));">Numerator</option>
<option value="denom((x^2-1)/(x+1));">Denominator</option>
<option value="rectform(3*exp(%pi*%i/3));">Cartesian form</option>
<option value="polarform(1+%i);">Polar form</option>
</select>

<select id="menu-solve" class="cmd-menu">
<option value="">Solve...</option>
<option value="solve(x^2-4=0,x);">Solve equation</option>
<option value="find_root(sin(x),x,3,4);">Num. solve</option>
<option value="realroots(x^3-x-1);">Real roots poly</option>
<option value="allroots(x^3-1);">All roots poly</option>
<option value="linsolve([x+y=5,3*x-y=1],[x,y]);">Linear system</option>
<option value="algsys([x^2+y=5,x*y=2],[x,y]);">Solve system</option>
<option value="subst(a+b,x,x^2);">Substitution</option>
<option value="ode2('diff(y,x)-y=x^2,y,x);">First-Order ODE</option>
<option value="ic1(ode2('diff(y,x)-y=2,y,x),x=0,y=1);">First-Order ODE IVP</option>
<option value="ode2('diff(y,x,2)+y=0,y,x);">Second-Order ODE</option>
<option value="ic2(ode2('diff(y,x,2)+y=0,y,x),x=0,y=1,'diff(y,x)=1);">Second-Order ODE IVP</option>
</select>

<select id="menu-deriv" class="cmd-menu">
<option value="">Calculus...</option>
<option value="diff(x^3+2*x,x);">Derivative</option>
<option value="diff(x^2*exp(-x),x,3);">n-th Derivative</option>
<option value="integrate(x^2+log(x),x);">Integral</option>
<option value="integrate((x^2-3*x+1)/(x^2+1),x,0,1);">Definite Integral</option>
<option value="risch(x^2*exp(x^2),x);">Risch</option>
<option value="romberg(sin(x)+x^2,x,0,%pi);">Romberg</option>
<option value="f(x):=x+sin(x);">Define f(x)</option>
</select>


<select id="menu-ana" class="cmd-menu">
<option value="">Analysis...</option>
<option value="limit(sin(x)/x,x,0);">Limit</option>
<option value="sum(1/i^2,i,1,inf),simpsum;">Sum</option>
<option value="product(k/(k^2+1),k,1,10);">Product</option>
<option value="partfrac((x^3+5*x-5)/(x^2-1),x);">Partial fractions</option>
<option value="taylor(log(1+x),x,0,5);">Taylor series</option>
<option value="gcd(2356,128);">GCD</option>
</select>


<select id="menu-plot" class="cmd-menu">
<option value="">Plot...</option>
<option value="draw2d(grid=true,xaxis=true,yaxis=true,xrange=[-5,5],yrange=[-2,12],color=black,line_width=1,explicit(sin(x)*x^3,x,-5,5));">2D curve</option>
<option value="draw2d(parametric(cos(t),sin(t),t,0,2*%pi),xrange=[-1.5,1.5],yrange=[-1.5,1.5],grid=true,xaxis=true,yaxis=true,line_width=1);">2D parametric</option>
<option value="draw2d(grid=true,line_width=2,color=red,implicit(x^3-3*x*y^2-1=0,x,-2,2,y,-2,2),grid=true,xaxis=true,yaxis=true);">2D implicit</option>
<option value="draw3d(surface_hide=true,palette=[blue,cyan,green,yellow,orange,red],explicit(sin(sqrt(x^2+y^2))/(sqrt(x^2+y^2)),x,-6,6,y,-6,6));">3D surface</option>
<option value="draw3d(surface_hide=true,parametric_surface((2-0.2*cos(phi))*sin(theta),(2-0.2*cos(phi))*cos(theta),0.2*sin(phi),phi,0,2*%pi,theta,0,2*%pi));">3D parametric surface</option>
<option value="draw3d(nticks=200,parametric((2-0.5*cos(t))*sin(t/4),(2-0.5*cos(t))*cos(t/4),0.5*sin(t),t,0,8*%pi)); ">3D parametric curve</option>
<option value="draw2d(grid=true,xrange=[-10,10],yrange=[-5,10],point_type=7,points([[-3,1],[2,5]]));">Plot points</option>
<option value="draw2d(xrange=[-10,10],yrange=[-5,10],point_type=5,points_joined = true,points([[-3,1],[2,5],[5,-2],[-3,1]]));">Plot lines</option>
<option value="draw_movie(lambda([c,options],
  draw2d(options, explicit(sin(x)+c,x,-%pi,%pi),yrange=[-2,2])),makelist(c/30,c,-30,30));">Animation</option>
</select>

<select id="menu-matrix" class="cmd-menu">
<option value="">Matrix...</option>
<option value="M:matrix([2,1,0],[1,0,1],[0,1,4]);">Define matrix</option>
<option value="invert(matrix([2,1,0],[1,0,1],[0,1,4]));">Inverse</option>
<option value="transpose(matrix([-2,1,5],[1,8,4],[0,1,4]));">Transpose</option>
<option value="matrix([1,2],[3,4]).matrix([-1,5],[0,8]);">Multiply</option>
<option value="determinant(matrix([2,1,0],[1,0,1],[0,1,4]));">Determinant</option>
<option value="expand(charpoly(matrix([2,1,0],[1,0,1],[0,1,4]),x)); ">Characteristic Polynomial</option>
<option value="eigenvalues(matrix([6,-11,6],[1,0,0],[0,1,0]));">Eigenvalues</option>
<option value="eigenvectors(matrix([6,-11,6],[1,0,0],[0,1,0]));">Eigenvectors</option>
</select>

</div>

<hr>


<?php
include('yamwi.php');

$default_code =
   "expand((x-2)^3*(x+1/3)^2);\n".
   "solve(x^2-x+2=0);\n".
   "invert(matrix([2,3,1], [a,0,0], [1,4,8]));\n".
   "integrate(x * sin(x), x);\n".
   "draw3d(explicit(x^2+y^2,x,-1,1,y,-1,1));\n".
   "plot2d(exp(-x)*sin(x),[x,0,2*%pi]);";

start($default_code);
?>


<hr>

<button id="btn-pdf">Save as PDF</button>

<script>
document.getElementById('btn-pdf').addEventListener('click', function () {
    // On capture tout le body (ou une zone spécifique si besoin)
    const element = document.body;

    const opt = {
        margin:       10,
        filename:     'resultats-maxima.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2 },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };

    html2pdf().set(opt).from(element).save();
});
</script>

<button id="saveBtn">Save commands (.mac)</button>
<script>
// Fonction pour sauvegarder le contenu de la textarea "max"
document.getElementById("saveBtn").addEventListener("click", function() {
    var textarea = document.getElementById("max");
    if (!textarea) {
        alert("La zone de texte 'max' est introuvable !");
        return;
    }
    var contenu = textarea.value;
    var blob = new Blob([contenu], {type: "text/plain"});
    var link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = "yamwi_commandes.mac";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(link.href);
});
</script>

<button id="loadFileBtn">Load commands (.mac)</button>
<input type="file" id="fileInput" accept=".mac" style="display:none" />

<script>
const loadFileBtn = document.getElementById('loadFileBtn');
const fileInput = document.getElementById('fileInput');
const textarea = document.getElementById('max');  // utilise la textarea unique

loadFileBtn.addEventListener('click', () => {
  fileInput.click();
});

fileInput.addEventListener('change', (event) => {
  const file = event.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      textarea.value = e.target.result;  // Remplace le contenu existant
    };
    reader.readAsText(file);
  }
});
</script>

<button id="saveBatchBtn">Save batch for wxmaxima (.mac)</button>
<script>
// Fonction pour sauvegarder le contenu modifié de la textarea "max"
document.getElementById("saveBatchBtn").addEventListener("click", function() {
    var textarea = document.getElementById("max");
    if (!textarea) {
        alert("La zone de texte 'max' est introuvable !");
        return;
    }
    var contenu = textarea.value;
    var lignes = contenu.split('\n');
    for (var i = 0; i < lignes.length; i++) {
        // Remplacer toute commande en début de ligne commençant par 'plot' par 'wxplot'
        lignes[i] = lignes[i].replace(/^plot/, 'wxplot');
        // Remplacer toute commande en début de ligne commençant par 'draw' par 'wxdraw'
        lignes[i] = lignes[i].replace(/^draw/, 'wxdraw');
    }
    var contenuModifie = lignes.join('\n');
    var blob = new Blob([contenuModifie], {type: "text/plain"});
    var link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = "batch-wxmaxima.mac";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(link.href);
});
</script>

<button onclick="window.scrollTo({top: 0, behavior: 'smooth'});">Back to top</button>

<p class="small-left"><a href="https://github.com/leo-butler/yamwi/" target = "_blank">Yamwi Source</a></p>


</body>
</html>

