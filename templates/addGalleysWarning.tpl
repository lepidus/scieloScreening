{**
 * plugins/generic/authorDOIScreening/templates/addGalleysWarning.tpl
 *
 * Template that adds the instructions for sending galleys to the galleys tab at the workflow.
 *}

 <link rel="stylesheet" type="text/css" href="/plugins/generic/authorDOIScreening/styles/addGalleysWarning.css">

<script>
    var galleysTab = document.getElementById("galleys");

    var warning = document.createElement('div');
    var header = document.createElement('div');
    var body = document.createElement('div');
    warning.setAttribute('id', 'warningGalleys');
    header.setAttribute('id', 'warningGalleysHeader');
    body.setAttribute('id', 'warningGalleysBody');

    var span = document.createElement('span');
    span.innerText = "{translate key="plugins.generic.authorDOIScreening.step4.manyPDFs.header"}";
    header.appendChild(span);

    var ul = document.createElement('ul');
    var li1 = document.createElement('li');
    var li2 = document.createElement('li');
    var li3 = document.createElement('li');
    li1.innerText = "{translate key="plugins.generic.authorDOIScreening.step4.manyPDFs.one"}";
    li2.innerText = "{translate key="plugins.generic.authorDOIScreening.step4.manyPDFs.two"}";
    li3.innerText = "{translate key="plugins.generic.authorDOIScreening.step4.manyPDFs.three"}";
    ul.appendChild(li1); ul.appendChild(li2); ul.appendChild(li3);
    body.appendChild(ul);

    warning.appendChild(header); warning.appendChild(body);
    galleysTab.appendChild(warning);
</script>