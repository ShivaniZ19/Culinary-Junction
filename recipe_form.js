let ingredientCount = 0;
let instructionCount = 0;

function addIngredient() {
  ingredientCount++;
  const container = document.getElementById("ingredientsContainer");
  const html = `<div id="ingredient_${ingredientCount}">
                    Ingredient: <input type="text" name="ingredient_name[]">
                    Quantity: <input type="text" name="quantity[]">
                    <button type="button" onclick="removeElement('ingredient_${ingredientCount}')">Remove</button>
                </div>`;
  container.insertAdjacentHTML("beforeend", html);
}

function addInstruction() {
  instructionCount++;
  const container = document.getElementById("instructionsContainer");
  const html = `<div id="instruction_${instructionCount}">
                    Step Number: <input type="number" name="step_number[]" min="1">
                    Instruction: <textarea name="instruction[]"></textarea>
                    <button type="button" onclick="removeElement('instruction_${instructionCount}')">Remove</button>
                </div>`;
  container.insertAdjacentHTML("beforeend", html);
}

function removeElement(id) {
  const element = document.getElementById(id);
  element.parentNode.removeChild(element);
}

function validateForm() {
  const stepNumbers = document.querySelectorAll('input[name="step_number[]"]');
  const steps = Array.from(stepNumbers).map((input) => input.value);
  const stepSet = new Set(steps);
  if (steps.length !== stepSet.size) {
    alert(
      "Duplicate step numbers found. Please ensure all step numbers are unique."
    );
    return false;
  }
  return true;
}

document.getElementById("picture").addEventListener("change", function () {
  var filePath = this.value.split("\\");
  var fileName = filePath[filePath.length - 1];
  document.getElementById("fileChosen").textContent = fileName
    ? fileName
    : "No file chosen";
});

document.getElementById("uploadBtn").addEventListener("click", function () {
  document.getElementById("picture").click();
});
