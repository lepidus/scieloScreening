$(document).ready(function () {
    let panelSections = document.getElementsByClassName('panelSection');
    let licenseInput = document.querySelectorAll('input[name="licenseUrl"]')[0]; 

    for (let panelSection of panelSections) {
        if (panelSection.contains(licenseInput)) {
            panelSection.style.display = 'none';
            break;
        }
    }
});