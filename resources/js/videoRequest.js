const numModulesInput = document.getElementsByName('num_modules')[0];
let numModules = 0;
let expectedDuration = 0; // in minutes

// const updateCost = () => {
//     expectedCost = (animationRequired ? expectedDuration * 3000 : expectedDuration * 2400);
//     document.getElementsByName('expected_cost')[0].value = expectedCost ? expectedCost : "";
//     document.getElementsByName('expected_cost')[0].style.color = expectedCost ? "green" : "red";
//     document.getElementsByName("advance_cost")[0].value = expectedCost ? expectedCost * 30 / 100 : "";
// };

numModulesInput.addEventListener('input', function(e) {
    numModules = parseInt(e.target.value) || 0;
    expectedDuration = numModules * 3;
    if (numModules > 30) return e.target.classList.add('is-invalid');
    else e.target.classList.remove('is-invalid');
    document.getElementById("expected_duration_label").innerText = expectedDuration ? "Expected Duration: " : "";
    document.getElementsByName('expected_duration')[0].value = numModules ? expectedDuration + " min" : "";
});

if (numModulesInput.value) numModulesInput.dispatchEvent(new Event('input'));