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

  <title>Maxima on line</title>
</head>




<body>

<h1><a href="index.php" class="homepage"><i>Maxima Online</i></a></h1>

<p class="small-right">Help: 
<a href="help/help_es.html" target="_blank">Español</a>, 
<a href="help/help_en.html" target="_blank">English</a>
<a href="help/help_gl.html" target="_blank">Galego</a>
</p>

<button id="expand-btn">Expand</button>
<button id="factoriser">Factor</button>
<button id="simplifier1">Simplify fraction</button>
<button id="simplifier2">Simplify trigonometry</button>
<button id="simplifier3">Simplify roots/logarithms</button>
<button id="simplifier4">Expand trigonometry</button>
<button id="simplifier5">Fully simplify fraction</button>
<button id="simplifier6">Numerator of fraction</button>
<button id="simplifier7">Denominator of fraction</button>
<br>
<button id="resoudre1">Solve equation</button> 
<button id="resoudre2">Numerically solve</button> 
<button id="resoudre3">Real roots of polynomial</button> 
<button id="resoudre4">Roots of polynomial</button> 
<button id="resoudre5">Solve linear system</button> 
<button id="resoudre6">Solve system </button> 
<button id="resoudre7">Substitution </button> 
<br>
<button id="derive1">Derivative</button> 
<button id="derive2">n-th Derivative</button> 
<button id="derive3">Integral</button> 
<button id="derive4">Definite Integral</button> 
<button id="derive5"> Risch Integration</button> 
<button id="derive6">Romberg Integration</button>
<button id="derive7">Define function f(x)</button>
<br>
<button id="ana1">Limit</button>
<button id="ana2">Sum</button>
<button id="ana3">Product</button>
<button id="ana4">Partial fraction decomposition</button>
<button id="ana5">Taylor series</button>
<button id="ana6">GCD (Greatest Common Divisor)</button>
<br>
<button id="plot1">2D Cartesian curve</button>
<button id="plot2">2D parametric curve</button>
<button id="plot3">2D implicit curve</button>
<button id="plot4">3D Surface </button>

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

