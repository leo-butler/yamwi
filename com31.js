document.getElementById("plot3").addEventListener("click", function() {
  var textarea = document.getElementById("max");
  // On ajoute toujours une nouvelle ligne si nécessaire, peu importe le contenu précédent
  if (textarea.value === "" || textarea.value.endsWith("\n")) {
    textarea.value += "draw2d(grid = true,line_width = 2,color = red,implicit(x^3 - 3*x*y^2 - 1 = 0, x, -2, 2, y, -2, 2),grid=true,xaxis=true,yaxis=true);";
  } else {
    textarea.value += "\ndraw2d(grid = true,line_width = 2,color = red,implicit(x^3 - 3*x*y^2 - 1 = 0, x, -2, 2, y, -2, 2),grid=true,xaxis=true,yaxis=true);";
  }
  textarea.focus(); // facultatif : replace le curseur dans la zone de texte
});