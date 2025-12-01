document.getElementById("derive2").addEventListener("click", function() {
  var textarea = document.getElementById("max");
  // On ajoute toujours une nouvelle ligne si nécessaire, peu importe le contenu précédent
  if (textarea.value === "" || textarea.value.endsWith("\n")) {
    textarea.value += "diff(x^2*exp(-x), x,3);";
  } else {
    textarea.value += "\ndiff(x^2*exp(-x), x,3);";
  }
  textarea.focus(); // facultatif : replace le curseur dans la zone de texte
});