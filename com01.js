document.getElementById("expand-btn").addEventListener("click", function() {
  var textarea = document.getElementById("max");
  // On ajoute toujours une nouvelle ligne si nécessaire, peu importe le contenu précédent
  if (textarea.value === "" || textarea.value.endsWith("\n")) {
    textarea.value += "expand((x+1)^2);";
  } else {
    textarea.value += "\nexpand((x+1)^2);";
  }
  textarea.focus(); // facultatif : replace le curseur dans la zone de texte
});
