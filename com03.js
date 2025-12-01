document.getElementById("resoudre2").addEventListener("click", function() {
  var textarea = document.getElementById("max");
  // On ajoute toujours une nouvelle ligne si nécessaire, peu importe le contenu précédent
  if (textarea.value === "" || textarea.value.endsWith("\n")) {
    textarea.value += "find_root(sin(x), x, 3, 4);";
  } else {
    textarea.value += "\nfind_root(sin(x), x, 3, 4);";
  }
  textarea.focus(); // facultatif : replace le curseur dans la zone de texte
});
