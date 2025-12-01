document.getElementById("simplifier7").addEventListener("click", function() {
  var textarea = document.getElementById("max");
  // On ajoute toujours une nouvelle ligne si nécessaire, peu importe le contenu précédent
  if (textarea.value === "" || textarea.value.endsWith("\n")) {
    textarea.value += "denom((x^2 - 1)/(x + 1));";
  } else {
    textarea.value += "\ndenom((x^2 - 1)/(x + 1));";
  }
  textarea.focus(); // facultatif : replace le curseur dans la zone de texte
});