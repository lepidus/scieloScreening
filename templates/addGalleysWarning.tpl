{**
 * plugins/generic/scieloScreening/templates/addGalleysWarning.tpl
 *
 * Template that adds the instructions for sending galleys to the galleys tab at the workflow.
 *}

<link rel="stylesheet" type="text/css" href="/plugins/generic/scieloScreening/styles/addGalleysWarning.css">

<div id="warningGalleys">
    <div id="warningGalleysHeader">
        <span>
            {translate key="plugins.generic.scieloScreening.info.manyPDFs.header"}
        </span>
    </div>
    <div id="warningGalleysBody">
        <ul>
            {translate key="plugins.generic.scieloScreening.info.manyPDFs.body"}
        </ul>
    </div>
</div>

<script>
    var galleysTab = document.getElementById("galleys");
    var warning = document.getElementById("warningGalleys");
    galleysTab.appendChild(warning);
</script>