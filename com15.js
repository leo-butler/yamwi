document.getElementById("resoudre6").addEventListener("click", function() {
  var textarea = document.getElementById("max");
  // On ajoute toujours une nouvelle ligne si nécessaire, peu importe le contenu précédent
  if (textarea.value === "" || textarea.value.endsWith("\n")) {
    textarea.value += "algsys([x^2 + y = 5, x*y = 2], [x, y]);";
  } else {
    textarea.value += "\nalgsys([x^2 + y = 5, x*y = 2], [x, y]);";
  }
  textarea.focus(); // facultatif : replace le curseur dans la zone de texte
});