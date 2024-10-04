<!doctype html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
  <link rel="stylesheet" media="all" href="style.css">
  <title>Maxima on line</title>
</head>




<body>

<h1><i>Maxima on line</i></h1>

<p class="small-right">Help: 
<a href="help/help_es.html" target="_blank">Español</a>, 
<a href="help/help_en.html" target="_blank">English</a>
<a href="help/help_gl.html" target="_blank">Galego</a></p>

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

<p class="small-left"><a href="https://github.com/leo-butler/yamwi/" target = "_blank">Yamwi Source</a></p>


</body>
</html>

