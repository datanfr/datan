let textArea = document.getElementById("textbox");
let characterCounter = document.getElementById("char_count");
const maxNumOfChars = 500;

const countCharacters = () => {
  //let counter = textArea.value.split(/\s+\b/).length; // Was for words
  let counter = textArea.value.length;
  characterCounter.textContent = counter + "/" + maxNumOfChars;

  if (counter > maxNumOfChars) {
    characterCounter.style.color = "red";
  } else if (counter > (maxNumOfChars - 100)) {
    characterCounter.style.color = "orange";
  } else {
    characterCounter.style.color = "black";
  }
}

textArea.addEventListener("input", countCharacters);
