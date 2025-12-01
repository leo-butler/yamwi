document.getElementById("ana3").addEventListener("click", function() {
  var textarea = document.getElementById("max");
  // On ajoute toujours une nouvelle ligne si nécessaire, peu importe le contenu précédent
  if (textarea.value === "" || textarea.value.endsWith("\n")) {
    textarea.value += "product(k/(k^2+1),k,1,10);";
  } else {
    textarea.value += "\nproduct(k/(k^2+1),k,1,10);";
  }
  textarea.focus(); // facultatif : replace le curseur dans la zone de texte
});