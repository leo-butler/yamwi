document.getElementById("derive5").addEventListener("click", function() {
  var textarea = document.getElementById("max");
  // On ajoute toujours une nouvelle ligne si nécessaire, peu importe le contenu précédent
  if (textarea.value === "" || textarea.value.endsWith("\n")) {
    textarea.value += "risch(x^2*exp(x^2),x);";
  } else {
    textarea.value += "\nrisch(x^2*exp(x^2),x);";
  }
  textarea.focus(); // facultatif : replace le curseur dans la zone de texte
});