document.getElementById("plot4").addEventListener("click", function() {
  var textarea = document.getElementById("max");
  // On ajoute toujours une nouvelle ligne si nécessaire, peu importe le contenu précédent
  if (textarea.value === "" || textarea.value.endsWith("\n")) {
    textarea.value += "draw3d(surface_hide = true,palette = [blue, cyan, green, yellow, orange, red],explicit( sin(sqrt(x^2 + y^2))/(sqrt(x^2 + y^2)), x, -6, 6, y, -6, 6));";
  } else {
    textarea.value += "\ndraw3d(surface_hide = true,palette = [blue, cyan, green, yellow, orange, red],explicit( sin(sqrt(x^2 + y^2))/(sqrt(x^2 + y^2)), x, -6, 6, y, -6, 6));";
  }
  textarea.focus(); // facultatif : replace le curseur dans la zone de texte
});