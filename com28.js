document.getElementById("ana6").addEventListener("click", function() {
  var textarea = document.getElementById("max");
  // On ajoute toujours une nouvelle ligne si nécessaire, peu importe le contenu précédent
  if (textarea.value === "" || textarea.value.endsWith("\n")) {
    textarea.value += "gcd(2356,128);";
  } else {
    textarea.value += "\ngcd(2356,128);";
  }
  textarea.focus(); // facultatif : replace le curseur dans la zone de texte
});