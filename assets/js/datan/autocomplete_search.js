function debounce(func, delay) {
  let debounceTimer;
  return function() {
    const context = this;
    const args = arguments;
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => func.apply(context, args), delay);
  };
}

function searchQuery() {
  let query = this.value;
  if (query.length > 0) {
    fetch(`search?q=${query}`)
      .then(response => response.json())
      .then(data => {
        let resultsDiv = document.getElementById("search-results");
        resultsDiv.innerHTML = "";
        data.forEach(result => {
          let div = document.createElement("div");
          div.textContent = result.text;
          div.className = result.source + ' result-source';
          div.appendChild(document.createElement("br"));
          let description = document.createElement("div");
          description.className = "description";
          description.textContent = result.description.replace(/<\/?[^>]+(>|$)/g, "");
          div.appendChild(description);
          resultsDiv.appendChild(div);
        });
      });
  } else {
    document.getElementById("search-results").innerHTML = "";
  }
}

document.getElementById("search").addEventListener("input", debounce(searchQuery, 250)); // 250 ms de délai


// Placeholder
let input = document.querySelector("input");
let slider = document.querySelector(".slider");
let slider_box = document.querySelector(".slider_box");
let placeholder = document.querySelector(".placeholder");

let list = ["un député", "une ville", "un vote"];
let word = 0;
let intervals = "";
text_animation();
setintervals();
slider_box.style.height = slider.clientHeight + "px";

input.onfocus = function(){
  placeholder.style.display = "none";
  clearInterval(intervals);
}
input.onblur = function(){
  if (input.value == "") {
    placeholder.style.display = "flex";
    word = 0;
    text_animation();
    setintervals();
  }
}

function setintervals(){
  intervals = setInterval(() => {
    text_animation();
  }, 2500);
}

function text_animation(){
  word++;
  slider.innerHTML = list[word - 1];
  slider.style.opacity = "1";
  slider.style.left = "0px";

  setTimeout(() => {
    slider.style.opacity = "0";
    slider.style.left = "-5px";
  }, 2000);
  if(list.length == word) {
    word = 0;
  }
}
