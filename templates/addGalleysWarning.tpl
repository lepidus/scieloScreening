{**
 * plugins/generic/scieloScreening/templates/addGalleysWarning.tpl
 *
 * Template that adds the instructions for sending galleys to the galleys tab at the workflow.
 *}

<link rel="stylesheet" type="text/css" href="/plugins/generic/scieloScreening/styles/addGalleysWarning.css">

<div id="warningGalleys">
    <div id="warningGalleysHeader">
        <span>
            {translate key="plugins.generic.scieloScreening.step4.manyPDFs.header"}
        </span>
    </div>
    <div id="warningGalleysBody">
        <ul>
            <li>{translate key="plugins.generic.scieloScreening.step4.manyPDFs.one"}</li>
            <li>{translate key="plugins.generic.scieloScreening.step4.manyPDFs.two"}</li>
            <li>{translate key="plugins.generic.scieloScreening.step4.manyPDFs.three"}</li>
        </ul>
    </div>
</div>

<script>
    var galleysTab = document.getElementById("galleys");
    var warning = document.getElementById("warningGalleys");
    galleysTab.appendChild(warning);
</script>