document.getElementById("plot2").addEventListener("click", function() {
  var textarea = document.getElementById("max");
  // On ajoute toujours une nouvelle ligne si nécessaire, peu importe le contenu précédent
  if (textarea.value === "" || textarea.value.endsWith("\n")) {
    textarea.value += "draw2d(parametric(cos(t), sin(t), t, 0, 2*%pi),xrange = [-1.5, 1.5],yrange = [-1.5, 1.5],grid=true,xaxis=true,yaxis=true,line_width=1);";
  } else {
    textarea.value += "\ndraw2d(parametric(cos(t), sin(t), t, 0, 2*%pi),xrange = [-1.5, 1.5],yrange = [-1.5, 1.5],grid=true,xaxis=true,yaxis=true,line_width=1);";
  }
  textarea.focus(); // facultatif : replace le curseur dans la zone de texte
});