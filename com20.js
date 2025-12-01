document.getElementById("derive4").addEventListener("click", function() {
  var textarea = document.getElementById("max");
  // On ajoute toujours une nouvelle ligne si nécessaire, peu importe le contenu précédent
  if (textarea.value === "" || textarea.value.endsWith("\n")) {
    textarea.value += "integrate((x^2-3*x+1)/(x^2+1),x,0,1);";
  } else {
    textarea.value += "\nintegrate((x^2-3*x+1)/(x^2+1),x,0,1);";
  }
  textarea.focus(); // facultatif : replace le curseur dans la zone de texte
});