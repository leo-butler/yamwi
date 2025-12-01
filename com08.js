document.getElementById("simplifier4").addEventListener("click", function() {
  var textarea = document.getElementById("max");
  // On ajoute toujours une nouvelle ligne si nécessaire, peu importe le contenu précédent
  if (textarea.value === "" || textarea.value.endsWith("\n")) {
    textarea.value += "trigexpand(sin(x+y));";
  } else {
    textarea.value += "\ntrigexpand(sin(x+y));";
  }
  textarea.focus(); // facultatif : replace le curseur dans la zone de texte
});