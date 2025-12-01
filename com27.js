document.getElementById("ana5").addEventListener("click", function() {
  var textarea = document.getElementById("max");
  // On ajoute toujours une nouvelle ligne si nécessaire, peu importe le contenu précédent
  if (textarea.value === "" || textarea.value.endsWith("\n")) {
    textarea.value += "taylor(log(1+x),x,0,5);";
  } else {
    textarea.value += "\ntaylor(log(1+x),x,0,5);";
  }
  textarea.focus(); // facultatif : replace le curseur dans la zone de texte
});