document.getElementById("resoudre1").addEventListener("click", function() {
  var textarea = document.getElementById("max");
  // On ajoute toujours une nouvelle ligne si nécessaire, peu importe le contenu précédent
  if (textarea.value === "" || textarea.value.endsWith("\n")) {
    textarea.value += "solve(x^2 - 4 = 0, x);";
  } else {
    textarea.value += "\nsolve(x^2 - 4 = 0, x);";
  }
  textarea.focus(); // facultatif : replace le curseur dans la zone de texte
});
