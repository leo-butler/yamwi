document.getElementById("ana1").addEventListener("click", function() {
  var textarea = document.getElementById("max");
  // On ajoute toujours une nouvelle ligne si nécessaire, peu importe le contenu précédent
  if (textarea.value === "" || textarea.value.endsWith("\n")) {
    textarea.value += "limit(sin(x)/x, x, 0);";
  } else {
    textarea.value += "\nlimit(sin(x)/x, x, 0);";
  }
  textarea.focus(); // facultatif : replace le curseur dans la zone de texte
});