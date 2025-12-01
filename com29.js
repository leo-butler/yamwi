document.getElementById("plot1").addEventListener("click", function() {
  var textarea = document.getElementById("max");
  // On ajoute toujours une nouvelle ligne si nécessaire, peu importe le contenu précédent
  if (textarea.value === "" || textarea.value.endsWith("\n")) {
    textarea.value += "draw2d(grid=true,xaxis=true,yaxis=true,xrange=[-5,5],yrange=[-2,12],color=black,line_width=1,explicit(sin(x)*x^3,x,-5,5));";
  } else {
    textarea.value += "\ndraw2d(grid=true,xaxis=true,yaxis=true,xrange=[-5,5],yrange=[-2,12],color=black,line_width=1,explicit(sin(x)*x^3,x,-5,5));";
  }
  textarea.focus(); // facultatif : replace le curseur dans la zone de texte
});