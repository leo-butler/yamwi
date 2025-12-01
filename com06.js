document.getElementById("simplifier2").addEventListener("click", function() {
  var textarea = document.getElementById("max");
  // On ajoute toujours une nouvelle ligne si nécessaire, peu importe le contenu précédent
  if (textarea.value === "" || textarea.value.endsWith("\n")) {
    textarea.value += "trigsimp(cos(x)^2+sin(x)^2);";
  } else {
    textarea.value += "\ntrigsimp(cos(x)^2+sin(x)^2);";
  }
  textarea.focus(); // facultatif : replace le curseur dans la zone de texte
});