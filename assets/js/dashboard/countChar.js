let textArea = document.getElementById("textbox");
let characterCounter = document.getElementById("char_count");
const maxNumOfChars = 100;

const countCharacters = () => {
  let counter = textArea.value.split(/\s+\b/).length;
  characterCounter.textContent = counter + "/" + maxNumOfChars;

  if (counter > maxNumOfChars) {
    characterCounter.style.color = "red";
  } else if (counter > (maxNumOfChars - 20)) {
    characterCounter.style.color = "orange";
  } else {
    characterCounter.style.color = "black";
  }
}

textArea.addEventListener("input", countCharacters);
