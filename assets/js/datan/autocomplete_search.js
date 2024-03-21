document.getElementById("search").addEventListener("input", function() {
  let query = this.value;
  if (query.length > 0) {
    fetch(`search?q=${query}`)
      .then(response => response.json())
      .then(data => {
        let resultsDiv = document.getElementById("search-results");
        resultsDiv.innerHTML = "";
        data.forEach(result => {
          let div = document.createElement("div");
          div.textContent = result.text + " - " + result.source;
          resultsDiv.appendChild(div);
        });
      });
  } else {
    document.getElementById("search-results").innerHTML = "";
  }
});
