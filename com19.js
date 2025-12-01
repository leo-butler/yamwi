document.getElementById("derive3").addEventListener("click", function() {
  var textarea = document.getElementById("max");
  // On ajoute toujours une nouvelle ligne si nécessaire, peu importe le contenu précédent
  if (textarea.value === "" || textarea.value.endsWith("\n")) {
    textarea.value += "integrate(x^2+log(x), x);";
  } else {
    textarea.value += "\nintegrate(x^2+log(x), x);";
  }
  textarea.focus(); // facultatif : replace le curseur dans la zone de texte
});